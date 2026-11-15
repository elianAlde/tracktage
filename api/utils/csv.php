<?php
/**
 * CSV compatibile con Excel in italiano: separatore ";", virgola decimale, BOM UTF-8.
 */
class Csv {
    public static function download(string $filename, array $header, iterable $rows): never {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $header, ';', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'cell'], $row), ';', '"', '');
        }
        fclose($out);
        exit;
    }

    private static function cell(mixed $v): string {
        if ($v === null) return '';
        if (is_bool($v)) return $v ? 'sì' : 'no';
        if (is_int($v) || is_float($v)) {
            return str_replace('.', ',', rtrim(rtrim(number_format((float)$v, 6, '.', ''), '0'), '.'));
        }
        $v = (string)$v;
        // Evita che Excel interpreti il testo come formula
        return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    }
}
