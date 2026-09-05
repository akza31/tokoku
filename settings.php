<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$user = current_user();
$pdo = db();
$settings = $pdo->query('SELECT * FROM settings LIMIT 1')->fetch(PDO::FETCH_ASSOC);
include __DIR__ . '/includes/header.php';
?>

<h5 class="fw-bold mb-3">Pengaturan</h5>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold"><i class="bi bi-shop"></i> Info Toko</div>
    <div class="card-body">
        <form id="storeForm">
            <div class="mb-2"><label class="form-label small">Nama Toko</label><input class="form-control" id="sName" value="<?= htmlspecialchars($settings['store_name'] ?? APP_NAME) ?>"></div>
            <div class="mb-2"><label class="form-label small">Alamat</label><textarea class="form-control" id="sAddress" rows="2"><?= htmlspecialchars($settings['store_address'] ?? '') ?></textarea></div>
            <div class="mb-2"><label class="form-label small">Telepon</label><input class="form-control" id="sPhone" value="<?= htmlspecialchars($settings['store_phone'] ?? '') ?>"></div>
            <div class="mb-2"><label class="form-label small">Catatan Struk</label><input class="form-control" id="sReceipt" value="<?= htmlspecialchars($settings['receipt_footer'] ?? 'Terima kasih atas kunjungannya!') ?>"></div>
            <button type="button" class="btn btn-primary w-100" onclick="saveStore()">Simpan</button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-truck"></i> Master Supplier</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#supplierModal" onclick="openSupplier()">+ Tambah</button>
    </div>
    <div class="card-body p-0" id="supplierList"></div>
</div>

<?php if ($user['role'] === 'owner'): ?>
<div class="card mb-3">
    <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
        <span><i class="bi bi-people"></i> Pengguna</span>
        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#userModal" onclick="openUser()">+ Tambah</button>
    </div>
    <div class="card-body p-0" id="userList"></div>
</div>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold"><i class="bi bi-cloud-arrow-down"></i> Backup & Restore</div>
    <div class="card-body">
        <p class="small text-muted mb-2">Download seluruh database untuk backup.</p>
        <a href="api/index.php?action=backup" class="btn btn-outline-primary w-100"><i class="bi bi-download"></i> Download Backup</a>
    </div>
</div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header bg-white fw-bold"><i class="bi bi-info-circle"></i> Aplikasi</div>
    <div class="card-body">
        <div class="small text-muted">Versi: 1.0.0</div>
        <div class="small text-muted">Login sebagai: <strong><?= htmlspecialchars($user['name']) ?></strong> (<?= $user['role'] ?>)</div>
    </div>
</div>

<a href="logout.php" class="btn btn-outline-danger w-100 mb-4"><i class="bi bi-box-arrow-right"></i> Keluar</a>

<!-- Supplier Modal -->
<div class="modal fade" id="supplierModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="supplierModalTitle">Tambah Supplier</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="supId">
                <div class="mb-2"><label class="form-label small">Nama *</label><input class="form-control" id="supName"></div>
                <div class="mb-2"><label class="form-label small">Telepon</label><input class="form-control" id="supPhone"></div>
                <div class="mb-2"><label class="form-label small">Alamat</label><textarea class="form-control" id="supAddress" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveSupplier()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<!-- User Modal -->
<div class="modal fade" id="userModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="userModalTitle">Pengguna</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="uId">
                <div class="mb-2"><label class="form-label small">Nama</label><input class="form-control" id="uName"></div>
                <div class="mb-2"><label class="form-label small">Username</label><input class="form-control" id="uUsername"></div>
                <div class="mb-2"><label class="form-label small">Password</label><input type="password" class="form-control" id="uPassword" placeholder="Kosongkan jika tidak diubah"></div>
                <div class="mb-2"><label class="form-label small">Role</label>
                    <select id="uRole" class="form-select">
                        <option value="kasir">Kasir</option>
                        <option value="owner">Owner</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveUser()">Simpan</button>
            </div>
        </div>
    </div>
</div>

<script>
const CURRENT_USER_ID = <?= (int)$user['id'] ?>;
</script>
<script src="assets/js/settings.js"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
