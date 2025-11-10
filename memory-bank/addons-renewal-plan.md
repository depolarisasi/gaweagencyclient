# Rencana Perbaikan & Penambahan: Add-ons, Renewal, dan Invoicing

## Latar Belakang
- Saat ini sistem telah:
  - Mendukung 1 subscription per order dan banyak add-on (`Order::addons()` / `order_addons`).
  - Menggabungkan item subscription, domain, dan add-ons pada invoice awal pembelian (payload Tripay `order_items`).
  - Membuat invoice renewal untuk subscription (`invoices:generate-recurring`).
  - Menandai invoice overdue (`invoices:mark-overdue`) dan mensuspend project+order H+14 jika renewal subscription tidak dibayar (`projects:suspend-overdue`).
- Belum tersedia:
  - Cancel add-on oleh client (aksi/route/UI).
  - Status/cancellation tracking pada `order_addons`.
  - Invoice renewal gabungan untuk add-ons (terpisah dari subscription).
  - Auto-cancel add-ons H+14 jika renewal invoice tidak dibayar.
  - Tampilan status add-on di halaman orders dan project.

## Keputusan Bisnis Terkonfirmasi (dari Big Pappa)
1. Renewal add-ons: 1 invoice gabungan untuk semua add-on recurring dalam satu order dan periode yang sama.
2. Cancel oleh client: tidak ada batasan dan tidak ada refund; add-on tetap aktif sampai masa berakhir (cancel berlaku di akhir periode berjalan).
3. Dampak cancel: tandai `cancelled` untuk add-ons saja; tidak mensuspend project.
4. Pajak (PPN 11%): diterapkan pada renewal add-ons sama seperti subscription.
5. Penjadwalan: ikuti jadwal di Kernel (konsisten dengan job existing).
6. Keterlacakan kuat: perlu tambahan struktur supaya setiap item add-on di invoice terlacak ke `order_addons`.

## Tujuan
- Mendukung semua keputusan di atas dengan perubahan minimal dan idempoten.
- Memisahkan clear konteks subscription vs add-ons pada renewal, sambil tetap menggabungkan saat pembelian awal.
- Membuat UI yang transparan untuk client terkait status add-ons.

## Perubahan Skema Database
- Tabel `order_addons` (minimal dan sesuai keputusan):
  - Tambah kolom:
    - `status` enum: `pending`, `active`, `cancelled` (default: `active` untuk data historis).
    - `started_at` datetime nullable.
    - `next_due_date` date nullable (jatuh tempo per add-on).
    - `cancel_at_period_end` boolean default false (menandai request cancel yang efektif di akhir periode).
    - `cancelled_at` datetime nullable (tanggal eksekusi cancel; untuk auto-cancel H+14 atau saat period end).
- Tabel `invoices`:
  - Tambah kolom:
    - `renewal_type` enum: `subscription`, `addons` nullable (null untuk invoice awal pembelian).
    - `items_snapshot` json nullable (snapshot item/breakdown untuk auditing, berdampingan dengan tabel item).
- Tabel baru `invoice_items` (keterlacakan kuat):
  - Kolom:
    - `id`, `invoice_id` FK.
    - `item_type` enum: `subscription`, `addon`, `domain`.
    - `order_id` FK.
    - `order_addon_id` FK nullable (untuk item `addon`).
    - `product_addon_id` FK nullable (referensi katalog).
    - `description` string (nama item).
    - `amount` decimal(15,2).
    - `quantity` integer default 1.
    - `billing_type` enum: `one_time`, `recurring` nullable.
    - `billing_cycle` enum (selaras dengan add-ons/subscription) nullable.

Catatan: `invoice_items` dipakai untuk renewal add-ons gabungan (banyak baris) dan dapat dipakai juga untuk invoice subscription (1 baris) agar konsisten.

## Perubahan Backend
- Command baru: `invoices:generate-recurring-addons`
  - Tujuan: membuat satu invoice gabungan per order untuk seluruh add-ons recurring yang `status=active` dan `next_due_date <= now()+14 hari`.
  - Langkah:
    - Ambil semua `order_addons` recurring aktif yang due dalam 14 hari, kelompokkan per `order_id` dan per `due_date`.
    - Buat satu `Invoice` dengan:
      - `is_renewal = true`, `renewal_type = 'addons'`, `due_date = due_date kelompok`, `status = 'sent'`.
      - `amount = sum(line_items)`, `tax_amount = 11%`, `total_amount = amount + tax`.
    - Buat `invoice_items` satu baris per add-on (isi `order_addon_id`, `product_addon_id`, `amount`, dsb.).
    - Simpan `items_snapshot` (JSON) untuk audit cepat.
    - Idempoten: sebelum membuat, cek eksistensi invoice `is_renewal=true` + `renewal_type='addons'` + `order_id` + `due_date`.
    - Kirim notifikasi `InvoiceGeneratedNotification`.
- Command baru: `addons:cancel-overdue`
  - Tujuan: auto-cancel add-ons yang renewal invoicenya `overdue` dan `due_date < now()-14 hari`.
  - Langkah:
    - Cari invoice dengan `renewal_type='addons'`, `status='overdue'`, `due_date < now()-14 hari`.
    - Untuk setiap item `invoice_items` bertipe `addon`, set:
      - `order_addons.status='cancelled'`, `cancelled_at=now()`.
    - Update invoice menjadi `cancelled`.
    - Tidak mensuspend project (sesuai keputusan).
- Command baru: `addons:apply-cancel-at-period-end`
  - Tujuan: eksekusi cancel untuk add-ons yang diminta user (tanpa refund) di akhir periode.
  - Langkah:
    - Cari `order_addons` dengan `cancel_at_period_end=true` dan `next_due_date <= now()` lalu set `status='cancelled'`, `cancelled_at=now()`.
- Update `TripayService`:
  - Format `order_items` untuk invoice renewal add-ons dari data `invoice_items` (gabungan semua add-on).
- Update kalkulasi renewal:
  - Subscription: tetap seperti saat ini (invoice terpisah).
  - Add-ons: gabungan per order (exclude `one_time`).
  - Domain: tidak ikut renewal (tetap hanya pembelian awal).

## Aksi Client (Cancel Add-on)
- Route: `POST client/orders/{order}/addons/{orderAddon}/cancel` (policy: pemilik order).
- Aturan:
  - Recurring: set `cancel_at_period_end=true` (tetap aktif hingga `next_due_date`), tanpa refund.
  - One-time: izinkan (sesuai keputusan), tandai `cancelled`—tidak ada refund, efek operasional minimal.
- Feedback:
  - Dialog konfirmasi: “Add-on akan tetap aktif hingga akhir periode dan tidak ada refund.”

## Perubahan UI/UX
- Halaman detail order client (`client.orders.show`):
  - Tambahkan daftar add-ons beserta status badge (`active`, `pending`, `cancelled`) + tombol “Cancel at end of term” untuk recurring.
  - Tampilkan invoice renewal add-ons gabungan (link ke `client.invoices.show`).
- Halaman project client:
  - Tambahkan seksi “Included Add-ons” (read-only status).
- Invoice client:
  - Menampilkan breakdown `invoice_items` untuk add-ons renewal (deskripsi, jumlah, pajak).

## Automasi & Scheduler (Kernel)
- Jadwal harian (ikuti jadwal existing):
  - `invoices:generate-recurring` (subscription).
  - `invoices:generate-recurring-addons` (baru).
  - `invoices:mark-overdue`.
  - `projects:suspend-overdue`.
  - `addons:cancel-overdue` (baru).
  - `addons:apply-cancel-at-period-end` (baru).
- Urutan rekomendasi: generate → mark overdue → suspend/cancel/addon cancel.

## Pengujian & Validasi
- Unit/Feature:
  - `AddonRecurringBillingTest`: memastikan invoice gabungan add-ons dibuat per order/periode (idempoten, exclude one-time).
  - `AddonCancellationTest`: memastikan cancel oleh client ditandai period-end dan berubah ke `cancelled` saat due.
  - `OverdueAddonsAutoCancelTest`: memastikan H+14 auto-cancel add-ons dan invoice menjadi `cancelled`.
  - `InvoiceItemsTraceabilityTest`: setiap item punya keterlacakan kuat ke `order_addons`.
- E2E (Playwright):
  - Checkout dengan add-ons → bayar → lihat orders/project (status add-ons).
  - Simulasi renewal → terlihat invoice add-ons gabungan; subscription tetap terpisah.
- Manual checklist:
  - Konsistensi subtotal vs itemisasi Tripay.
  - Tidak ada duplikasi invoice gabungan per periode.

## Risiko & Mitigasi
- Duplikasi invoice gabungan:
  - Mitigasi: cek idempoten `(order_id, renewal_type='addons', due_date)` sebelum `Invoice::create`.
- Inkonsistensi jumlah:
  - Mitigasi: normalisasi `total_amount` = sum(`invoice_items`) + pajak; sinkronisasi.
- Kompleksitas data:
  - Gunakan `invoice_items` sebagai sumber kebenaran dan `items_snapshot` sebagai referensi cepat.

## Estimasi Implementasi
- Fase 1 (DB & Model): 0.5 hari
- Fase 2 (Command generate renewal add-ons + scheduler): 1 hari
- Fase 3 (Auto-cancel H+14 + cancel period-end + scheduler): 0.75 hari
- Fase 4 (UI order detail + cancel add-on): 1 hari
- Fase 5 (Testing & dokumentasi): 1 hari

## Next Steps
1. Approval rencana revisi ini dari Big Pappa.
2. Setelah approve, mulai Fase 1 (migrasi `order_addons`, `invoices`, `invoice_items`)—tanpa mengeksekusi job.
3. Update Memory Bank (`activeContext.md` dan `progress.md`) setelah tiap fase.