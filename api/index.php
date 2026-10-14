<?php

/*
|--------------------------------------------------------------------------
| Vercel serverless bootstrap (API-only)
|--------------------------------------------------------------------------
|
| Backend ini murni JSON (tanpa Blade), jadi tidak butuh compiled views.
| Vercel read-only kecuali /tmp: paksa default yang aman di sini, hanya
| jika env var belum diset di dashboard Vercel.
|
| NOTE: file ini harus mulai tepat di <?php byte pertama (tanpa baris
| kosong/BOM) dan display_errors mati, supaya response tidak rusak.
|
*/

ini_set('display_errors', '0');

foreach ([
    'LOG_CHANNEL' => 'stderr',
    'LOG_LEVEL' => 'error',
    'SESSION_DRIVER' => 'array',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
] as $key => $fallback) {
    if (getenv($key) === false && ! isset($_ENV[$key]) && ! isset($_SERVER[$key])) {
        putenv("{$key}={$fallback}");
        $_ENV[$key] = $fallback;
        $_SERVER[$key] = $fallback;
    }
}

require __DIR__ . '/../public/index.php';
