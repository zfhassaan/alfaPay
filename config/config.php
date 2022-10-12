<?php

return [
    'api_url'       => env('ALFAPAY_API_URL', 'https://sandbox.bankalfalah.com/HS/api/HSAPI/HSAPI'),
    'mode'          => env('ALFAPAY_MODE', 'sandbox'),
    'channel_id'    => env('ALFAPAY_CHANNEL_ID', '1002'),
    'merchant_id'   => env('ALFAPAY_MERCHANT_ID', '197'),
    'store_id'      => env('ALFAPAY_STORE_ID', '000001'),
    'return_url'    => env('ALFAPAY_RETURN_URL'),
    'merchant_username' => env('ALFAPAY_MERCHANT_USERNAME'),
    'merchant_password' => env('ALFAPAY_MERCHANT_PASSWORD'),
    'merchant_hash'     => env('ALFAPAY_MERCHANT_HASH'),
    'key_1'         => env('ALFAPAY_KEY_1'),
    'key_2'         => env('ALFAPAY_KEY_2'),
];
