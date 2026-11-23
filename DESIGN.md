# Pache Logistic — Design System & Visual Specification (`DESIGN.md`)

> **Status**: Active | **Version**: 1.0.0 | **Project**: **Pache Logistic** (_Integrated Non-COD Logistics ERP_)  
> **Mode**: 100% Offline / Self-Contained (Zero External CDN & Zero Web Lookup Required)  
> **Global Standard**: Derived from `global-design-reference` (`Cool-Paper Editorial SaaS & Modular Workspace`).  
> **Authority**: Dokumen ini adalah _Single Source of Truth_ (SSOT) untuk seluruh keputusan antarmuka (UI), tata visual (UX), token desain, dan implementasi kode frontend (Vue 3.5 + Inertia v3 + Tailwind CSS v4) pada **Pache Logistic**.

---

## 1. Visual Direction & Archetype

- **Brand Identity**: **Pache Logistic** — Platform ERP Logistik Terpadu (_OMS, WMS, TMS, Finance Non-COD, & Live Tracking_) yang beroperasi secara **Strictly Non-COD** (hanya mendukung **Online Payment QRIS/VA**, **Cash di Loket Asal**, dan **B2B Term of Payment / TOP**).
- **Aesthetic Archetype**: **Cool-Paper Editorial SaaS & Modular Workspace**.
  Menggabungkan dua permukaan visual yang menyatu:
    1. **Landing & Public Tracking Surface (`/`, `/tracking`)**: Latar kertas biru-dingin (`#f7f9fc`) dengan tipografi rapat self-hosted (`Plus Jakarta Sans`), aksen biru tegas (`#2563eb`), tombol CTA utama bersudut siku tajam (`border-radius: 0`), _frosted-glass sticky header_, _soft neumorphism tab bar_ (`#edf2f8`) khusus pada showcase modul, _zig-zag split story_ dengan _comic speech bubbles_, akordeon FAQ native `<details>`, dan blok penutup kontras tinggi gelap (`#111111`).
    2. **ERP Workspace & Operational Dashboard Surface (`/dashboard`, `/oms/*`, `/wms/*`, `/tms/*`, `/finance`, `/master/*`)**: Kanvas biru-pucat lembut (`#edf3fa` di mode terang, `#090909` di mode gelap) dengan _fixed transparent sidebar_ (`188px`), _rounded floating content sheet_ (`border-radius: 24px`) berwarna putih murni (`#ffffff` / `#111111` dark), _bento KPI cards_ dengan ornamen `.metric-motif` SVG, tabel operasional kepadatan tinggi, serta kontras **WCAG AA $\ge 4.5:1$**.
- **Target Emotion**: Presisi operasional, kecepatan _scanner-first_, transparan pada angka berat & tarif, tepercaya untuk klien korporat B2B, dan nyaman dipakai staf gudang/kasir sepanjang shift.
- **Key Metaphor**: _Crisp Waybill Ledger & Tactile Dispatch Studio_.

---

## 2. Design Tokens (High Specificity)

> **Aturan**: Wajib menggunakan kode HEX / RGB pasti di bawah ini. Dilarang menebak warna atau memakai nama warna generik.

### 2.1 Color Palette — Landing & Public Surface

| Role                       | Token Name       | Value                    | Penggunaan & Keterangan                                                     |
| :------------------------- | :--------------- | :----------------------- | :-------------------------------------------------------------------------- |
| **Canvas / Paper**         | `--paper`        | `#f7f9fc`                | Latar belakang utama seluruh halaman publik & cek resi                      |
| **Pure White**             | `--white`        | `#ffffff`                | Kartu pratinjau, gelembung fitur, panel cek resi, tombol di blok gelap      |
| **Primary Ink**            | `--ink`          | `#18202c`                | Heading dan teks kontras tinggi                                             |
| **Muted Text**             | `--muted`        | `#596579`                | Paragraf deskripsi, tautan navigasi sekunder, subjudul                      |
| **Subtle Caption**         | —                | `#748095` / `#667085`    | Catatan kecil, ikon `+` FAQ, metadata resi                                  |
| **Brand Accent**           | `--accent`       | `#2563eb`                | CTA utama, teks highlight judul (`h1 > span + span`), fokus, seleksi teks   |
| **Accent Hover**           | `--accent-hover` | `#1d4ed8`                | Hover tombol utama, tab aktif, link interaktif                              |
| **Blue Soft**              | `--blue-soft`    | `#dbeafe`                | Hover tombol terang (`.button-light`) pada section gelap                    |
| **Blue Faint**             | `--blue-faint`   | `#eff6ff`                | Latar status info / hover halus                                             |
| **Structural Line**        | `--line`         | `#dce5f1`                | Garis pembatas header (`rgb(220 229 241 / 72%)`) & divider                  |
| **Header Frosted**         | —                | `rgb(247 249 252 / 88%)` | Latar sticky header dengan `backdrop-filter: blur(14px)`                    |
| **Neumorphism Base**       | `--neu-surface`  | `#edf2f8`                | Latar bilah tab `.showcase-tabs`, tab idle, dan stage media                 |
| **Neumorphism Active**     | `--neu-active`   | `#e9eff7`                | Latar tab `.showcase-tab.is-active` dengan inset shadow                     |
| **Showcase / Brand BG**    | —                | `#eef3fb` / `#edf3fc`    | Latar panel `.document-showcase` & section `.brand-section`                 |
| **Dark Surface (Closing)** | `--surface-dark` | `#111111`                | Latar section penutup (`.closing`)                                          |
| **Dark Surface Accent**    | —                | `#93c5fd` / `#cbd5e1`    | Aksen headline (`#93c5fd`) dan teks keterangan (`#cbd5e1`) di section gelap |
| **Footer Surface**         | `--footer-bg`    | `#f0f4fa`                | Latar belakang `.site-footer`                                               |
| **Input Border**           | —                | `#d5dfed`                | Border input form (`1px solid #d5dfed`)                                     |
| **Validation Error**       | —                | `#b42318`                | Pesan error & border `[aria-invalid="true"]`                                |

### 2.2 Color Palette — ERP Workspace Surface (Light & Dark Mode)

| Role                         | Token Name               | Light Mode (`:root`)          | Dark Mode (`html.dark`)                   | Penggunaan                                                               |
| :--------------------------- | :----------------------- | :---------------------------- | :---------------------------------------- | :----------------------------------------------------------------------- |
| **Outer Canvas**             | `--bone`                 | `#edf3fa`                     | `#090909`                                 | Latar luar shell aplikasi & area sidebar                                 |
| **Canvas Light**             | `--bone-light`           | `#f8fbff`                     | `#121212`                                 | Variasi kanvas halus                                                     |
| **Main Surface**             | `--surface`              | `#ffffff`                     | `#111111`                                 | Sheet utama `.main`, panel `.glass-panel`, modal, dropdown               |
| **Surface Soft**             | `--surface-soft`         | `#f3f7fc`                     | `#1a1a1a`                                 | Kartu metrik standar, header tabel, container `.page-tabs`, input search |
| **Active / Hover Tint**      | `--surface-pink`         | `#eaf3ff`                     | `#242424`                                 | Nav link aktif, hover baris tabel, badge lembut, search hit              |
| **Hairline Border**          | `--hairline`             | `#e2ebf5`                     | `#303030`                                 | Border `1px` panel, kartu, input, pemisah tabel                          |
| **Primary Ink**              | `--ink` / `--brand-navy` | `#172b46`                     | `#f4f4f5`                                 | Heading, angka KPI, nilai tabel, tombol aksi netral                      |
| **Muted Text**               | `--muted`                | `#64748b`                     | `#a1a1aa`                                 | Label sekunder, header kolom tabel, metadata                             |
| **Brand Blue**               | `--brand-blue`           | `#2563eb`                     | `#60a5fa`                                 | Ikon aktif, tab aktif, grafik utama, fokus ring                          |
| **Brand Pine**               | `--brand-pine`           | `#1d4ed8`                     | `#bfdbfe`                                 | Aksen teks banner info & hover link                                      |
| **Action CTA BG**            | `--action-bg`            | `#172b46`                     | `#60a5fa`                                 | Latar `.solid-button` & avatar inverse                                   |
| **Action CTA FG**            | `--action-fg`            | `#ffffff`                     | `#090909`                                 | Warna teks di atas `--action-bg`                                         |
| **Action CTA Hover**         | `--action-hover-bg`      | `#2563eb`                     | `#93c5fd`                                 | Hover `.solid-button`                                                    |
| **Secondary Gold**           | `--brand-gold`           | `#d3a56b`                     | `#d3a56b`                                 | Aksen sorotan status TOP / warning kontrak                               |
| **Success (Paid/Delivered)** | —                        | Text `#15803d` / BG `#ecfdf3` | Text `#86efac` / BG `rgba(22,163,74,.16)` | Status `PAID` (Online/Cash), `Delivered`, valid scan                     |
| **Warning (Pending/TOP)**    | —                        | Text `#92400e` / BG `#fef3c7` | Text `#fcd34d` / BG `rgba(217,119,6,.16)` | Status `PENDING`, peringatan STNK/KIR H-30                               |
| **Error State**              | —                        | Text `#991b1b` / BG `#fef2f2` | Text `#fca5a5` / BG `rgba(220,38,38,.14)` | Over credit limit (`BR-02`), blokir unpaid (`BR-08`), error scan         |

### 2.3 Warna Kartu Metrik Bento

- **Kartu Metrik ke-1 (Highlight Utama)**: Background solid `var(--customer-accent, #2563eb)`, teks & motif SVG `.metric-motif` putih (`opacity: .22`, `.12` di dark mode).
- **Kartu Metrik ke-2 (Soft Accent)**: Background `#dcecff`, border `#d3e5ff`, teks utama `#214f8a`, caption `#617fa6`. Di dark mode kembali menjadi `--surface-soft` (`#1a1a1a`) & `--hairline` (`#303030`).
- **Kartu Metrik ke-3+**: Background `var(--surface-soft)` (`#f3f7fc`), border `1px solid var(--hairline)`, motif `.metric-motif` opacity `.16`.

### 2.4 Tipografi (100% Self-Hosted Offline `.woff2`)

> **Lokasi Font Lokal**: Tersimpan di `public/fonts/` dan dimuat melalui `@font-face` lokal (`/fonts/*.woff2`).

1. **Landing & Public Surface**: `"Plus Jakarta Sans", sans-serif` (`/fonts/plus-jakarta-sans.woff2`, weight `200–800`, `font-display: swap`)
    - **H1**: `font-size: clamp(40px, 4.1vw, 58px); line-height: 1.17; font-weight: 650; letter-spacing: -.045em;`
    - **H2**: `font-size: clamp(28px, 3.1vw, 40px); line-height: 1.25; font-weight: 650; letter-spacing: -.045em;`
    - **Body**: `font-size: 16px; line-height: 1.7;`
2. **ERP Workspace Surface**: `'Source Sans 3', ui-sans-serif, system-ui, sans-serif` (`/fonts/source-sans-3-*.woff2`, weight `200–900`)
    - **Page Heading**: `23px`, `font-weight: 650`, `letter-spacing: -0.7px`.
    - **Metric Value**: `26px`, `font-weight: 650`, `letter-spacing: -0.035em`, wajib `font-variant-numeric: tabular-nums`.
    - **Tabular Numbers**: Wajib `font-variant-numeric: tabular-nums` pada seluruh nominal IDR, berat (`kg`), dimensi (`cm`), nomor AWB, tanggal jatuh tempo TOP, dan metrik KPI.

### 2.5 Geometri & Border Radius

- **Landing**:
    - **Primary CTA Header (`.workspace-link`) & Hero CTA (`.hero-copy .button`)**: `border-radius: 0` (siku tajam editorial).
    - **Secondary Buttons**: `border-radius: 9px`.
    - **Showcase & Panels**: `16px`; **Neumorphic `.showcase-tabs` / `.showcase-tab`**: `20px` / `13px`.
    - **Story Bubbles**: `15px 15px 15px 5px` (hard shadow `3px 4px 0 #e0eaff`).
- **Workspace**:
    - **Main Sheet (`.main` & `.content-scroll`)**: `border-radius: 24px` (`20px` di `< 900px`).
    - **Panels**: `18px`; **Metric Cards**: `15px`; **Inputs / Buttons / Nav Links**: `9px–10px`; **Status Badges**: `6px`.

---

## 3. Negative Rules ("Do Not Use" — Anti-Slop & Offline-First Layer)

1. **Zero External CDN**: Dilarang memuat `fonts.googleapis.com`, `cdn.tailwindcss.com`, `unpkg.com`, atau `jsdelivr.net`. Semua font `.woff2`, CSS, dan ikon SVG harus tersaji lokal.
2. **No Default Inter/Roboto**: Wajib `Plus Jakarta Sans` (landing/public) dan `Source Sans 3` (workspace).
3. **Zero Unicode Emoji sebagai Ikon UI**: Semua ikon navigasi, status, aksi, dan modul wajib menggunakan Inline SVG vector (`stroke="currentColor"`, `stroke-width="1.5–1.8"`, `stroke-linecap="round"`).
4. **No Purple/Indigo Hero Gradients & No Neon Glow**: Tetap pada Cool-Paper `#f7f9fc`, Blue `#2563eb`, Deep Navy `#172b46`, dan Dark `#111111`.
5. **Restricted Neumorphism**: Efek _soft neumorphism_ (`#edf2f8`) hanya pada `.showcase-tabs` di halaman landing, tidak pada form/tabel workspace.
6. **No Predictable 3-Equal-Card Feature Grid**: Gunakan Zig-Zag Split Story + Speech Bubbles di landing dan Bento Grid bervariasi di dashboard.
7. **Domain Guardrail — Strictly Non-COD**: Tidak boleh ada input nominal COD, menu Setoran COD Kurir, atau opsi pembayaran COD di seluruh UI **Pache Logistic**. Hanya **Online Payment**, **Cash (Loket Asal)**, dan **B2B TOP**.

---

## 4. Changelog

### [2026-11-21] - v1.0.0: Inisialisasi Design System Pache Logistic

- **Dibuat**: Adaptasi penuh standar `global-design-reference` (_Cool-Paper Editorial SaaS & Modular Workspace_) untuk **Pache Logistic** (Laravel 13 + Inertia v3 + Vue 3.5 + Tailwind CSS v4 + PostgreSQL/PostGIS), mencakup permukaan Landing/Cek Resi Publik dan ERP Workspace Light/Dark mode dengan kebijakan Strictly Non-COD.
- **Penyusun**: User & Antigravity AI.
