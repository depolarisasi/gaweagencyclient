# Hasil Playwright E2E Tests

Ringkasan eksekusi E2E untuk GaweClient menggunakan Playwright.

## Eksekusi
- Perintah: `npx playwright test --reporter=list`
- Web server otomatis: `php artisan serve` melalui konfigurasi `playwright.config.js`
- Hasil (sebelumnya): 3 passed, 10 failed (durasi ~1.3m)
- Hasil (terbaru): 1 passed untuk `e2e/checkout-new-flow.spec.js` (≈51s), setelah penyesuaian selektor template (`.template-card`), selektor addons (`selected_addons[]`), dan penanganan guard revisit domain.

## Ringkasan Kegagalan Utama
- Banyak kegagalan terjadi pada langkah awal karena selector `.template-card` tidak ditemukan saat membuka `/checkout`.
  - Indikasi kuat bahwa halaman awal checkout saat ini mengarah ke langkah `domain`, bukan `template`, sehingga test yang mengharapkan halaman template gagal.
- Contoh error (dipotong):
  - `e2e/checkout-flow.spec.js:362:50` menunggu `locator('.template-card').first()` lalu gagal.
- Daftar spesifik yang gagal (chromium):
  - `admin-subscription-plan.spec.js` (satu kasus terkait readonly cycle)
  - `checkout-comprehensive.spec.js` (3 kasus: flow lengkap, validasi missing fields, back navigation)
  - `checkout-flow.spec.js` (6 kasus: flow lengkap, QRIS, validasi, cek ketersediaan domain, pricing, simulasi status pembayaran)

## Analisis Akar Masalah
- Rute dan guard checkout telah berubah:
  - `Route::get('/checkout', ...)` kini mengarah ke `index` yang redirect ke `checkout.domain`.
  - Test E2E mengasumsikan urutan awal adalah halaman `template` dengan kartu `.template-card` tersedia.
- Guard di `CheckoutController@configure` untuk GET mewajibkan `domain_data`, `template_id`, dan `customer_info` (hingga redirect ke `personal-info`).
  - Hal ini tidak selaras dengan beberapa skenario test yang mengisi data personal setelah konfigurasi.

## Rekomendasi Sinkronisasi
- Pilih salah satu pendekatan, lalu terapkan konsisten:
  1) Selaraskan test agar mengikuti flow baru: `domain → template → configure → addon → personal-info → summary → billing`.
  2) Atau longgarkan guard `configure` sehingga mengizinkan akses dengan `template_id` saja (personal-info diwajibkan menjelang `summary`).
- Setelah sinkronisasi, jalankan ulang: `npx playwright test`.

### Catatan Terbaru
- `checkout-new-flow.spec.js` telah mengikuti alur baru, dan lulus. Sisa suite `checkout-flow` dan `checkout-comprehensive` perlu diselaraskan penuh agar hasil keseluruhan meningkat.

## Artefak
- Laporan Playwright: direktori `playwright-report/` jika reporter HTML diaktifkan.
- Error context: lihat file di `test-results/` mis. `error-context.md` untuk tiap skenario gagal.