<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireAuth();

$s = Connection::get()->prepare("DELETE FROM assets WHERE id = ?");
$s->execute([(int)input('id', 0)]);
if ($s->rowCount() === 0) Response::error("Voce non trovata", 404);
Response::success(null, "Voce eliminata");
