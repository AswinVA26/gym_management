<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    /**
     * Registration bootstraps the very first account: the platform owner gets
     * super admin privileges. Once an owner exists the signup is closed.
     */
    public function showRegisterForm()
    {
        if (User::count() > 0) {
            abort(403, 'Registration is closed. Ask your platform administrator for an account.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (User::count() > 0) {
            abort(403, 'Registration is closed.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'role' => User::ROLE_SUPER_ADMIN,
            'status' => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('platform.index')
            ->with('flash_success', 'Welcome! Your platform account is ready. Create your first gym below.');
    }
}
