<?php
session_start();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/business.php';

function out($data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function err(string $msg, int $code = 400): void { out(['error' => $msg], $code); }
function input(): array { $raw = file_get_contents('php://input'); return json_decode($raw, true) ?? []; }

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

if (!current_user()) err('Belum login', 401);

$pdo = db();
switch ($action) {

case 'products':
    if ($method === 'POST') {
        $d = input();
        $st = $pdo->prepare('INSERT INTO products (sku, barcode, name, category_id, unit, purchase_price, selling_price, stock, minimum_stock) VALUES (?,?,?,?,?,?,?,?,?)');
        $st->execute([$d['sku'] ?? '', $d['barcode'] ?? '', $d['name'], $d['category_id'] ?? null, $d['unit'] ?? 'pcs', $d['purchase_price'] ?? 0, $d['selling_price'], $d['stock'] ?? 0, $d['minimum_stock'] ?? 5]);
        audit_log('Tambah produk', $d['name']);
        out(['id' => (int)$pdo->lastInsertId(), 'ok' => true]);
    }
    if ($method === 'PUT') {
        $d = input();
        $st = $pdo->prepare('UPDATE products SET sku=?, barcode=?, name=?, category_id=?, unit=?, purchase_price=?, selling_price=?, minimum_stock=?, status=? WHERE id=?');
        $st->execute([$d['sku'] ?? '', $d['barcode'] ?? '', $d['name'], $d['category_id'] ?? null, $d['unit'] ?? 'pcs', $d['purchase_price'] ?? 0, $d['selling_price'], $d['minimum_stock'] ?? 5, $d['status'] ?? 1, $d['id']]);
        audit_log('Edit produk', $d['name']);
        out(['ok' => true]);
    }
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        $pdo->prepare('DELETE FROM products WHERE id=?')->execute([$id]);
        audit_log('Hapus produk', 'id=' . $id);
        out(['ok' => true]);
    }
    $rows = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON c.id = p.category_id ORDER BY p.name")->fetchAll(PDO::FETCH_ASSOC);
    out(['products' => $rows]);
    break;

case 'categories':
    if ($method === 'POST') {
        $d = input();
        try {
            $pdo->prepare('INSERT INTO categories (name) VALUES (?)')->execute([$d['name']]);
            out(['id' => (int)$pdo->lastInsertId(), 'ok' => true]);
        } catch (Exception $e) { err('Kategori sudah ada'); }
    }
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        out(['ok' => true]);
    }
    out(['categories' => $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC)]);
    break;

case 'suppliers':
    if ($method === 'POST') {
        $d = input();
        $name = trim($d['name'] ?? '');
        if ($name === '') err('Nama supplier wajib diisi');
        if (!empty($d['id'])) {
            $pdo->prepare('UPDATE suppliers SET name=?, phone=?, address=? WHERE id=?')
                ->execute([$name, $d['phone'] ?? '', $d['address'] ?? '', (int)$d['id']]);
            out(['ok' => true]);
        }
        try {
            $pdo->prepare('INSERT INTO suppliers (name, phone, address) VALUES (?,?,?)')
                ->execute([$name, $d['phone'] ?? '', $d['address'] ?? '']);
            out(['id' => (int)$pdo->lastInsertId(), 'ok' => true]);
        } catch (Exception $e) { err('Gagal menambah supplier'); }
    }
    if ($method === 'DELETE') {
        $id = (int)($_GET['id'] ?? 0);
        $used = $pdo->prepare('SELECT COUNT(*) FROM purchases WHERE supplier_id = ?');
        $used->execute([$id]);
        if ((int)$used->fetchColumn() > 0) err('Supplier dipakai di data pembelian, tidak bisa dihapus');
        $pdo->prepare('DELETE FROM suppliers WHERE id=?')->execute([$id]);
        out(['ok' => true]);
    }
    out(['suppliers' => $pdo->query('SELECT * FROM suppliers ORDER BY name')->fetchAll(PDO::FETCH_ASSOC)]);
    break;

case 'sale':
    if ($method !== 'POST') err('Method not allowed', 405);
    $d = input();
    try {
        $result = create_sale($d['items'], (float)($d['discount'] ?? 0), $d['method'] ?? 'tunai', (float)($d['paid'] ?? 0));
        out($result);
    } catch (Exception $e) { err($e->getMessage(), 422); }
    break;

case 'purchase':
    if ($method !== 'POST') err('Method not allowed', 405);
    $d = input();
    try {
        $result = create_purchase($d['supplier'] ?? '', $d['items'], $d['note'] ?? '');
        out($result);
    } catch (Exception $e) { err($e->getMessage(), 422); }
    break;

case 'sale_detail':
    $id = (int)($_GET['id'] ?? 0);
    $st = $pdo->prepare("SELECT s.*, u.name as user_name FROM sales s LEFT JOIN users u ON u.id = s.user_id WHERE s.id = ?");
    $st->execute([$id]);
    $sale = $st->fetch(PDO::FETCH_ASSOC);
    if (!$sale) err('Transaksi tidak ditemukan');
    $stItems = $pdo->prepare("SELECT si.*, p.name as product_name, p.unit FROM sale_items si JOIN products p ON p.id = si.product_id WHERE si.sale_id = ?");
    $stItems->execute([$id]);
    $sale['items'] = $stItems->fetchAll(PDO::FETCH_ASSOC);
    out($sale);
    break;

case 'purchases':
    $rows = $pdo->query("SELECT p.*, s.name as supplier_name FROM purchases p LEFT JOIN suppliers s ON s.id = p.supplier_id ORDER BY p.purchase_date DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    out(['purchases' => $rows]);
    break;

case 'purchase_items':
    $id = (int)($_GET['id'] ?? 0);
    $rows = $pdo->prepare("SELECT pi.*, p.name as product_name FROM purchase_items pi JOIN products p ON p.id = pi.product_id WHERE pi.purchase_id=?");
    $rows->execute([$id]);
    out(['items' => $rows->fetchAll(PDO::FETCH_ASSOC)]);
    break;

case 'sales':
    $rows = $pdo->query("SELECT s.*, u.name as user_name FROM sales s LEFT JOIN users u ON u.id = s.user_id ORDER BY s.transaction_date DESC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
    out(['sales' => $rows]);
    break;

case 'cash':
    if ($method === 'POST') {
        $d = input();
        if (($d['type'] ?? '') === 'in') {
            cash_in((float)$d['amount'], $d['category'], null, null, $d['description'] ?? '');
        } else {
            cash_out((float)$d['amount'], $d['category'], null, null, $d['description'] ?? '');
        }
        audit_log(($d['type'] ?? '') === 'in' ? 'Pemasukan' : 'Pengeluaran', ($d['category'] ?? '') . ' ' . ($d['amount'] ?? 0));
        out(['ok' => true, 'balance' => cash_balance()]);
    }
    $rows = $pdo->query("SELECT * FROM cash_transactions ORDER BY transaction_date DESC LIMIT 100")->fetchAll(PDO::FETCH_ASSOC);
    out(['transactions' => $rows, 'balance' => cash_balance()]);
    break;

case 'adjust_stock':
    if ($method !== 'POST') err('Method not allowed', 405);
    $d = input();
    $pid = (int)$d['product_id'];
    $p = $pdo->prepare('SELECT stock FROM products WHERE id=?');
    $p->execute([$pid]);
    $prod = $p->fetch(PDO::FETCH_ASSOC);
    if (!$prod) err('Produk tidak ditemukan');
    $diff = (float)$d['new_stock'] - (float)$prod['stock'];
    if ($diff != 0) {
        $pdo->prepare('UPDATE products SET stock=? WHERE id=?')->execute([$d['new_stock'], $pid]);
        $pdo->prepare('INSERT INTO stock_movements (product_id, type, qty, reference_type, note) VALUES (?, ?, ?, ?, ?)')
            ->execute([$pid, $diff > 0 ? 'in' : 'out', abs($diff), 'adjustment', $d['reason'] ?? 'Penyesuaian']);
    }
    out(['ok' => true]);
    break;

case 'users':
    if (current_user()['role'] !== 'owner') err('Akses ditolak', 403);
    if ($method === 'POST') {
        $d = input();
        if (empty($d['id'])) {
            $pdo->prepare('INSERT INTO users (name, username, password, role) VALUES (?,?,?,?)')
                ->execute([$d['name'], $d['username'], password_hash($d['password'], PASSWORD_DEFAULT), $d['role']]);
        } elseif (!empty($d['password'])) {
            $pdo->prepare('UPDATE users SET name=?, username=?, password=?, role=? WHERE id=?')
                ->execute([$d['name'], $d['username'], password_hash($d['password'], PASSWORD_DEFAULT), $d['role'], $d['id']]);
        } else {
            $pdo->prepare('UPDATE users SET name=?, username=?, role=? WHERE id=?')
                ->execute([$d['name'], $d['username'], $d['role'], $d['id']]);
        }
        out(['ok' => true]);
    }
    if ($method === 'DELETE') {
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([(int)($_GET['id'] ?? 0)]);
        out(['ok' => true]);
    }
    out(['users' => $pdo->query('SELECT id, name, username, role FROM users ORDER BY name')->fetchAll(PDO::FETCH_ASSOC)]);
    break;

case 'audit':
    if (current_user()['role'] !== 'owner') err('Akses ditolak', 403);
    out(['logs' => $pdo->query('SELECT a.*, u.name as user_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 50')->fetchAll(PDO::FETCH_ASSOC)]);
    break;

case 'backup':
    if (current_user()['role'] !== 'owner') err('Akses ditolak', 403);
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="tokoku_backup_' . date('Ymd_His') . '.db"');
    readfile(APP_DB_PATH);
    exit;

case 'settings':
    if ($method === 'POST') {
        if (current_user()['role'] !== 'owner') err('Akses ditolak, hanya owner', 403);
        $d = input();
        $pdo->prepare("INSERT INTO settings (id, store_name, store_address, store_phone, receipt_footer) VALUES (1,?,?,?,?)
            ON CONFLICT(id) DO UPDATE SET store_name=excluded.store_name, store_address=excluded.store_address, store_phone=excluded.store_phone, receipt_footer=excluded.receipt_footer")
            ->execute([$d['store_name'] ?? '', $d['store_address'] ?? '', $d['store_phone'] ?? '', $d['receipt_footer'] ?? '']);
        audit_log('Update pengaturan toko', $d['store_name'] ?? '');
        out(['ok' => true]);
    }
    out($pdo->query('SELECT * FROM settings WHERE id=1')->fetch(PDO::FETCH_ASSOC) ?: []);
    break;

default:
    err('Unknown action', 404);
}

