<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/Portfolio.php';
require_once __DIR__ . '/../../services/PriceService.php';
requirePost();
requireAuth();

$portfolio = new Portfolio();
$id = (int)input('id', 0);
$category = $portfolio->category((int)input('category_id', 0));
if (!$category) Response::error("Sezione non trovata", 404);

$name = (string)input('name', '');
$notes = (string)input('notes', '');
$ticker = null; $quantity = null; $avgCost = null; $value = null;

if ($category['kind'] === 'market') {
    $ticker = strtoupper((string)input('ticker', ''));
    $quantity = inputNumber('quantity');
    $avgCost = inputNumber('avg_cost');
    if (!preg_match('/^[A-Z0-9.\-=^]{1,20}$/', $ticker)) Response::error("Ticker non valido", 422);
    if ($quantity === null || $quantity < 0) Response::error("Inserisci il numero di quote", 422);

    $quote = (new PriceService())->quote($ticker);
    if (!$quote) Response::error("Ticker '$ticker' non trovato su Yahoo Finance", 422);
    if ($name === '') $name = $quote['name'];
} else {
    $value = inputNumber('value');
    if ($value === null) Response::error("Inserisci il valore", 422);
}

if ($name === '' || mb_strlen($name) > 120) Response::error("Nome obbligatorio (max 120 caratteri)", 422);

$db = Connection::get();
$params = [$name, $ticker, $quantity, $avgCost, $value, $notes ?: null];

if ($id > 0) {
    $s = $db->prepare("UPDATE assets SET name = ?, ticker = ?, quantity = ?, avg_cost = ?, value = ?, notes = ?,
                       updated_at = CURRENT_TIMESTAMP WHERE id = ? AND category_id = ?");
    $s->execute([...$params, $id, $category['id']]);
    if ($s->rowCount() === 0) Response::error("Voce non trovata", 404);
    Response::success(['id' => $id], "Voce aggiornata");
}

$db->prepare("INSERT INTO assets (name, ticker, quantity, avg_cost, value, notes, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)")
   ->execute([...$params, $category['id']]);
Response::success(['id' => (int)$db->lastInsertId()], "Voce aggiunta", 201);
