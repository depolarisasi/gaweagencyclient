<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for Tripay Payment Gateway integration
    |
    */

    'is_sandbox' => env('TRIPAY_IS_SANDBOX', true),
    
    'api_key' => env('TRIPAY_API_KEY', 'DEV-VVX1Hmcjq33x7ZeGFqkQIofSXifgbcAouaVCfvmO'),
    
    'private_key' => env('TRIPAY_PRIVATE_KEY', '0WH9P-kEmXs-8DUEv-vQKnN-bqNCA'),
    
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', 'T30514'),
    
    'callback_url' => env('TRIPAY_CALLBACK_URL', null),
    
    /*
    |--------------------------------------------------------------------------
    | Payment Methods
    |--------------------------------------------------------------------------
    |
    | Available payment methods with their configurations
    |
    */
    
    'payment_methods' => [
        'BRIVA' => [
            'name' => 'BRI Virtual Account',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 10000000,
            'fee_flat' => 4250,
            'fee_percent' => 0,
        ],
        'BCAVA' => [
            'name' => 'BCA Virtual Account',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 10000000,
            'fee_flat' => 5500,
            'fee_percent' => 0,
        ],
        'BNIVA' => [
            'name' => 'BNI Virtual Account',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 10000000,
            'fee_flat' => 4250,
            'fee_percent' => 0,
        ],
        'MANDIRIVA' => [
            'name' => 'Mandiri Virtual Account',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 10000000,
            'fee_flat' => 4250,
            'fee_percent' => 0,
        ],
        'PERMATAVA' => [
            'name' => 'Permata Virtual Account',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 10000000,
            'fee_flat' => 4250,
            'fee_percent' => 0,
        ],
        'QRIS' => [
            'name' => 'QRIS by ShopeePay',
            'type' => 'direct',
            'min_amount' => 1000,
            'max_amount' => 5000000,
            'fee_flat' => 750,
            'fee_percent' => 0.7,
        ],
        'QRIS2' => [
            'name' => 'QRIS',
            'type' => 'direct',
            'min_amount' => 1000,
            'max_amount' => 5000000,
            'fee_flat' => 750,
            'fee_percent' => 0.7,
        ],
        'ALFAMART' => [
            'name' => 'Alfamart',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 2500000,
            'fee_flat' => 3500,
            'fee_percent' => 0,
        ],
        'INDOMARET' => [
            'name' => 'Indomaret',
            'type' => 'direct',
            'min_amount' => 10000,
            'max_amount' => 2500000,
            'fee_flat' => 3500,
            'fee_percent' => 0,
        ],
        'OVO' => [
            'name' => 'OVO',
            'type' => 'redirect',
            'min_amount' => 1000,
            'max_amount' => 10000000,
            'fee_flat' => 0,
            'fee_percent' => 3,
        ],
        'DANA' => [
            'name' => 'DANA',
            'type' => 'redirect',
            'min_amount' => 1000,
            'max_amount' => 10000000,
            'fee_flat' => 0,
            'fee_percent' => 3,
        ],
        'SHOPEEPAY' => [
            'name' => 'ShopeePay',
            'type' => 'redirect',
            'min_amount' => 1000,
            'max_amount' => 10000000,
            'fee_flat' => 0,
            'fee_percent' => 3,
        ],
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Transaction Status
    |--------------------------------------------------------------------------
    |
    | Mapping of Tripay status to application status
    |
    */
    
    'status_mapping' => [
        'UNPAID' => 'pending',
        'PAID' => 'paid',
        'EXPIRED' => 'expired',
        'FAILED' => 'failed',
        'REFUND' => 'refunded',
    ],
];