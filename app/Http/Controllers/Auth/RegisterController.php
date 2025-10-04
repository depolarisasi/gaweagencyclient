<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Show the registration form
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'phone' => 'nullable|string|max:20',
            'company_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Create new user with client role by default
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'company_name' => $request->company_name,
            'role' => 'client', // Default role for registration
            'status' => 'active', // Active by default for testing
        ]);

        // Auto-login the user and redirect to appropriate dashboard
        Auth::login($user);
        
        // Redirect based on user role
        switch ($user->role) {
            case 'admin':
                return redirect('/admin/dashboard');
            case 'staff':
                return redirect('/staff/dashboard');
            case 'client':
            default:
                return redirect('/client/dashboard');
        }
    }
}
