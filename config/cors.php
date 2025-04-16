<?php

return [
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' => ['http://localhost:3000'], // เปลี่ยนเป็น URL Next.js
    'allowed_headers' => ['*'],
    'supports_credentials' => false, // ❌ ปิด Sanctum
];