// Cetak ulang nota dari riwayat transaksi
let STORE = { store_name: 'TokoKu', store_address: '', store_phone: '', receipt_footer: 'Terima kasih atas kunjungannya!' };

function fmtRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }
function esc(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

async function loadStore() {
    try {
        const res = await fetch('api/index.php?action=settings');
        const d = await res.json();
        if (d.store_name) Object.assign(STORE, d);
    } catch (e) {}
}

async function reprintReceipt(id) {
    try {
        const res = await fetch('api/index.php?action=sale_detail&id=' + id);
        if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal memuat transaksi'); return; }
        const s = await res.json();
        const tgl = new Date(s.transaction_date.replace(' ', 'T')).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        const jam = new Date(s.transaction_date.replace(' ', 'T')).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const itemRows = (s.items || []).map(i => `
            <tr>
                <td class="text-start">${esc(i.product_name)}<br><small class="text-muted">${i.qty} ${esc(i.unit || 'pcs')} x ${fmtRp(i.price)}</small></td>
                <td class="text-end">${fmtRp(i.subtotal)}</td>
            </tr>
        `).join('');
        document.getElementById('reprintContent').innerHTML = `
            <div class="receipt">
                <div class="fw-bold fs-5">${esc(STORE.store_name)}</div>
                ${STORE.store_address ? `<div class="small">${esc(STORE.store_address)}</div>` : ''}
                ${STORE.store_phone ? `<div class="small">Telp: ${esc(STORE.store_phone)}</div>` : ''}
                <hr class="my-2">
                <div class="small text-muted d-flex justify-content-between">
                    <span>${tgl} ${jam}</span><span>${esc(s.invoice_number)}</span>
                </div>
                <table class="w-100 small my-2"><tbody>${itemRows}</tbody></table>
                <hr class="my-2">
                <div class="d-flex justify-content-between small"><span>Subtotal</span><span>${fmtRp(s.subtotal)}</span></div>
                <div class="d-flex justify-content-between small"><span>Diskon</span><span>-${fmtRp(s.discount)}</span></div>
                <div class="d-flex justify-content-between fw-bold fs-6"><span>TOTAL</span><span>${fmtRp(s.total)}</span></div>
                <div class="d-flex justify-content-between small mt-1"><span>Bayar (${esc(s.payment_method)})</span><span>${fmtRp(s.paid)}</span></div>
                <div class="d-flex justify-content-between small"><span>Kembali</span><span>${fmtRp(s.change_amount)}</span></div>
                <hr class="my-2">
                <div class="small text-muted">- Cetak Ulang -</div>
                <div class="small text-muted">${esc(STORE.receipt_footer)}</div>
            </div>
        `;
        new bootstrap.Modal(document.getElementById('reprintModal')).show();
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

function printReprint() { window.print(); }

document.addEventListener('DOMContentLoaded', loadStore);