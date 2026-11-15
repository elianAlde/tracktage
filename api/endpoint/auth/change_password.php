<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireAuth();

$current = (string)input('current_password', '');
$new = (string)input('new_password', '');

if (strlen($new) < 10) {
    Response::error(t('api.password_short'), 422);
}

$db = Connection::get();
$s = $db->prepare("SELECT password FROM users WHERE id = ?");
$s->execute([$_SESSION['id']]);
$hash = $s->fetchColumn();

if (!$hash || !password_verify($current, $hash)) {
    Response::error(t('api.password_wrong'), 422);
}

$db->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?")
   ->execute([password_hash($new, PASSWORD_DEFAULT), $_SESSION['id']]);
session_regenerate_id(true);

Response::success(null, t('api.password_updated'));
