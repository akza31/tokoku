<?php
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO('sqlite:' . APP_DB_PATH);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA journal_mode = WAL');
        init_schema($pdo);
        seed_default($pdo);
    }
    return $pdo;
}

function init_schema(PDO $pdo): void {
    $pdo->exec("
    CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        username TEXT UNIQUE NOT NULL,
        password TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'kasir',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT UNIQUE NOT NULL
    );
    CREATE TABLE IF NOT EXISTS suppliers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        phone TEXT,
        address TEXT
    );
    CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sku TEXT,
        barcode TEXT,
        name TEXT NOT NULL,
        category_id INTEGER,
        unit TEXT DEFAULT 'pcs',
        purchase_price REAL DEFAULT 0,
        selling_price REAL NOT NULL DEFAULT 0,
        stock REAL DEFAULT 0,
        minimum_stock REAL DEFAULT 5,
        status INTEGER DEFAULT 1,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    );
    CREATE TABLE IF NOT EXISTS sales (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT UNIQUE NOT NULL,
        user_id INTEGER NOT NULL,
        transaction_date TEXT DEFAULT CURRENT_TIMESTAMP,
        subtotal REAL NOT NULL DEFAULT 0,
        discount REAL DEFAULT 0,
        total REAL NOT NULL DEFAULT 0,
        payment_method TEXT DEFAULT 'tunai',
        paid REAL DEFAULT 0,
        change_amount REAL DEFAULT 0,
        status TEXT DEFAULT 'completed',
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    CREATE TABLE IF NOT EXISTS sale_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        sale_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        qty REAL NOT NULL,
        price REAL NOT NULL,
        cost REAL DEFAULT 0,
        subtotal REAL NOT NULL,
        FOREIGN KEY (sale_id) REFERENCES sales(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    );
    CREATE TABLE IF NOT EXISTS purchases (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        invoice_number TEXT UNIQUE NOT NULL,
        supplier_id INTEGER,
        user_id INTEGER NOT NULL,
        purchase_date TEXT DEFAULT CURRENT_TIMESTAMP,
        total REAL NOT NULL DEFAULT 0,
        note TEXT,
        FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
        FOREIGN KEY (user_id) REFERENCES users(id)
    );
    CREATE TABLE IF NOT EXISTS purchase_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        purchase_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        qty REAL NOT NULL,
        price REAL NOT NULL,
        subtotal REAL NOT NULL,
        FOREIGN KEY (purchase_id) REFERENCES purchases(id) ON DELETE CASCADE,
        FOREIGN KEY (product_id) REFERENCES products(id)
    );
    CREATE TABLE IF NOT EXISTS cash_transactions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        transaction_date TEXT DEFAULT CURRENT_TIMESTAMP,
        type TEXT NOT NULL,
        category TEXT NOT NULL,
        amount REAL NOT NULL,
        reference_type TEXT,
        reference_id INTEGER,
        description TEXT
    );
    CREATE TABLE IF NOT EXISTS stock_movements (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        product_id INTEGER NOT NULL,
        type TEXT NOT NULL,
        qty REAL NOT NULL,
        reference_type TEXT,
        reference_id INTEGER,
        note TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id)
    );
    CREATE TABLE IF NOT EXISTS audit_logs (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        activity TEXT NOT NULL,
        detail TEXT,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    );
    CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY CHECK (id = 1),
        store_name TEXT DEFAULT 'TokoKu',
        store_address TEXT DEFAULT '',
        store_phone TEXT DEFAULT '',
        receipt_footer TEXT DEFAULT 'Terima kasih atas kunjungannya!'
    );
    CREATE TABLE IF NOT EXISTS settings_init (dummy INTEGER);
    DROP TABLE IF EXISTS settings_init;
    CREATE INDEX IF NOT EXISTS idx_sales_date ON sales(transaction_date);
    CREATE INDEX IF NOT EXISTS idx_purchases_date ON purchases(purchase_date);
    CREATE INDEX IF NOT EXISTS idx_cash_date ON cash_transactions(transaction_date);
    CREATE INDEX IF NOT EXISTS idx_products_name ON products(name);
    CREATE INDEX IF NOT EXISTS idx_products_barcode ON products(barcode);
    ");
}

function seed_default(PDO $pdo): void {
    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $stmt = $pdo->prepare('INSERT INTO users (name, username, password, role) VALUES (?, ?, ?, ?)');
        $stmt->execute(['Pemilik', 'owner', password_hash('owner123', PASSWORD_DEFAULT), 'owner']);
        $stmt->execute(['Kasir', 'kasir', password_hash('kasir123', PASSWORD_DEFAULT), 'kasir']);

        $cats = ['Sembako','Minuman','Makanan','Rokok','Peralatan rumah','Toiletries','Snack','Lainnya'];
        $cs = $pdo->prepare('INSERT INTO categories (name) VALUES (?)');
        foreach ($cats as $c) $cs->execute([$c]);

        $ps = $pdo->prepare('INSERT INTO products (sku,barcode,name,category_id,unit,purchase_price,selling_price,stock,minimum_stock) VALUES (?,?,?,?,?,?,?,?,?)');
        $ps->execute(['SKU001','8991001111','Indomie Goreng',3,'pcs',2500,3500,50,10]);
        $ps->execute(['SKU002','8991002222','Aqua 600ml',2,'pcs',2000,3000,48,10]);
        $ps->execute(['SKU003','8991003333','Gula Pasir 1 Kg',1,'kg',14000,18000,25,5]);
        $ps->execute(['SKU004','8991004444','Minyak Goreng 1L',1,'liter',15000,19000,20,5]);
        $ps->execute(['SKU005','8991005555','Teh Botol',2,'pcs',3000,5000,30,5]);

        $pdo->exec("INSERT OR IGNORE INTO settings (id) VALUES (1)");
    }
    // settings selalu tersedia walau DB sudah ada dari versi lama
    $pdo->exec("INSERT OR IGNORE INTO settings (id) VALUES (1)");
}
