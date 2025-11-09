# Project Brief

Nama Proyek: Gawe Agency Client

Tujuan Utama:
- Menyederhanakan alur checkout agar cepat, jelas, dan minim langkah.
- Menggabungkan pemilihan paket berlangganan dan Add-ons dalam satu halaman `configure`.
- Mengarahkan langsung ke `summary` setelah submit `configure` (POST), mengurangi friksi.
- Otomatis mengisi `customer_info` untuk user yang sudah login saat migrasi cart.

Ruang Lingkup:
- Fokus pada checkout flow (domain → template → personal-info → configure → summary).
- Pemetaan data cart konsisten melalui `CartService` dan cookie helper.
- Penyesuaian UI hanya pada halaman `configure` untuk integrasi Add-ons.

Hasil yang Diharapkan:
- Alur checkout stabil, mudah dipahami, dan teruji (feature & e2e).
- Dokumentasi Memory Bank mencerminkan keputusan dan alur terbaru.