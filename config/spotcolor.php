<?php

return [
    'devices_max' => (int) env('SPOTCOLOR_DEVICES_MAX', 3),

    'cors_origins' => array_filter(array_map(
        'trim',
        explode(',', env('SPOTCOLOR_CORS_ORIGINS', '*'))
    )),
];
