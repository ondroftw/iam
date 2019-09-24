<?php

/**
 * @var array
 */
return [
    'server' => env('IAM_MANAGER_SERVER', null),
    'grant_type' => env('IAM_MANAGER_GRANT_TYPE', 'password'),
    'client_id' => env('IAM_MANAGER_CLIENT_ID', null),
    'client_secret' => env('IAM_MANAGER_CLIENT_SECRET', null),
    'redirect_url' => env('IAM_MANAGER_REDIRECT_URL', '/manager/success')
];
