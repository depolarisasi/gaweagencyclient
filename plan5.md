# Rencana Perbaikan Invoice PDF

- [ ] Audit & Investigasi
 --> [X] Tinjau `InvoicePdfService::generate()` dan pastikan relasi diload: `user`, `items`, `order.product`, `order.template`, `order.orderAddons.productAddon`
 --> [X] Audit `resources/views/pdf/invoice.blade.php` untuk kelengkapan: header brand, alamat/kontak, status (LUNAS/BELUM LUNAS/DIBATALKAN), domain, template, addons, periode, subtotal/pajak/total, dan terbilang
 --> [X] Verifikasi `InvoiceItem` (model) mencakup `description`, `amount`, `quantity`, `billing_type`, `billing_cycle`; siapkan fallback jika `items` kosong (pakai `order`, `orderAddons`, `domain_amount`)
--> [ ] Pastikan sumber data brand (config: `app.company_name`, `company_email`, `company_phone`, `company_address`, `company_website`) tersedia via `AppServiceProvider`
--> [ ] Tentukan strategi logo (opsional): gunakan file `public/images/gawe-logo.png` atau setting terpisah jika ada; konfirmasi kebutuhan `isRemoteEnabled` pada DomPDF untuk asset

- [ ] Redesign Template PDF (Profesional)
 --> [ ] Header: tampilkan `[LOGO/Nama Perusahaan]`, `[Alamat]`, `[Kota, Kode Pos]`, `[Telepon | Email | Website]` menggunakan `config('app.company_*')`
 --> [ ] Status: tampilkan `STATUS: [LUNAS / BELUM LUNAS / DIBATALKAN / KEDALUWARSA]` secara menonjol di dekat judul INVOICE
 --> [ ] Meta: kolom kiri "DITAGIHKAN KEPADA (BILL TO)" berisi nama/email klien; kolom kanan "RINCIAN INVOICE" berisi `Nomor Invoice`, `Tanggal Terbit`, `Tanggal Jatuh Tempo`
 --> [X] Tabel item: ubah kolom menjadi `DESKRIPSI`, `QTY`, `HARGA SATUAN`, `JUMLAH (IDR)`; untuk `InvoiceItem` hitung `unit_price = amount / quantity` (default qty=1 bila null)
 --> [X] Subscription & Template: saat `items` kosong, baris layanan utama menampilkan nama produk + "Template: {template->name}" (atau dari `order_details` bila ada)
 --> [X] Domain: tambahkan baris domain jika `order->domain_amount > 0` dan tampilkan `order->domain_name` (opsional periode `-`)
 --> [X] Add-ons: tampilkan semua `order->orderAddons` (fallback saat `items` kosong) dengan `name`, `billing_cycle_label` dan `price`
--> [ ] Ringkasan total: tampilkan `SUBTOTAL (invoice->amount)`, `Pajak (invoice->tax_amount)`, `Diskon/Potongan (opsional, default Rp 0)`, dan `TOTAL (invoice->total_amount)`
 --> [X] Terbilang: tambahkan "TERBILANG: [angka dalam kata]" menggunakan helper baru (contoh fungsi `terbilang_idr($invoice->total_amount)`)
--> [ ] Payment details: tambahkan instruksi pembayaran generik (Tripay) dan fallback jika payment channel kadaluarsa, sesuai teks spesifikasi
--> [ ] Notes/Terms: tambahkan 2–3 poin ketentuan pembayaran dan pengingat mencantumkan nomor invoice pada bukti transfer
--> [ ] Styling: perbaiki tipografi, garis pemisah, dan spasi; siapkan opsi `isRemoteEnabled` untuk gambar/logo bila diperlukan

- [ ] Implementasi & Validasi
 --> [X] Update `resources/views/pdf/invoice.blade.php` sesuai layout baru dan gunakan data `config('app.company_*')`
 --> [X] Tambahkan helper `terbilang_idr()` di `app/helpers.php` (KISS, dukung angka umum) dan gunakan di template
 --> [X] Opsional: set opsi DomPDF `isRemoteEnabled` di `InvoicePdfService` bila logo remote/URL
--> [ ] Uji unduh/inline PDF dari Admin (`admin.invoices.download`) dan Client (`client.invoices.download`)
--> [ ] Uji kasus: invoice dengan `items` lengkap, hanya fallback `order`, dengan domain, dengan ≥1 add-on, status `sent/paid/overdue/cancelled`
--> [ ] Verifikasi format IDR di seluruh angka (`formatIDR`/`number_format`) dan konsistensi subtotal vs total
 --> [X] Verifikasi output "Terbilang" untuk contoh nilai (mis. Rp 2.220.000)
 --> [X] Perbarui Memory Bank (`activeContext.md`, `progress.md`) mencatat perubahan layout & data PDF