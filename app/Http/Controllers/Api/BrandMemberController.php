<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Members of the current brand, managed by that brand's admins (or a super admin).
 * New members join through BrandInvitationController.
 * Guards keep every brand with at least one admin.
 */
class BrandMemberController extends Controller
{
    // Resolved per call: controller instances can outlive a request (tests, Octane).
    private function tenant(): Tenant
    {
        return app(CurrentTenant::class)->get();
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->tenant()->users();

        // wherePivot lives on the relation, so it can't go inside when().
        if ($request->role && $request->role !== 'all') {
            $query->wherePivot('role', $request->role);
        }

        $members = $query
            ->select('users.id', 'users.name', 'users.email', 'users.avatar')
            ->when($request->search, fn ($q, $s) => $q->where(fn ($q) =>
                $q->where('users.name', 'like', "%{$s}%")->orWhere('users.email', 'like', "%{$s}%")))
            ->orderBy('users.name')
            ->paginate(min((int) ($request->per_page ?? 20), 100))
            ->through(fn ($u) => [
                ...$u->only('id', 'name', 'email', 'avatar'),
                'role'      => $u->pivot->role,
                'joined_at' => $u->pivot->created_at,
            ]);

        return response()->json($members);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(config('tenancy.roles'))]]);

        $this->guardChange($request, $user, demoting: $data['role'] !== 'admin');
        $old = $this->tenant()->roleFor($user);
        $this->tenant()->users()->updateExistingPivot($user->id, ['role' => $data['role']]);
        Audit::record('member.role_changed', $user, ['role' => [$old, $data['role']]]);

        return response()->json(['message' => 'Role updated.']);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        $this->guardChange($request, $user, demoting: true);
        Audit::record('member.removed', $user, ['role' => [$this->tenant()->roleFor($user), null]]);
        $this->tenant()->users()->detach($user->id);

        return response()->json(['message' => 'Member removed.']);
    }

    private function guardChange(Request $request, User $user, bool $demoting): void
    {
        $role = $this->tenant()->roleFor($user);

        if ($role === null) {
            abort(404, 'User is not a member of this brand.');
        }

        // A brand admin changing their own membership could lock themselves out.
        if ($user->is($request->user()) && ! $request->user()->isSuperAdmin()) {
            abort(422, 'You cannot change your own role. Ask another admin.');
        }

        if ($demoting && $role === 'admin' && $this->tenant()->admins()->count() <= 1) {
            abort(422, 'A brand needs at least one admin.');
        }
    }
}
