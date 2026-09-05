async function loadUsers() {
    const res = await fetch('api/index.php?action=users');
    const data = await res.json();
    const el = document.getElementById('userList');
    if (!res.ok || !data.users) { el.innerHTML = '<div class="text-muted text-center py-3">Tidak ada akses</div>'; return; }
    if (!data.users.length) { el.innerHTML = '<div class="text-muted text-center py-3">Tidak ada pengguna</div>'; return; }
    el.innerHTML = data.users.map(u => `
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <div>
                <div class="fw-bold small">${esc(u.name)} <span class="badge bg-secondary">${u.role}</span></div>
                <div class="text-muted" style="font-size:0.75rem">@${esc(u.username)}</div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="editUser(${u.id}, '${esc(u.name)}', '${esc(u.username)}', '${u.role}')"><i class="bi bi-pencil"></i></button>
                ${u.id !== CURRENT_USER_ID ? `<button class="btn btn-sm btn-outline-danger" onclick="deleteUser(${u.id})"><i class="bi bi-trash"></i></button>` : ''}
            </div>
        </div>
    `).join('');
}

function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function openUser() {
    document.getElementById('userModalTitle').textContent = 'Tambah Pengguna';
    ['uId', 'uName', 'uUsername', 'uPassword'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('uRole').value = 'kasir';
}

function editUser(id, name, username, role) {
    document.getElementById('userModalTitle').textContent = 'Edit Pengguna';
    document.getElementById('uId').value = id;
    document.getElementById('uName').value = name;
    document.getElementById('uUsername').value = username;
    document.getElementById('uPassword').value = '';
    document.getElementById('uRole').value = role;
    new bootstrap.Modal(document.getElementById('userModal')).show();
}

async function saveUser() {
    const payload = {
        id: document.getElementById('uId').value || undefined,
        name: document.getElementById('uName').value,
        username: document.getElementById('uUsername').value,
        password: document.getElementById('uPassword').value,
        role: document.getElementById('uRole').value
    };
    const res = await fetch('api/index.php?action=users', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
    loadUsers();
}

async function deleteUser(id) {
    if (!confirm('Hapus pengguna ini?')) return;
    await fetch('api/index.php?action=users&id=' + id, { method: 'DELETE' });
    loadUsers();
}

async function loadStoreSettings() {
    try {
        const res = await fetch('api/index.php?action=settings');
        const s = await res.json();
        document.getElementById('sName').value = s.store_name || '';
        document.getElementById('sAddress').value = s.store_address || '';
        document.getElementById('sPhone').value = s.store_phone || '';
        document.getElementById('sReceipt').value = s.receipt_footer || '';
        const topTitle = document.querySelector('.top-bar h5');
        if (topTitle && s.store_name) topTitle.textContent = s.store_name;
    } catch (e) {}
}

async function saveStore() {
    const payload = {
        store_name: document.getElementById('sName').value.trim(),
        store_address: document.getElementById('sAddress').value,
        store_phone: document.getElementById('sPhone').value,
        receipt_footer: document.getElementById('sReceipt').value
    };
    if (!payload.store_name) { alert('Nama toko wajib diisi'); return; }
    try {
        const res = await fetch('api/index.php?action=settings', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Gagal');
        await loadStoreSettings();
        alert('Pengaturan tersimpan');
    } catch (err) {
        alert('Gagal menyimpan: ' + err.message);
    }
}

let SUPPLIERS = [];

async function loadSuppliers() {
    const res = await fetch('api/index.php?action=suppliers');
    const data = await res.json();
    SUPPLIERS = data.suppliers || [];
    const el = document.getElementById('supplierList');
    if (!SUPPLIERS.length) { el.innerHTML = '<div class="text-muted text-center py-3">Belum ada supplier</div>'; return; }
    el.innerHTML = SUPPLIERS.map(s => `
        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
            <div>
                <div class="fw-bold small">${esc(s.name)}</div>
                <div class="text-muted" style="font-size:0.75rem">${esc(s.phone || '')} ${s.phone && s.address ? '·' : ''} ${esc(s.address || '')}</div>
            </div>
            <div>
                <button class="btn btn-sm btn-outline-secondary" onclick="editSupplier(${s.id})"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-outline-danger" onclick="deleteSupplier(${s.id})"><i class="bi bi-trash"></i></button>
            </div>
        </div>
    `).join('');
}

function openSupplier() {
    document.getElementById('supplierModalTitle').textContent = 'Tambah Supplier';
    ['supId', 'supName', 'supPhone', 'supAddress'].forEach(id => document.getElementById(id).value = '');
}

function editSupplier(id) {
    const s = SUPPLIERS.find(x => x.id == id);
    if (!s) return;
    document.getElementById('supplierModalTitle').textContent = 'Edit Supplier';
    document.getElementById('supId').value = s.id;
    document.getElementById('supName').value = s.name;
    document.getElementById('supPhone').value = s.phone || '';
    document.getElementById('supAddress').value = s.address || '';
    new bootstrap.Modal(document.getElementById('supplierModal')).show();
}

async function saveSupplier() {
    const name = document.getElementById('supName').value.trim();
    if (!name) { alert('Nama supplier wajib diisi'); return; }
    const payload = {
        id: document.getElementById('supId').value || undefined,
        name,
        phone: document.getElementById('supPhone').value,
        address: document.getElementById('supAddress').value
    };
    const res = await fetch('api/index.php?action=suppliers', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('supplierModal')).hide();
    loadSuppliers();
}

async function deleteSupplier(id) {
    if (!confirm('Hapus supplier ini?')) return;
    await fetch('api/index.php?action=suppliers&id=' + id, { method: 'DELETE' });
    loadSuppliers();
}

document.addEventListener('DOMContentLoaded', () => {
    loadUsers();
    loadStoreSettings();
    loadSuppliers();
});
