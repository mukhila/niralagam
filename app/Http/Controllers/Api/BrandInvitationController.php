<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Mail\TenantInvitationMail;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/** Invitations to the current brand, managed by its admins (or a super admin). */
class BrandInvitationController extends Controller
{
    // Resolved per call: controller instances can outlive a request (tests, Octane).
    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    public function index(): JsonResponse
    {
        $invitations = TenantInvitation::with('inviter:id,name')
            ->where('tenant_id', $this->tenant()->id)
            ->pending()
            ->latest()
            ->get()
            ->map(fn ($i) => [
                ...$i->only('id', 'email', 'role', 'created_at', 'expires_at'),
                'invited_by' => $i->inviter?->name,
                'expired'    => $i->isExpired(),
            ]);

        return response()->json($invitations);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|max:255',
            'role'  => ['required', Rule::in(config('tenancy.roles'))],
        ]);
        $email = Str::lower($data['email']);

        if ($this->tenant()->users()->whereRaw('LOWER(users.email) = ?', [$email])->exists()) {
            return response()->json(['message' => 'That person is already a member.'], 422);
        }

        if (TenantInvitation::where('tenant_id', $this->tenant()->id)->where('email', $email)->pending()->exists()) {
            return response()->json(['message' => 'That email already has a pending invitation. Resend it instead.'], 422);
        }

        $invitation = new TenantInvitation([
            'tenant_id'  => $this->tenant()->id,
            'email'      => $email,
            'role'       => $data['role'],
            'invited_by' => $request->user()->id,
        ]);
        $token = $invitation->refreshToken();
        $invitation->save();
        Audit::record('invitation.sent', $invitation, ['role' => [null, $invitation->role]]);

        return response()->json($this->deliver($invitation, $token), 201);
    }

    public function resend(TenantInvitation $invitation): JsonResponse
    {
        $this->ensureOwnPending($invitation);

        $token = $invitation->refreshToken(); // old link stops working
        $invitation->save();
        Audit::record('invitation.resent', $invitation);

        return response()->json($this->deliver($invitation, $token));
    }

    public function destroy(TenantInvitation $invitation): JsonResponse
    {
        $this->ensureOwnPending($invitation);
        Audit::record('invitation.revoked', $invitation, ['role' => [$invitation->role, null]]);
        $invitation->delete();

        return response()->json(['message' => 'Invitation revoked.']);
    }

    /**
     * Emails the link. The link is also returned once (it can't be recovered later)
     * so the admin can share it by hand if mail delivery fails.
     */
    private function deliver(TenantInvitation $invitation, string $token): array
    {
        $url = TenantInvitation::url($token);

        try {
            Mail::to($invitation->email)->send(new TenantInvitationMail($invitation->load('tenant', 'inviter'), $url));
            $sent = true;
        } catch (Throwable $e) {
            report($e);
            $sent = false;
        }

        return [
            ...$invitation->only('id', 'email', 'role', 'created_at', 'expires_at'),
            'invited_by' => $invitation->inviter?->name,
            'expired'    => false,
            'invite_url' => $url,
            'email_sent' => $sent,
        ];
    }

    private function ensureOwnPending(TenantInvitation $invitation): void
    {
        if ($invitation->tenant_id !== $this->tenant()->id || $invitation->accepted_at) {
            abort(404);
        }
    }
}
