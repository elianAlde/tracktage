<?php
    require_once __DIR__ . '/../config/config.php';

    if (!isset($_SESSION['id'])) {
        header("Location: " . BASE_URL . "login");
        exit;
    }
