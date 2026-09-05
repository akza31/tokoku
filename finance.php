<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
include __DIR__ . '/includes/header.php';
?>

<h5 class="fw-bold mb-3">Kas & Keuangan</h5>

<div class="card bg-success text-white mb-3">
    <div class="card-body d-flex justify-content-between align-items-center">
        <div><i class="bi bi-wallet2"></i> Saldo Kas</div>
        <div class="fw-bold fs-4" id="balance">Rp 0</div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-6">
        <button class="btn btn-success w-100 py-3" data-bs-toggle="modal" data-bs-target="#cashModal" onclick="openCash('in')">
            <i class="bi bi-plus-circle fs-4 d-block"></i> Uang Masuk
        </button>
    </div>
    <div class="col-6">
        <button class="btn btn-danger w-100 py-3" data-bs-toggle="modal" data-bs-target="#cashModal" onclick="openCash('out')">
            <i class="bi bi-dash-circle fs-4 d-block"></i> Uang Keluar
        </button>
    </div>
</div>

<div id="cashList" class="mb-4"></div>

<div class="modal fade" id="cashModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="cashTitle">Uang Masuk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="cType">
                <div class="mb-2">
                    <label class="form-label small">Jumlah</label>
                    <input type="number" id="cAmount" class="form-control form-control-lg" min="0">
                </div>
                <div class="mb-2">
                    <label class="form-label small">Kategori</label>
                    <select id="cCategory" class="form-select">
                        <option value="Penjualan">Penjualan</option>
                        <option value="Modal">Modal / Setoran</option>
                        <option value="Pengeluaran Operasional">Operasional</option>
                        <option value="Pembelian">Pembelian</option>
                        <option value="Listrik">Listrik</option>
                        <option value="Sewa">Sewa</option>
                        <option value="Gaji">Gaji</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label small">Keterangan</label>
                    <input id="cDesc" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveCash()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/finance.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
