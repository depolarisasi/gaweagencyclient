# Product Context

Mengapa proyek ini ada:
- Memberikan pengalaman checkout yang ringkas dan terarah untuk produk/jasa digital.
- Mengurangi kebingungan dengan menyatukan pemilihan paket dan Add-ons dalam satu konteks.

Masalah yang diselesaikan:
- Banyak langkah terpisah menyebabkan drop-off dan data tidak konsisten.
- Add-ons sering terlupakan ketika dipisah ke halaman lain.

Bagaimana seharusnya bekerja:
- Pengguna memilih domain → template → personal-info.
- Halaman `configure` menampilkan paket berlangganan, fitur paket, serta daftar Add-ons (checkbox).
- Submit `configure (POST)` menyimpan `subscription_plan_id`, `billing_cycle`, dan `selected_addons[]` lalu redirect ke `summary`.

Tujuan UX:
- Minim klik, jelas, dan langsung ke ringkasan setelah konfigurasi.
- Preselect Add-ons dari cart bila sudah ada; informasi paket mudah dibandingkan.