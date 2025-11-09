# Tech Context

Teknologi & Setup:
- `PHP` (Laravel), Blade templating; kemungkinan Livewire di beberapa komponen.
- Server dev: `php artisan serve` pada `http://127.0.0.1:8000`.
- Penyimpanan state checkout: cookie (via `CheckoutCookieHelper`) dan model Cart.

Keterbatasan Teknis:
- Harus menjaga konsistensi antara cookie dan Cart sebelum pembayaran.
- UI harus ringan (KISS), tanpa over-engineering.

Dependensi Penting:
- `CartService` untuk semua mutasi cart.
- `routes/web.php` untuk nama route konsisten di view/controller.

Pengembangan:
- Verifikasi visual menggunakan preview halaman `checkout/configure/` setelah perubahan UI.
- Sesuaikan feature/e2e tests jika bergantung pada halaman `addon` terpisah.