<?php

return [
    'enabled' => env('APP_ENV', 'local') === 'production',
    'webhook' => env('SLACK_INCOMING_WEBHOOK', 'localhost'),
    'settings' => [
        'username' => env('SLACK_DEFAULT_USER', 'Pillar Science V2'),
        'channel' => env('SLACK_DEFAULT_CHANNEL', '#debug'),
        'link_name' => env('SLACK_LINK_NAMES', true)
    ]
];
