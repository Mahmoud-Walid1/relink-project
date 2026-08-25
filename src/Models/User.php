<?php

require_once __DIR__ . '/../../config/database.php';

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
        $this->ensureDefaultAdmin();
    }

    private function ensureDefaultAdmin(): void {
        $stmt = $this->db->query("SELECT COUNT(*) FROM admins");
        if ($stmt->fetchColumn() == 0) {
            $config = require __DIR__ . '/../../config/app.php';
            $username = $config['default_admin_user'];
            $passHash = password_hash($config['default_admin_pass'], PASSWORD_BCRYPT);
            
            $insert = $this->db->prepare("INSERT INTO admins (username, password_hash) VALUES (:username, :password_hash)");
            $insert->execute([
                ':username' => $username,
                ':password_hash' => $passHash
            ]);
        }
    }

    public function authenticate(string $username, string $password): ?array {
        $stmt = $this->db->prepare("SELECT * FROM admins WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password_hash'])) {
            return $admin;
        }
        return null;
    }

    public function updatePassword(int $adminId, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE admins SET password_hash = :hash WHERE id = :id");
        return $stmt->execute([':hash' => $hash, ':id' => $adminId]);
    }
}
