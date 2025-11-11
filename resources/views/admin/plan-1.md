# Investigasi & Rencana Aksi — Admin Order Pricing, Admin Invoice Detail, Cancel Add-ons, Info Orders Client, Aktivasi Project

Status: Draft untuk review Big Pappa  
Tujuan: Membetulkan perhitungan/label pricing di admin order, menyamakan layout halaman invoice detail admin dan menambah aksi PDF/Email, memperbaiki error cancel add-ons, memperkaya informasi order di halaman client, serta memastikan pembuatan project otomatis saat order diaktifkan/dibayar.

## Ringkasan Temuan

1) Admin Order Pricing Breakdown salah menampilkan “Order Amount”
- Observasi: `resources/views/admin/orders/show.blade.php` menghitung “Total” sebagai penjumlahan `order->amount`, `subscription_amount`, `addons_amount`, `setup_fee`. Hal ini menyebabkan total ganda, karena `order->amount` biasanya sudah mewakili keseluruhan.
- Ekspektasi dari Big Pappa: 
  - Subscription Amount: Rp 1.999.000  
  - Addons Amount: Rp 300.000  
  - Total: Rp 2.549.000  
  - “Order Amount” tidak perlu ada atau harus direlabel menjadi “Total”.
- Akar masalah: Penjumlahan `order->amount` dengan komponen lain membuat duplikasi.

2) Admin Invoice Detail layout rusak dan kurang aksi
- Observasi: Halaman `/admin/invoices/{id}` tidak konsisten dengan layout admin lainnya dan kurang aksi seperti generate PDF, download, print, kirim email ke client, serta kurang informasi breakdown.
- Kesiapan sistem: 
  - `InvoicePdfService` sudah ada; `routes/web.php` menyediakan route download PDF (`admin.invoices.download`).
  - View PDF `resources/views/pdf/invoice.blade.php` tersedia namun perlu peningkatan layout dan itemisasi.
  - Email `emails/payment_successful.blade.php` ada; perlu aksi “Send Invoice” (status `sent`) dan “Send Email Invoice” jika diperlukan.

3) Client cancel add-on menyebabkan error fatal (timeout Cookie.php)
- Observasi: Error “Maximum execution time of 60 seconds exceeded” berasal dari `vendor\symfony\http-foundation\Cookie.php:19`. Ini indikasi siklus set-cookie/redirect berkepanjangan atau operasi blok panjang.
- Konteks data: 
  - Kolom `cancel_at_period_end` sudah ada di migration `2025_11_10_110001_update_order_addons_for_renewal_fields.php` (model `OrderAddon` juga sudah mendukung).
  - `Client\DashboardController@cancelAddon` mengeset `cancel_at_period_end=true` untuk recurring dan melakukan cancel langsung untuk one-time.
- Hipotesis akar masalah:
  - Ada loop redirect atau proses set-cookie berat di halaman detail orders setelah cancel.
  - Potensi konflik dengan helper cookie checkout (jika dipanggil tidak sengaja di client area).
  - Proses DB lambat akibat eager loading berat tanpa batas, namun error menunjuk `Cookie.php` sehingga loop set-cookie lebih mungkin.

4) Informasi Orders Client kurang lengkap
- Observasi: Halaman `client/orders/show.blade.php` menampilkan status order, tanggal, domain, template, dan daftar add-ons. Informasi tentang paket subscription (nama, siklus), kapan berakhir, invoice terkait, dan kapan add-on berakhir belum lengkap.
- Kebutuhan: Tambahkan detail subscription plan, periode aktif (billing_period_start/end bila ada), daftar invoice terkait order (termasuk status dan link), dan due date per add-on + status `cancel_at_period_end`.

5) Aktivasi order tidak membuat project
- Observasi: Pada `Admin\OrderController`, jalur “Activate” memanggil `createProjectForOrder`, namun jika status diubah via “Edit/Update” ke `active`, tidak ada pemanggilan fungsi ini.
- PaymentController saat invoice `PAID`: sudah mengaktifkan order dan membuat/mengaktifkan project otomatis. Ini berarti jalur pembayaran aman.
- Akar masalah: Aktivasi manual lewat update tidak memicu pembuatan project (logika hanya ada di `activate` route khusus).

## Rencana Perbaikan (Tanpa Mengubah Kode Saat Ini)

A. Admin Order Pricing Breakdown
- Ubah tampilan breakdown:
  - Hapus baris “Order Amount” atau relabel menjadi “Total”.
  - Hitung “Total” sebagai `subscription_amount + addons_amount (+ setup_fee jika ada)`.
- Validasi terhadap `Order::getTotalAmountAttribute` dan pastikan accessor sesuai.

B. Admin Invoice Detail — Layout & Aksi
- Samakan layout dengan halaman admin lain (card header, grid detail, actions top-right).
- Tambahkan aksi:
  - Generate PDF (menggunakan `InvoicePdfService@generate`).
  - Download PDF (`admin.invoices.download` sudah ada).
  - Print PDF (buka PDF di tab/iframe lalu print).
  - Send invoice email ke client (notifikasi khusus “InvoiceSentNotification” atau gunakan mailable baru).
- Lengkapi informasi:
  - Status, amounts (subtotal `invoice.amount`, pajak `invoice.tax_amount`, total `invoice.total_amount`).
  - Periode tagihan (`billing_period_start/end` bila tersedia).
  - Metode pembayaran dan `tripay_reference` (bila ada).
  - Breakdown item:
    - Prefer `invoice.items` (tabel `invoice_items`) jika tersedia untuk deskripsi dan jumlah per item.
    - Fallback ke `order_details` (subscription + template, domain, add-ons) bila `invoice.items` kosong.

C. Client Cancel Add-ons — Error Timeout
- Audit alur setelah `cancelAddon` dieksekusi:
  - Pastikan redirect kembali ke `client.orders.show` bukan ke alur checkout.
  - Hindari pemanggilan helper cookie checkout di client area orders.
  - Tampilkan flash message “Recurring add-on akan dibatalkan di akhir periode (tanpa refund)” atau “Add-on sekali pakai dibatalkan segera”.
- Tambahkan guard UI:
  - Disabled tombol setelah klik, menunggu feedback agar tidak trigger berkali-kali.
- Pastikan kolom `cancel_at_period_end` tersedia (konfirmasi migrasi sudah dijalankan).

D. Informasi Lengkap Orders Client
- Tambahkan di `client/orders/show.blade.php`:
  - “Paket Subscription: [nama] (Siklus: Bulanan/Tahunan)” dengan label konsisten.
  - “Periode Berjalan: [start] - [end]” bila ada; atau tampilkan “Next Due Date: [tanggal]”.
  - Seksi “Invoice Terkait” dengan daftar invoice order (status: sent/paid/overdue/cancelled) dan link “Lihat”.
  - Daftar add-ons: tampilkan `started_at`, `next_due_date`, dan badge “Cancel at period end” bila `cancel_at_period_end=true`.
- Backend:
  - Eager load `order.invoices` (atau scope khusus), `subscriptionPlan`, `template`, dan `orderAddons`.

E. Aktivasi Order → Buat Project
- Tambahkan logika pada `Admin\OrderController@update`: ketika status berubah ke `active` dan belum ada project, panggil `createProjectForOrder`.
- Validation & UX:
  - Saat aktivasi lewat update, tampilkan notifikasi “Project dibuat otomatis untuk order ini”.
- Testing:
  - Buat acceptance check bahwa jalur update ke `active` tetap membuat project bila belum ada.

## Dampak & Pengujian

- Pengujian Admin Order Pricing:
  - Verifikasi order id 51 sesuai contoh Big Pappa: Total Rp 2.549.000 (Subscription 1.999.000 + Add-ons 300.000).
- Pengujian Admin Invoice:
  - Tombol generate/download/print/send tersedia; PDF menampilkan item lengkap dan totals konsisten.
- Pengujian Cancel Add-ons:
  - Klik tombol cancel recurring tidak menyebabkan timeout; status add-on berubah `cancel_at_period_end=true`; one-time dibatalkan segera.
- Pengujian Orders Client:
  - Halaman detail menampilkan paket subscription, periode/due date, invoice terkait, informasi lengkap add-ons.
- Pengujian Aktivasi Manual:
  - Set status order menjadi `active` via edit → project dibuat otomatis bila belum ada.

## Risiko & Mitigasi
- Risiko ganda total akibat accessor salah:
  - Mitigasi: definisikan sumber kebenaran “Total” dan pastikan tampilan tidak melakukan penjumlahan ulang terhadap `order->amount`.
- Risiko PDF item kosong:
  - Mitigasi: fallback ke `order_details` + relasi `order.orderAddons`.
- Risiko email fail:
  - Mitigasi: try/catch saat kirim mailable; tampilkan log per kasus.
 

## Catatan Kompatibilitas
- `PaymentController` sudah mengaktifkan order & membuat/mengaktifkan project otomatis setelah invoice `PAID`. Fokus perbaikan adalah jalur admin manual (`update`) yang saat ini tidak memicu pembuatan project.

## Next Steps
- Menunggu jawaban atas pertanyaan klarifikasi di atas dari Big Pappa.
- Setelah approve, saya eksekusi rencana fase demi fase dan memperbarui Memory Bank (`activeContext.md` dan `progress.md`) setelah tiap fase selesai.