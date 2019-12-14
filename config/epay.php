<?php

return [

    'production' => env('EPAY_PRODUCTION'),
    'min' => env('EPAY_MIN'),
    'secret' => env('EPAY_SECRET'),
    'url_ok' => env('EPAY_DEFAULT_URL_OK'),
    'url_cancel' => env('EPAY_DEFAULT_URL_CANCEL'),
    'currency' => env('EPAY_DEFAULT_CURRENCY'),

    'generate_invoice' => env('EPAY_GENERATE_INVOICE'),
    'expiration_period' => env('EPAY_EXPIRATION_HOURS'),

];