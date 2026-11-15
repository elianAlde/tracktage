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

-- Net worth sections.
-- kind = 'market'   -> holdings have a ticker and a quantity, valued at the live price
-- kind = 'manual'   -> holdings have a value entered by hand (cash, vehicles...)
-- is_liability = 1  -> the section is subtracted from net worth (mortgage, loans...)
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
    avg_cost REAL NULL,          -- average cost per share (base currency)
    value REAL NULL,             -- manual value (base currency)
    notes TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS assets_category_idx ON assets (category_id);

-- Price cache, including FX rates (e.g. USDEUR=X)
CREATE TABLE IF NOT EXISTS prices (
    symbol TEXT PRIMARY KEY,
    name TEXT NULL,
    price REAL NOT NULL,
    currency TEXT NOT NULL,
    change_pct REAL NULL,
    fetched_at INTEGER NOT NULL
);

-- One net worth snapshot per day, for the history chart
CREATE TABLE IF NOT EXISTS snapshots (
    snapshot_date TEXT PRIMARY KEY,
    total_assets REAL NOT NULL,
    total_liabilities REAL NOT NULL,
    net_worth REAL NOT NULL,
    breakdown TEXT NOT NULL      -- JSON {category_id: value}
);

-- Default sections are created by Connection::seed() in the configured language
