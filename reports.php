<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();
$from = $_GET['from'] ?? date('Y-m-01');
$to = $_GET['to'] ?? date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) $from = date('Y-m-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) $to = date('Y-m-d');

$st = $pdo->prepare("SELECT COALESCE(SUM(subtotal),0), COALESCE(SUM(discount),0), COALESCE(SUM(total),0), COUNT(*) FROM sales WHERE date(transaction_date) BETWEEN ? AND ?");
$st->execute([$from, $to]);
list($subtotal, $discount, $revenue, $trxCount) = $st->fetch(PDO::FETCH_NUM);

$st = $pdo->prepare("SELECT COALESCE(SUM((si.price - si.cost) * si.qty),0) FROM sale_items si JOIN sales s ON s.id = si.sale_id WHERE date(s.transaction_date) BETWEEN ? AND ?");
$st->execute([$from, $to]);
$profit = (float)$st->fetchColumn();

$st = $pdo->prepare("SELECT type, COALESCE(SUM(amount),0) FROM cash_transactions WHERE date(transaction_date) BETWEEN ? AND ? GROUP BY type");
$st->execute([$from, $to]);
$cashFlow = ['in' => 0, 'out' => 0];
foreach ($st->fetchAll(PDO::FETCH_NUM) as $r) $cashFlow[$r[0]] = (float)$r[1];

$st = $pdo->prepare("SELECT p.name, SUM(si.qty) as qty, SUM(si.subtotal) as revenue FROM sale_items si JOIN sales s ON s.id = si.sale_id JOIN products p ON p.id = si.product_id WHERE date(s.transaction_date) BETWEEN ? AND ? GROUP BY si.product_id ORDER BY qty DESC LIMIT 10");
$st->execute([$from, $to]);
$best = $st->fetchAll(PDO::FETCH_ASSOC);

$st = $pdo->prepare("SELECT s.*, u.name as user_name FROM sales s LEFT JOIN users u ON u.id = s.user_id WHERE date(s.transaction_date) BETWEEN ? AND ? ORDER BY s.transaction_date DESC LIMIT 20");
$st->execute([$from, $to]);
$sales = $st->fetchAll(PDO::FETCH_ASSOC);

$lowStock = $pdo->query("SELECT name, stock, unit, minimum_stock FROM products WHERE stock <= minimum_stock AND status = 1 ORDER BY stock ASC")->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Laporan</h5>
    <span class="text-muted small"><?= tgl_indo($from) ?> — <?= tgl_indo($to) ?></span>
</div>

<form method="GET" class="row g-2 mb-3">
    <div class="col-5"><input type="date" name="from" value="<?= $from ?>" class="form-control form-control-sm"></div>
    <div class="col-5"><input type="date" name="to" value="<?= $to ?>" class="form-control form-control-sm"></div>
    <div class="col-2"><button class="btn btn-primary btn-sm w-100">Filter</button></div>
</form>

<div class="row g-3 mb-3">
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Pendapatan</div><h6 class="fw-bold mb-0 text-primary"><?= rp($revenue) ?></h6></div></div></div>
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Laba Kotor</div><h6 class="fw-bold mb-0 text-success"><?= rp($profit) ?></h6></div></div></div>
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Uang Masuk</div><h6 class="fw-bold mb-0 text-success"><?= rp($cashFlow['in']) ?></h6></div></div></div>
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Uang Keluar</div><h6 class="fw-bold mb-0 text-danger"><?= rp($cashFlow['out']) ?></h6></div></div></div>
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Jumlah Transaksi</div><h6 class="fw-bold mb-0"><?= $trxCount ?></h6></div></div></div>
    <div class="col-6"><div class="card"><div class="card-body py-3"><div class="text-muted small">Rata-rata</div><h6 class="fw-bold mb-0"><?= rp($trxCount ? $revenue / $trxCount : 0) ?></h6></div></div></div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold"><i class="bi bi-trophy"></i> Produk Terlaris</div>
    <div class="card-body p-0">
        <?php if (!$best): ?>
            <div class="text-muted text-center py-3">Belum ada penjualan</div>
        <?php else: foreach ($best as $b): ?>
            <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                <span class="small"><?= htmlspecialchars($b['name']) ?></span>
                <span class="small"><span class="badge bg-primary"><?= $b['qty'] ?></span> · <?= rp($b['revenue']) ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold"><i class="bi bi-receipt"></i> Riwayat Transaksi</div>
    <div class="card-body p-0">
        <?php if (!$sales): ?>
            <div class="text-muted text-center py-3">Belum ada transaksi</div>
        <?php else: foreach ($sales as $s): ?>
            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <div>
                    <div class="small fw-bold"><?= $s['invoice_number'] ?></div>
                    <div class="text-muted" style="font-size:0.75rem"><?= $s['user_name'] ?: '-' ?> · <?= date('d/m H:i', strtotime($s['transaction_date'])) ?> · <?= $s['payment_method'] ?></div>
                    <div class="fw-bold small"><?= rp($s['total']) ?></div>
                </div>
                <button class="btn btn-sm btn-outline-primary" onclick="reprintReceipt(<?= (int)$s['id'] ?>)"><i class="bi bi-printer"></i> Cetak Ulang</button>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header bg-white fw-bold"><i class="bi bi-exclamation-triangle text-warning"></i> Stok Menipis</div>
    <div class="card-body p-0">
        <?php if (!$lowStock): ?>
            <div class="text-muted text-center py-3">Semua stok aman</div>
        <?php else: foreach ($lowStock as $ls): ?>
            <div class="d-flex justify-content-between px-3 py-2 border-bottom">
                <span class="small"><?= htmlspecialchars($ls['name']) ?></span>
                <span class="badge bg-danger"><?= $ls['stock'] ?> <?= htmlspecialchars($ls['unit']) ?> / min <?= $ls['minimum_stock'] ?></span>
            </div>
        <?php endforeach; endif; ?>
    </div>
</div>

<!-- Modal Cetak Ulang -->
<div class="modal fade" id="reprintModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div id="reprintContent"></div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary" onclick="printReprint()"><i class="bi bi-printer"></i> Cetak</button>
                    <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<style>@media print { body * { visibility: hidden; } #reprintModal, #reprintModal * { visibility: visible; } #reprintModal { position: absolute; inset: 0; } .modal-backdrop { display: none; } }</style>
<script src="assets/js/reports.js"></script>
