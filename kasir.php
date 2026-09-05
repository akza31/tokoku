<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
include __DIR__ . '/includes/header.php';
?>

<div id="kasir-app">
    <div class="input-group mb-3">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
        <input type="text" id="searchProduct" class="form-control form-control-lg" placeholder="Cari nama / barcode..." autofocus autocomplete="off">
        <button class="btn btn-outline-secondary" id="btnScan" type="button"><i class="bi bi-camera"></i></button>
    </div>

    <!-- Scanner Camera View -->
    <div id="scannerContainer" style="display:none; position:relative; width:100%; height:250px; background:#000; margin-bottom:15px; border-radius:8px; overflow:hidden;">
        <video id="scannerVideo" style="width:100%; height:100%; object-fit:cover;"></video>
        <button type="button" class="btn btn-danger btn-sm" id="btnStopScan" style="position:absolute; top:10px; right:10px; z-index:10;">Batal</button>
    </div>

    <div id="productList" class="mb-3 list-group" style="max-height: 250px; overflow-y: auto; display:none;"></div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
            <span><i class="bi bi-cart"></i> Keranjang</span>
            <button class="btn btn-sm btn-outline-danger" id="btnClearCart" type="button">Kosongkan</button>
        </div>
        <div class="card-body p-0">
            <div id="cartItems" class="list-group list-group-flush">
                <div class="text-muted text-center py-4" id="emptyCart">Keranjang kosong</div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span><span id="subtotal" class="fw-bold">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between mb-2 align-items-center">
                <span>Diskon</span>
                <input type="number" id="discount" class="form-control form-control-sm text-end" style="width:120px" value="0" min="0">
            </div>
            <hr>
            <div class="d-flex justify-content-between mb-3">
                <span class="fw-bold fs-5">TOTAL</span><span id="total" class="fw-bold fs-5 text-primary">Rp 0</span>
            </div>
            <div class="mb-3">
                <label class="form-label small">Metode Bayar</label>
                <select id="payMethod" class="form-select">
                    <option value="tunai">Tunai</option>
                    <option value="transfer">Transfer</option>
                    <option value="qris">QRIS</option>
                    <option value="ewallet">E-Wallet</option>
                </select>
            </div>
            <div id="tunaiSection">
                <div class="mb-2">
                    <label class="form-label small">Uang Diterima</label>
                    <input type="number" id="paid" class="form-control form-control-lg text-end" value="0" min="0">
                </div>
                <div class="d-flex justify-content-between mb-3">
                    <span>Kembalian</span><span id="change" class="fw-bold text-success">Rp 0</span>
                </div>
            </div>
            <button id="btnPay" class="btn btn-primary btn-lg w-100"><i class="bi bi-check-circle"></i> BAYAR</button>
        </div>
    </div>
</div>

<div class="modal fade" id="receiptModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div id="receiptContent"></div>
                <div class="d-grid gap-2 mt-3">
                    <button class="btn btn-primary" onclick="printReceipt()"><i class="bi bi-printer"></i> Cetak Nota</button>
                    <button class="btn btn-outline-secondary" onclick="location.reload()">Transaksi Baru</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/kasir.js"></script>
<script src="assets/js/kasir2.js"></script>
<script src="assets/js/kasir3.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
