# 🏪 TokoKu - Aplikasi Kasir Toko Kelontong

Aplikasi Point of Sale (POS) berbasis web yang dirancang khusus untuk toko kelontong. Dibangun dengan PHP, SQLite, dan Bootstrap 5 — ringan, mudah diinstall, dan siap digunakan tanpa konfigurasi server database yang rumit.

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple)
![License](https://img.shields.io/badge/license-MIT-green)

---

## ✨ Fitur Utama

| Fitur | Deskripsi |
|-------|-----------|
| 📊 **Dashboard** | Ringkasan penjualan hari ini, tren perbandingan, produk terlaris, stok menipis, dan saldo kas |
| 💰 **Kasir (POS)** | Transaksi penjualan cepat dengan pencarian produk, diskon, multi metode bayar, dan cetak struk |
| 📦 **Manajemen Produk** | CRUD produk dengan kategori, SKU, barcode, stok minimum, harga beli & jual |
| 📥 **Pembelian** | Catat pembelian dari supplier, otomatis update stok |
| 💵 **Keuangan** | Catatan kas masuk & keluar, saldo otomatis |
| 📈 **Laporan** | Laporan penjualan, laba, dan stok harian/mingguan/bulanan |
| ⚙️ **Pengaturan** | Nama toko, alamat, telepon, catatan struk, manajemen user & supplier |
| 💾 **Backup & Restore** | Download backup database SQLite |
| 📱 **PWA** | Progressive Web App — bisa diinstall di HP seperti aplikasi native |

---

## 🛠️ Technologie

- **Backend:** PHP 7.4+
- **Database:** SQLite3 (tanpa install database terpisah)
- **Frontend:** Bootstrap 5.3, Bootstrap Icons
- **PWA:** Service Worker + Manifest

---

## 🚀 Instalasi

### Prasyarat
- Web server dengan PHP 7.4 atau lebih baru (XAMPP, Laragon, WAMP, atau PHP built-in server)
- Modul `pdo_sqlite` aktif di PHP

### Cara Install

1. **Clone atau copy** project ini ke folder web server:
   ```
   # Contoh untuk XAMPP
   C:\xampp\htdocs\tokel
   ```

2. **Jalankan** di terminal / command prompt:
   ```powershell
   cd C:\xampp\htdocs\tokel
   php -S localhost:8080
   ```

3. **Buka browser** dan akses:
   ```
   http://localhost:8080
   ```

4. **Login** dengan akun default:
   | Role | Username | Password |
   |------|----------|----------|
   | Owner | `owner` | `owner123` |
   | Kasir | `kasir` | `kasir123` |

> Database SQLite akan otomatis dibuat beserta data sample saat pertama kali dijalankan.

---

## 📁 Struktur Project

```
tokel/
├── api/
│   └── index.php           # API endpoint untuk semua operasi CRUD
├── assets/
│   ├── css/
│   │   └── app.css         # Style tambahan
│   ├── icon.png            # Icon aplikasi
│   └── js/
│       ├── app.js          # Utility umum
│       ├── finance.js      # Logic halaman keuangan
│       ├── kasir.js        # Logic kasir (POS)
│       ├── products.js     # Logic halaman produk
│       ├── purchases.js    # Logic halaman pembelian
│       ├── reports.js      # Logic halaman laporan
│       └── settings.js     # Logic halaman pengaturan
├── data/
│   └── tokoku.db           # Database SQLite (auto-generated)
├── includes/
│   ├── auth.php            # Autentikasi & session
│   ├── business.php        # Logic bisnis
│   ├── config.php          # Konfigurasi aplikasi
│   ├── db.php              # Koneksi DB & schema
│   ├── footer.php          # Footer template
│   ├── functions.php       # Helper functions
│   └── header.php          # Header template
├── dashboard.php           # Halaman dashboard
├── kasir.php               # Halaman kasir / POS
├── products.php            # Halaman manajemen produk
├── purchases.php           # Halaman pembelian
├── finance.php             # Halaman keuangan
├── reports.php             # Halaman laporan
├── settings.php            # Halaman pengaturan
├── login.php               # Halaman login
├── logout.php              # Logout handler
├── index.php               # Entry point (redirect ke dashboard/login)
├── manifest.json           # PWA manifest
├── sw.js                   # Service Worker
└── README.md
```

---

## 🔐 Hak Akses

| Role | Akses |
|------|-------|
| **Owner** | Full access — termasuk pengaturan user, backup database |
| **Kasir** | Penjualan, melihat produk, laporan terbatas |

---

## 📱 Install sebagai Aplikasi (PWA)

1. Buka aplikasi di browser mobile (Chrome/Edge)
2. Klik menu **"Add to Home Screen"** atau **"Install App"**
3. Aplikasi akan muncul di home screen seperti aplikasi native

---

## ⚙️ Konfigurasi

Edit file `includes/config.php` untuk mengubah pengaturan default:

```php
define('APP_NAME', 'TokoKu');           // Nama aplikasi
define('APP_DB_PATH', __DIR__ . '/../data/tokoku.db'); // Path database
define('APP_TIMEZONE', 'Asia/Jakarta');  // Timezone
define('APP_CURRENCY', 'Rp');            // Simbol mata uang
```

> Nama toko, alamat, dan catatan struk dapat diubah langsung melalui halaman **Pengaturan** di aplikasi.

---

## 📝 Database

- Menggunakan **SQLite** — tidak perlu install MySQL/PostgreSQL
- File database tersimpan di `data/tokoku.db`
- Auto-create schema dan seed data saat pertama kali dijalankan
- Tabel utama: `users`, `categories`, `products`, `sales`, `sale_items`, `purchases`, `purchase_items`, `cash_transactions`, `stock_movements`, `audit_logs`, `settings`

---

## 📄 License

MIT License - Silakan gunakan dan modifikasi sesuai kebutuhan.

---

> Dibuat dengan ❤️ untuk toko kelontong Indonesia 🇮🇩
