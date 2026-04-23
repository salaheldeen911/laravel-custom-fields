<?php

// config for CustomFields /LaravelCustomFields
return [
    'models' => [
        // 'post' => Post::class,
    ],
    'routing' => [
        'api' => [
            'enabled' => false,
            'prefix' => 'api/custom-fields',
            // WARNING: No auth middleware by default. Add 'auth:sanctum' or your guard here.
            // Example: ['api', 'auth:sanctum', 'throttle:api']
            'middleware' => ['api'],
        ],
        'web' => [
            'enabled' => true,
            'prefix' => 'custom-fields',
            // WARNING: No auth middleware by default. Add 'auth' or your guard here.
            // Example: ['web', 'auth']
            'middleware' => ['web'],
        ],
    ],

    /**
     * Integrity Check (Sealed Lifecycle)
     * If enabled, the package will throw an exception if you attempt to save
     * custom fields that haven't passed through the service's validation.
     */
    'strict_validation' => true,

    /**
     * File Upload Configuration
     */
    'files' => [
        'disk' => 'public',
        'path' => 'custom-fields',
        'cleanup' => true, // Automatically delete files when updated or model deleted
    ],

    /**
     * Caching Strategy
     * Control how field definitions are cached to optimize performance.
     */
    'cache' => [
        'ttl' => 3600, // seconds (1 hour)
        'prefix' => 'custom_fields_',
        'octane_compatibility' => true, // Set to false if you want to use static in-memory caching (not recommended for Octane)
    ],

    /**
     * Security & Sanitization
     */
    'security' => [
        'sanitize_html' => true, // Strip dangerous tags from text/textarea fields
    ],

    /**
     * Automated Maintenance
     */
    'pruning' => [
        'prune_deleted_after_days' => 30, // Permanently delete soft-deleted fields after X days
    ],

    /**
     * Authorization
     * Define a Gate ability name to protect custom field management routes.
     * Set to null to skip authorization (ensure your middleware handles it).
     */
    'authorization' => [
        'ability' => null, // e.g., 'manage-custom-fields'
    ],
];
