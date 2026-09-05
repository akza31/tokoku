// TokoKu Kasir
let cart = [];
let ALL_PRODUCTS = [];

function fmtRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

async function loadProducts() {
    const res = await fetch('api/index.php?action=products');
    const data = await res.json();
    ALL_PRODUCTS = data.products || [];
}

function renderProducts(filter) {
    const list = document.getElementById('productList');
    if (!filter.trim()) { list.style.display = 'none'; list.innerHTML = ''; return; }
    const f = filter.toLowerCase();
    const found = ALL_PRODUCTS.filter(p =>
        p.name.toLowerCase().includes(f) ||
        (p.sku || '').toLowerCase().includes(f) ||
        (p.barcode || '').includes(f)
    );
    if (!found.length) {
        list.innerHTML = '<div class="text-muted p-3">Tidak ditemukan</div>';
        list.style.display = 'block'; return;
    }
    list.innerHTML = found.map(p => `
        <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" onclick="addToCart(${p.id})">
            <div>
                <div class="fw-bold">${esc(p.name)}</div>
                <div class="text-muted small">${fmtRp(p.selling_price)} · Stok: ${p.stock} ${esc(p.unit)}</div>
            </div>
            <span class="badge bg-primary rounded-pill"><i class="bi bi-plus-lg"></i></span>
        </button>
    `).join('');
    list.style.display = 'block';
}

function addToCart(id) {
    const p = ALL_PRODUCTS.find(x => x.id == id);
    if (!p) return;
    const existing = cart.find(x => x.id == id);
    if (existing) {
        if (existing.qty >= p.stock) { alert('Stok tidak cukup!'); return; }
        existing.qty++;
    } else {
        if (p.stock <= 0) { alert('Stok habis!'); return; }
        cart.push({ id: p.id, name: p.name, price: p.selling_price, qty: 1, stock: p.stock, unit: p.unit });
    }
    renderCart();
    document.getElementById('searchProduct').value = '';
    document.getElementById('productList').style.display = 'none';
}

function changeQty(id, delta) {
    const item = cart.find(x => x.id == id);
    if (!item) return;
    item.qty += delta;
    if (item.qty <= 0) cart = cart.filter(x => x.id != id);
    if (item.qty > item.stock) { item.qty = item.stock; alert('Stok tidak cukup!'); }
    renderCart();
}

function removeItem(id) { cart = cart.filter(x => x.id != id); renderCart(); }

function renderCart() {
    const el = document.getElementById('cartItems');
    if (cart.length === 0) {
        el.innerHTML = '<div class="text-muted text-center py-4">Keranjang kosong</div>';
        updateTotals(); return;
    }
    el.innerHTML = cart.map(item => `
        <div class="list-group-item d-flex justify-content-between align-items-center">
            <div class="flex-grow-1">
                <div class="fw-bold small">${esc(item.name)}</div>
                <div class="text-muted small">${item.qty} x ${fmtRp(item.price)}</div>
            </div>
            <div class="text-end">
                <div class="fw-bold small">${fmtRp(item.qty * item.price)}</div>
                <div class="btn-group btn-group-sm mt-1">
                    <button class="btn btn-outline-secondary" onclick="changeQty(${item.id},-1)">-</button>
                    <button class="btn btn-outline-secondary" onclick="changeQty(${item.id},1)">+</button>
                    <button class="btn btn-outline-danger" onclick="removeItem(${item.id})"><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
    `).join('');
    updateTotals();
}

function updateTotals() {
    const subtotal = cart.reduce((s, i) => s + i.qty * i.price, 0);
    const discount = parseInt(document.getElementById('discount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    const paid = parseInt(document.getElementById('paid').value) || 0;
    document.getElementById('subtotal').textContent = fmtRp(subtotal);
    document.getElementById('total').textContent = fmtRp(total);
    document.getElementById('change').textContent = fmtRp(Math.max(0, paid - total));
}
