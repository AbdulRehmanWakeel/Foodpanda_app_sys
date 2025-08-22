<?php

return [
    'paths' => ['api/*', 'auth/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:9001', '*'],
    'allowed_headers' => ['*'],
    'supports_credentials' => true,
];
