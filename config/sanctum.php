<?php

/*
|--------------------------------------------------------------------------
| Auth strategy note
|--------------------------------------------------------------------------
| This project uses Sanctum in "API token" mode (Bearer tokens), NOT the
| cookie-based SPA mode. Nuxt runs on a different origin/port than Laravel
| and does its data fetching from the Nuxt server during SSR, so a plain
| bearer token (stored in an httpOnly cookie set by Nuxt) is simpler and
| avoids first-party/third-party cookie issues across origins.
*/

return [

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', 'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000')),

    'guard' => ['web'],

    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'verify_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];
