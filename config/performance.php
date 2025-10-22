<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains performance-related configurations for the application.
    |
    */

    'cache' => [
        'attendance_settings' => [
            'ttl' => 3600, // 1 hour
        ],
        'holiday_schedules' => [
            'ttl' => 1800, // 30 minutes
        ],
        'special_schedules' => [
            'ttl' => 1800, // 30 minutes
        ],
        'user_roles' => [
            'ttl' => 3600, // 1 hour
        ],
    ],

    'database' => [
        'chunk_size' => 1000,
        'max_queries' => 100,
    ],

    'memory' => [
        'max_memory' => 128, // MB
        'chunk_processing' => true,
    ],

    'query' => [
        'enable_query_log' => env('APP_DEBUG', false),
        'slow_query_threshold' => 100, // milliseconds
    ],
];

