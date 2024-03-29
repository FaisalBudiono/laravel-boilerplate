<?php

declare(strict_types=1);

return [
    'audience' => [
        env('APP_URL', ''),
    ],
    'key' => [
        'rsa' => [
            'public' => base64_decode(env('JWT_RSA_BASE64_PUBLIC_KEY', '')),
            'private' => base64_decode(env('JWT_RSA_BASE64_PRIVATE_KEY', '')),
        ],
    ],
    'refresh' => [
        'prefix' => env('JWT_REFRESH_PREFIX', 'jwt-refresh'),
        'ttl' => env('JWT_REFRESH_TTL_IN_SECONDS', 86400),
        'grace-period' => env('JWT_REFRESH_GRACE_PERIOD_SECONDS', 5),
    ],
    'ttl' => env('JWT_TTL_IN_SECONDS', 5),
];
