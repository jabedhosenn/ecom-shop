<?php

declare(strict_types=1);

// config for HasinHayder/Sslcommerz

return [
    /**
     * Enable/Disable Sandbox mode
     */
    'sandbox' => filter_var(env('SSLC_SANDBOX', true), FILTER_VALIDATE_BOOLEAN),

    /**
     * The API credentials given from SSLCommerz
     */
    'store' => [
        'id' => env('SSLC_STORE_ID'),
        'password' => env('SSLC_STORE_PASSWORD'),
        'currency' => env('SSLC_STORE_CURRENCY', 'BDT'),
    ],

    /**
     * Route names for success/failure/cancel
     */
    'route' => [
        'success' => env('SSLC_ROUTE_SUCCESS', 'shop.payments.sslcommerz.success'),
        'failure' => env('SSLC_ROUTE_FAILURE', 'shop.payments.sslcommerz.failure'),
        'cancel' => env('SSLC_ROUTE_CANCEL', 'shop.payments.sslcommerz.cancel'),
        'ipn' => env('SSLC_ROUTE_IPN', 'api.payments.sslcommerz.ipn'),
    ],

    /**
     * Product profile required from SSLC
     * By default it is "general"
     *
     * AVAILABLE PROFILES
     *  general
     *  physical-goods
     *  non-physical-goods
     *  airline-tickets
     *  travel-vertical
     *  telecom-vertical
     */
    'product_profile' => 'general',
];
