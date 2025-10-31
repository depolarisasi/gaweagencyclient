# Ringkasan Proyek

## Deskripsi Singkat
Platform SaaS untuk pembuatan website berbasis template dengan langganan. Sistem menyediakan alur checkout bertahap, manajemen produk/template, proyek, tiket dukungan, serta integrasi pembayaran Tripay. Aplikasi berbasis peran (admin, staff, client) dengan dashboard dan hak akses masing-masing.

## Fitur Utama
- Autentikasi & peran: registrasi, login/logout, reset password, redirect dashboard per peran.
- Dashboard per peran: Admin (statistik & manajemen), Staff (proyek/pesanan/invoice), Client (produk, pesanan, proyek, invoice, profil).
- Manajemen Produk & Template: CRUD, status aktif/nonaktif, pencarian, statistik, bulk actions.
- Rencana Langganan: CRUD rencana, toggle status, integrasi ke checkout.
- Checkout bertahap: pilih template → konfigurasi (rencana/siklus) → addons → data pribadi & domain → ringkasan → billing → sukses.
- Domain: cek ketersediaan, saran, daftar TLD, pilih domain baru/eksisting/subdomain.
- Pembayaran & Invoice: generate invoice, pilih channel (VA/e-wallet/QRIS), instruksi pembayaran, cek status, callback Tripay.
- Pesanan (Orders): CRUD, aktivasi/suspend/cancel, statistik, bulk actions.
- Proyek: assignment ke staff, progress/hold/resume/complete, statistik, bulk actions.
- Tiket Dukungan: buat, balas, in-progress, resolve, close, assign, bulk actions (admin & client).
- Subscription Management (Client): lihat langganan, upgrade paket, buat order upgrade.
- Notifikasi: notifikasi proyek baru/aktivasi ke admin.
- API: domain (availability, suggestions, TLDs) dan pembayaran (cek status).

## Workflow Utama
- Autentikasi: pengguna register/login; sistem memvalidasi status; redirect ke `admin/staff/client` dashboard; reset password melalui email.
- Checkout: pilih template → set rencana/siklus → pilih addons → isi data & domain → lihat ringkasan (total langganan+addons+domain, pilih channel) → submit → sistem membuat `order`, `invoice`, `project` → tampilkan billing & instruksi → halaman sukses.
- Pembayaran: client membuka invoice, memilih channel, menerima instruksi; callback Tripay memperbarui status `payment/invoice/order`; client dapat cek status pembayaran.
- Recurring Billing: invoice awal dibayar → proyek aktif → sistem set `next_due_date` → pada jatuh tempo dibuat invoice berulang; client mengelola dari SubscriptionManager.
- Admin Manajemen: CRUD user/produk/template/rencana/pesanan/proyek/tiket; toggle status & bulk actions; pesanan dapat diaktifkan/suspend/cancel; proyek dikelola (assign/progress/hold/resume/complete); tiket diassign dan ditutup.
- Client: jelajah produk → lakukan checkout → bayar → pantau pesanan/invoice/proyek → kelola tiket dukungan → ubah profil & password → upgrade langganan bila perlu.
- Staff: lihat proyek yang ditugaskan, perbarui progres, tangani tiket sesuai penugasan.

## Catatan Teknis Singkat
UI checkout/domain/invoice memanfaatkan Livewire; integrasi pembayaran menggunakan `TripayService`; pengecekan domain via `DomainService`; data inti meliputi `User`, `Product`, `Template`, `SubscriptionPlan`, `Order`, `Invoice`, `Project`, `SupportTicket`.