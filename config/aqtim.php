<?php

return [
    'webhook' => [
        'url' => env('AQTIM_WEBHOOK_URL'),
        'secret' => env('AQTIM_WEBHOOK_SECRET'),
        'queue' => env('AQTIM_WEBHOOK_QUEUE'),
    ],
    'ticket' => [
        'url' => env('AQTIM_TICKET_URL', 'https://ticket.aqt.im'),
    ],
];
