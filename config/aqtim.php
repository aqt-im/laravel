<?php

return [
    'mode' => env('AQTIM_MODE'),

    'webhook' => [
        'url' => null,
        'secret' => env('AQTIM_WEBHOOK_SECRET'),
        'queue' => null,
    ],

    'ticket' => [
        'url' => null,
    ],
];
