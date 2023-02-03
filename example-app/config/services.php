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
        'merchant_name' => env('EGHL_MERCHANT_NAME'),
        "password" => env("EGHL_PASSWORD"),
        "service_id" => env("EGHL_SERVICE_ID"),
        "service_url" => env("EGHL_SERVICE_URL"),
        "merchant_return_url" => env("MERCHANT_RETURN_URL"),
        "merchant_approval_url" => env("MERCHANT_APPROVAL_URL"),
        "merchant_unapproval_url" => env("MERCHANT_UNAPPROVAL_URL"),
        "merchant_callback_url" => env("MERCHANT_CALLBACK_URL"),
        "currency_code" => env("CURRENCY_CODE", "MYR"),
        "page_timeout" => env("PAGE_TIMOUT", 300),
        "transaction_type" => env("EGHL_TRANSACTION_TYPE", "SALE"),
        "payment_method" => env("EGHL_PAYMENT_METHOD", "ANY"),
        'payment_description' => env('EGHL_PAYMENT_DESCRIPTION'),
        'phone_number' => env('EGHL_PAYER_PHONE_NUMBER'),
        'payment_status_page_url' => env('EGHL_PAYMENT_STATUS_PAGE_URL'),
    ]
];
