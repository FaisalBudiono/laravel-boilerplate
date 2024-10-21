<?php

declare(strict_types=1);


return [
    'rsa' => [
        'access-token' => [
            'public' => base64_decode((string) env('JWT_RSA_ACCESS_TOKEN_PUBLIC_KEY', '')),
            'private' => base64_decode((string) env('JWT_RSA_ACCESS_TOKEN_PRIVATE_KEY', '')),
        ],
    ],
];
