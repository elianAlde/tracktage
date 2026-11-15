<?php
/**
 * Excel-friendly CSV export: ";" and decimal comma in Italian, "," and decimal point in English.
 * A UTF-8 BOM is always written so Excel detects the encoding.
 */
class Csv {
    public static function download(string $filename, array $header, iterable $rows): never {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-store');

        $sep = LANG === 'it' ? ';' : ',';
        $out = fopen('php://output', 'w');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, $header, $sep, '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'cell'], $row), $sep, '"', '');
        }
        fclose($out);
        exit;
    }

    private static function cell(mixed $v): string {
        if ($v === null) return '';
        if (is_bool($v)) return $v ? t('csv.yes') : t('csv.no');
        if (is_int($v) || is_float($v)) {
            $n = rtrim(rtrim(number_format((float)$v, 6, '.', ''), '0'), '.');
            return LANG === 'it' ? str_replace('.', ',', $n) : $n;
        }
        $v = (string)$v;
        // Prevent spreadsheet formula injection
        return preg_match('/^[=+\-@\t\r]/', $v) ? "'" . $v : $v;
    }
}
