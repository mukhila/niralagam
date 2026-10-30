<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Check SMTP settings: php artisan mail:test you@example.com
Artisan::command('mail:test {to : Address to send the test email to}', function (string $to) {
    $mailer = config('mail.default');
    $this->line("Mailer: {$mailer}".($mailer === 'smtp' ? ' ('.config('mail.mailers.smtp.host').':'.config('mail.mailers.smtp.port').')' : ''));
    $this->line('From:   '.config('mail.from.address'));

    try {
        Illuminate\Support\Facades\Mail::raw(
            'This is a test email from '.config('app.name').'. If you can read this, email sending works.',
            fn ($m) => $m->to($to)->subject(config('app.name').' test email'),
        );
    } catch (Throwable $e) {
        $this->error('Sending failed: '.$e->getMessage());
        return 1;
    }

    $this->info($mailer === 'log'
        ? 'Written to storage/logs/laravel.log (MAIL_MAILER=log does not send real email).'
        : "Sent to {$to}. Check the inbox (and spam folder).");
})->purpose('Send a test email to check the mail settings');

// Flip scheduled posts whose time has come to "published". The site already shows
// them on time (Post::scopeLive), so this keeps the stored status honest.
// Needs the scheduler: `php artisan schedule:work` locally, or a cron/Task Scheduler
// entry running `php artisan schedule:run` every minute in production.
Artisan::command('posts:publish-scheduled', function () {
    // No current tenant in the console, so this covers every brand. Saved one by one
    // (not a bulk update) so the post's "saved" event emails its followers.
    $count = 0;
    App\Models\Post::where('status', 'scheduled')
        ->where('published_at', '<=', now())
        ->chunkById(100, function ($posts) use (&$count) {
            foreach ($posts as $post) {
                $post->status = 'published';
                $post->save();
                $count++;
            }
        });

    $this->info("Published {$count} scheduled post(s).");
})->purpose('Publish scheduled posts whose time has come');

Illuminate\Support\Facades\Schedule::command('posts:publish-scheduled')->everyMinute()->withoutOverlapping();

// Weekly newsletter digest for every brand with it switched on (skips brands with no new posts).
Artisan::command('newsletter:send-digest {--brand= : Only this brand slug}', function (App\Support\Newsletter\Digest $digest) {
    $tenants = App\Models\Tenant::where('status', 'active')
        ->when($this->option('brand'), fn ($q, $slug) => $q->where('slug', $slug))
        ->get();

    foreach ($tenants as $tenant) {
        if (! App\Support\Newsletter\Digest::settings($tenant)['digest_enabled']) {
            continue;
        }
        $sent = $digest->send($tenant);
        $this->line("{$tenant->slug}: ".($sent ? "sent to {$sent}" : 'nothing new'));
    }
})->purpose('Email each brand\'s subscribers the posts published since the last digest');

Illuminate\Support\Facades\Schedule::command('newsletter:send-digest')->weeklyOn(1, '08:00')->withoutOverlapping();

// Audit log retention: keep a year (AUDIT_RETENTION_DAYS), then prune daily.
Artisan::command('audit:prune', function () {
    $days  = (int) env('AUDIT_RETENTION_DAYS', 365);
    $count = App\Models\AuditLog::where('created_at', '<', now()->subDays($days))->delete();
    $this->info("Pruned {$count} audit entries older than {$days} days.");
})->purpose('Delete audit log entries past the retention period');

Illuminate\Support\Facades\Schedule::command('audit:prune')->dailyAt('03:00');
