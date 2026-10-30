<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** The invitee's side: look up an invitation by its link token, then accept it. */
class InvitationController extends Controller
{
    public function show(string $token): JsonResponse
    {
        $invitation = $this->find($token);

        return response()->json([
            'brand'       => $invitation->tenant->only('name', 'slug', 'logo'),
            'email'       => $invitation->email,
            'role'        => $invitation->role,
            'invited_by'  => $invitation->inviter?->name,
            'expires_at'  => $invitation->expires_at,
            'has_account' => User::whereRaw('LOWER(email) = ?', [$invitation->email])->exists(),
        ]);
    }

    public function accept(Request $request, string $token): JsonResponse
    {
        $invitation = $this->find($token);
        $user       = $request->user();

        // A forwarded link must not let someone else join.
        if (Str::lower($user->email) !== $invitation->email) {
            return response()->json([
                'message' => "This invitation was sent to {$invitation->email}. Sign in with that email to accept it.",
            ], 403);
        }

        $tenant = $invitation->tenant;
        $role   = $this->higherRole($tenant->roleFor($user), $invitation->role);

        DB::transaction(function () use ($tenant, $user, $role, $invitation) {
            $tenant->users()->syncWithoutDetaching([$user->id => ['role' => $role]]);
            $invitation->forceFill(['accepted_at' => now(), 'accepted_by' => $user->id])->save();
        });
        Audit::record('invitation.accepted', $user, ['role' => [null, $role]], tenant: $tenant, actor: $user);

        return response()->json([
            'message' => "You've joined {$tenant->name} as {$role}.",
            'brand'   => $tenant->only('name', 'slug'),
            'role'    => $role,
        ]);
    }

    private function find(string $token): TenantInvitation
    {
        $invitation = TenantInvitation::with('tenant', 'inviter:id,name')->where('token_hash', hash('sha256', $token))->first();

        if (! $invitation) {
            abort(404, 'This invitation link is not valid. It may have been revoked or replaced by a newer one.');
        }
        if ($invitation->accepted_at) {
            abort(410, 'This invitation has already been used.');
        }
        if ($invitation->isExpired()) {
            abort(410, 'This invitation has expired. Ask the brand admin to resend it.');
        }

        return $invitation;
    }

    /** Accepting never downgrades an existing member (e.g. an admin invited as author). */
    private function higherRole(?string $current, string $invited): string
    {
        $rank = array_flip(config('tenancy.roles')); // lower index = more access

        return $current !== null && $rank[$current] < $rank[$invited] ? $current : $invited;
    }
}
