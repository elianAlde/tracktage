<?php
    // Con il server di sviluppo PHP (php -S localhost:8000 index.php) questo file fa da router
    if (PHP_SAPI === 'cli-server') {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (preg_match('#^/(database|bin|config|middleware|components|views|api/(utils|services|log))(/|$)|/\.|\.(sqlite|sql|md)$#i', $path)) {
            http_response_code(403);
            exit('Forbidden');
        }
        if ($path !== '/' && is_file(__DIR__ . $path)) {
            return false;
        }
    }

    require_once __DIR__ . '/config/config.php';

    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');

    $publicRoutes = ['login'];
    $isLogged = isset($_SESSION['id']);
    $defaultRoute = $isLogged ? 'dashboard' : 'login';

    $route = $_GET['page'] ?? null;

    if ($route === null) {
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?: '/';

        if ($basePath !== '/' && str_starts_with($path, $basePath)) {
            $path = substr($path, strlen($basePath));
        }

        $route = trim($path, '/');
    }

    if ($route === '' || $route === 'index.php') {
        $route = $defaultRoute;
    }

    if ($route === 'logout') {
        require __DIR__ . '/logout.php';
        exit;
    }

    if (!preg_match('/^[a-zA-Z0-9_-]+$/', $route)) {
        $route = $defaultRoute;
    }

    if (!$isLogged && !in_array($route, $publicRoutes, true)) {
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    if ($isLogged && in_array($route, $publicRoutes, true)) {
        header('Location: ' . BASE_URL . 'dashboard');
        exit;
    }

    $viewFile = __DIR__ . '/views/' . $route . '.php';

    if (!is_file($viewFile)) {
        http_response_code(404);
        $viewFile = __DIR__ . '/views/' . $defaultRoute . '.php';
    }

    require $viewFile;
