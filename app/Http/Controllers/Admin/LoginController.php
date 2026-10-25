<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Throttle: max 5 percobaan per menit per IP+email
        $throttleKey = strtolower($request->input('email')) . '|' . $request->ip();
        if (method_exists($this, 'hasTooManyLoginAttempts')) {
            // fallback manual di bawah
        }
        $key = 'login_attempts_' . md5($throttleKey);
        $attempts = cache()->get($key, 0);
        if ($attempts >= 5) {
            return back()->withErrors(['email' => 'Terlalu banyak percobaan. Coba lagi 1 menit.'])->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            cache()->forget($key);
            $request->session()->forget('current_branch_id');
            return redirect()->intended(route('admin.dashboard'));
        }

        cache()->put($key, $attempts + 1, 60);
        return back()->withErrors(['email' => 'Email atau password salah.'])->withInput();
    }

    public function magicLogin(Request $request, $id, $token)
    {
        $user = User::find($id);
        $storedHash = Cache::get('magic_login_user:' . $id);

        if (!$user || !$storedHash || !hash_equals($storedHash, hash('sha256', $token))) {
            abort(403, 'Link login tidak valid atau sudah diganti. Minta link baru.');
        }

        // Ingat perangkat ini (~5 tahun) agar tidak perlu memasukkan password lagi.
        Auth::login($user, true);
        $request->session()->forget('current_branch_id');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Masuk sebagai ' . $user->name . '. Perangkat ini akan tetap login.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
