let items = [];
function fmtRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }

async function loadPurchases() {
    const res = await fetch('api/index.php?action=purchases');
    const data = await res.json();
    const el = document.getElementById('purchaseList');
    if (!data.purchases.length) { el.innerHTML = '<div class="text-muted text-center py-4">Belum ada pembelian</div>'; return; }
    el.innerHTML = data.purchases.map(p => `
        <div class="card mb-2">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between">
                    <div>
                        <div class="fw-bold">${p.invoice_number}</div>
                        <div class="text-muted small">${p.purchase_date.substring(0,10)} · ${p.supplier_name || 'Tanpa supplier'}</div>
                    </div>
                    <div class="text-end fw-bold">${fmtRp(p.total)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

function openForm() {
    items = [];
    document.getElementById('fSupplier').value = '';
    document.getElementById('fNote').value = '';
    renderItems();
}

document.getElementById('selProduct')?.addEventListener('change', e => {
    const opt = e.target.options[e.target.selectedIndex];
    if (opt.value) {
        document.getElementById('iQty').value = '1';
        document.getElementById('iPrice').value = opt.dataset.price;
    }
});

function addItem() {
    const sel = document.getElementById('selProduct');
    const id = sel.value;
    if (!id) return;
    const name = sel.options[sel.selectedIndex].text;
    const qty = parseFloat(document.getElementById('iQty').value) || 0;
    const price = parseFloat(document.getElementById('iPrice').value) || 0;
    if (qty <= 0) return;
    const ex = items.find(x => x.product_id == id);
    if (ex) { ex.qty += qty; ex.price = price; } else { items.push({ product_id: id, name, qty, price }); }
    sel.value = ''; document.getElementById('iQty').value = ''; document.getElementById('iPrice').value = '';
    renderItems();
}

function delItem(i) { items.splice(i, 1); renderItems(); }

function renderItems() {
    const b = document.getElementById('itemRows');
    b.innerHTML = items.map((x, i) => `<tr>
        <td class="small">${x.name}</td>
        <td>${x.qty}</td>
        <td>${fmtRp(x.price)}</td>
        <td>${fmtRp(x.qty * x.price)}</td>
        <td><button class="btn btn-sm btn-outline-danger" onclick="delItem(${i})"><i class="bi bi-trash"></i></button></td>
    </tr>`).join('');
    const total = items.reduce((s, x) => s + (x.qty * x.price), 0);
    document.getElementById('grandTotal').textContent = fmtRp(total);
}

async function savePurchase() {
    if (!items.length) { alert('Tambahkan produk dulu'); return; }
    const payload = {
        supplier: document.getElementById('fSupplier').value,
        note: document.getElementById('fNote').value,
        items
    };
    const res = await fetch('api/index.php?action=purchase', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('purchaseModal')).hide();
    loadPurchases();
}

document.addEventListener('DOMContentLoaded', loadPurchases);
