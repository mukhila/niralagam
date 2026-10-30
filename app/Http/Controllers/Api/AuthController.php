<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Tenancy\CurrentTenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'                  => 'required|string|max:100',
            'email'                 => 'required|email|unique:users',
            'password'              => 'required|min:8|confirmed',
        ]);

        $user  = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role'     => 'user',
        ]);

        // Readers who sign up on a brand's site join that brand as subscribers.
        app(CurrentTenant::class)->get()?->users()->attach($user->id, ['role' => 'subscriber']);

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        // Check credentials without starting a session: Auth::attempt() would set a
        // session cookie, and Sanctum prefers that cookie over the bearer token, so a
        // later sign-in as someone else in the same browser would still act as this user.
        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->password || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['This account has been '.$user->status.'.'],
            ]);
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['user' => $user, 'token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /** Brands the user can open in the admin panel, with their role in each. */
    public function brands(Request $request): JsonResponse
    {
        $user = $request->user();
        $cols = ['tenants.id', 'tenants.name', 'tenants.slug', 'tenants.logo', 'tenants.status'];

        $brands = $user->isSuperAdmin()
            ? Tenant::orderBy('name')->get($cols)->map(fn ($t) => [...$t->toArray(), 'role' => 'super_admin'])
            : $user->tenants()->orderBy('name')->get($cols)
                ->map(fn ($t) => [...$t->only('id', 'name', 'slug', 'logo', 'status'), 'role' => $t->pivot->role]);

        return response()->json($brands->values());
    }
}
