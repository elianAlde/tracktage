<?php
    require_once __DIR__ . '/../../config/config.php';

    class Connection {
        private static ?PDO $conn = null;

        public static function get(): PDO {
            if (self::$conn !== null) {
                return self::$conn;
            }

            $dir = dirname(DB_PATH);
            if (!is_dir($dir)) {
                mkdir($dir, 0770, true);
            }
            $isNew = !file_exists(DB_PATH);

            try {
                $pdo = new PDO('sqlite:' . DB_PATH, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]);
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }

            $pdo->exec('PRAGMA foreign_keys = ON');
            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA busy_timeout = 5000');

            // Al primo avvio crea tabelle e sezioni di default
            if ($isNew || !$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch()) {
                $pdo->exec(file_get_contents(ROOT_PATH . '/database/schema.sql'));
                @chmod(DB_PATH, 0660);
            }

            return self::$conn = $pdo;
        }
    }
