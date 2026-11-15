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

    /**
     * Converte un numero scritto all'italiana in float; stringa vuota -> null.
     * "1.234,56" -> 1234.56 · "7.400" -> 7400 · "0,12" -> 0.12 · "12.5" -> 12.5
     */
    function inputNumber(string $key): ?float {
        $v = input($key);
        if ($v === null || $v === '') return null;
        $v = str_replace([' ', "\u{00A0}", '€'], '', (string)$v);
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $v)) {
            // Solo punti a gruppi di 3 cifre: sono separatori delle migliaia
            $v = str_replace('.', '', $v);
        }
        if (!is_numeric($v)) {
            Response::error("Valore numerico non valido per '$key'", 422);
        }
        return (float)$v;
    }
