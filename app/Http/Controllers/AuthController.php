<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login() {
        return view('auth.login');
    }
    public function authenticate(Request $request) {
        if (Auth::attempt($request->validate(['email' => 'required', 'password' => 'required']))) {
            $request->session()->regenerate();
            return redirect()->route('admin.index');
        }
        return back()->withErrors(['email' => 'Email atau password salah.']);
    }
    public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
