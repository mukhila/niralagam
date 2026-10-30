<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $users = User::query()
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->role && $request->role !== 'all', fn ($q, $r) => $q->where('role', $r))
            ->when($request->status && $request->status !== 'all', fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate((int) ($request->per_page ?? 15));

        return response()->json($users);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'role'   => 'sometimes|in:user,super_admin',
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        // Stop a super admin from demoting or suspending themselves.
        if ($user->is($request->user())) {
            return response()->json(['message' => 'You cannot change your own role or status.'], 422);
        }

        $user->forceFill($data)->save();
        Audit::record('user.updated', $user, Audit::diff($user, ['role', 'status']), tenant: null);

        // Revoke API tokens so a demoted or suspended user loses access right away.
        if (($data['role'] ?? null) === 'user' || ($data['status'] ?? 'active') !== 'active') {
            $user->tokens()->delete();
        }
        return response()->json($user);
    }
}
