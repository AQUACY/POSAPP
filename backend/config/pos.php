<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Refund Settings
    |--------------------------------------------------------------------------
    |
    | Configure refund-related settings for your POS system
    |
    */

    'refund' => [
        // Maximum number of days after a sale that a refund can be processed
        'time_limit' => env('REFUND_TIME_LIMIT', 30),

        // Whether to require condition check for refunds
        'require_condition_check' => env('REQUIRE_CONDITION_CHECK', false),

        // Minimum reason length for refund requests
        'min_reason_length' => env('REFUND_MIN_REASON_LENGTH', 10),

        // Maximum refund amount as percentage of sale amount
        'max_amount_percentage' => env('REFUND_MAX_AMOUNT_PERCENTAGE', 100),

        // Whether to allow partial refunds
        'allow_partial' => env('ALLOW_PARTIAL_REFUNDS', true),

        // Whether to require manager approval for refunds above certain amount
        'require_approval_above' => env('REFUND_REQUIRE_APPROVAL_ABOVE', 1000),
    ],

    /*
    |--------------------------------------------------------------------------
    | Inventory Settings
    |--------------------------------------------------------------------------
    |
    | Configure inventory-related settings
    |
    */

    'inventory' => [
        // Low stock threshold
        'low_stock_threshold' => env('LOW_STOCK_THRESHOLD', 10),

        // Whether to track item conditions
        'track_conditions' => env('TRACK_ITEM_CONDITIONS', false),

        // Whether to allow negative stock
        'allow_negative_stock' => env('ALLOW_NEGATIVE_STOCK', false),
    ],
]; 