<?php

/**
 * @var array
 */
return [
    'server' => env('IAM_MANAGER_SERVER', null),
    'client_id' => env('IAM_MANAGER_CLIENT_ID', null),
    'client_secret' => env('IAM_MANAGER_CLIENT_SECRET', null),
    'redirect_url' => env('IAM_MANAGER_REDIRECT_URL', '/'),
    'use_cache' => env('IAM_MANAGER_USE_CACHE', true),
    'public_key' => env('IAM_MANAGER_PUBLIC_KEY'),
    'redirect_callback' => env('IAM_MANAGER_REDIRECT_CALLBACK', '/'),
];
