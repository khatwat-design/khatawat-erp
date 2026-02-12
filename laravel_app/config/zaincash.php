<?php

return [
    /*
    |--------------------------------------------------------------------------
    | ZainCash Payment Gateway
    |--------------------------------------------------------------------------
    | Placeholder config for future ZainCash API integration.
    | See: https://zaincash.iq
    */
    'enabled' => env('ZAINCASH_ENABLED', false),
    'merchant_id' => env('ZAINCASH_MERCHANT_ID', ''),
    'secret' => env('ZAINCASH_SECRET', ''),
    'redirect_url' => env('ZAINCASH_REDIRECT_URL', '/payment/callback'),
];
