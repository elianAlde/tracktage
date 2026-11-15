<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/response.php';

/** Blocca le richieste non autenticate e, per i POST, quelle senza token CSRF valido. */
function requireAuth(): void {
    if (empty($_SESSION['id'])) {
        Response::error("Non autorizzato", 401);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        requireCsrf();
    }
}

function requireCsrf(): void {
    $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? ($_POST['csrf_token'] ?? '');
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$token)) {
        Response::error("Token di sicurezza non valido, ricarica la pagina", 419);
    }
}

function requirePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        Response::error("Metodo non consentito", 405);
    }
}
