# Pache Logistic

**Pache Logistic** adalah platform **Integrated Non-COD Logistics ERP** berbasis web yang menyatukan operasional **OMS**, **WMS**, **TMS**, **Finance Non-COD**, **Master Data Multi-Cabang**, dan **Live Public Tracking** dalam satu sistem terpadu.

Aplikasi ini dirancang dengan kebijakan **Strictly Non-COD** — hanya mendukung pembayaran melalui **Online Payment (QRIS / Virtual Account)**, **Cash di Loket Asal**, dan **B2B Term of Payment (TOP)** — serta dibangun dengan arsitektur modular dan antarmuka *offline-first* tanpa ketergantungan CDN eksternal.

---

## Fitur & Modul Utama

### 1. Public Site & Multi-AWB Tracking (`/`, `/tracking`)
- Halaman landing publik dan pelacakan resi (AWB) secara langsung.
- Mendukung pengecekan timeline pengiriman lengkap mulai dari *Booked* hingga *Delivered* (beserta catatan e-POD).

### 2. OMS — Order Management System (`/oms/shipments`)
- Pembuatan resi pengiriman (*booking*) dengan nomor AWB otomatis dan kalkulasi **actual weight vs volumetric weight** (*chargeable weight*).
- Dukungan layanan **REG**, **EXP**, dan **CRG** dengan *volumetric divisor* dan berat minimum per rute.
- Cetak label pengiriman (`/oms/shipments/{awb}/label`).
- Konfirmasi pembayaran Non-COD (*Online Payment* & *Cash Loket*) serta validasi *credit limit* untuk pelanggan B2B TOP.
- **Non-COD Dispatch Gate**: mencegah pengiriman belum lunas (*unpaid*) keluar dari proses operasional.
- Transisi status pengiriman terkontrol dan pencatatan **e-POD** (nama penerima, relasi, koordinat GPS, dan catatan).

### 3. WMS — Warehouse Management System (`/wms/inventory`)
- Pengelolaan gudang multi-zona (*Dry* dan *Cold Storage*), *storage bins*, serta SKU produk per pelanggan.
- Mendukung aturan alokasi **FIFO** dan **FEFO** (pelacakan *batch number* dan tanggal kedaluwarsa).
- **Immutable Inventory Ledger** untuk pencatatan mutasi stok (*inbound*, *outbound*, dan penyesuaian) dengan validasi ketersediaan stok bin.

### 4. TMS — Transportation Management System (`/tms/manifests`)
- Manajemen armada (*vehicles*) dan pengemudi (*drivers*) lengkap dengan pemantauan masa berlaku **STNK**, **KIR**, dan **SIM** (*fleet compliance*).
- Pembuatan manifest *linehaul* antar cabang, *seal number*, dan uang jalan pengemudi (*driver advance*).
- **Scan Bagging** untuk memasukkan AWB ke manifest, diikuti proses keberangkatan (*depart*) dan kedatangan (*arrive*).

### 5. FIN — Finance Non-COD (`/finance`)
- Pencatatan *Financial Ledger* otomatis untuk pelunasan *Cash Loket*, *Online Payment*, dan penagihan B2B TOP.
- **B2B TOP Auto-Invoicing**: pembuatan faktur konsolidasi otomatis dari resi terkirim lengkap dengan kalkulasi **PPN (11%)** dan **PPh 23 (2%)**.
- Pelacakan jatuh tempo dan penandaan pelunasan faktur (*mark paid*).

### 6. MDM — Master Data Management (`/master`)
- Hirarki multi-cabang (*Head Office*, *Regional Hub*, *Sorting Center*) dan fitur pergantian cabang aktif (*switch branch*).
- Manajemen pelanggan (*Retail* & *B2B Contract*) beserta konfigurasi TOP (*top days*) dan *credit limit*.
- **Rate Engine** berbasis rute asal–tujuan dan tipe layanan.

### 7. Dashboard & Keamanan
- Dashboard operasional dengan KPI bento grid, grafik ringkasan, dan pencarian global ERP (`/api/erp/search`).
- Autentikasi berbasis **Laravel Fortify** dengan dukungan verifikasi email, **Two-Factor Authentication (2FA)**, dan **Passkeys**.

---

## Tech Stack

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend** | PHP `^8.3`, Laravel `^13.17`, Inertia Laravel `^3.0`, Laravel Fortify, Wayfinder |
| **Frontend** | Vue `^3.5`, Inertia Vue 3 `^3.0`, TypeScript, Tailwind CSS `^4.1`, Reka UI, Lucide Vue, Vite+ |
| **Database** | PostgreSQL 16/17/18 (+ PostGIS) |
| **Design System** | Cool-Paper Editorial SaaS & Modular Workspace — 100% offline-first ([DESIGN.md](DESIGN.md)) |
| **Testing & QA** | PHPUnit 12, Larastan (PHPStan), Laravel Pint, `vue-tsc` |

---

## Struktur Direktori

```text
app/
├── Models/                 # Eloquent models (Shipment, Manifest, Invoice, InventoryLedger, dll.)
└── Modules/                # Arsitektur modular per domain
    ├── Dashboard/          # Dashboard KPI & global search
    ├── OMS/                # Order Management, Booking, Payment Gate, e-POD
    ├── WMS/                # Warehouse & Immutable Inventory Ledger
    ├── TMS/                # Manifests, Scan Bagging, Fleet Compliance
    ├── FIN/                # Finance Non-COD & B2B Auto-Invoicing
    ├── MDM/                # Branches, Customers, Tariffs
    ├── PublicSite/         # Landing page & Public Multi-AWB Tracking
    └── Shared/             # Domain Enums & Value Objects (AwbNumber, Dimensions, dll.)
database/
├── migrations/             # Skema tabel master, OMS, WMS, TMS, Finance, Auth
└── seeders/                # DatabaseSeeder dengan data cabang, pelanggan, AWB, gudang, armada
resources/js/
├── components/             # Komponen UI (Reka UI) & komponen domain ERP
├── composables/            # Scanner barcode, chargeable weight, appearance, 2FA
├── layouts/                # AppLayout, AuthLayout, ErpLayout
└── pages/                  # Halaman Vue/Inertia (Dashboard, OMS, WMS, TMS, Finance, Master, Public)
tests/
└── Feature/                # Feature tests termasuk PacheNonCodErpTest
```

---

## Prasyarat

- **PHP** >= 8.3 (dengan ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `xml`, `ctype`, `fileinfo`)
- **Composer** >= 2.x
- **Node.js** >= 20.x & **npm**
- **PostgreSQL** 16 / 17 / 18

---

## Instalasi & Menjalankan Secara Lokal

1. **Clone repositori**
   ```bash
   git clone https://github.com/101Man101/Pache-Logistic.git
   cd Pache-Logistic
   ```

2. **Install dependensi backend & frontend**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Sesuaikan konfigurasi database PostgreSQL di `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=pache_logistic
   DB_USERNAME=postgres
   DB_PASSWORD=password_postgres_anda
   ```

4. **Migrasi & seed database**
   ```bash
   php artisan migrate --seed
   ```

5. **Jalankan server pengembangan**
   ```bash
   composer dev
   ```
   Atau jalankan secara terpisah:
   ```bash
   php artisan serve
   npm run dev
   ```

   Untuk build produksi frontend:
   ```bash
   npm run build
   ```

---

## Akun Demo (Hasil Seeder)

Setelah menjalankan `php artisan migrate --seed`, akun berikut siap digunakan untuk login (password default semua akun: **`password`**):

| Nama | Email | Peran (`role`) | Cabang |
| :--- | :--- | :--- | :--- |
| Raka Pradipta | `admin@pachelogistic.id` | `super_admin` | JKT — Jakarta Pusat Head Office |
| Nadia Kusuma | `oms@pachelogistic.id` | `admin_oms` | JKT — Jakarta Pusat Head Office |
| Dimas Santoso | `kasir@pachelogistic.id` | `finance_cashier` | JKT — Jakarta Pusat Head Office |
| Bagus Wijaya | `wms@pachelogistic.id` | `operator_wms` | JKT — Jakarta Pusat Head Office |
| Arif Hidayat | `tms@pachelogistic.id` | `dispatcher_tms` | SBY — Surabaya Regional Hub |

Seeder juga menyiapkan 6 cabang (`JKT`, `BDG`, `SBY`, `SMG`, `MDN`, `MKS`), 6 pelanggan (B2B TOP & Retail), matriks tarif, 10 contoh pengiriman (`PCH-JKT-261121-00001` hingga `PCH-JKT-261121-00010`), data gudang/stok, armada/pengemudi, serta manifest linehaul untuk langsung diuji coba.

---

## Pengujian & Kualitas Kode

```bash
# Menjalankan seluruh rangkaian test, lint check (Pint), dan static analysis (PHPStan)
composer test

# Format kode PHP dengan Laravel Pint
composer lint

# Analisis tipe statis PHP (Larastan)
composer types:check

# Cek tipe TypeScript / Vue
npm run types:check

# Cek lint/format frontend
npm run check

# Menjalankan seluruh pengecekan CI (frontend + backend)
composer ci:check
```

---

## Dokumentasi Desain

Panduan visual, palet warna (*Light/Dark Mode*), tipografi self-hosted (`Plus Jakarta Sans` & `Source Sans 3`), dan aturan antarmuka terdokumentasi lengkap di [DESIGN.md](DESIGN.md).

---

## Lisensi

Proyek ini menggunakan lisensi [MIT](https://opensource.org/licenses/MIT).
