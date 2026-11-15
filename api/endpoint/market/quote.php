<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../services/PriceService.php';
requireAuth();

$quote = (new PriceService())->quote((string)input('symbol', ''));
if (!$quote) Response::error(t('api.ticker_unknown'), 404);
Response::success($quote);
