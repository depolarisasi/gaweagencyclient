<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->isActive()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
                ]);
            }

            // Update last login timestamp
            $user->update(['last_login_at' => now()]);

            // Redirect based on role
            switch ($user->role) {
                case 'admin':
                    return redirect()->intended('/admin/dashboard');
                case 'staff':
                    return redirect()->intended('/staff/dashboard');
                case 'client':
                    return redirect()->intended('/client/dashboard');
                default:
                    return redirect()->intended('/dashboard');
            }
        }

        return back()->withErrors([
            'email' => 'Email atau password tidak valid.'
        ])->withInput();
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/')->with('success', 'Anda telah berhasil logout.');
    }
}
