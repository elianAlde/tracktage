<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/Portfolio.php';
requireAuth();

$portfolio = new Portfolio();
$category = $portfolio->category((int)input('category_id', 0));
if (!$category) Response::error("Sezione non trovata", 404);

$assets = $portfolio->assets((int)$category['id'], input('refresh') === '1');
$category['total'] = array_sum(array_column($assets, 'current_value'));
Response::success(['category' => $category, 'assets' => $assets, 'currency' => BASE_CURRENCY]);
