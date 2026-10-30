<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(): View
    {
        $tenants = Tenant::with('creator')
            ->withCount('users')
            ->latest()
            ->paginate(15);

        $stats = [
            'total'    => Tenant::count(),
            'active'   => Tenant::where('status', 'active')->count(),
            'inactive' => Tenant::where('status', '!=', 'active')->count(),
            'users'    => User::where('role', 'user')->count(),
        ];

        return view('super-admin.tenants.index', compact('tenants', 'stats'));
    }

    public function create(): View
    {
        return view('super-admin.tenants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'nullable|string|max:50|alpha_dash|unique:tenants,slug',
            'domain' => 'nullable|url|unique:tenants,domain',
            'status' => 'in:active,inactive',
            // Tenant admin
            'admin_name'  => 'required|string|max:100',
            'admin_email' => 'required|email|unique:users,email',
        ]);

        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);
        $data['created_by'] = auth()->id();

        $tenant = Tenant::create($data);

        // Create tenant admin user
        $admin = User::create([
            'name'  => $data['admin_name'],
            'email' => $data['admin_email'],
            'role'  => 'user',
        ]);

        $tenant->users()->attach($admin->id, ['role' => 'admin']);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', "Tenant \"{$tenant->name}\" created. Admin: {$admin->email}");
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load(['creator', 'users']);

        return view('super-admin.tenants.show', compact('tenant'));
    }

    public function edit(Tenant $tenant): View
    {
        return view('super-admin.tenants.edit', compact('tenant'));
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:100',
            'slug'   => 'required|string|max:50|alpha_dash|unique:tenants,slug,'.$tenant->id,
            'domain' => 'nullable|url|unique:tenants,domain,'.$tenant->id,
            'status' => 'in:active,inactive,suspended',
        ]);

        $tenant->update($data);

        return redirect()->route('super-admin.tenants.index')
            ->with('success', "Tenant \"{$tenant->name}\" updated.");
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        $tenant->delete();

        return redirect()->route('super-admin.tenants.index')
            ->with('success', 'Tenant deleted.');
    }
}
