<?php
/**
 * Translations. The language is chosen per device in Settings and stored in the LANG_COOKIE
 * cookie; without it, DEFAULT_LANG from .env is used. Strings live in lang/<code>.php.
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

/** All strings of the current language; missing keys fall back to Italian. */
function translations(): array {
    static $strings = null;
    if ($strings === null) {
        $base = require __DIR__ . '/../lang/it.php';
        $strings = LANG === 'it' ? $base : array_merge($base, require __DIR__ . '/../lang/' . LANG . '.php');
    }
    return $strings;
}

/** Translates a key, replacing {placeholders} with $params. */
function t(string $key, array $params = []): string {
    $s = translations()[$key] ?? $key;
    foreach ($params as $k => $v) {
        $s = str_replace('{' . $k . '}', (string)$v, $s);
    }
    return $s;
}

/** Like t(), HTML-escaped: use it for attributes and plain text. */
function te(string $key, array $params = []): string {
    return htmlspecialchars(t($key, $params), ENT_QUOTES);
}
