<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireCsrf();

const MAX_ATTEMPTS = 5;
const WINDOW_SECONDS = 15 * 60;

$db = Connection::get();
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$db->prepare("DELETE FROM login_attempts WHERE attempted_at < ?")->execute([time() - WINDOW_SECONDS]);
$s = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ?");
$s->execute([$ip]);
if ((int)$s->fetchColumn() >= MAX_ATTEMPTS) {
    Response::error(t('api.too_many_attempts'), 429);
}

$email = (string)input('email', '');
$password = (string)input('password', '');

$s = $db->prepare("SELECT * FROM users WHERE email = ?");
$s->execute([$email]);
$user = $s->fetch();

// Always run password_verify, even for unknown emails, so response times don't reveal which accounts exist
$hash = $user['password'] ?? '$2y$12$invalidinvalidinvalidinvalidinvalidinvalidinvalidinvali';
if (!password_verify($password, $hash) || !$user) {
    $db->prepare("INSERT INTO login_attempts (ip, attempted_at) VALUES (?, ?)")->execute([$ip, time()]);
    usleep(random_int(200000, 500000));
    Response::error(t('api.bad_credentials'), 401);
}

if (password_needs_rehash($user['password'], PASSWORD_DEFAULT)) {
    $db->prepare("UPDATE users SET password = ? WHERE id = ?")->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
}
$db->prepare("DELETE FROM login_attempts WHERE ip = ?")->execute([$ip]);

session_regenerate_id(true);
$_SESSION['id'] = (int)$user['id'];
$_SESSION['first_name'] = $user['first_name'];
$_SESSION['last_name'] = $user['last_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));

Response::success(null, t('api.logged_in'));
