Product Requirements Document (PRD): GaweClient

Versi: 1.0
Tanggal: 11 September 2025
Penulis: AI Project Manager
1. Ringkasan Eksekutif

GaweClient adalah platform Website as a Service (WaaS) yang dirancang untuk agensi "Gawe Agency". Aplikasi ini berfungsi sebagai portal klien terpusat yang mengotomatiskan dan menyederhanakan seluruh siklus hidup klien, mulai dari pemesanan layanan, manajemen proyek, penagihan (billing & invoicing), hingga layanan dukungan (support). Tujuannya adalah untuk meningkatkan efisiensi operasional agensi, memberikan transparansi kepada klien, dan menciptakan pengalaman pelanggan yang profesional.

Stack Teknologi: Laravel, Livewire, Tailwind CSS + DaisyUI, MySQL.
2. Visi dan Tujuan Produk

Visi: Menjadi platform terpusat yang andal dan mudah digunakan bagi Gawe Agency dan kliennya untuk mengelola semua aspek layanan website secara efisien dan transparan.

Tujuan Bisnis:

    Mengurangi waktu administrasi manual hingga 70% terkait penagihan dan pembuatan proyek.

    Meningkatkan tingkat retensi klien dengan menyediakan portal layanan mandiri yang profesional.

    Menciptakan alur pendapatan berulang (recurring revenue) yang terkelola secara otomatis.

    Menstandarisasi proses onboarding dan dukungan klien.

Tujuan Pengguna:

    Untuk Klien: Memberikan kemudahan dalam memesan layanan, melacak progres proyek, membayar tagihan, dan mendapatkan dukungan dalam satu tempat.

    Untuk Staf/Admin: Menyediakan alat untuk mengelola produk, proyek, tagihan, dan interaksi klien secara efisien tanpa perlu berpindah-pindah platform.

3. Persona Pengguna

    Klien (User):

        Deskripsi: Pemilik bisnis kecil atau individu yang memesan layanan pembuatan website dari Gawe Agency. Mereka mungkin tidak terlalu teknis.

        Kebutuhan: Proses pemesanan yang jelas, kemudahan melihat status proyek, pengingat tagihan yang tepat waktu, dan cara mudah untuk menghubungi dukungan jika ada masalah.

    Staf (Staff):

        Deskripsi: Tim operasional Gawe Agency (misalnya, manajer proyek atau tim support).

        Kebutuhan: Antarmuka untuk mengelola status proyek yang ditugaskan, membalas tiket dukungan, dan melihat informasi detail klien tanpa harus bertanya ke bagian lain.

    Admin:

        Deskripsi: Pemilik agensi atau manajer utama dengan hak akses penuh.

        Kebutuhan: Dasbor untuk memantau kesehatan bisnis (pendapatan, proyek baru), mengelola daftar produk dan harga, mengelola akun pengguna (staf dan klien), dan melakukan konfigurasi sistem secara keseluruhan.

4. Fitur dan Fungsionalitas Rinci
4.1. Manajemen Penagihan (Billing)

    Produk & Layanan:

        Admin dapat melakukan CRUD (Create, Read, Update, Delete) untuk produk.

        Atribut Produk: Nama, Deskripsi, Harga dengan siklus penagihan (Bulanan, Triwulanan, Tahunan, Dua Tahunan, Tiga Tahunan).

    Add-On Produk:

        Admin dapat melakukan CRUD untuk add-on.

        Atribut Add-on: Nama, Deskripsi, Harga dengan tipe penagihan (Sekali Bayar / One Time, atau Berulang seperti produk utama).

    Invoice:

        Pembuatan invoice otomatis saat klien melakukan pemesanan baru.

        Pembuatan invoice berulang (recurring) otomatis untuk perpanjangan layanan.

        Admin dapat membuat invoice manual (one-time).

        Struktur Invoice: Nomor unik, detail klien, item tagihan (produk & add-on), subtotal, pajak, total, tanggal terbit, tanggal jatuh tempo, status (Unpaid, Paid, Cancelled).

        Notifikasi email otomatis untuk invoice baru dan pengingat pembayaran.

4.2. Manajemen Proyek

    Setiap pesanan yang lunas akan secara otomatis membuat sebuah proyek.

    Admin/Staf dapat mengelola proyek.

    Atribut Proyek: Nama Proyek, Klien Pemilik, Status Proyek (Pending, Aktif, Suspended, Dibatalkan), Catatan internal, Detail akses website (URL, username, password).

    Klien dapat melihat daftar proyek mereka dan detailnya (kecuali catatan internal).

4.3. Sistem Tiket Dukungan

    Klien dapat membuat tiket baru dari area klien.

    Formulir Tiket: Subjek, Prioritas (Rendah, Sedang, Tinggi), Departemen (misal: Teknis, Penagihan), Isi Pesan, Lampiran File.

    Klien, Staf, dan Admin dapat saling membalas di dalam sebuah tiket.

    Status Tiket: Open, In Progress, Closed.

    Notifikasi email untuk setiap balasan tiket.

4.4. Manajemen Pengguna

    Tiga peran pengguna: user (klien), staff, admin.

    Klien dapat mendaftar saat proses pemesanan.

    Admin dapat melakukan CRUD untuk semua akun pengguna (termasuk Staf).

    Fitur standar: Login, Lupa Password.

4.5. Manajemen Konten & Template

    Manajemen Template Website:

        Admin dapat melakukan CRUD untuk template website yang ditawarkan.

        Atribut Template: Nama, Deskripsi, URL Demo, Gambar Thumbnail.

    Manajemen Template Email:

        Admin dapat mengedit konten template email sistem.

        Jenis Template: Selamat Datang, Notifikasi Invoice Baru, Pengingat Pembayaran, Konfirmasi Pembayaran, Notifikasi Balasan Tiket, dll.

4.6. Manajemen Pembayaran

    Konfigurasi Pajak: Admin dapat mengatur persentase pajak (misal: PPN 11%) yang akan ditambahkan ke invoice.

    Pengaturan Gateway Pembayaran: Admin dapat memasukkan kredensial API untuk integrasi dengan Tripay.

    Log Transaksi: Sistem mencatat semua transaksi pembayaran yang berhasil maupun gagal.

5. Alur Pengguna (User Flow)
5.1. Alur Pemesanan Baru

    Pemilihan: Calon klien mengunjungi halaman publik, melihat galeri template website, dan memilih salah satu.

    Konfigurasi: Klien memilih siklus penagihan (misal: Tahunan) dan menambahkan add-on jika diperlukan.

    Checkout: Sistem menampilkan ringkasan pesanan. Klien mengisi informasi pribadi dan mendaftarkan akun.

    Invoice Terbit: Invoice secara otomatis dibuat dengan status Unpaid dan jatuh tempo 7 hari dari sekarang (H+7).

    Pembayaran: Klien diarahkan ke halaman invoice untuk melakukan pembayaran melalui Tripay.

    Callback: Setelah pembayaran berhasil, Tripay mengirimkan callback ke sistem. Status invoice diubah menjadi Paid.

    Proyek Dibuat: Proyek baru secara otomatis dibuat dengan status Pending. Notifikasi dikirim ke Admin.

    Aktivasi Proyek: Admin memproses proyek, mengubah statusnya menjadi Aktif, dan mengisi detail akses website untuk klien. Notifikasi dikirim ke klien bahwa proyek telah aktif.

    Pembatalan Otomatis: Jika invoice tidak dibayar dalam 7 hari, statusnya otomatis berubah menjadi Cancelled.

5.2. Alur Perpanjangan & Suspensi

    Invoice Perpanjangan: 14 hari sebelum layanan berakhir, sistem otomatis membuat invoice perpanjangan.

    Notifikasi: Klien menerima email notifikasi mengenai invoice perpanjangan.

    Pembayaran: Klien membayar invoice tersebut.

    Suspensi: Jika 14 hari setelah tanggal jatuh tempo layanan invoice belum dibayar (H+14), status proyek terkait otomatis diubah menjadi Suspended.

    Pembatalan: Invoice perpanjangan yang tidak dibayar juga akan berstatus Cancelled setelah melewati batas waktu.

6. Kriteria Keberhasilan

    95% invoice dibuat dan dikirim secara otomatis tanpa intervensi manual.

    Proses onboarding klien baru (dari pemesanan hingga proyek aktif) memakan waktu kurang dari 24 jam.

    Jumlah tiket dukungan terkait pertanyaan status proyek atau tagihan berkurang 50%.