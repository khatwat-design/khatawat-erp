<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
    'allowed_methods' => ['*'],
    'allowed_origins' => array_values(array_filter([
        'http://localhost:3000',
        'http://localhost:3001',
        'http://localhost:3002',
        'http://127.0.0.1:3000',
        'http://127.0.0.1:3001',
        'http://127.0.0.1:3002',
        'http://187.77.68.2:3000',
        'http://187.77.68.2:3001',
        env('APP_ENV') === 'production' ? 'https://khatawat.com' : null,
        env('APP_ENV') === 'production' ? 'https://www.khatawat.com' : null,
        env('APP_ENV') === 'production' ? 'https://store.khatawat.com' : null,
    ])),
    'allowed_origins_patterns' => [
        '#^https://[a-z0-9-]+\.khatawat\.com$#', // *.khatawat.com subdomains
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => true,
];
