<?php

class ResponseHelper {
    public static function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void {
        self::json(['success' => false, 'error' => $message], $statusCode);
    }

    public static function success(array $payload = [], string $message = 'تمت العملية بنجاح'): void {
        self::json(array_merge(['success' => true, 'message' => $message], $payload), 200);
    }
}
