<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/Portfolio.php';
requireAuth();

$days = max(7, min(3650, (int)input('days', 365)));
$portfolio = new Portfolio();
Response::success([
    'categories' => $portfolio->categories(),
    'snapshots'  => $portfolio->history($days),
]);
