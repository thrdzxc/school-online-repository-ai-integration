<?php
namespace App\Http\Controllers;

use App\Support\Audit;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin(Request $request, ?string $role = null)
    {
        if (auth()->check()) return redirect()->route('dashboard');
        $role = $request->attributes->get('workspace') ?: ($role ?: $request->query('role'));
        if ($role && !in_array($role, ['student','adviser','admin'], true)) $role = null;
        return view('auth.login', compact('role'));
    }

    public function login(Request $request, RateLimiter $limiter)
    {
        $validated = $request->validate([
            'email' => ['required','email',new \App\Rules\EmailAddress(),'max:255'],
            'password' => ['required','string','max:255'],
            'portal' => ['nullable','in:student,adviser,admin'],
        ], [
            'email.required' => 'Enter your email address.',
            'email.email' => 'Enter a valid email address.',
            'password.required' => 'Enter your password.',
        ]);

        $email = Str::lower(trim($validated['email']));
        $throttleKey = $email.'|'.$request->ip();

        if ($limiter->tooManyAttempts($throttleKey, 5)) {
            $seconds = max(1, $limiter->availableIn($throttleKey));
            return back()->withErrors(['email' => 'Too many failed sign-in attempts. Try again in '.$seconds.' seconds.'])->onlyInput('email');
        }

        $remember = $request->boolean('remember');
        if (!Auth::attempt(['email' => $email, 'password' => $validated['password']], $remember)) {
            $limiter->hit($throttleKey, 60);
            return back()->withErrors(['email' => 'The email or password is incorrect.'])->onlyInput('email');
        }

        $limiter->clear($throttleKey);
        $request->session()->regenerate();
        $user = $request->user();

        if (!$user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'Your account is inactive. Contact the administrator.']);
        }

        $portal = $request->attributes->get('workspace') ?: ($validated['portal'] ?? null);
        if ($portal && $portal !== $user->role) {
            $actualRole = $user->role;
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return back()->withErrors(['email' => 'This account belongs to the '.ucfirst($actualRole).' portal. Please use the correct portal.'])->onlyInput('email');
        }

        Audit::log($user, 'login', 'user', $user->id, ['portal' => $user->role]);
        return redirect()->route($user->role.'.dashboard');
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) Audit::log($user, 'logout');
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')->with('success', 'You have been signed out successfully.');
    }
}
