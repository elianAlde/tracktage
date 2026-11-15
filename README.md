# Tracktage

PWA personale (mobile-first) per tenere traccia del proprio patrimonio: ETF e azioni con quotazione
live, crypto, liquidità, fondo emergenza, veicoli, immobili e debiti. Pensata per un solo utente.

## Stack

| Layer | Tecnologia |
|---|---|
| Backend | PHP 8.1+ (vanilla) |
| Database | SQLite (file unico, creato in automatico) |
| Frontend | jQuery 3.7, Bootstrap 5, Chart.js |
| Quotazioni | Yahoo Finance (nessuna API key) con cache in DB |
| Auth | Sessioni PHP, bcrypt, CSRF, rate-limit sul login |

## Struttura

```
api/
  endpoint/   auth, categories, assets, market, portfolio  (un file per azione)
  services/   PriceService (quotazioni + cambi), Portfolio (valorizzazione)
  utils/      bootstrap, connection (SQLite + migrazione automatica), auth, response, logger
bin/          create-user.php (CLI)
database/     schema.sql + file .sqlite (non servito dal web)
views/        login, dashboard, category, statistics, settings
script/       JS per pagina + app.js (helper comuni)
```

## Setup

```bash
cp .env.example .env              # e imposta BASE_URL
php bin/create-user.php tua@email.it "Nome" "Cognome"
php -S localhost:8000 index.php   # sviluppo locale
```

Al primo avvio il database viene creato con le sezioni di default. Non c'è registrazione dal web:
l'unico modo per creare un utente (o reimpostare la password) è lo script CLI.

In produzione: Apache con `mod_rewrite` (il `.htaccess` blocca `.env`, `database/`, `bin/`…),
HTTPS obbligatorio e `SESSION_SECURE=true`, `APP_DEBUG=false`. La cartella `database/` deve essere
scrivibile dall'utente del web server. Ancora meglio: metti `DB_PATH` fuori dalla document root.

## Come funziona

- **Sezioni**: ognuna è di tipo *quotazione di mercato* (ticker + quote) o *valore manuale*.
  Una sezione può essere marcata come *passività* e viene sottratta dal patrimonio netto.
- **Ticker**: formato Yahoo Finance, con suffisso della borsa: `VWCE.DE` (Xetra), `SWDA.MI`
  (Borsa Italiana), `IWDA.AS` (Amsterdam), `AAPL` (USA), `BTC-EUR` (crypto). Nel form si può
  cercare anche per ISIN o nome. Prezzi in altre valute vengono convertiti in `BASE_CURRENCY`.
- **Prezzo medio** (opzionale): abilita il calcolo di guadagno/perdita.
- **Storico**: ogni giorno in cui apri la dashboard viene salvato uno snapshot; la pagina
  *Andamento* mostra l'evoluzione nel tempo.

## Backup

Basta copiare il file `database/tracktage.sqlite` (meglio con `sqlite3 database/tracktage.sqlite ".backup backup.sqlite"`).
