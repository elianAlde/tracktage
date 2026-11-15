<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/connection.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/auth.php';

set_exception_handler(function (Throwable $e) {
    Logger::error($e->getMessage(), ['file' => $e->getFile(), 'line' => $e->getLine()]);
    Response::error(APP_DEBUG ? $e->getMessage() : t('api.internal'), 500);
});

/** Reads a request parameter from POST or GET, trimmed. */
function input(string $key, mixed $default = null): mixed {
    $v = $_POST[$key] ?? $_GET[$key] ?? $default;
    return is_string($v) ? trim($v) : $v;
}

/**
 * Parses a number typed in the current language's format; empty string -> null.
 * it: "1.234,56" -> 1234.56 · "7.400" -> 7400 · "0,12" -> 0.12 · "12.5" -> 12.5
 * en: "1,234.56" -> 1234.56 · "7,400" -> 7400 · "0.12" -> 0.12
 */
function inputNumber(string $key): ?float {
    $v = input($key);
    if ($v === null || $v === '') return null;
    $v = str_replace([' ', "\u{00A0}", '€'], '', (string)$v);
    if (LANG === 'en') {
        $v = str_replace(',', '', $v);
    } elseif (str_contains($v, ',')) {
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    } elseif (preg_match('/^-?\d{1,3}(\.\d{3})+$/', $v)) {
        // Dots only in groups of three digits are thousands separators
        $v = str_replace('.', '', $v);
    }
    if (!is_numeric($v)) {
        Response::error(t('api.invalid_number', ['field' => $key]), 422);
    }
    return (float)$v;
}
