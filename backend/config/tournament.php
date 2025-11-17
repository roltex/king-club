<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tournament Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration settings for the poker tournament system
    |
    */

    'total_tables' => env('TOURNAMENT_TOTAL_TABLES', 6),
    'seats_per_table' => env('TOURNAMENT_SEATS_PER_TABLE', 9),
    'total_seats' => env('TOURNAMENT_TOTAL_SEATS', 54),
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:5173'),
    
    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    */
    
    'rate_limit' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | QR Code Settings
    |--------------------------------------------------------------------------
    */
    
    'qr_code' => [
        'size' => 300,
        'format' => 'svg',
        'error_correction' => 'H', // High
    ],
];

