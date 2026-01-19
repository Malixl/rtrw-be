<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'storage/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['*'],  // Izinkan semua origin (untuk development/demo)
    'allowed_origins_patterns' => [
        'https://*.vercel.app',   // Semua subdomain Vercel
        'https://*.ngrok-free.dev', // Ngrok
    ],
    'allowed_headers' => ['*'],
    'exposed_headers' => [],
    'max_age' => 86400,  // Cache preflight selama 24 jam
    'supports_credentials' => true,
];
