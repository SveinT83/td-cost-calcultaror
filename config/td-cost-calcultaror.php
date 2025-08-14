<?php

return [
    /*
    |--------------------------------------------------------------------------
    | TD Cost Calculator Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for the TD Cost Calculator module.
    |
    */

    // Default pagination settings
    'pagination' => [
        'per_page' => 15,
    ],

    // Export settings
    'export' => [
        // Maximum number of records to export at once
        'max_records' => 5000,
        
        // Enable/disable export formats
        'formats' => [
            'csv' => true,
            'excel' => true,
            'pdf' => true,
            'json' => true,
        ],
        
        // Default export settings
        'defaults' => [
            'include_headers' => true,
            'delimiter' => ',',
            'enclosure' => '"',
        ],
    ],

    // API rate limiting settings
    'api_rate_limit' => [
        'enabled' => true,
        'max_requests' => 60,
        'decay_minutes' => 1,
    ],

    // Dashboard settings
    'dashboard' => [
        'default_chart_type' => 'bar',
        'show_cost_item_count' => true,
        'show_product_count' => true,
        'show_total_cost' => true,
        'charts' => [
            'costs_by_category' => true,
            'costs_by_period' => true,
            'top_cost_items' => true,
            'top_products' => true,
        ],
    ],

    // Cost calculation settings
    'calculation' => [
        'default_model' => 'percentage', // percentage, quantity, fixed
        'precision' => 2,
    ],
    
    // Language settings
    'languages' => [
        'available' => [
            'en' => 'English',
            'no' => 'Norwegian',
        ],
        'default' => 'en',
    ],
    
    // Cache settings
    'cache' => [
        'enabled' => true,
        'ttl' => 60 * 24, // Time to live in minutes (24 hours)
        'prefix' => 'td_cost_calculator_',
    ],
];
