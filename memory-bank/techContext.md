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

---

## Catatan Teknis (ringkas)
- Kolom JSON `invoices.reminders` ditambahkan (nullable, cast ke array) untuk menandai pengingat per-offset: `before_7`, `before_1`, `after_3`, `after_7`, `after_14`.
- `SendInvoiceReminders`: guard `due_date` tidak null, cek status `sent/overdue`, skip jika penanda offset sudah ada, try/catch per-notifikasi, dan logging hasil.
- Penjadwalan harian: `invoices:mark-overdue` sebelum reminder, `invoices:send-reminders` pukul 08:00; `projects:suspend-overdue` H+14.
- Harmonisasi enum billing: `6_months` aktif bersama `semi_annually` (backward-compat), prefer `6_months` untuk input baru.