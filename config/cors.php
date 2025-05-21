<?php
return [

'paths' => ['*'],

'allowed_methods' => ['GET', 'POST', 'OPTIONS', 'PUT', 'DELETE'],

'allowed_origins' => [
    'http://localhost',
    'https://localhost',
    'http://localhost:5173',
    'https://localhost:5173',
    'http://localhost:5174',
    'https://localhost:5174',
    'http://localhost:3000',
    'https://localhost:3000',
    'http://localhost:8000',
    'https://localhost:8000',
    'https://mardev.es',
    'http://mardev.es',
],

'allowed_origins_patterns' => [],

'allowed_headers' => ['*'],

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => true,

];
