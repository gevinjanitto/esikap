<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $data = $r->validate(['login' => 'required|string|max:120', 'password' => 'required|string']);
        $login = Str::lower(trim($data['login']));
        $key = 'login:'.$r->ip().':'.$login;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $min = ceil(RateLimiter::availableIn($key) / 60);

            return back()->withErrors(['login' => "Terlalu banyak percobaan. Coba lagi dalam {$min} menit."])->onlyInput('login');
        }

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        if (Auth::attempt([$field => $login, 'password' => $data['password'], 'is_active' => true], $r->boolean('remember'))) {
            RateLimiter::clear($key);
            $r->session()->regenerate();
            AuditLog::record('login', Auth::user(), null, null);

            return redirect()->intended(route('dashboard'));
        }

        RateLimiter::hit($key, 900);

        return back()->withErrors(['login' => 'Email/username atau kata sandi tidak sesuai.'])->onlyInput('login');
    }

    public function logout(Request $r)
    {
        AuditLog::record('logout', Auth::user(), null, null);
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login');
    }
}
