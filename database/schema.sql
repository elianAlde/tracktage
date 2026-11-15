PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL DEFAULT '',
    email TEXT NOT NULL UNIQUE COLLATE NOCASE,
    password TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip TEXT NOT NULL,
    attempted_at INTEGER NOT NULL
);
CREATE INDEX IF NOT EXISTS login_attempts_ip_idx ON login_attempts (ip, attempted_at);

-- Sezioni del patrimonio.
-- kind = 'market' -> asset con ticker e quantità, valore da quotazione live
-- kind = 'manual' -> asset con valore inserito a mano (liquidità, veicoli...)
-- is_liability = 1 -> la sezione viene sottratta (mutuo, finanziamenti...)
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    kind TEXT NOT NULL DEFAULT 'manual' CHECK (kind IN ('market', 'manual')),
    icon TEXT NOT NULL DEFAULT 'bi-wallet2',
    color TEXT NOT NULL DEFAULT '#0d6efd',
    is_liability INTEGER NOT NULL DEFAULT 0,
    sort_order INTEGER NOT NULL DEFAULT 0,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS assets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    category_id INTEGER NOT NULL REFERENCES categories (id) ON DELETE CASCADE,
    name TEXT NOT NULL,
    ticker TEXT NULL,
    quantity REAL NULL,
    avg_cost REAL NULL,          -- prezzo medio di carico per quota (valuta base)
    value REAL NULL,             -- valore manuale (valuta base)
    notes TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS assets_category_idx ON assets (category_id);

-- Cache delle quotazioni (anche tassi di cambio, es. USDEUR=X)
CREATE TABLE IF NOT EXISTS prices (
    symbol TEXT PRIMARY KEY,
    name TEXT NULL,
    price REAL NOT NULL,
    currency TEXT NOT NULL,
    change_pct REAL NULL,
    fetched_at INTEGER NOT NULL
);

-- Una fotografia al giorno del patrimonio, per il grafico storico
CREATE TABLE IF NOT EXISTS snapshots (
    snapshot_date TEXT PRIMARY KEY,
    total_assets REAL NOT NULL,
    total_liabilities REAL NOT NULL,
    net_worth REAL NOT NULL,
    breakdown TEXT NOT NULL      -- JSON {category_id: valore}
);

INSERT INTO categories (name, kind, icon, color, is_liability, sort_order)
SELECT * FROM (VALUES
    ('ETF e Azioni',    'market', 'bi-graph-up-arrow',   '#0d6efd', 0, 10),
    ('Crypto',          'market', 'bi-currency-bitcoin', '#f7931a', 0, 20),
    ('Liquidità',       'manual', 'bi-wallet2',          '#198754', 0, 30),
    ('Fondo emergenza', 'manual', 'bi-shield-check',     '#20c997', 0, 40),
    ('Veicoli',         'manual', 'bi-car-front',        '#6f42c1', 0, 50),
    ('Immobili',        'manual', 'bi-house',            '#fd7e14', 0, 60),
    ('Debiti',          'manual', 'bi-credit-card',      '#dc3545', 1, 70)
)
WHERE NOT EXISTS (SELECT 1 FROM categories);
