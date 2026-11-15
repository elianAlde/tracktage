<?php
class Logger {
    public static function error(string $message, array $context = []): void {
        $logDir = __DIR__ . "/../log/";
        if (!is_dir($logDir)) {
            mkdir($logDir, 0770, true);
            file_put_contents($logDir . '.htaccess', "Require all denied\n");
        }
        $file = $logDir . date("Y-m") . ".txt";
        $date = date("Y-m-d H:i:s");
        $contextString = !empty($context) ? json_encode($context, JSON_UNESCAPED_UNICODE) : "";
        file_put_contents($file, "[{$date}] ERROR: {$message} {$contextString}" . PHP_EOL, FILE_APPEND);
    }
}
