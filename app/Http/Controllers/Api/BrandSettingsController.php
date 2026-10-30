<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Tenant;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The current brand's public look: name, logo, copy, colours, fonts, layout.
 * Theme values live in tenants.settings['theme']; other settings keys are untouched.
 */
class BrandSettingsController extends Controller
{
    // Resolved per call: controller instances can outlive a request (tests, Octane).
    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    /** Public: what the site needs to render this brand. */
    public function show(): JsonResponse
    {
        return response()->json($this->payload());
    }

    /** Brand admins only (route middleware). */
    public function update(Request $request): JsonResponse
    {
        $hex  = ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'];
        $font = ['sometimes', Rule::in(config('tenancy.fonts'))];

        $data = $request->validate([
            'name'          => 'sometimes|required|string|max:100',
            'logo'          => 'sometimes|nullable|url:http,https|max:500',
            'tagline'       => 'sometimes|nullable|string|max:80',
            'about'         => 'sometimes|nullable|string|max:300',
            'hero_title'    => 'sometimes|nullable|string|max:80',
            'hero_subtitle' => 'sometimes|nullable|string|max:160',
            'primary_color' => ['sometimes', ...$hex],
            'accent_color'  => ['sometimes', ...$hex],
            'heading_font'  => $font,
            'body_font'     => $font,
            'home_layout'   => ['sometimes', Rule::in(config('tenancy.layouts'))],
            'social'        => 'sometimes|array:'.implode(',', config('tenancy.social')),
            'social.*'      => 'nullable|url:http,https|max:255',
            // Languages the brand publishes in (config/languages.php codes).
            'languages'           => 'sometimes|array',
            'languages.default'   => ['required_with:languages', Rule::in(array_keys(config('languages')))],
            'languages.enabled'   => 'required_with:languages|array|min:1|max:50',
            'languages.enabled.*' => [Rule::in(array_keys(config('languages')))],
        ]);

        $theme = array_diff_key($data, array_flip(['name', 'logo', 'languages']));
        if (isset($theme['social'])) {
            $theme['social'] = array_filter($theme['social']); // drop cleared links
        }

        $tenant            = $this->tenant();
        $settings          = $tenant->settings ?? [];
        $settings['theme'] = array_merge($settings['theme'] ?? [], $theme);
        if (isset($data['languages'])) {
            $settings['languages'] = [
                'default' => $data['languages']['default'],
                'enabled' => array_values(array_unique([$data['languages']['default'], ...$data['languages']['enabled']])),
            ];
        }

        $tenant->fill(array_intersect_key($data, array_flip(['name', 'logo'])));
        $tenant->settings = $settings;
        $tenant->save();
        Audit::record('brand.settings_updated', $tenant, Audit::diff($tenant, ['name', 'logo']) + ['fields' => [null, array_keys($data)]]);

        return response()->json($this->payload());
    }

    private function payload(): array
    {
        $theme = array_merge(config('tenancy.theme_defaults'), $this->tenant()->settings['theme'] ?? []);

        $languages = $this->tenant()->languages();

        return [
            ...$this->tenant()->only('id', 'name', 'slug', 'logo', 'domain'),
            'theme'     => $theme,
            // Enabled languages with their names and text direction, default first.
            'languages' => [
                'default' => $languages['default'],
                'enabled' => collect($languages['enabled'])
                    ->filter(fn ($c) => isset(config('languages')[$c]))
                    ->map(fn ($c) => ['code' => $c, ...config('languages')[$c]])->values(),
            ],
            'options'   => [
                'fonts'     => config('tenancy.fonts'),
                'layouts'   => config('tenancy.layouts'),
                'social'    => config('tenancy.social'),
                'languages' => collect(config('languages'))->map(fn ($l, $c) => ['code' => $c, ...$l])->values(),
            ],
        ];
    }
}
