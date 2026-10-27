<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API User
    |--------------------------------------------------------------------------
    |
    | Credentials used by the ApiUserSeeder to create the user that issues
    | access tokens. Define them in your environment file.
    |
    */

    'user' => [
        'name' => env('API_USER_NAME', 'API Admin'),
        'email' => env('API_USER_EMAIL'),
        'password' => env('API_USER_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Requests allowed per minute for the API and for token issuing.
    |
    */

    'rate_limit' => [
        'api' => (int) env('API_RATE_LIMIT', 60),
        'login' => (int) env('API_LOGIN_RATE_LIMIT', 5),
    ],

];
