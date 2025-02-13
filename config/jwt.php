<?php

return [
    /*
    |--------------------------------------------------------------------------
    | JWT Secret
    |--------------------------------------------------------------------------
    |
    | This value is used to sign your tokens. Make sure to keep it secret!
    |
    */
    'secret' => env('JWT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | JWT Time to Live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token will be valid for.
    | Defaults to 1 hour
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | JWT Refresh Time to Live
    |--------------------------------------------------------------------------
    |
    | Specify the length of time (in minutes) that the token can be refreshed
    | within. For example, the user can refresh their token within a 2 week
    | window of the original token being created until they must re-authenticate.
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 20160), // 14 days
];
