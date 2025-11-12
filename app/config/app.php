<?php

return [
    'app_name' => 'AI Video Editor',
    'app_url' => 'http://localhost',

    'upload_max_size' => 2147483648,
    'allowed_video_types' => ['video/mp4', 'video/webm', 'video/quicktime', 'video/x-msvideo'],
    'allowed_extensions' => ['mp4', 'webm', 'mov', 'avi'],

    'storage_path' => __DIR__ . '/../storage',
    'public_path' => __DIR__ . '/../public',

    'timezone' => 'UTC',
];
