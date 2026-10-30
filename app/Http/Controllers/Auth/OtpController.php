<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class OtpController extends Controller
{
    public function showRequestForm(): View
    {
        return view('auth.login');
    }

    public function sendOtp(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $user = User::firstOrCreate(
            ['email' => $request->email],
            ['name' => explode('@', $request->email)[0]]
        );

        $user->update([
            'otp'            => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($otp));

        return redirect()->route('otp.verify.form')
            ->with('otp_email', $request->email);
    }

    public function showVerifyForm(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('otp_email') ?? $request->query('email');

        if (! $email) {
            return redirect()->route('login');
        }

        return view('auth.otp-verify', ['email' => $email]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('otp', $request->otp)
            ->where('otp_expires_at', '>', now())
            ->first();

        if (! $user) {
            return back()
                ->withInput()
                ->withErrors(['otp' => 'Invalid or expired code. Please try again.']);
        }

        $user->update([
            'otp'            => null,
            'otp_expires_at' => null,
            'last_login_at'  => now(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->intended($this->redirectAfterLogin($user));
    }

    private function redirectAfterLogin(User $user): string
    {
        return $user->isSuperAdmin()
            ? route('super-admin.tenants.index')
            : route('admin.dashboard');
    }
}
