<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();
$products = $pdo->query('SELECT id, name, unit, purchase_price FROM products ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Pembelian</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#purchaseModal" onclick="openForm()">+ Catat</button>
</div>

<div id="purchaseList" class="mb-4"></div>

<div class="modal fade" id="purchaseModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Pembelian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-2">
                    <label class="form-label small">Supplier</label>
                    <input id="fSupplier" class="form-control" placeholder="Nama supplier (opsional)">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Catatan</label>
                    <input id="fNote" class="form-control">
                </div>
                <div class="card mb-2">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <select id="selProduct" class="form-select form-select-sm">
                                    <option value="">Pilih produk...</option>
                                    <?php foreach ($products as $pr): ?>
                                        <option value="<?= $pr['id'] ?>" data-price="<?= $pr['purchase_price'] ?>" data-unit="<?= htmlspecialchars($pr['unit']) ?>"><?= htmlspecialchars($pr['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2"><input id="iQty" type="number" min="1" class="form-control form-control-sm" placeholder="Qty"></div>
                            <div class="col-md-3"><input id="iPrice" type="number" min="0" class="form-control form-control-sm" placeholder="Harga"></div>
                            <div class="col-md-1"><button type="button" class="btn btn-sm btn-primary w-100" onclick="addItem()">+</button></div>
                        </div>
                    </div>
                </div>
                <table class="table table-sm">
                    <thead><tr><th>Produk</th><th>Qty</th><th>Harga</th><th>Subtotal</th><th></th></tr></thead>
                    <tbody id="itemRows"></tbody>
                </table>
                <div class="text-end fw-bold fs-5">Total: <span id="grandTotal">Rp 0</span></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="savePurchase()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/purchases.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
