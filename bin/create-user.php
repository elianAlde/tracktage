<?php
/**
 * Creates the user, or resets the password if the email already exists.
 *
 *   php bin/create-user.php you@example.com "First" "Last"
 *
 * The password is prompted without echo, or read from stdin when not running in a terminal.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../api/utils/connection.php';

const MIN_PASSWORD_LENGTH = 10;

function fail(string $message): never {
    fwrite(STDERR, $message . "\n");
    exit(1);
}

function promptHidden(string $label): string {
    echo $label;
    system('stty -echo');
    $value = trim((string)fgets(STDIN));
    system('stty echo');
    echo "\n";
    return $value;
}

[, $email, $firstName, $lastName] = array_pad($argv, 4, '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Usage: php bin/create-user.php email "First name" ["Last name"]');
}

$interactive = function_exists('posix_isatty') ? posix_isatty(STDIN) : true;
if ($interactive) {
    $password = promptHidden('Password (min ' . MIN_PASSWORD_LENGTH . ' characters): ');
    if ($password !== promptHidden('Repeat password: ')) {
        fail('Passwords do not match');
    }
} else {
    $password = trim((string)fgets(STDIN));
}

if (strlen($password) < MIN_PASSWORD_LENGTH) {
    fail('Password too short (min ' . MIN_PASSWORD_LENGTH . ' characters)');
}

$db = Connection::get();
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);
$userId = $stmt->fetchColumn();

if ($userId) {
    $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
       ->execute([$hash, $userId]);
    echo "Password updated for $email\n";
} else {
    $db->prepare("INSERT INTO users (email, first_name, last_name, password) VALUES (?, ?, ?, ?)")
       ->execute([$email, $firstName ?: 'User', $lastName, $hash]);
    echo "User $email created\n";
}
