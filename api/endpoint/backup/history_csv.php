<?php
require_once __DIR__ . '/../../utils/bootstrap.php';
require_once __DIR__ . '/../../utils/csv.php';
require_once __DIR__ . '/../../services/Portfolio.php';
requireAuth();

$portfolio = new Portfolio();
$categories = $portfolio->categories();

$rows = [];
foreach ($portfolio->history(36500) as $s) {
    $row = [$s['snapshot_date'], (float)$s['total_assets'], (float)$s['total_liabilities'], (float)$s['net_worth']];
    foreach ($categories as $c) {
        $row[] = isset($s['breakdown'][$c['id']]) ? (float)$s['breakdown'][$c['id']] : null;
    }
    $rows[] = $row;
}

Csv::download(t('csv.file_history') . '-' . date('Y-m-d') . '.csv',
    array_merge([t('csv.date'), t('csv.assets'), t('csv.liabilities'), t('csv.net_worth')], array_column($categories, 'name')),
    $rows
);
