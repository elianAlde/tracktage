<?php
class Response {
    public static function success(mixed $data = null, ?string $message = null, int $httpCode = 200): never {
        self::send(["success" => true, "message" => $message, "data" => $data], $httpCode);
    }

    public static function error(string $message, int $httpCode = 500): never {
        self::send(["success" => false, "message" => $message], $httpCode);
    }

    private static function send(array $payload, int $httpCode): never {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
