<?php

return [
    'url' => env('SLACK_WEBHOOK_URL'),
    'username' => env('SLACK_USERNAME'),
    'icon' => env('SLACK_ICON'),
    'channel' => env('SLACK_CHANNEL'),
    'proxy' => [
        'http' => env('HTTP_PROXY'),
        'https' => env('HTTPS_PROXY')
    ]
];
