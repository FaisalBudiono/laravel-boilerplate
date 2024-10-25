<?php

declare(strict_types=1);


return [
    'aud' => env('JWT_AUD', ''),
    'ttl' => [
        'access-token' => intval(env('JWT_ACCESS_TOKEN_TTL_IN_SECONDS', 60)),
    ],
    'rsa' => [
        'access-token' => [
            'public' => base64_decode((string) env('JWT_RSA_ACCESS_TOKEN_PUBLIC_KEY', '')),
            'private' => base64_decode((string) env('JWT_RSA_ACCESS_TOKEN_PRIVATE_KEY', '')),
        ],
        'refresh-token' => [
            'public' => base64_decode((string) env('JWT_RSA_REFRESH_TOKEN_PUBLIC_KEY', '')),
            'private' => base64_decode((string) env('JWT_RSA_REFRESH_TOKEN_PRIVATE_KEY', '')),
        ],
    ],
];
