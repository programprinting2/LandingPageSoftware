<?php

return [
    'devices_max' => (int) env('SPOTCOLOR_DEVICES_MAX', 3),

    'desktop_oauth_scheme' => env('SPOTCOLOR_DESKTOP_OAUTH_SCHEME', 'spotcolor'),

    'cors_origins' => array_filter(array_map(
        'trim',
        explode(',', env('SPOTCOLOR_CORS_ORIGINS', '*'))
    )),
];
