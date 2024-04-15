<?php


use Illuminate\Support\Str;

return [
    'default' => 'bitrix24',

    'connections' => [
        'bitrix24' => [
            'driver' => 'bitrix24',
            'type' => env('BX24_TYPE', 'webhook'),

            //Webhook auth
            'webhook' => env('BX24_WEBHOOK', 'https://example.bitrix24.ru/rest/1/token'),

            //Oauth2
            'client_id' => env('DB_USERNAME', 'forge'),
            'client_secret' => env('DB_PASSWORD', ''),
            'refresh_path' => storage_path('app/bitrix24_refresh.json'),
        ],
    ],
];
