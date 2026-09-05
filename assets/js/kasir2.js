document.addEventListener('DOMContentLoaded', () => {
    loadProducts();
    loadStore();
    document.getElementById('searchProduct').addEventListener('input', e => renderProducts(e.target.value));
    document.getElementById('discount').addEventListener('input', updateTotals);
    document.getElementById('paid').addEventListener('input', updateTotals);
    document.getElementById('btnClearCart').addEventListener('click', () => { cart = []; renderCart(); });
    document.getElementById('payMethod').addEventListener('change', e => {
        document.getElementById('tunaiSection').style.display = e.target.value === 'tunai' ? '' : 'none';
    });
    document.getElementById('btnScan').addEventListener('click', startScanner);
    document.getElementById('btnStopScan').addEventListener('click', stopScanner);
    document.getElementById('btnPay').addEventListener('click', pay);
});

let STORE = { store_name: 'TokoKu', store_address: '', store_phone: '', receipt_footer: 'Terima kasih atas kunjungannya!' };
async function loadStore() {
    try {
        const res = await fetch('api/index.php?action=settings');
        const d = await res.json();
        if (d.store_name) Object.assign(STORE, d);
    } catch (e) {}
}

async function pay() {
    if (cart.length === 0) { alert('Keranjang kosong!'); return; }
    const subtotal = cart.reduce((s, i) => s + i.qty * i.price, 0);
    const discount = parseInt(document.getElementById('discount').value) || 0;
    const total = Math.max(0, subtotal - discount);
    const method = document.getElementById('payMethod').value;
    const paid = method === 'tunai' ? (parseInt(document.getElementById('paid').value) || 0) : total;
    if (method === 'tunai' && paid < total) { alert('Uang tidak cukup!'); return; }

    const btn = document.getElementById('btnPay');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    try {
        const res = await fetch('api/index.php?action=sale', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ items: cart.map(i => ({ product_id: i.id, qty: i.qty, price: i.price })), discount, method, paid })
        });
        const data = await res.json();
        if (!res.ok) throw new Error(data.error || 'Gagal');
        showReceipt(data);
    } catch (err) {
        alert('Error: ' + err.message);
    } finally {
        btn.disabled = false; btn.textContent = 'BAYAR';
    }
}

function showReceipt(data) {
    const now = new Date();
    const tgl = now.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
    const jam = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    const itemRows = cart.map(i => `
        <tr>
            <td class="text-start">${esc(i.name)}<br><small class="text-muted">${i.qty} ${esc(i.unit)} x ${fmtRp(i.price)}</small></td>
            <td class="text-end">${fmtRp(i.qty * i.price)}</td>
        </tr>
    `).join('');
    document.getElementById('receiptContent').innerHTML = `
        <div class="receipt">
            <div class="fw-bold fs-5">${esc(STORE.store_name)}</div>
            ${STORE.store_address ? `<div class="small">${esc(STORE.store_address)}</div>` : ''}
            ${STORE.store_phone ? `<div class="small">Telp: ${esc(STORE.store_phone)}</div>` : ''}
            <hr class="my-2">
            <div class="small text-muted d-flex justify-content-between">
                <span>${tgl} ${jam}</span><span>${esc(data.invoice)}</span>
            </div>
            <table class="w-100 small my-2"><tbody>${itemRows}</tbody></table>
            <hr class="my-2">
            <div class="d-flex justify-content-between small"><span>Subtotal</span><span>${fmtRp(data.subtotal)}</span></div>
            <div class="d-flex justify-content-between small"><span>Diskon</span><span>-${fmtRp(data.discount)}</span></div>
            <div class="d-flex justify-content-between fw-bold fs-6"><span>TOTAL</span><span>${fmtRp(data.total)}</span></div>
            <div class="d-flex justify-content-between small mt-1"><span>Bayar (${esc(data.method)})</span><span>${fmtRp(data.paid)}</span></div>
            <div class="d-flex justify-content-between small"><span>Kembali</span><span>${fmtRp(data.change)}</span></div>
            <hr class="my-2">
            <div class="small text-muted">${esc(STORE.receipt_footer)}</div>
        </div>
    `;
    cart = [];
    new bootstrap.Modal(document.getElementById('receiptModal')).show();
}

function printReceipt() {
    window.print();
}
