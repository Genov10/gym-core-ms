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

    'wayforpay' => (static function (): array {
        $appUrl = rtrim((string) env('APP_URL', ''), '/');

        return [
            'merchant_account' => env('WAYFORPAY_MERCHANT_ACCOUNT', ''),
            'secret_key' => env('WAYFORPAY_SECRET_KEY', ''),

            // По доке: доменное имя веб-сайта торговца (обычно без протокола).
            'merchant_domain_name' => env('WAYFORPAY_DOMAIN_NAME', ''),

            'currency' => env('WAYFORPAY_CURRENCY', 'UAH'),
            'language' => env('WAYFORPAY_LANGUAGE', 'UA'),

            // Куда редиректить клиента после оплаты (returnUrl).
            'return_url' => env('WAYFORPAY_RETURN_URL') ?: ($appUrl !== '' ? $appUrl.'/result' : ''),

            // Callback от WayForPay (serviceUrl).
            'service_url' => env('WAYFORPAY_SERVICE_URL') ?: ($appUrl !== '' ? $appUrl.'/api/wayforpay/callback' : ''),
        ];
    })(),
];
