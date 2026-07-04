<?php
/** Application configuration for cPanel/Core PHP deployments. */
return [
    'app_url' => getenv('APP_URL') ?: 'https://example.com',
    'db' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'name' => getenv('DB_NAME') ?: 'smart_id',
        'user' => getenv('DB_USER') ?: 'root',
        'pass' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
    ],
    'security' => [
        'session_name' => 'SMARTIDSESSID',
        'session_timeout' => 1800,
        'max_login_attempts' => 5,
        'upload_max_bytes' => 5242880,
        'allowed_uploads' => ['jpg','jpeg','png','webp','pdf','doc','docx','xls','xlsx','zip'],
    ],
];
