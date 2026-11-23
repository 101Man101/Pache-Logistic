# Pache Logistic

**Pache Logistic** adalah platform **Integrated Non-COD Logistics ERP** berbasis web yang menyatukan operasional **OMS**, **WMS**, **TMS**, **Finance Non-COD**, **Master Data Multi-Cabang**, dan **Live Public Tracking** dalam satu sistem terpadu.

Aplikasi ini dirancang dengan kebijakan **Strictly Non-COD** — hanya mendukung pembayaran melalui **Online Payment (QRIS / Virtual Account)**, **Cash di Loket Asal**, dan **B2B Term of Payment (TOP)** — serta dibangun dengan pendekatan **Clean Architecture**, **RBAC per role**, dan antarmuka *offline-first* tanpa ketergantungan CDN eksternal.

---

## Fitur & Modul Utama

### 1. Public Site & Multi-AWB Tracking (`/`, `/tracking`)
- Halaman landing editorial bertema kargo logistik (aset gambar self-hosted di `public/images/landing/`) dan pelacakan resi (AWB) secara langsung.
- Mendukung pengecekan timeline pengiriman lengkap mulai dari *Booked* hingga *Delivered* (beserta catatan e-POD).

### 2. OMS — Order Management System (`/oms/shipments`)
- Pembuatan resi pengiriman multi-koli (`/oms/shipments/create`) dengan nomor AWB atomik dan kalkulasi otomatis **actual weight vs volumetric weight** (*chargeable weight*).
- Dukungan layanan **REG** (`÷ 6000`), **EXP** (`÷ 5000`), dan **CRG** (`÷ 4000`) lengkap dengan *volumetric divisor*, berat minimum, dan asuransi per rute.
- **Surcharge, Vendor 3PL & Add Charge**: pemilihan *preset surcharge*, penugasan mitra vendor 3PL (`vendor_awb_no` & `vendor_cost`), serta penambahan/penghapusan biaya tambahan custom (*additional charges*) langsung saat booking maupun pada detail resi (`/oms/shipments/{awb}`).
- Cetak label pengiriman termal A6 (`/oms/shipments/{awb}/label`).
- Konfirmasi pembayaran Non-COD (*Online Payment* & *Cash Loket*) serta validasi *credit limit* untuk pelanggan B2B TOP.
- **Non-COD Dispatch Gate**: mencegah pengiriman belum lunas (*unpaid*) masuk manifest maupun berangkat operasional.
- Transisi status pengiriman terkontrol dan pencatatan **e-POD** (nama penerima, relasi, koordinat GPS, dan catatan).

### 3. WMS — Warehouse Management System (`/wms/inventory`)
- Pengelolaan gudang multi-zona (*Dry* dan *Cold Storage*), *storage bins*, serta SKU produk per pelanggan.
- Mendukung aturan alokasi **FIFO** dan **FEFO** (pelacakan *batch number* dan tanggal kedaluwarsa).
- **Immutable Inventory Ledger** untuk pencatatan mutasi stok (*inbound*, *outbound*, dan penyesuaian) dengan validasi saldo bin agar tidak negatif.

### 4. TMS — Transportation Management System (`/tms/manifests`)
- Manajemen armada (*vehicles*) dan pengemudi (*drivers*) lengkap dengan pemantauan masa berlaku **STNK**, **KIR**, dan **SIM** (*fleet compliance*).
- Pembuatan manifest *linehaul* antar cabang, *seal number*, uang jalan pengemudi (*driver advance*), serta proses keberangkatan (`depart`) dan kedatangan (`arrive`).
- **Scan Bagging** untuk memasukkan AWB ke manifest dengan validasi Non-COD Dispatch Gate.

### 5. FIN — Finance Non-COD (`/finance`)
- Ringkasan arus kas Non-COD melalui `FinanceSummaryQueryService` serta pencatatan *Financial Ledger* otomatis untuk pelunasan *Cash Loket*, *Online Payment*, dan B2B TOP.
- **B2B TOP Auto-Invoicing**: pembuatan faktur konsolidasi otomatis dari resi terkirim lengkap dengan kalkulasi **PPN (11%)** dan **PPh 23 (2%)**.
- Pelacakan umur piutang (*aging bucket*), jatuh tempo, dan penandaan pelunasan faktur (*mark paid*).

### 6. MDM — Master Data Management (`/master`)
- Hirarki multi-cabang (*Head Office*, *Regional Hub*, *Sorting Center*) dan fitur pergantian cabang aktif (`/master/switch-branch`).
- Manajemen pelanggan (*Retail* & *B2B Contract*) beserta konfigurasi termin TOP (*top days*) dan *credit limit*.
- **Setting Tarif & Surcharge Rute**: pengaturan `rate_per_kg`, asuransi (`%`), *fuel surcharge* (`%`), *handling fee*, serta ambang dan biaya *heavy weight* per rute & layanan.
- **Master Surcharge Catalog**: konfigurasi biaya tambahan preset dengan skema `fixed`, `percentage`, maupun `per_kg`.
- **Vendor & Mitra 3PL**: pengelolaan daftar vendor (`linehaul`, `air_freight`, `sea_freight`, `last_mile`, `customs`) beserta `payment_terms_days` dan estimasi `base_cost_per_kg`.

### 7. Dashboard, RBAC & Keamanan
- Dashboard operasional dengan KPI bento grid, status armada/manifest, dan pencarian global ERP (`/api/erp/search`).
- Autentikasi berbasis **Laravel Fortify** dengan desain split-hero login, verifikasi email, **Two-Factor Authentication (2FA)**, dan **Passkeys**.
- **Role-Based Access Control (RBAC)** berbasis enum `UserRole` dan middleware `EnsureUserHasRole` (`role:...`) pada rute modul serta penyaringan navigasi UI di `ErpLayout.vue`:
  - `super_admin`: akses penuh seluruh modul (`oms`, `wms`, `tms`, `finance`, `master`).
  - `admin_oms`: operasional OMS (booking resi, vendor, add charge) & Master Data.
  - `finance_cashier`: Finance Non-COD, Master Data, & konfirmasi pembayaran OMS (tanpa buat resi/gudang/manifest).
  - `operator_wms`: Gudang / WMS.
  - `dispatcher_tms`: Manifest & Armada TMS serta transisi status & e-POD OMS.

---

## Tech Stack

| Lapisan | Teknologi |
| :--- | :--- |
| **Backend** | PHP `^8.3` (diuji & dijalankan pada **PHP 8.5.9**), Laravel `^13.17`, Inertia Laravel `^3.0`, Laravel Fortify, Wayfinder |
| **Frontend** | Vue `^3.5`, Inertia Vue 3 `^3.0`, TypeScript, Tailwind CSS `^4.1`, Reka UI, Lucide Vue, Vite+ |
| **Database** | PostgreSQL 16 / 17 / 18 (+ PostGIS) |
| **Design System** | Cool-Paper Editorial SaaS & Modular Workspace — 100% offline-first ([DESIGN.md](DESIGN.md)) |
| **Testing & QA** | PHPUnit 12, Larastan (PHPStan), Laravel Pint, `vue-tsc`, Vite+ (`vp check`) |

---

## Struktur Direktori

```text
app/
├── Actions/Fortify/        # Aksi autentikasi Fortify
├── Http/Middleware/        # EnsureUserHasRole (RBAC) & HandleInertiaRequests (shared props & allowed_modules)
├── Models/                 # Eloquent models (Shipment, ShipmentCharge, Surcharge, Vendor, Tariff, Manifest, Invoice, dll.)
├── Providers/              # AppServiceProvider (binding Domain Contracts/Repositories -> Infrastructure & CarbonImmutable)
└── Modules/                # Arsitektur modular Clean Architecture per domain
    ├── Dashboard/          # Dashboard KPI & pencarian global ERP
    ├── OMS/                # Application/UseCases, Domain/Repositories, Infrastructure/Persistence, Presentation/Http/{Controllers,Requests}
    ├── WMS/                # Gudang, Immutable Inventory Ledger (agregasi SQL MAX(id)), Request & Repository
    ├── TMS/                # Manifests, Scan Bagging, Depart/Arrive Use Cases, Repository & Requests
    ├── FIN/                # Finance Non-COD, B2B Auto-Invoicing, FinanceSummaryQueryService & InvoiceRepository
    ├── MDM/                # Master Cabang, Customer, Tarif, Surcharge, Vendor Use Cases & Repository
    ├── PublicSite/         # Landing page & Public Multi-AWB Tracking
    └── Shared/
        ├── Domain/
        │   ├── Contracts/  # TransactionManager, TrackingLogger, FinancialLedgerRecorder
        │   ├── Enums/      # UserRole, ManifestStatus, VendorType, SurchargeCalcMode, ServiceType, dll.
        │   └── ValueObjects/
        └── Infrastructure/ # Implementasi Laravel/Eloquent dari Domain Contracts
database/
├── migrations/             # Skema tabel master, OMS, vendors, surcharges, shipment_charges, WMS, TMS, Finance, Auth
└── seeders/                # DatabaseSeeder terpisah per domain (Branch, User, Customer, Tariff, Surcharge, Vendor, Warehouse, Transport, Shipment, Invoice)
public/
└── images/landing/         # Aset gambar landing page offline
resources/js/
├── components/
│   ├── master/             # TariffSettingsTab, SurchargeCatalogTab, VendorDirectoryTab, CustomerDirectoryTab, BranchDirectoryTab
│   └── oms/                # OmsBookingSummaryStrip, OmsVendorAndChargesSection, OmsPaymentMethodSection, OmsColliTableSection
├── composables/            # Scanner barcode, chargeable weight, appearance, 2FA
├── layouts/                # AppLayout, AuthLayout, ErpLayout (navigasi terfilter RBAC)
├── pages/                  # Halaman Vue/Inertia (Welcome, Login, Dashboard, OMS, WMS, TMS, Finance, Master, Public)
└── types/erp.ts            # Definisi tipe TypeScript bersama modul MDM & OMS
tests/
└── Feature/                # Feature tests termasuk PacheNonCodErpTest (termasuk pengujian RBAC & rate/vendor/surcharge)
```

---

## Prasyarat

- **PHP** >= 8.3 (kompatibel hingga PHP 8.5.x; diuji dengan PHP 8.5.9, beserta ekstensi `pdo_pgsql`, `pgsql`, `mbstring`, `openssl`, `xml`, `ctype`, `fileinfo`)
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

Seeder terpisah di `database/seeders/` juga menyiapkan 6 cabang (`JKT`, `BDG`, `SBY`, `SMG`, `MDN`, `MKS`), 6 pelanggan (B2B TOP & Retail), matriks tarif rute, katalog master surcharge (`SurchargeSeeder`), daftar vendor 3PL (`VendorSeeder`), 10 contoh pengiriman (`PCH-JKT-261121-00001` hingga `PCH-JKT-261121-00010`), data gudang/stok, armada/pengemudi, manifest linehaul, serta data faktur B2B.

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
