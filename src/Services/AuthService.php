<?php

class AuthService {
    public static function initSession(): void {
        if (session_status() === PHP_SESSION_NONE) {
            $config = require __DIR__ . '/../../config/app.php';
            session_name($config['session_name']);
            session_start();
        }
    }

    public static function isLoggedIn(): bool {
        self::initSession();
        return isset($_SESSION['admin_id']);
    }

    public static function login(array $admin): void {
        self::initSession();
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
    }

    public static function logout(): void {
        self::initSession();
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_username']);
        session_destroy();
    }

    public static function requireAuth(): void {
        if (!self::isLoggedIn()) {
            require_once __DIR__ . '/../Helpers/ResponseHelper.php';
            ResponseHelper::error('غير مصرح لك بالوصول. يرجى تسجيل الدخول أولاً.', 401);
        }
    }
}
