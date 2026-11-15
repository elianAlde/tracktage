<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireAuth();

$s = Connection::get()->prepare("DELETE FROM assets WHERE id = ?");
$s->execute([(int)input('id', 0)]);
if ($s->rowCount() === 0) Response::error(t('api.item_not_found'), 404);
Response::success(null, t('api.item_deleted'));
