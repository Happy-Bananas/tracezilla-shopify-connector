<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    
    'tracezilla' => [
        'team_slug'                 => env('TRACEZILLA_TEAM_SLUG'),
        'order_ref_prefix'          => env('TRACEZILLA_ORDER_REF_PREFIX'),
        'customer_location_number'  => env('TRACEZILLA_CUSTOMER_LOCATION_NUMBER'),
        'warehouse_location_number' => env('TRACEZILLA_WAREHOUSE_LOCATION_NUMBER'),
        'order_tag'                 => env('TRACEZILLA_ORDER_TAG', 'Shopify'),
        'sku_tag'                   => env('TRACEZILLA_SKU_TAG', 'Shopify'),
    ],

    'shopify' => [
        'shop_url'      => env('SHOPIFY_SHOP_URL'),
        'client_id'     => env('SHOPIFY_CLIENT_ID'),
        'client_secret' => env('SHOPIFY_CLIENT_SECRET'),
        'scope'         => env('SHOPIFY_SCOPE', 'read_products'),
    ],

];
