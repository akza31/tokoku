function fmtRp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID'); }

async function loadCash() {
    const res = await fetch('api/index.php?action=cash');
    const data = await res.json();
    document.getElementById('balance').textContent = fmtRp(data.balance);
    const el = document.getElementById('cashList');
    if (!data.transactions.length) { el.innerHTML = '<div class="text-muted text-center py-4">Belum ada transaksi</div>'; return; }
    el.innerHTML = data.transactions.map(t => `
        <div class="card mb-2">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold">${t.category}</div>
                    <div class="text-muted small">${t.transaction_date.substring(0,16)} ${t.description ? '· ' + t.description : ''}</div>
                </div>
                <div class="fw-bold ${t.type === 'in' ? 'text-success' : 'text-danger'}">${t.type === 'in' ? '+' : '-'}${fmtRp(t.amount)}</div>
            </div>
        </div>
    `).join('');
}

function openCash(type) {
    document.getElementById('cType').value = type;
    document.getElementById('cashTitle').textContent = type === 'in' ? 'Uang Masuk' : 'Uang Keluar';
    document.getElementById('cAmount').value = '';
    document.getElementById('cDesc').value = '';
    document.getElementById('cCategory').value = type === 'in' ? 'Penjualan' : 'Pengeluaran Operasional';
}

async function saveCash() {
    const amount = parseFloat(document.getElementById('cAmount').value) || 0;
    if (amount <= 0) { alert('Jumlah harus lebih dari 0'); return; }
    const payload = {
        type: document.getElementById('cType').value,
        amount,
        category: document.getElementById('cCategory').value,
        description: document.getElementById('cDesc').value
    };
    const res = await fetch('api/index.php?action=cash', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    if (!res.ok) { const e = await res.json(); alert(e.error || 'Gagal'); return; }
    bootstrap.Modal.getInstance(document.getElementById('cashModal')).hide();
    loadCash();
}

document.addEventListener('DOMContentLoaded', loadCash);
