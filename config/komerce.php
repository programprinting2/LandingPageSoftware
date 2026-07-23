<?php

return [
    'enabled' => (bool) env('KOMERCE_ENABLED', false),

    'base_url' => rtrim((string) env('KOMERCE_BASE_URL', 'https://api-sandbox.collaborator.komerce.id/user'), '/'),

    'api_key' => env('KOMERCE_API_KEY'),

    'callback_api_key' => env('KOMERCE_CALLBACK_API_KEY'),

    'callback_path' => '/api/webhooks/komerce/payment-notification',

    'payment_page_url' => rtrim((string) env('KOMERCE_PAYMENT_PAGE_URL', 'https://pay-sandbox.komerce.my.id'), '/'),

    'sandbox_base_url' => rtrim((string) env('KOMERCE_SANDBOX_BASE_URL', 'https://api-sandbox.collaborator.komerce.id'), '/'),

    'min_amount' => (int) env('KOMERCE_MIN_AMOUNT', 10000),

    'timeout' => (int) env('KOMERCE_TIMEOUT', 30),

    'allow_simulate' => env('KOMERCE_ALLOW_SIMULATE', null),
];
