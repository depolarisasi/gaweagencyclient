- [x] Tambah CRUD "TLD Pricing" di Admin Panel 
 --> [x] Tambah resource route `admin.tld-pricings` di `routes/web.php` 
 --> [x] Buat `TldPricing` model dan migration `tld_pricings` 
 --> [x] Implement `Admin\TldPricingController` untuk CRUD 
 --> [x] Buat views `admin/tld-pricings/{index,create,edit}.blade.php` 

- [x] Integrasi TLD Pricing ke DomainService 
 --> [x] Update `DomainService` untuk load harga TLD dari DB 
 --> [x] Tambahkan fallback harga statis jika DB kosong/error 
 --> [x] Merge TLD dari DB ke `getSupportedTlds()` 

- [x] Integrasi UI Sidebar Admin 
 --> [x] Tambah link "TLD Pricing" dengan ikon globe di `layouts/sidebar.blade.php` 
 --> [x] Pastikan route prefix `admin.` dan active state panel 

- [ ] Konsistensi Perhitungan Diskon di Billing/Checkout 
 --> [ ] Selaraskan `billing.blade.php` dengan `CheckoutSummaryComponent` untuk diskon 
 --> [ ] Tambahkan subtotal diskon bila ada `discount_percentage` 
 --> [ ] Pastikan `domainAmount` mengikuti tipe domain via `DomainService` 

- [ ] Update Label Biaya Admin pada Billing 
 --> [ ] Ganti label ke "Biaya Admin (Dibayar Customer)" 
 --> [ ] Pastikan `customerFee` ditambahkan ke `totalAmountWithFees` 

- [ ] Lengkapi Alur `markAsPaid` di Admin Invoice 
 --> [x] Set `status=paid`, isi `paid_date` dan `payment_method` 
 --> [x] Aktifkan order, set `activated_at` dan `next_due_date` 
 --> [x] Buat project jika belum ada, generate nama project dinamis 
--> [ ] Kirim notifikasi `PaymentSuccessful` ke user dan attach PDF jika ada 

- [ ] Validasi & Notifikasi di PaymentController 
 --> [ ] Tinjau alur `checkPaymentStatus` untuk aktivasi order/project 
 --> [ ] Pastikan pengiriman `PaymentSuccessful` pada success callback berjalan 

- [x] Operasional Database untuk TLD Pricing 
 --> [x] Jalankan migrasi tabel `tld_pricings` 
 --> [x] Seed data awal TLD atau input manual via admin panel 

- [ ] Pengujian 
 --> [ ] Unit test `DomainService`: harga TLD dari DB dan fallback statis 
 --> [ ] E2E: CRUD TLD Pricing dan verifikasi total pada checkout/billing 

- [ ] Refactor Aktivasi 
 --> [ ] Pertimbangkan ekstraksi aktivasi order/project ke service terpisah 
 --> [ ] Dokumentasikan service baru jika dibuat 

- [ ] Recurring Invoices 
 --> [ ] Implement generator invoice berkala dengan seleksi `next_due_date <= now()->addDays(14)` 
 --> [ ] Tambah schedule/command di `Console\Kernel` untuk eksekusi harian 

- [ ] Dokumentasi Memory Bank 
 --> [ ] Update `memory-bank/activeContext.md` mengikuti perubahan terbaru 
 --> [ ] Catat status di `memory-bank/progress.md` 
 --> [ ] Tambah pola di `memory-bank/systemPatterns.md` dan `techContext.md` 

- [ ] Rute & Itemisasi Tripay 
 --> [ ] Verifikasi itemisasi order ke Tripay sesuai harga `TldPricing` 
 --> [ ] Pastikan invoice PDF menampilkan diskon dan harga domain dengan benar