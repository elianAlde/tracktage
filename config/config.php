<?php
    require_once __DIR__ . '/env.php';

    loadEnv(__DIR__ . '/../.env');

    /*
    |--------------------------------------------------------------------------
    | APP CONFIG
    |--------------------------------------------------------------------------
    */

    define('ROOT_PATH', realpath(__DIR__ . '/..'));
    define('BASE_URL', $_ENV['BASE_URL'] ?? '/');
    define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'tracktage_session');
    define('BASE_CURRENCY', strtoupper($_ENV['BASE_CURRENCY'] ?? 'EUR'));
    define('PRICE_CACHE_MINUTES', (int)($_ENV['PRICE_CACHE_MINUTES'] ?? 15));
    define('STALE_MONTHS', max(1, (int)($_ENV['STALE_MONTHS'] ?? 6)));
    define('APP_DEBUG', filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN));

    require_once __DIR__ . '/i18n.php';

    $dbPath = $_ENV['DB_PATH'] ?? 'database/tracktage.sqlite';
    define('DB_PATH', str_starts_with($dbPath, '/') ? $dbPath : ROOT_PATH . '/' . $dbPath);

    if (APP_DEBUG) {
        ini_set('display_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
    }

    /*
    |--------------------------------------------------------------------------
    | SESSION (cookie blindato: HttpOnly, SameSite=Strict, Secure in HTTPS)
    |--------------------------------------------------------------------------
    */

    if (session_status() === PHP_SESSION_NONE && PHP_SAPI !== 'cli') {
        session_name(SESSION_NAME);
        ini_set('session.use_strict_mode', 1);
        ini_set('session.use_only_cookies', 1);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => parse_url(BASE_URL, PHP_URL_PATH) ?: '/',
            'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();

        // Scadenza per inattività: 7 giorni
        $maxIdle = 60 * 60 * 24 * 7;
        if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > $maxIdle) {
            session_unset();
            session_destroy();
            session_start();
        }
        $_SESSION['last_activity'] = time();

        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }
