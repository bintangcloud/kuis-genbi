
<?php

/*
|--------------------------------------------------------------------------
| Vercel serverless bootstrap
|--------------------------------------------------------------------------
|
| Vercel's filesystem is read-only except for /tmp. Laravel needs writable
| paths for compiled Blade views, and file-based log/session/cache
| drivers will fatal (white screen) if left at their defaults. Force
| serverless-safe defaults here, but only when the user has not set
| the corresponding env var in the Vercel dashboard.
|
*/

foreach ([
    'VIEW_COMPILED_PATH' => '/tmp/views',
    'LOG_CHANNEL' => 'stderr',
    'LOG_LEVEL' => 'error',
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
] as $key => $fallback) {
    if (getenv($key) === false && ! isset($_ENV[$key]) && ! isset($_SERVER[$key])) {
        putenv("{$key}={$fallback}");
        $_ENV[$key] = $fallback;
        $_SERVER[$key] = $fallback;
    }
}

foreach (['/tmp/views', '/tmp/framework/sessions', '/tmp/framework/cache'] as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';
