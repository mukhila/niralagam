<?php

namespace App\Support\Media;

use App\Models\Media;
use App\Models\Tenant;
use App\Models\User;
use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Stores an uploaded image for a brand:
 *   - the original is re-encoded (which strips EXIF data such as GPS location),
 *     rotated upright from its EXIF orientation, and capped at 2400px;
 *   - WebP variants are made for thumbnails (400px), cards (960px) and posts (1600px).
 * Animated GIFs are kept as they are so they still animate.
 */
class ImageProcessor
{
    public const MAX_EDGE = 2400;
    public const SIZES    = ['thumb' => 400, 'medium' => 960, 'large' => 1600];

    public function store(UploadedFile $file, Tenant $tenant, ?User $user): Media
    {
        $mime = $file->getMimeType();
        $src  = $this->load($file->getRealPath(), $mime);
        $src  = $this->orient($src, $file->getRealPath(), $mime);
        [$w, $h] = [imagesx($src), imagesy($src)];

        $dir  = "tenants/{$tenant->id}/media/".now()->format('Y/m');
        $name = Str::uuid()->toString();
        $disk = Storage::disk('public');

        if ($mime === 'image/gif') {
            $path = "{$dir}/{$name}.gif";
            $disk->put($path, file_get_contents($file->getRealPath()));
        } else {
            [$main, $w, $h] = $this->fit($src, self::MAX_EDGE);
            $ext  = ['image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? 'jpg';
            $path = "{$dir}/{$name}.{$ext}";
            $disk->put($path, $this->encode($main, $ext));
            $mime = $ext === 'jpg' ? 'image/jpeg' : $mime;
        }

        $variants = [];
        foreach (self::SIZES as $size => $edge) {
            if (max(imagesx($src), imagesy($src)) <= $edge) {
                continue; // smaller than this size: the original serves it
            }
            [$img, $vw, $vh] = $this->fit($src, $edge);
            $vpath = "{$dir}/{$name}-{$size}.webp";
            $disk->put($vpath, $this->encode($img, 'webp'));
            $variants[$size] = ['path' => $vpath, 'width' => $vw, 'height' => $vh];
        }

        return Media::create([
            'tenant_id'     => $tenant->id,
            'user_id'       => $user?->id,
            'path'          => $path,
            'original_name' => Str::limit($file->getClientOriginalName(), 250, ''),
            'mime'          => $mime,
            'size'          => $disk->size($path),
            'width'         => $w,
            'height'        => $h,
            'variants'      => $variants,
        ]);
    }

    private function load(string $path, string $mime): GdImage
    {
        $img = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($path),
            'image/png'  => @imagecreatefrompng($path),
            'image/webp' => @imagecreatefromwebp($path),
            'image/gif'  => @imagecreatefromgif($path),
            default      => false,
        };

        if (! $img) {
            throw new RuntimeException('This image could not be read.');
        }
        imagealphablending($img, false);
        imagesavealpha($img, true);

        return $img;
    }

    /** Phone photos are often stored sideways with an EXIF "rotate me" flag. */
    private function orient(GdImage $img, string $path, string $mime): GdImage
    {
        if ($mime !== 'image/jpeg' || ! function_exists('exif_read_data')) {
            return $img;
        }
        $o = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        return match ($o) {
            3 => imagerotate($img, 180, 0),
            6 => imagerotate($img, -90, 0),
            8 => imagerotate($img, 90, 0),
            default => $img,
        };
    }

    /** Scales down (never up) so the longest edge is at most $edge. */
    private function fit(GdImage $src, int $edge): array
    {
        [$w, $h] = [imagesx($src), imagesy($src)];
        $scale = min(1, $edge / max($w, $h));
        if ($scale === 1) {
            return [$src, $w, $h];
        }

        [$nw, $nh] = [max(1, (int) round($w * $scale)), max(1, (int) round($h * $scale))];
        $dst = imagecreatetruecolor($nw, $nh);
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);

        return [$dst, $nw, $nh];
    }

    private function encode(GdImage $img, string $ext): string
    {
        ob_start();
        match ($ext) {
            'png'  => imagepng($img, null, 7),
            'webp' => imagewebp($img, null, 82),
            default => imagejpeg($img, null, 85),
        };

        return (string) ob_get_clean();
    }
}
