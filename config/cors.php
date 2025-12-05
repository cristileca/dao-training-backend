<?php
return [

'paths' => ['api/*', 'sanctum/csrf-cookie'],

'allowed_methods' => ['*'], // permite GET, POST, etc.

'allowed_origins' => ['http://localhost:3000'],


'allowed_origins_patterns' => [],

'allowed_headers' => ['*'], // permite toate header-ele

'exposed_headers' => [],

'max_age' => 0,

'supports_credentials' => false, // pune true dacă folosești cookies
];
