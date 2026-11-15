<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/response.php';

/** Rejects unauthenticated requests and, for POST, requests without a valid CSRF token. */
function requireAuth(): void {
    if (empty($_SESSION['id'])) {
        Response::error(t('api.unauthorized'), 401);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireCsrf();
    }
}

function requireCsrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        Response::error(t('api.csrf'), 419);
    }
}

function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error(t('api.method'), 405);
    }
}
