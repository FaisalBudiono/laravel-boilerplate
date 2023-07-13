<?php

return [
    'key' => [
        'rsa' => [
            'public' => base64_decode(env('JWT_RSA_BASE64_PUBLIC_KEY', '')),
            'private' => base64_decode(env('JWT_RSA_BASE64_PRIVATE_KEY', '')),
        ],
    ],
    'refresh' => [
        'prefix' => env('JWT_REFRESH_PREFIX', 'jwt-refresh'),
        'ttl' => env('JWT_REFRESH_TTL_IN_MINUTE', 1440),
    ]
];
