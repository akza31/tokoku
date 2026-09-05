<?php
require_once __DIR__ . '/functions.php';

function add_stock(int $productId, float $qty, float $price, string $refType, int $refId, string $note = ''): void {
    $pdo = db();
    $p = $pdo->prepare('SELECT stock, purchase_price FROM products WHERE id = ?');
    $p->execute([$productId]);
    $prod = $p->fetch(PDO::FETCH_ASSOC);
    if (!$prod) throw new Exception('Produk tidak ditemukan');
    $oldStock = (float)$prod['stock'];
    $oldPrice = (float)$prod['purchase_price'];
    $newStock = $oldStock + $qty;
    $avgPrice = $newStock > 0 ? (($oldStock * $oldPrice) + ($qty * $price)) / $newStock : $price;
    $pdo->prepare('UPDATE products SET stock = ?, purchase_price = ? WHERE id = ?')->execute([$newStock, $avgPrice, $productId]);
    $pdo->prepare('INSERT INTO stock_movements (product_id, type, qty, reference_type, reference_id, note) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$productId, 'in', $qty, $refType, $refId, $note]);
}

function reduce_stock(int $productId, float $qty, string $refType, int $refId, string $note = ''): void {
    $pdo = db();
    $p = $pdo->prepare('SELECT stock FROM products WHERE id = ?');
    $p->execute([$productId]);
    $prod = $p->fetch(PDO::FETCH_ASSOC);
    if (!$prod) throw new Exception('Produk tidak ditemukan');
    $pdo->prepare('UPDATE products SET stock = stock - ? WHERE id = ?')->execute([$qty, $productId]);
    $pdo->prepare('INSERT INTO stock_movements (product_id, type, qty, reference_type, reference_id, note) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$productId, 'out', $qty, $refType, $refId, $note]);
}

function create_sale(array $items, float $discount, string $method, float $paid): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $u = $_SESSION['user'] ?? ['id' => 1];
        $count = (int)$pdo->query("SELECT COUNT(*) FROM sales WHERE date(transaction_date) = date('now','localtime')")->fetchColumn();
        $invoice = 'INV' . date('Ymd') . sprintf('%04d', $count + 1);

        $subtotal = 0;
        $rows = [];
        foreach ($items as $it) {
            $st = $pdo->prepare('SELECT id, name, selling_price, purchase_price, stock FROM products WHERE id = ?');
            $st->execute([$it['product_id']]);
            $p = $st->fetch(PDO::FETCH_ASSOC);
            if (!$p) throw new Exception('Produk tidak ditemukan');
            if ((float)$p['stock'] < $it['qty']) throw new Exception('Stok tidak cukup: ' . $p['name']);
            $line = $it['qty'] * $it['price'];
            $subtotal += $line;
            $rows[] = ['p' => $p, 'qty' => $it['qty'], 'price' => $it['price'], 'line' => $line];
        }
        $total = $subtotal - $discount;
        $change = max(0, $paid - $total);

        $pdo->prepare('INSERT INTO sales (invoice_number, user_id, subtotal, discount, total, payment_method, paid, change_amount) VALUES (?,?,?,?,?,?,?,?)')
            ->execute([$invoice, $u['id'], $subtotal, $discount, $total, $method, $paid, $change]);
        $saleId = (int)$pdo->lastInsertId();

        foreach ($rows as $r) {
            $pdo->prepare('INSERT INTO sale_items (sale_id, product_id, qty, price, cost, subtotal) VALUES (?,?,?,?,?,?)')
                ->execute([$saleId, $r['p']['id'], $r['qty'], $r['price'], $r['p']['purchase_price'], $r['line']]);
            reduce_stock((int)$r['p']['id'], $r['qty'], 'sale', $saleId, 'Penjualan ' . $invoice);
        }
        cash_in($total, 'Penjualan', 'sale', $saleId, 'Penjualan ' . $invoice);
        if (function_exists('audit_log')) audit_log('Penjualan', $invoice . ' total ' . $total);

        $pdo->commit();
        return ['id' => $saleId, 'invoice' => $invoice, 'subtotal' => $subtotal, 'discount' => $discount, 'total' => $total, 'paid' => $paid, 'change' => $change, 'method' => $method];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function create_purchase(string $supplier, array $items, string $note = ''): array {
    $pdo = db();
    $pdo->beginTransaction();
    try {
        $u = $_SESSION['user'] ?? ['id' => 1];
        $sid = null;
        if ($supplier !== '') {
            $pdo->prepare('INSERT INTO suppliers (name) VALUES (?)')->execute([$supplier]);
            $sid = (int)$pdo->lastInsertId();
        }
        $total = 0;
        foreach ($items as $it) $total += $it['qty'] * $it['price'];
        $count = (int)$pdo->query("SELECT COUNT(*) FROM purchases WHERE date(purchase_date) = date('now','localtime')")->fetchColumn();
        $invoice = 'PO' . date('Ymd') . sprintf('%04d', $count + 1);
        $pdo->prepare('INSERT INTO purchases (invoice_number, supplier_id, user_id, total, note) VALUES (?,?,?,?,?)')
            ->execute([$invoice, $sid, $u['id'], $total, $note]);
        $purId = (int)$pdo->lastInsertId();
        foreach ($items as $it) {
            $line = $it['qty'] * $it['price'];
            $pdo->prepare('INSERT INTO purchase_items (purchase_id, product_id, qty, price, subtotal) VALUES (?,?,?,?,?)')
                ->execute([$purId, $it['product_id'], $it['qty'], $it['price'], $line]);
            add_stock((int)$it['product_id'], $it['qty'], $it['price'], 'purchase', $purId, 'Pembelian ' . $invoice);
        }
        cash_out($total, 'Pembelian barang', 'purchase', $purId, 'Pembelian ' . $invoice);
        if (function_exists('audit_log')) audit_log('Pembelian', $invoice . ' total ' . $total);
        $pdo->commit();
        return ['id' => $purId, 'invoice' => $invoice, 'total' => $total];
    } catch (Exception $e) {
        $pdo->rollBack();
        throw $e;
    }
}
