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

        // First run: create the schema and the default sections
        if ($isNew || !$pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetch()) {
            $pdo->exec(file_get_contents(ROOT_PATH . '/database/schema.sql'));
            self::seed($pdo);
            @chmod(DB_PATH, 0660);
        }

        return self::$conn = $pdo;
    }

    /** Default sections, named in the current language. */
    private static function seed(PDO $pdo): void {
        if ($pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn() > 0) return;
        $rows = [
            ['seed.etf',        'market', 'bi-graph-up-arrow',   '#0d6efd', 0],
            ['seed.crypto',     'market', 'bi-currency-bitcoin', '#f7931a', 0],
            ['seed.cash',       'manual', 'bi-wallet2',          '#198754', 0],
            ['seed.emergency',  'manual', 'bi-shield-check',     '#20c997', 0],
            ['seed.vehicles',   'manual', 'bi-car-front',        '#6f42c1', 0],
            ['seed.realestate', 'manual', 'bi-house',            '#fd7e14', 0],
            ['seed.debts',      'manual', 'bi-credit-card',      '#dc3545', 1],
        ];
        $s = $pdo->prepare("INSERT INTO categories (name, kind, icon, color, is_liability, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($rows as $i => [$key, $kind, $icon, $color, $liability]) {
            $s->execute([t($key), $kind, $icon, $color, $liability, ($i + 1) * 10]);
        }
    }
}
