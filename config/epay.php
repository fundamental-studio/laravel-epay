<?php

return [

    'production' => env('EPAY_PRODUCTION'),
    'min' => env('EPAY_MIN'),
    'secret' => env('EPAY_SECRET'),
    'urlOk' => env('EPAY_DEFAULT_URL_OK'),
    'urlCancel' => env('EPAY_DEFAULT_URL_CANCEL'),
    'currency' => env('EPAY_DEFAULT_CURRENCY'),
    'generateInvoice' => env('EPAY_GENERATE_INVOICE'),
    'expirationPeriod' => env('EPAY_EXPIRATION_HOURS'),

];