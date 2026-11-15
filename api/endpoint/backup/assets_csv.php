<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../utils/csv.php';
require_once __DIR__ . '/../../services/Portfolio.php';
requireAuth();

$portfolio = new Portfolio();
$rows = [];
foreach ($portfolio->categories() as $c) {
    foreach ($portfolio->assets((int)$c['id']) as $a) {
        $rows[] = [
            $c['name'],
            $c['kind'] === 'market' ? t('csv.market') : t('csv.manual'),
            (bool)$c['is_liability'],
            $a['name'],
            $a['ticker'],
            $a['quantity'] !== null ? (float)$a['quantity'] : null,
            $a['avg_cost'] !== null ? (float)$a['avg_cost'] : null,
            isset($a['quote']['price_base']) ? round($a['quote']['price_base'], 4) : null,
            round($a['current_value'], 2),
            $a['gain'] !== null ? round($a['gain'], 2) : null,
            $a['notes'],
            $a['updated_at'],
        ];
    }
}

$cur = ['currency' => BASE_CURRENCY];
Csv::download(t('csv.file_items') . '-' . date('Y-m-d') . '.csv', [
    t('csv.section'), t('csv.kind'), t('csv.liability'), t('csv.name'), t('csv.ticker'), t('csv.shares'),
    t('csv.avg_price', $cur), t('csv.price', $cur), t('csv.value', $cur), t('csv.gain', $cur),
    t('csv.notes'), t('csv.updated'),
], $rows);
