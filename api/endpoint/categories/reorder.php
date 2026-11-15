<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireAuth();

$ids = array_map('intval', (array)input('ids', []));
$db = Connection::get();
$s = $db->prepare("UPDATE categories SET sort_order = ? WHERE id = ?");
$db->beginTransaction();
foreach ($ids as $i => $id) $s->execute([($i + 1) * 10, $id]);
$db->commit();
Response::success(null, t('api.order_updated'));
