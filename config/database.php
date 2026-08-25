<?php

class Database {
    private static ?PDO $instance = null;

    public static function getConnection(): PDO {
        if (self::$instance === null) {
            $config = require __DIR__ . '/app.php';
            $dbPath = $config['db_sqlite_path'];
            
            $dbDir = dirname($dbPath);
            if (!is_dir($dbDir)) {
                @mkdir($dbDir, 0777, true);
            }
            @chmod($dbDir, 0777);

            if (!in_array('sqlite', PDO::getAvailableDrivers())) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'إضافة SQLite PDO غير مفعلة على السيرفر. يرجى تفعيل pdo_sqlite من لوحة التحكم.']);
                exit;
            }

            try {
                self::$instance = new PDO("sqlite:" . $dbPath);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec('PRAGMA foreign_keys = ON;');

                @chmod($dbPath, 0666);

                // Auto initialize schema if tables missing
                $schemaFile = __DIR__ . '/../database/schema.sql';
                if (file_exists($schemaFile)) {
                    $schema = file_get_contents($schemaFile);
                    $statements = array_filter(array_map('trim', explode(';', $schema)));
                    foreach ($statements as $stmt) {
                        if (!empty($stmt)) {
                            self::$instance->exec($stmt);
                        }
                    }
                }
            } catch (PDOException $e) {
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'error' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
                exit;
            }
        }
        return self::$instance;
    }
}
