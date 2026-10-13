<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials)) {
            return response()->json(['message' => 'Email atau password salah.'], 401);
        }

        $token = $request->user()->createToken('frontend')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $request->user()->only(['id', 'name', 'email']),
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user()->only(['id', 'name', 'email']));
    }

    public function logout(Request $request)
    {
        // Cabut semua token milik user. Aman dipanggil baik saat
        // autentikasi via Bearer token maupun via session (TransientToken).
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Berhasil logout.']);
    }
}
