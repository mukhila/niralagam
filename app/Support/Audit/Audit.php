<?php

namespace App\Support\Audit;

use App\Models\AuditLog;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Throwable;

/**
 * Records who did what. Called explicitly from controllers so each entry says
 * what happened in editorial terms ("post.approved"), not just "row updated".
 *
 *   Audit::record('post.published', $post);
 *   Audit::record('member.role_changed', $user, ['role' => ['author', 'editor']]);
 *   Audit::record('tenant.updated', $tenant, Audit::diff($tenant, ['name', 'status']), tenant: null);
 *
 * Auditing never breaks the action it records: failures are reported and swallowed.
 */
class Audit
{
    /** Fields whose values are too large or sensitive to store; only "changed" is noted. */
    private const REDACT = ['content', 'search_text', 'password', 'token', 'token_hash', 'otp'];

    /**
     * @param  Tenant|null|false  $tenant  false (default) = the current tenant; null = platform event
     */
    public static function record(string $action, ?Model $subject = null, array $changes = [], Tenant|null|false $tenant = false, ?User $actor = null): void
    {
        try {
            $actor ??= request()?->user('sanctum') ?? request()?->user();
            $tenantId = $tenant === false ? app(CurrentTenant::class)->id() : $tenant?->id;

            AuditLog::create([
                'tenant_id'     => $tenantId,
                'user_id'       => $actor?->id,
                'actor_name'    => $actor?->name,
                'action'        => $action,
                'subject_type'  => $subject ? Str::snake(class_basename($subject)) : null,
                'subject_id'    => $subject?->getKey(),
                'subject_label' => $subject ? Str::limit(self::label($subject), 250, '…') : null,
                'changes'       => $changes ?: null,
                'ip_address'    => request()?->ip(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /**
     * { field: [old, new] } for the given fields that changed in the model's last save
     * (call right after save()). Large or sensitive fields are recorded as changed only.
     */
    public static function diff(Model $model, array $fields): array
    {
        $before = $model->getPrevious();
        $out    = [];

        foreach ($fields as $field) {
            if (! $model->wasChanged($field)) {
                continue;
            }
            $out[$field] = in_array($field, self::REDACT, true)
                ? ['(changed)', '(changed)']
                : [self::scalar($before[$field] ?? null), self::scalar($model->getAttribute($field))];
        }

        return $out;
    }

    private static function label(Model $subject): string
    {
        return (string) ($subject->getAttribute('title')
            ?? $subject->getAttribute('name')
            ?? $subject->getAttribute('email')
            ?? $subject->getAttribute('original_name')
            ?? '#'.$subject->getKey());
    }

    private static function scalar(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_ATOM);
        }
        if (is_array($value)) {
            return $value;
        }

        return is_string($value) ? Str::limit($value, 300, '…') : $value;
    }
}
