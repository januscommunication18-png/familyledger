<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Paddle Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | When true, the Paddle Sandbox environment will be used. Set to false
    | for production use. Remember to use sandbox credentials when in
    | sandbox mode and production credentials when in production.
    |
    */

    'sandbox' => env('PADDLE_SANDBOX', true),

    /*
    |--------------------------------------------------------------------------
    | Paddle Vendor ID
    |--------------------------------------------------------------------------
    |
    | Your Paddle vendor ID. This can be found in your Paddle dashboard
    | under Developer Tools > Authentication.
    |
    */

    'vendor_id' => env('PADDLE_VENDOR_ID'),

    /*
    |--------------------------------------------------------------------------
    | Paddle API Key
    |--------------------------------------------------------------------------
    |
    | Your Paddle API key for server-side API calls. This can be generated
    | in your Paddle dashboard under Developer Tools > Authentication.
    |
    */

    'api_key' => env('PADDLE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Paddle Webhook Secret
    |--------------------------------------------------------------------------
    |
    | Your webhook signing secret for verifying webhook signatures. This can
    | be found in your Paddle dashboard under Developer Tools > Notifications.
    |
    */

    'webhook_secret' => env('PADDLE_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Paddle Client Token
    |--------------------------------------------------------------------------
    |
    | Your client-side token for Paddle.js. This can be generated in your
    | Paddle dashboard under Developer Tools > Authentication.
    |
    */

    'client_token' => env('PADDLE_CLIENT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Paddle API URLs
    |--------------------------------------------------------------------------
    |
    | The API endpoints for Paddle sandbox and production environments.
    |
    */

    'urls' => [
        'sandbox' => 'https://sandbox-api.paddle.com',
        'production' => 'https://api.paddle.com',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paddle.js URLs
    |--------------------------------------------------------------------------
    |
    | The JavaScript SDK URLs for Paddle sandbox and production environments.
    |
    */

    'js_urls' => [
        'sandbox' => 'https://sandbox-cdn.paddle.com/paddle/v2/paddle.js',
        'production' => 'https://cdn.paddle.com/paddle/v2/paddle.js',
    ],

];
