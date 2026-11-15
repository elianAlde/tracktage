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
            $c['kind'] === 'market' ? 'mercato' : 'manuale',
            (bool)$c['is_liability'],
            $a['name'],
            $a['ticker'],
            $a['quantity'] !== null ? (float)$a['quantity'] : null,
            $a['avg_cost'] !== null ? (float)$a['avg_cost'] : null,
            $a['quote']['price_base'] ?? null,
            round($a['current_value'], 2),
            $a['gain'] !== null ? round($a['gain'], 2) : null,
            $a['notes'],
            $a['updated_at'],
        ];
    }
}

Csv::download('tracktage-voci-' . date('Y-m-d') . '.csv', [
    'Sezione', 'Tipo', 'Passività', 'Nome', 'Ticker', 'Quote', 'Prezzo medio (' . BASE_CURRENCY . ')',
    'Prezzo attuale (' . BASE_CURRENCY . ')', 'Valore (' . BASE_CURRENCY . ')', 'Guadagno (' . BASE_CURRENCY . ')',
    'Note', 'Aggiornato il (UTC)',
], $rows);
