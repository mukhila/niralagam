<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\Audit\Audit;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tenants = Tenant::withCount(['users', 'posts'])
            ->when($request->search, fn ($q, $s) =>
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('slug', 'like', "%{$s}%")
                  ->orWhere('domain', 'like', "%{$s}%")
            )
            ->when($request->status && $request->status !== 'all', fn ($q, $s) => $q->where('status', $s))
            ->orderByDesc('created_at')
            ->paginate(min((int) ($request->per_page ?? 15), 100));

        return response()->json($tenants);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'nullable|string|max:63|alpha_dash|unique:tenants,slug',
            'domain' => 'nullable|string|max:255|unique:tenants,domain',
            'status' => 'nullable|in:active,inactive,suspended',
        ]);

        $data['slug']       = Str::lower($data['slug'] ?? Str::slug($data['name']));
        $data['domain']     = isset($data['domain']) ? Str::lower($data['domain']) : null;
        $data['status']     = $data['status'] ?? 'active';
        $data['created_by'] = $request->user()?->id;

        if (Tenant::where('slug', $data['slug'])->exists()) {
            $data['slug'] .= '-' . Str::lower(Str::random(4));
        }

        $tenant = Tenant::create($data);
        Audit::record('tenant.created', $tenant, ['slug' => [null, $tenant->slug]], tenant: null);

        return response()->json($tenant->loadCount(['users']), 201);
    }

    public function show(Tenant $tenant): JsonResponse
    {
        $tenant->loadCount(['users', 'posts']);
        $tenant->load('creator:id,name,email');

        $members = $tenant->users()
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->take(20)
            ->get()
            ->map(fn ($u) => [...$u->only('id', 'name', 'email', 'created_at'), 'tenant_role' => $u->pivot->role]);

        $stats = [
            'users_count' => $tenant->users_count,
            'posts_count' => $tenant->posts_count,
        ];

        return response()->json(compact('tenant', 'members', 'stats'));
    }

    public function update(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'name'   => 'sometimes|string|max:100',
            'domain' => ['sometimes', 'nullable', 'string', 'max:255', Rule::unique('tenants', 'domain')->ignore($tenant->id)],
            'status' => 'sometimes|in:active,inactive,suspended',
        ]);

        if ($tenant->isDefault() && isset($data['status']) && $data['status'] !== 'active') {
            return response()->json(['message' => 'The default tenant must stay active.'], 422);
        }

        if (array_key_exists('domain', $data) && $data['domain']) {
            $data['domain'] = Str::lower($data['domain']);
        }

        $tenant->update($data);
        Audit::record('tenant.updated', $tenant, Audit::diff($tenant, ['name', 'domain', 'status']), tenant: $tenant);
        return response()->json($tenant->loadCount(['users']));
    }

    public function destroy(Tenant $tenant): JsonResponse
    {
        if ($tenant->isDefault()) {
            return response()->json(['message' => 'The default tenant cannot be deleted.'], 422);
        }

        if ($tenant->hasContent()) {
            return response()->json(['message' => 'This tenant still has posts, categories or tags. Suspend it instead, or remove its content first.'], 422);
        }

        // Media rows cascade with the tenant; their files have to be removed explicitly.
        Storage::disk('public')->deleteDirectory("tenants/{$tenant->id}");

        Audit::record('tenant.deleted', $tenant, ['slug' => [$tenant->slug, null]], tenant: null);
        $tenant->delete();
        return response()->json(['message' => 'Tenant deleted.']);
    }

    // ── Members ─────────────────────────────────────────────

    public function addMember(Request $request, Tenant $tenant): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email|exists:users,email',
            'role'  => ['required', Rule::in(config('tenancy.roles'))],
        ]);

        $user = User::where('email', $data['email'])->firstOrFail();
        $tenant->users()->syncWithoutDetaching([$user->id => ['role' => $data['role']]]);
        Audit::record('member.added', $user, ['role' => [null, $data['role']]], tenant: $tenant);

        return response()->json(['message' => "{$user->name} added as {$data['role']}."], 201);
    }

    public function updateMember(Request $request, Tenant $tenant, User $user): JsonResponse
    {
        $data = $request->validate(['role' => ['required', Rule::in(config('tenancy.roles'))]]);

        if (! $tenant->users()->whereKey($user->id)->exists()) {
            abort(404, 'User is not a member of this tenant.');
        }

        $old = $tenant->roleFor($user);
        $tenant->users()->updateExistingPivot($user->id, ['role' => $data['role']]);
        Audit::record('member.role_changed', $user, ['role' => [$old, $data['role']]], tenant: $tenant);

        return response()->json(['message' => 'Role updated.']);
    }

    public function removeMember(Tenant $tenant, User $user): JsonResponse
    {
        Audit::record('member.removed', $user, ['role' => [$tenant->roleFor($user), null]], tenant: $tenant);
        $tenant->users()->detach($user->id);
        return response()->json(['message' => 'Member removed.']);
    }
}
