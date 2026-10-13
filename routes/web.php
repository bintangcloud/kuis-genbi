<?php

use Illuminate\Support\Facades\Route;

// Backend API only — frontend terpisah (lihat folder frontend/).
// Dokumentasi endpoint: GET /api/questions (publik), POST /api/login (admin).
Route::get('/', function () {
    return response()->json([
        'app' => config('app.name'),
        'mode' => 'api-only',
        'health' => '/up',
        'endpoints' => [
            'GET /api/questions',
            'GET /api/questions/{id}',
            'POST /api/login',
            'GET /api/me (token)',
            'POST /api/logout (token)',
            'POST /api/questions (token)',
            'PUT /api/questions/{id} (token)',
            'DELETE /api/questions/{id} (token)',
        ],
    ]);
});
