<?php

/*
 * CORS: mengizinkan browser di alamat frontend memanggil API ini.
 * 8080 = frontend di Docker Compose, 5173 = server pengembangan Vite.
 * Bisa diganti lewat variabel CORS_ALLOWED_ORIGINS di .env (pisahkan dengan koma).
 */
return [
    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(explode(',', env(
        'CORS_ALLOWED_ORIGINS',
        'http://localhost:8080,http://localhost:5173'
    ))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Token dikirim lewat header Authorization (bukan cookie), jadi tidak perlu credentials.
    'supports_credentials' => false,
];