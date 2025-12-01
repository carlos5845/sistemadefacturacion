<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'sunat' => [
        // Endpoints para ambiente beta (pruebas)
        'endpoint_invoices' => env('SUNAT_ENDPOINT_INVOICES', 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService'),
        'endpoint_retentions' => env('SUNAT_ENDPOINT_RETENTIONS', 'https://e-beta.sunat.gob.pe/ol-ti-itemision-otroscpe-gem-beta/billService'),
        
        'timeout' => env('SUNAT_TIMEOUT', 30),
        'retry_attempts' => env('SUNAT_RETRY_ATTEMPTS', 3),
        'retry_delay' => env('SUNAT_RETRY_DELAY', 5),
    ],

];
