<?php
require_once __DIR__ . '/../utils/connection.php';
require_once __DIR__ . '/../utils/logger.php';

/**
 * Market prices from Yahoo Finance (no API key needed), cached in SQLite.
 * Example tickers: VWCE.DE (Xetra), SWDA.MI (Borsa Italiana), AAPL, BTC-EUR.
 */
class PriceService {
    private const UA = 'Mozilla/5.0 (compatible; Tracktage/1.0)';
    private PDO $db;

    public function __construct() { $this->db = Connection::get(); }

    /**
     * Returns ['symbol','name','price','currency','change_pct','fetched_at','price_base','stale'],
     * where price_base is the price converted to BASE_CURRENCY.
     * Falls back to the cached quote when Yahoo is unreachable; null if nothing is known.
     */
    public function quote(string $symbol, bool $force = false): ?array {
        $symbol = strtoupper(trim($symbol));
        $cached = $this->cached($symbol);
        $fresh = $cached && (time() - $cached['fetched_at']) < PRICE_CACHE_MINUTES * 60;

        $quote = ($fresh && !$force) ? $cached : ($this->fetch($symbol) ?? $cached);
        if (!$quote) return null;

        $quote['stale'] = $quote['fetched_at'] < time() - PRICE_CACHE_MINUTES * 60;
        $rate = $this->fxRate($quote['currency'], $force);
        $quote['price_base'] = $rate === null ? null : $quote['price'] * $rate;
        return $quote;
    }

    /** Exchange rate from $currency to BASE_CURRENCY. */
    public function fxRate(string $currency, bool $force = false): ?float {
        $currency = strtoupper($currency);
        if ($currency === BASE_CURRENCY) return 1.0;
        $fx = $this->quoteRaw($currency . BASE_CURRENCY . '=X', $force);
        return $fx ? (float)$fx['price'] : null;
    }

    /** Search instruments by name, ticker or ISIN. */
    public function search(string $query): array {
        $url = 'https://query2.finance.yahoo.com/v1/finance/search?' . http_build_query([
            'q' => $query, 'quotesCount' => 10, 'newsCount' => 0, 'listsCount' => 0,
        ]);
        $json = $this->http($url);
        $out = [];
        foreach ($json['quotes'] ?? [] as $q) {
            if (empty($q['symbol'])) continue;
            $out[] = [
                'symbol'   => $q['symbol'],
                'name'     => $q['longname'] ?? $q['shortname'] ?? $q['symbol'],
                'type'     => $q['typeDisp'] ?? $q['quoteType'] ?? '',
                'exchange' => $q['exchDisp'] ?? $q['exchange'] ?? '',
            ];
        }
        return $out;
    }

    private function quoteRaw(string $symbol, bool $force): ?array {
        $cached = $this->cached($symbol);
        if ($cached && !$force && (time() - $cached['fetched_at']) < PRICE_CACHE_MINUTES * 60) {
            return $cached;
        }
        return $this->fetch($symbol) ?? $cached;
    }

    private function cached(string $symbol): ?array {
        $s = $this->db->prepare("SELECT * FROM prices WHERE symbol = ?");
        $s->execute([$symbol]);
        return $s->fetch() ?: null;
    }

    private function fetch(string $symbol): ?array {
        $url = 'https://query1.finance.yahoo.com/v8/finance/chart/' . rawurlencode($symbol) . '?range=1d&interval=1d';
        $json = $this->http($url);
        $meta = $json['chart']['result'][0]['meta'] ?? null;
        if (!$meta || !isset($meta['regularMarketPrice'])) {
            return null;
        }

        $price = (float)$meta['regularMarketPrice'];
        $currency = $meta['currency'] ?? BASE_CURRENCY;
        // London-listed instruments are quoted in pence (GBp / GBX)
        if ($currency === 'GBp' || $currency === 'GBX') {
            $price /= 100;
            $currency = 'GBP';
        }
        $prev = $meta['chartPreviousClose'] ?? $meta['previousClose'] ?? null;
        $quote = [
            'symbol'     => $symbol,
            'name'       => $meta['longName'] ?? $meta['shortName'] ?? $symbol,
            'price'      => $price,
            'currency'   => strtoupper($currency),
            'change_pct' => $meta['regularMarketChangePercent'] ?? ($prev ? (($meta['regularMarketPrice'] - $prev) / $prev) * 100 : null),
            'fetched_at' => time(),
        ];

        $this->db->prepare(
            "INSERT INTO prices (symbol, name, price, currency, change_pct, fetched_at)
             VALUES (:symbol, :name, :price, :currency, :change_pct, :fetched_at)
             ON CONFLICT(symbol) DO UPDATE SET name = excluded.name, price = excluded.price,
                currency = excluded.currency, change_pct = excluded.change_pct, fetched_at = excluded.fetched_at"
        )->execute($quote);

        return $quote;
    }

    private function http(string $url): ?array {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 4,
            CURLOPT_USERAGENT      => self::UA,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);

        if ($body === false || $code >= 400) {
            if ($code === 404) return json_decode((string)$body, true) ?: null; // unknown ticker
            Logger::error("Price API error", ['url' => $url, 'code' => $code, 'error' => $err]);
            return null;
        }
        return json_decode($body, true) ?: null;
    }
}
