<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requireAuth();

// VACUUM INTO produces a consistent copy, including changes still in the WAL file
$tmp = tempnam(sys_get_temp_dir(), 'tracktage_');
unlink($tmp);
$db = Connection::get();
$db->prepare("VACUUM INTO ?")->execute([$tmp]);

// Login attempts are transient state, not worth backing up
$copy = new PDO('sqlite:' . $tmp);
$copy->exec("DELETE FROM login_attempts");
$copy = null;

header('Content-Type: application/vnd.sqlite3');
header('Content-Disposition: attachment; filename="tracktage-backup-' . date('Y-m-d') . '.sqlite"');
header('Content-Length: ' . filesize($tmp));
header('Cache-Control: no-store');
readfile($tmp);
unlink($tmp);
exit;
