<?php

return [
    'paths' => ['*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'],
    
    'allowed_origins_patterns' => [
        'http://localhost:[0-9]+',
        'http://[a-zA-Z0-9-]+\.localhost:[0-9]+',
        'http://rglasswindows.localhost:[0-9]+',
        'https://localhost:[0-9]+',
        'https://[a-zA-Z0-9-]+\.localhost:[0-9]+',
    ],

    'allowed_headers' => ['*'],
    
    'exposed_headers' => [],
    
    'max_age' => 0,
    
    'supports_credentials' => true,
];