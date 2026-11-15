<?php
/**
 * Crea l'utente (o reimposta la password se l'email esiste già).
 * Uso: php bin/create-user.php email@esempio.it "Nome" "Cognome"
 * La password viene chiesta in modo nascosto (o letta da stdin se non c'è un terminale).
 */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once __DIR__ . '/../api/utils/connection.php';

[$_, $email, $first, $last] = array_pad($argv, 4, '');
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Uso: php bin/create-user.php email \"Nome\" [\"Cognome\"]\n");
    exit(1);
}

$tty = function_exists('posix_isatty') ? posix_isatty(STDIN) : true;
if ($tty) {
    echo "Password (min 10 caratteri): ";
    system('stty -echo');
    $password = trim(fgets(STDIN));
    system('stty echo');
    echo "\nRipeti password: ";
    system('stty -echo');
    $confirm = trim(fgets(STDIN));
    system('stty echo');
    echo "\n";
    if ($password !== $confirm) { fwrite(STDERR, "Le password non coincidono\n"); exit(1); }
} else {
    $password = trim(fgets(STDIN));
}

if (strlen($password) < 10) { fwrite(STDERR, "Password troppo corta (min 10 caratteri)\n"); exit(1); }

$db = Connection::get();
$hash = password_hash($password, PASSWORD_DEFAULT);
$s = $db->prepare("SELECT id FROM users WHERE email = ?");
$s->execute([$email]);

if ($id = $s->fetchColumn()) {
    $db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")->execute([$hash, $id]);
    echo "Password aggiornata per $email\n";
} else {
    $db->prepare("INSERT INTO users (email, first_name, last_name, password) VALUES (?, ?, ?, ?)")
       ->execute([$email, $first ?: 'Utente', $last, $hash]);
    echo "Utente $email creato\n";
}
