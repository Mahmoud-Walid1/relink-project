<?php

return [
    'app_name' => 'Relink - نظام إدارة الإنتاج المعرفي',
    'base_url' => '', // Auto-detected if empty
    'db_type' => 'sqlite', // 'sqlite' or 'mysql'
    'db_sqlite_path' => __DIR__ . '/../database/database.sqlite',
    'session_name' => 'relink_admin_session',
    'default_admin_user' => 'admin',
    'default_admin_pass' => 'admin123', // Initial password
    'secret_key' => 'relink_secret_key_2026_safe_hash'
];
