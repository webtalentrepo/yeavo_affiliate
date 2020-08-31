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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'passport' => [
        'login_end_point'        => env('PASSPORT_LOGIN_ENDPOINT'),
        'login_client_end_point' => env('PASSPORT_CLIENT_ENDPOINT'),
        'passport_client_id'     => env('PASSPORT_CLIENT_ID'),
        'passport_client_secret' => env('PASSPORT_CLIENT_SECRET'),
        'expires_hours'          => 8,
        'expires_remember_me'    => 2,
    ],

    'clickbank' => [
        'dev_key' => env('CLICKBANK_DEV_KEY'),
        'clerk_key' => env('CLICKBANK_CLERK_KEY')
    ],

    'cj_access_token' => env('CJ_ACCESS_TOKEN'),
    'rakuten' => [
        'sec_key' => env('RAKUTEN_SEC_KEY'),
        'token' => env('LINKSHARE_TOKEN')
    ],

    'jvzoo_key' => env('JVZOO_KEY'),
    'share_a_sale' => [
        'key' => env('SHARE_A_SALE_KEY'),
        'sec' => env('SHARE_A_SALE_SEC')
    ]

];
