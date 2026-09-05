let allProducts = [];

function fmtRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }

async function loadProducts() {
    const res = await fetch('api/index.php?action=products');
    const data = await res.json();
    allProducts = data.products || [];
    renderList(allProducts);
}

function renderList(list) {
    const el = document.getElementById('prodList');
    if (!list.length) { el.innerHTML = '<div class="text-muted text-center py-4">Tidak ada produk</div>'; return; }
    el.innerHTML = list.map(p => `
        <div class="card mb-2">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-bold">${esc(p.name)}</div>
                        <div class="text-muted small">${esc(p.category_name || 'Tanpa kategori')} · ${fmtRp(p.selling_price)}</div>
                        <div class="small">Stok: <span class="badge ${p.stock <= p.minimum_stock ? 'bg-danger' : 'bg-success'}">${p.stock} ${esc(p.unit)}</span></div>
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown"><i class="bi bi-three-dots-vertical"></i></button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="#" onclick="editProduct(${p.id})">Edit</a></li>
                            <li><a class="dropdown-item" href="#" onclick="adjustStock(${p.id})">Sesuaikan Stok</a></li>
                            <li><a class="dropdown-item text-danger" href="#" onclick="deleteProduct(${p.id})">Hapus</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    `).join('');
}

function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

function openForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Produk';
    ['fId', 'fName', 'fSku', 'fBarcode'].forEach(id => document.getElementById(id).value = '');
    document.getElementById('fCategory').value = '';
    document.getElementById('fUnit').value = 'pcs';
    ['fBuy', 'fSell', 'fStock', 'fMinStock'].forEach(id => document.getElementById(id).value = id === 'fMinStock' ? '5' : '0');
}

function editProduct(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    openForm();
    document.getElementById('modalTitle').textContent = 'Edit Produk';
    document.getElementById('fId').value = p.id;
    document.getElementById('fName').value = p.name;
    document.getElementById('fSku').value = p.sku || '';
    document.getElementById('fBarcode').value = p.barcode || '';
    document.getElementById('fCategory').value = p.category_id || '';
    document.getElementById('fUnit').value = p.unit || 'pcs';
    document.getElementById('fBuy').value = p.purchase_price;
    document.getElementById('fSell').value = p.selling_price;
    document.getElementById('fStock').value = p.stock;
    document.getElementById('fMinStock').value = p.minimum_stock;
    new bootstrap.Modal(document.getElementById('productModal')).show();
}

async function saveProduct() {
    const id = document.getElementById('fId').value;
    const payload = {
        id: id || undefined,
        name: document.getElementById('fName').value,
        sku: document.getElementById('fSku').value,
        barcode: document.getElementById('fBarcode').value,
        category_id: document.getElementById('fCategory').value,
        unit: document.getElementById('fUnit').value,
        purchase_price: parseFloat(document.getElementById('fBuy').value) || 0,
        selling_price: parseFloat(document.getElementById('fSell').value) || 0,
        stock: parseFloat(document.getElementById('fStock').value) || 0,
        minimum_stock: parseFloat(document.getElementById('fMinStock').value) || 0,
    };
    const res = await fetch('api/index.php?action=products', {
        method: id ? 'PUT' : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('productModal')).hide();
    loadProducts();
}

async function deleteProduct(id) {
    if (!confirm('Hapus produk ini?')) return;
    await fetch('api/index.php?action=products&id=' + id, { method: 'DELETE' });
    loadProducts();
}

function adjustStock(id) {
    const p = allProducts.find(x => x.id == id);
    if (!p) return;
    document.getElementById('aId').value = p.id;
    document.getElementById('aName').textContent = p.name;
    document.getElementById('aSysStock').textContent = p.stock + ' ' + (p.unit || 'pcs');
    document.getElementById('aNewStock').value = p.stock;
    new bootstrap.Modal(document.getElementById('adjustModal')).show();
}

async function saveAdjust() {
    const payload = {
        product_id: document.getElementById('aId').value,
        new_stock: parseFloat(document.getElementById('aNewStock').value) || 0,
        reason: document.getElementById('aReason').value
    };
    const res = await fetch('api/index.php?action=adjust_stock', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('adjustModal')).hide();
    loadProducts();
}

document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    document.getElementById('searchProd').addEventListener('input', e => {
        const f = e.target.value.toLowerCase();
        renderList(allProducts.filter(p => p.name.toLowerCase().includes(f) || (p.barcode || '').includes(f) || (p.sku || '').toLowerCase().includes(f)));
    });
    document.getElementById('btnScanProd').addEventListener('click', startProdScanner);
    document.getElementById('btnStopScanProd').addEventListener('click', stopProdScanner);
});

// ==== Barcode Scanner untuk input produk ====
let prodScannerStream = null;

async function startProdScanner() {
    if (!navigator.mediaDevices?.getUserMedia) { alert('Kamera tidak didukung browser ini.'); return; }
    try {
        prodScannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        const video = document.getElementById('prodScannerVideo');
        video.srcObject = prodScannerStream;
        document.getElementById('prodScannerContainer').style.display = 'block';
        await video.play();
        prodScanLoop(video);
    } catch (err) {
        alert('Kamera tidak tersedia. Masukkan barcode manual.');
    }
}

function stopProdScanner() {
    if (prodScannerStream) { prodScannerStream.getTracks().forEach(t => t.stop()); prodScannerStream = null; }
    document.getElementById('prodScannerContainer').style.display = 'none';
}

function prodScanLoop(video) {
    if (!prodScannerStream) return;
    if (window.BarcodeDetector) {
        const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'upc_a'] });
        detector.detect(video).then(codes => {
            if (codes.length) {
                document.getElementById('fBarcode').value = codes[0].rawValue;
                stopProdScanner();
                document.getElementById('fName').focus();
            } else {
                setTimeout(() => prodScanLoop(video), 300);
            }
        }).catch(() => setTimeout(() => prodScanLoop(video), 300));
    } else {
        // Fallback: tidak ada BarcodeDetector, biarkan input manual
        stopProdScanner();
        alert('Browser tidak mendukung deteksi barcode otomatis. Masukkan manual.');
    }
}
