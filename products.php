<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$pdo = db();
$categories = $pdo->query('SELECT * FROM categories ORDER BY name')->fetchAll(PDO::FETCH_ASSOC);
include __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0 fw-bold">Produk</h5>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openForm()">+ Tambah</button>
</div>

<div class="input-group mb-3">
    <span class="input-group-text"><i class="bi bi-search"></i></span>
    <input type="text" id="searchProd" class="form-control" placeholder="Cari produk...">
</div>

<div id="prodList" class="mb-4"></div>

<!-- Modal Produk -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Tambah Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="fId">
                <div class="row g-2">
                    <div class="col-12"><label class="form-label small">Nama *</label><input id="fName" class="form-control" required></div>
                    <div class="col-6"><label class="form-label small">SKU</label><input id="fSku" class="form-control"></div>
                    <div class="col-12">
                        <label class="form-label small">Barcode</label>
                        <div class="input-group">
                            <input id="fBarcode" class="form-control">
                            <button class="btn btn-outline-secondary" type="button" id="btnScanProd" title="Scan barcode"><i class="bi bi-camera"></i></button>
                        </div>
                    </div>
                    <!-- Scanner Camera -->
                    <div class="col-12" id="prodScannerContainer" style="display:none; position:relative; width:100%; height:220px; background:#000; border-radius:8px; overflow:hidden;">
                        <video id="prodScannerVideo" style="width:100%; height:100%; object-fit:cover;"></video>
                        <button type="button" class="btn btn-danger btn-sm" id="btnStopScanProd" style="position:absolute; top:10px; right:10px; z-index:10;">Batal</button>
                    </div>
                    <div class="col-6"><label class="form-label small">Kategori</label>
                        <select id="fCategory" class="form-select">
                            <option value="">-</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6"><label class="form-label small">Satuan</label><input id="fUnit" class="form-control" value="pcs"></div>
                    <div class="col-6"><label class="form-label small">Harga Beli</label><input type="number" id="fBuy" class="form-control" value="0" min="0"></div>
                    <div class="col-6"><label class="form-label small">Harga Jual *</label><input type="number" id="fSell" class="form-control" value="0" min="0"></div>
                    <div class="col-6"><label class="form-label small">Stok Awal</label><input type="number" id="fStock" class="form-control" value="0" min="0"></div>
                    <div class="col-6"><label class="form-label small">Min. Stok</label><input type="number" id="fMinStock" class="form-control" value="5" min="0"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveProduct()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Penyesuaian Stok -->
<div class="modal fade" id="adjustModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Penyesuaian Stok</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="aId">
                <div class="mb-2"><strong id="aName"></strong></div>
                <div class="text-muted small mb-2">Stok sistem: <span id="aSysStock"></span></div>
                <label class="form-label small">Stok Fisik (hasil hitung)</label>
                <input type="number" id="aNewStock" class="form-control mb-2" min="0">
                <label class="form-label small">Alasan</label>
                <select id="aReason" class="form-select">
                    <option>Barang rusak</option>
                    <option>Selisih hitung</option>
                    <option>Barang hilang</option>
                    <option>Kadaluarsa</option>
                    <option>Lainnya</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveAdjust()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script src="assets/js/products.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
