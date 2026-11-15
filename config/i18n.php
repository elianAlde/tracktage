<?php
/**
 * Multilingua: la lingua si sceglie da Impostazioni ed è salvata nel cookie LANG_COOKIE
 * (per dispositivo). Senza cookie si usa DEFAULT_LANG del .env.
 */

const LANGS = ['it' => 'it-IT', 'en' => 'en-GB'];
const LANG_COOKIE = 'tracktage_lang';

$lang = $_COOKIE[LANG_COOKIE] ?? '';
if (!isset(LANGS[$lang])) {
    $lang = strtolower($_ENV['DEFAULT_LANG'] ?? 'it');
}
if (!isset(LANGS[$lang])) {
    $lang = 'it';
}

define('LANG', $lang);
define('LOCALE', LANGS[$lang]);

/** Tutte le stringhe della lingua corrente (con fallback sull'italiano). */
function translations(): array {
    static $strings = null;
    if ($strings === null) {
        $base = require __DIR__ . '/../lang/it.php';
        $strings = LANG === 'it' ? $base : array_merge($base, require __DIR__ . '/../lang/' . LANG . '.php');
    }
    return $strings;
}

/** Traduce una chiave sostituendo i segnaposto {nome}. */
function t(string $key, array $params = []): string {
    $s = translations()[$key] ?? $key;
    foreach ($params as $k => $v) {
        $s = str_replace('{' . $k . '}', (string)$v, $s);
    }
    return $s;
}

/** Come t() ma con escape HTML, per attributi e testo semplice. */
function te(string $key, array $params = []): string {
    return htmlspecialchars(t($key, $params), ENT_QUOTES);
}
