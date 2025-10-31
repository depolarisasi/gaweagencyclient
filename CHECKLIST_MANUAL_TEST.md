# Checklist Uji Manual Sistem

Catatan: Setiap item di bawah ini memiliki format `[ ] FUNGSI` dan disusun per peran dan modul agar uji manual terstruktur dan menyeluruh.

## Umum (Public)
[X] Pengunjung dapat mengakses beranda
[X] Pengunjung dapat melihat detail template
[ ] Pengguna dapat membuka halaman invoice publik lewat tautan
[ ] Sistem menerima callback pembayaran dari Tripay (verifikasi manual)

## Autentikasi
[ ] User dapat register akun baru
[ ] User dapat login dengan kredensial valid
[ ] User tidak dapat login jika status user tidak aktif
[ ] User diarahkan ke dashboard sesuai peran (admin/staff/client)
[ ] User dapat logout
[ ] User dapat meminta reset password (kirim email)
[ ] User dapat reset password

## Checkout
[ ] User dapat memilih template (Step 1)
[ ] User dapat menyimpan pilihan template dan lanjut ke konfigurasi
[ ] User dapat memilih rencana langganan dan siklus billing (Step 2)
[ ] User dapat memilih addons produk (Step 3)
[ ] User dapat mengisi informasi pribadi dan domain (Step 4)
[ ] User dapat melihat ringkasan pesanan dan memilih metode pembayaran (Step 5)
[ ] User dapat submit checkout dan pembuatan order (Step 5 Submit)
[ ] User dapat melihat halaman billing beserta instruksi pembayaran (VA/QRIS) (Step 6)
[ ] User melihat halaman sukses setelah proses checkout (Step 7)

### Domain & Ringkasan
[ ] User dapat mengecek ketersediaan domain
[ ] User dapat memilih domain baru/eksisting/subdomain dan integrasi ke ringkasan
[ ] User dapat memilih channel pembayaran (VA bank, e-wallet, QRIS)
[ ] Ringkasan menghitung total (langganan, addons, domain) dengan benar
[ ] Order, invoice, dan project dibuat saat checkout

## Pembayaran & Invoice (Client)
[ ] Client dapat melihat daftar invoice
[ ] Client dapat membuka detail invoice
[ ] Client dapat melihat instruksi pembayaran untuk invoice
[ ] Client dapat membuat pembayaran (generate payment) untuk invoice
[ ] Client dapat mengecek status pembayaran invoice
[ ] Callback pembayaran memperbarui status invoice/order

## Dashboard Admin
[ ] Admin dapat melihat statistik pengguna, produk, proyek, pesanan
[ ] Admin dapat melihat pengguna, pesanan, proyek terbaru
[ ] Admin dapat membuka halaman pengaturan

## Manajemen User (Admin)
[ ] Admin dapat melihat daftar user
[ ] Admin dapat membuat user
[ ] Admin dapat mengedit user
[ ] Admin dapat menghapus user
[ ] Admin dapat mengaktifkan/menonaktifkan user (toggle status)
[ ] Admin dapat melakukan bulk action pada user (aktif/nonaktif/hapus)

## Manajemen Produk (Admin)
[ ] Admin dapat melihat daftar produk
[ ] Admin dapat membuat produk
[ ] Admin dapat mengedit produk
[ ] Admin dapat menghapus produk
[ ] Admin dapat melakukan force delete produk (hapus permanen)
[ ] Admin dapat mengaktifkan/menonaktifkan produk (toggle status)
[ ] Admin dapat melakukan bulk action pada produk

## Manajemen Template (Admin)
[ ] Admin dapat melihat daftar template
[ ] Admin dapat membuat template
[ ] Admin dapat mengedit template
[ ] Admin dapat menghapus template
[ ] Admin dapat melakukan bulk action pada template
[ ] Admin dapat mengaktifkan/menonaktifkan template
[ ] Admin dapat melihat statistik template
[ ] Admin dapat mencari template

## Rencana Berlangganan (Admin)
[ ] Admin dapat melihat daftar rencana langganan
[ ] Admin dapat membuat rencana langganan
[ ] Admin dapat mengedit rencana langganan
[ ] Admin dapat menghapus rencana langganan
[ ] Admin dapat mengaktifkan/menonaktifkan rencana langganan (toggle status)

## Manajemen Pesanan (Admin)
[ ] Admin dapat melihat daftar pesanan
[ ] Admin dapat membuat pesanan
[ ] Admin dapat mengedit pesanan
[ ] Admin dapat menghapus pesanan
[ ] Admin dapat mengaktifkan pesanan (activate)
[ ] Admin dapat menangguhkan pesanan (suspend)
[ ] Admin dapat membatalkan pesanan (cancel)
[ ] Admin dapat melakukan bulk action pada pesanan
[ ] Admin dapat melihat statistik pesanan

## Manajemen Proyek (Admin)
[ ] Admin dapat melihat daftar proyek
[ ] Admin dapat membuat proyek
[ ] Admin dapat mengedit proyek
[ ] Admin dapat menghapus proyek
[ ] Admin dapat menetapkan proyek ke staff (assign)
[ ] Admin dapat memperbarui progres proyek
[ ] Admin dapat menyelesaikan proyek
[ ] Admin dapat menahan proyek (hold)
[ ] Admin dapat melanjutkan proyek (resume)
[ ] Admin dapat melakukan bulk action pada proyek
[ ] Admin dapat melihat statistik proyek

## Tiket Dukungan (Admin)
[ ] Admin dapat melihat daftar tiket
[ ] Admin dapat membuat tiket
[ ] Admin dapat mengedit tiket
[ ] Admin dapat menghapus tiket
[ ] Admin dapat assign tiket ke staff
[ ] Admin dapat menandai tiket in-progress
[ ] Admin dapat menyelesaikan tiket (resolve)
[ ] Admin dapat menutup tiket (close)
[ ] Admin dapat melakukan bulk action pada tiket

## Dashboard Staff
[ ] Staff dapat melihat dashboard proyek, pesanan, invoice
[ ] Staff dapat melihat daftar proyek
[ ] Staff dapat melihat dukungan/tiket
[ ] Staff dapat melihat pesanan
[ ] Staff dapat melihat invoice

## Dashboard Client & Profil
[ ] Client dapat melihat dashboard statistik
[ ] Client dapat melihat daftar produk
[ ] Client dapat melihat daftar pesanan
[ ] Client dapat melihat daftar proyek
[ ] Client dapat melihat detail proyek
[ ] Client dapat mengakses dukungan
[ ] Client dapat melihat dan mengubah profil
[ ] Client dapat memperbarui password

## Tiket Dukungan (Client)
[ ] Client dapat melihat daftar tiket
[ ] Client dapat membuat tiket
[ ] Client dapat melihat detail tiket
[ ] Client dapat mengedit tiket
[ ] Client dapat menghapus tiket
[ ] Client dapat membalas tiket
[ ] Client dapat menutup tiket

## API Domain
[ ] API dapat mengecek ketersediaan domain
[ ] API dapat memberikan saran domain
[ ] API dapat menyediakan daftar TLD yang didukung

## API Pembayaran
[ ] API dapat mengecek status pembayaran Tripay

## Komponen Livewire & Halaman
[ ] ProductShowcase: menampilkan template/produk aktif, pilih template, lanjut ke konfigurasi
[ ] CheckoutConfigure: pengguna memilih rencana/siklus, menyimpan konfigurasi
[ ] CheckoutSummary: ringkasan pesanan, buat order/invoice/proyek, notifikasi admin
[ ] CheckoutSummaryComponent: memuat channel pembayaran Tripay dan dapat dipilih
[ ] InvoiceShow: halaman invoice menampilkan detail dan tautan pembayaran
[ ] DomainSelector: pengecekan domain dan integrasi sesi checkout
[ ] SubscriptionManager: client melihat langganan, upgrade, membuat order upgrade
[ ] CartNotification: notifikasi keranjang muncul saat item berada di sesi

## Alur Khusus & Edge Cases
[ ] Redirect `/dashboard` mengarahkan sesuai peran
[ ] Checkout untuk user login dan guest (registrasi otomatis bila diperlukan)
[ ] Perhitungan total mencakup langganan, addons, dan domain
[ ] Masa berlaku sesi checkout ditangani dengan benar
[ ] Pembuatan proyek setelah invoice dibayar (recurring billing)
[ ] Sistem membuat invoice berulang sesuai `next_due_date`