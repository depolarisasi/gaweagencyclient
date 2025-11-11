# System Patterns

Arsitektur & Komponen Kunci:
- `Laravel` MVC: Controller mengorkestrasi flow, Service kapsulasi bisnis, Blade untuk UI.
- `CheckoutController@configure`: GET menyiapkan data template, paket, add-ons; POST menyimpan pilihan & redirect ke `summary`.
- `CartService`: `updateTemplate`, `updateSubscriptionPlan`, `syncAddons`, migrasi customer info dari session/cookies.
- `CheckoutCookieHelper`: util penyimpanan data checkout (template, paket, billing, add-ons, customer_info, domain, payment channel, tripay) via cookie.
- `routes/web.php`: grup `checkout` dengan nama route `checkout.configure`, `checkout.configure.post`, `checkout.summary`, dll.

Pola Desain:
- Single source of truth untuk cart di `CartService` (hindari duplikasi logic di controller/views).
- Integrasi Add-ons dalam satu form di `configure` untuk konsistensi data.
- Guard berlapis di `configure (GET)` memastikan `domain_data`, `template_id`, `customer_info` tersedia sebelum lanjut.

Relasi Komponen:
- View `checkout/configure.blade.php` → Controller `CheckoutController@configure` → Service `CartService` → Model/Cart → CookieHelper.
 - View `checkout/billing.blade.php` → Controller `CheckoutController@billing` → Model `Order` (accessor: `customer_info`, `domain_info`, `domain_amount`) atau `CartService::getCartSummary` bila Order belum ada.

Keputusan Teknis:
- Redirect `POST /checkout/configure` langsung ke `checkout.summary` agar ringkas.
- Menjaga kompatibilitas dengan data lama via migrasi dari session/cookies.
 - Menambahkan accessor di `Order` untuk menjaga KISS dan kompatibilitas view tanpa mengubah controller secara luas.

---

## Pola Status Invoice & Reminders (ringkas)
- Status konsisten: `sent` untuk belum dibayar, `overdue` untuk lewat jatuh tempo, `paid` untuk lunas, `cancelled` untuk dibatalkan.
- Mark-overdue: harian menandai `sent` yang `due_date < now()` menjadi `overdue` (tanpa auto-cancel).
- Suspend H+14: proyek & order disuspend jika renewal invoice tetap `overdue` selama 14 hari, invoice dicancel saat suspend.
- Reminders idempoten: kolom JSON `invoices.reminders` menyimpan penanda per-offset (H-7/H-1/H+3/H+7/H+14); command `send-reminders` skip bila sudah bertanda.
- Chunk & logging: pemrosesan reminders memakai `chunkById(100)` dan mencatat jumlah terkirim per-batch untuk ketahanan.

## Siklus Penagihan (ringkas)
- Harmonisasi enum: dukung `6_months` berdampingan dengan `semi_annually` untuk kompatibilitas model/UI.
- `GenerateRecurringInvoices`: menggunakan `order.next_due_date` sebagai `due_date` invoice dan tidak mengubah `order.next_due_date` saat generate.