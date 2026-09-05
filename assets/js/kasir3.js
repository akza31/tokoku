// ==== Barcode Scanner (Camera API) ====
let scannerStream = null;

async function startScanner() {
    try {
        scannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
        const video = document.getElementById('scannerVideo');
        video.srcObject = scannerStream;
        document.getElementById('scannerContainer').style.display = 'block';
        await video.play();
        scanLoop(video);
    } catch (err) {
        alert('Kamera tidak tersedia. Masukkan kode manual pada pencarian.');
    }
}

function stopScanner() {
    if (scannerStream) { scannerStream.getTracks().forEach(t => t.stop()); scannerStream = null; }
    document.getElementById('scannerContainer').style.display = 'none';
}

function scanLoop(video) {
    if (!scannerStream) return;
    if (window.BarcodeDetector) {
        const detector = new BarcodeDetector({ formats: ['ean_13', 'ean_8', 'code_128', 'upc_a'] });
        detector.detect(video).then(codes => {
            if (codes.length) {
                const code = codes[0].rawValue;
                const p = ALL_PRODUCTS.find(x => x.barcode === code);
                if (p) { addToCart(p.id); stopScanner(); }
                else alert('Barcode ' + code + ' tidak ditemukan');
            } else {
                setTimeout(() => scanLoop(video), 300);
            }
        }).catch(() => setTimeout(() => scanLoop(video), 300));
    } else {
        setTimeout(() => scanLoop(video), 500);
    }
}
