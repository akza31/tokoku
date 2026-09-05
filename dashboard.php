<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();
$today = date('Y-m-d');

// Today's stats
$salesToday = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE date(transaction_date) = '$today'")->fetchColumn();
$trxToday = (int)$pdo->query("SELECT COUNT(*) FROM sales WHERE date(transaction_date) = '$today'")->fetchColumn();
$profitToday = (float)$pdo->query("SELECT COALESCE(SUM((si.price - si.cost) * si.qty),0) FROM sale_items si JOIN sales s ON s.id = si.sale_id WHERE date(s.transaction_date) = '$today'")->fetchColumn();
$cashIn = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM cash_transactions WHERE type='in' AND date(transaction_date) = '$today'")->fetchColumn();
$cashOut = (float)$pdo->query("SELECT COALESCE(SUM(amount),0) FROM cash_transactions WHERE type='out' AND date(transaction_date) = '$today'")->fetchColumn();
$balance = cash_balance();

// Yesterday comparison
$yesterday = date('Y-m-d', strtotime('-1 day'));
$salesYesterday = (float)$pdo->query("SELECT COALESCE(SUM(total),0) FROM sales WHERE date(transaction_date) = '$yesterday'")->fetchColumn();
$trend = $salesYesterday > 0 ? round((($salesToday - $salesYesterday) / $salesYesterday) * 100) : 0;

// Low stock
$lowStock = $pdo->query("SELECT name, stock, unit FROM products WHERE stock <= minimum_stock AND status = 1 ORDER BY stock ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);

// Best seller
$best = $pdo->query("SELECT p.name, SUM(si.qty) as total FROM sale_items si JOIN products p ON p.id = si.product_id GROUP BY si.product_id ORDER BY total DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

include __DIR__ . '/includes/header.php';
?>

<div class="text-muted small mb-3"><?= tgl_indo($today . ' ' . date('H:i:s')) ?></div>

<div class="card bg-primary text-white">
    <div class="card-body">
        <div class="small">PENJUALAN HARI INI</div>
        <h2 class="fw-bold mb-0"><?= rp($salesToday) ?></h2>
        <?php if ($salesYesterday > 0): ?>
            <div class="small mt-1">
                <i class="bi <?= $trend >= 0 ? 'bi-arrow-up' : 'bi-arrow-down' ?>"></i>
                <?= abs($trend) ?>% dibanding kemarin
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row g-3 mb-0">
    <div class="col-6">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Transaksi</div>
            <h4 class="mb-0 fw-bold"><?= $trxToday ?></h4>
        </div></div>
    </div>
    <div class="col-6">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Laba Kotor</div>
            <h4 class="mb-0 fw-bold"><?= rp($profitToday) ?></h4>
        </div></div>
    </div>
    <div class="col-6">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Uang Masuk</div>
            <h6 class="mb-0 fw-bold text-success"><?= rp($cashIn) ?></h6>
        </div></div>
    </div>
    <div class="col-6">
        <div class="card h-100"><div class="card-body py-3">
            <div class="text-muted small">Uang Keluar</div>
            <h6 class="mb-0 fw-bold text-danger"><?= rp($cashOut) ?></h6>
        </div></div>
    </div>
</div>

<div class="card bg-success text-white">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div><i class="bi bi-wallet2"></i> Saldo Kas</div>
        <div class="fw-bold fs-5"><?= rp($balance) ?></div>
    </div>
</div>

<?php if ($best): ?>
<div class="card"><div class="card-body py-3">
    <div class="text-muted small"><i class="bi bi-fire"></i> Produk Terlaris</div>
    <div class="fw-bold"><?= htmlspecialchars($best['name']) ?> <span class="text-muted small">(<?= $best['total'] ?> terjual)</span></div>
</div></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <div class="fw-bold mb-2"><i class="bi bi-exclamation-triangle text-warning"></i> Stok Menipis (<?= count($lowStock) ?>)</div>
        <?php if (count($lowStock) === 0): ?>
            <div class="text-muted small">Semua stok aman.</div>
        <?php else: ?>
            <?php foreach ($lowStock as $ls): ?>
                <div class="d-flex justify-content-between border-bottom py-2">
                    <span><?= htmlspecialchars($ls['name']) ?></span>
                    <span class="badge bg-danger"><?= $ls['stock'] ?> <?= htmlspecialchars($ls['unit']) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<a href="kasir.php" class="btn btn-primary btn-lg w-100 mb-4"><i class="bi bi-plus-lg"></i> PENJUALAN</a>

<?php include __DIR__ . '/includes/footer.php'; ?>
