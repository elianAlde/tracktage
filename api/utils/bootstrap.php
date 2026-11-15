<?php
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/connection.php';
    require_once __DIR__ . '/response.php';
    require_once __DIR__ . '/logger.php';
    require_once __DIR__ . '/auth.php';

    set_exception_handler(function (Throwable $e) {
        Logger::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
        Response::error(APP_DEBUG ? $e->getMessage() : "Errore interno", 500);
    });

    /** Legge un parametro da POST/GET. */
    function input(string $key, mixed $default = null): mixed {
        $v = $_POST[$key] ?? $_GET[$key] ?? $default;
        return is_string($v) ? trim($v) : $v;
    }

    /** Converte "1.234,56" o "1234.56" in float; stringa vuota -> null. */
    function inputNumber(string $key): ?float {
        $v = input($key);
        if ($v === null || $v === '') return null;
        $v = str_replace(' ', '', (string)$v);
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        if (!is_numeric($v)) {
            Response::error("Valore numerico non valido per '$key'", 422);
        }
        return (float)$v;
    }
