<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Support\Arabic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function show()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'اكتب الإيميل.',
            'email.email' => 'الإيميل مش مظبوط.',
            'password.required' => 'اكتب كلمة السر.',
        ]);

        $credentials['email'] = Arabic::digits(mb_strtolower(trim($credentials['email'])));

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => 'الإيميل أو كلمة السر غلط.',
            ]);
        }

        if (! Auth::user()->is_active) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => 'الحساب ده موقوف. كلّم المدير.',
            ]);
        }

        $request->session()->regenerate();

        Auth::user()->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ])->saveQuietly();

        Activity::log('login', 'سجّل دخول');

        return redirect()->intended(route(Auth::user()->homeRoute()));
    }

    public function logout(Request $request)
    {
        Activity::log('logout', 'سجّل خروج');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
