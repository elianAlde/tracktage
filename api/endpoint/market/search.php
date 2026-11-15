<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/PriceService.php';
requireAuth();

$q = (string)input('q', '');
if (mb_strlen($q) < 2) Response::success([]);
Response::success((new PriceService())->search($q));
