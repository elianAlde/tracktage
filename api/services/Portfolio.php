<?php
require_once __DIR__ . '/../utils/connection.php';
require_once __DIR__ . '/PriceService.php';

/** Values holdings, sections and the overall net worth. */
class Portfolio {
    private PDO $db;
    private PriceService $prices;

    public function __construct() {
        $this->db = Connection::get();
        $this->prices = new PriceService();
    }

    public function categories(): array {
        return $this->db->query("SELECT * FROM categories ORDER BY sort_order, id")->fetchAll();
    }

    public function category(int $id): ?array {
        $s = $this->db->prepare("SELECT * FROM categories WHERE id = ?");
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }

    /** Holdings of a section, with their current value. */
    public function assets(int $categoryId, bool $forceRefresh = false): array {
        $category = $this->category($categoryId);
        if (!$category) return [];
        $s = $this->db->prepare("SELECT * FROM assets WHERE category_id = ? ORDER BY name COLLATE NOCASE");
        $s->execute([$categoryId]);
        return array_map(fn($a) => $this->valuate($a, $category, $forceRefresh), $s->fetchAll());
    }

    public function valuate(array $asset, array $category, bool $forceRefresh = false): array {
        $asset['current_value'] = 0.0;
        $asset['cost'] = null;
        $asset['gain'] = null;
        $asset['gain_pct'] = null;
        $asset['quote'] = null;
        $asset['price_error'] = false;
        $asset['stale'] = false;
        $asset['months_since_update'] = null;

        if ($category['kind'] === 'market' && !empty($asset['ticker'])) {
            $quote = $this->prices->quote($asset['ticker'], $forceRefresh);
            $asset['quote'] = $quote;
            if ($quote && $quote['price_base'] !== null) {
                $asset['current_value'] = (float)$asset['quantity'] * $quote['price_base'];
            } else {
                $asset['price_error'] = true;
            }
            if ($asset['avg_cost'] !== null && $asset['quantity'] !== null) {
                $asset['cost'] = (float)$asset['avg_cost'] * (float)$asset['quantity'];
                if (!$asset['price_error']) {
                    $asset['gain'] = $asset['current_value'] - $asset['cost'];
                    $asset['gain_pct'] = $asset['cost'] > 0 ? $asset['gain'] / $asset['cost'] * 100 : null;
                }
            }
        } else {
            $asset['current_value'] = (float)($asset['value'] ?? 0);
            // Manual values never update themselves, so flag the ones nobody has touched in a while
            $updated = new DateTimeImmutable($asset['updated_at'], new DateTimeZone('UTC'));
            $diff = $updated->diff(new DateTimeImmutable('now', new DateTimeZone('UTC')));
            $asset['months_since_update'] = $diff->y * 12 + $diff->m;
            $asset['stale'] = $asset['months_since_update'] >= STALE_MONTHS;
        }
        return $asset;
    }

    /** Totals per section and net worth. Also stores today's snapshot for the history chart. */
    public function summary(bool $forceRefresh = false): array {
        $categories = [];
        $totalAssets = 0.0;
        $totalLiabilities = 0.0;
        $breakdown = [];

        foreach ($this->categories() as $c) {
            $assets = $this->assets((int)$c['id'], $forceRefresh);
            $total = array_sum(array_column($assets, 'current_value'));
            $costs = array_filter(array_column($assets, 'cost'), fn($v) => $v !== null);
            $gains = array_filter(array_column($assets, 'gain'), fn($v) => $v !== null);

            $c['total'] = $total;
            $c['count'] = count($assets);
            $c['gain'] = $gains ? array_sum($gains) : null;
            $c['cost'] = $costs ? array_sum($costs) : null;
            $c['price_errors'] = count(array_filter($assets, fn($a) => $a['price_error']));
            $c['stale_count'] = count(array_filter($assets, fn($a) => $a['stale']));
            $categories[] = $c;

            if ($c['is_liability']) $totalLiabilities += $total; else $totalAssets += $total;
            $breakdown[$c['id']] = round($total, 2);
        }

        $netWorth = $totalAssets - $totalLiabilities;
        $this->saveSnapshot($totalAssets, $totalLiabilities, $netWorth, $breakdown);

        return [
            'currency'          => BASE_CURRENCY,
            'stale_months'      => STALE_MONTHS,
            'stale_total'       => array_sum(array_column($categories, 'stale_count')),
            'total_assets'      => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'net_worth'         => $netWorth,
            'categories'        => $categories,
        ];
    }

    public function history(int $days = 365): array {
        $s = $this->db->prepare("SELECT * FROM snapshots WHERE snapshot_date >= date('now', ?) ORDER BY snapshot_date");
        $s->execute(['-' . $days . ' days']);
        return array_map(function ($r) {
            $r['breakdown'] = json_decode($r['breakdown'], true);
            return $r;
        }, $s->fetchAll());
    }

    private function saveSnapshot(float $assets, float $liabilities, float $net, array $breakdown): void {
        $this->db->prepare(
            "INSERT INTO snapshots (snapshot_date, total_assets, total_liabilities, net_worth, breakdown)
             VALUES (date('now', 'localtime'), ?, ?, ?, ?)
             ON CONFLICT(snapshot_date) DO UPDATE SET total_assets = excluded.total_assets,
                total_liabilities = excluded.total_liabilities, net_worth = excluded.net_worth,
                breakdown = excluded.breakdown"
        )->execute([round($assets, 2), round($liabilities, 2), round($net, 2), json_encode($breakdown)]);
    }
}
