<?php

return [
    'storage' => [
        'datasets' => [
            'disk' => env('DATASETS_DISK', 'wasabi'),
            'upload_dir' => env('DATASETS_UPLOAD_DIR', 'datasets')
        ],
        'desktop_clients' => [
            'disk' => env('DESKTOP_CLIENT_DISK', 'wasabi'),
            'upload_dir' => env('DESKTOP_CLIENT_UPLOAD_DIR', 'desktop_clients')
        ]
    ],
    'join_invitation' => [
        'expiration' => 30 * 24 // In hours
    ]
];