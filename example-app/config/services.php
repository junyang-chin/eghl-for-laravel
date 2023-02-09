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

    'eghl' => [
        "service_url" => env("EGHL_SERVICE_URL"),
        "password" => env("EGHL_PASSWORD"),
        "transaction_type" => env("EGHL_TRANSACTION_TYPE", 'SALE'),
        "payment_method" => env("EGHL_PAYMENT_METHOD", 'ANY'),
        "service_id" => env("EGHL_SERVICE_ID"),
        "merchant_return_url" => env("EGHL_MERCHANT_RETURN_URL"),
        "currency_code" => env("EGHL_CURRENCY_CODE", 'MYR'),
        "merchant_name" => env("EGHL_MERCHANT_NAME", 'FooBar'),
        "merchant_callback_url" => env("EGHL_MERCHANT_CALLBACK_URL"),
        'language_code' => env('EGHL_LANGUAGE_CODE', 'EN'),
        "page_timeout" => env("EGHL_PAGE_TIMEOUT", 600),
    ]
];
