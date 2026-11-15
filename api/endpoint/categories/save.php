<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
requirePost();
requireAuth();

$id = (int)input('id', 0);
$name = (string)input('name', '');
$kind = input('kind', 'manual') === 'market' ? 'market' : 'manual';
$icon = (string)input('icon', 'bi-wallet2');
$color = (string)input('color', '#0d6efd');
$isLiability = input('is_liability') === '1' ? 1 : 0;

if ($name === '' || mb_strlen($name) > 60) Response::error("Nome sezione obbligatorio (max 60 caratteri)", 422);
if (!preg_match('/^bi-[a-z0-9-]+$/', $icon)) $icon = 'bi-wallet2';
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $color)) $color = '#0d6efd';

$db = Connection::get();
if ($id > 0) {
    $s = $db->prepare("UPDATE categories SET name = ?, kind = ?, icon = ?, color = ?, is_liability = ? WHERE id = ?");
    $s->execute([$name, $kind, $icon, $color, $isLiability, $id]);
    if ($s->rowCount() === 0) Response::error("Sezione non trovata", 404);
    Response::success(['id' => $id], "Sezione aggiornata");
}

$order = (int)$db->query("SELECT COALESCE(MAX(sort_order), 0) + 10 FROM categories")->fetchColumn();
$db->prepare("INSERT INTO categories (name, kind, icon, color, is_liability, sort_order) VALUES (?, ?, ?, ?, ?, ?)")
   ->execute([$name, $kind, $icon, $color, $isLiability, $order]);
Response::success(['id' => (int)$db->lastInsertId()], "Sezione creata", 201);
