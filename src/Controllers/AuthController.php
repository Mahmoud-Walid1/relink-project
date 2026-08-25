<?php

require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Services/AuthService.php';
require_once __DIR__ . '/../Helpers/ResponseHelper.php';
require_once __DIR__ . '/../Helpers/RequestHelper.php';

class AuthController {
    public function login(): void {
        $input = RequestHelper::getJsonInput();
        $username = trim($input['username'] ?? '');
        $password = trim($input['password'] ?? '');

        if (empty($username) || empty($password)) {
            ResponseHelper::error('اسم المستخدم وكلمة المرور مطلوبان');
            return;
        }

        $userModel = new User();
        $admin = $userModel->authenticate($username, $password);

        if ($admin) {
            AuthService::login($admin);
            ResponseHelper::success(['username' => $admin['username']], 'تم تسجيل الدخول بنجاح');
        } else {
            ResponseHelper::error('بيانات الدخول غير صحيحة');
        }
    }

    public function logout(): void {
        AuthService::logout();
        ResponseHelper::success([], 'تم تسجيل الخروج بنجاح');
    }

    public function me(): void {
        if (AuthService::isLoggedIn()) {
            ResponseHelper::success(['logged_in' => true, 'username' => $_SESSION['admin_username']]);
        } else {
            ResponseHelper::success(['logged_in' => false]);
        }
    }
}
