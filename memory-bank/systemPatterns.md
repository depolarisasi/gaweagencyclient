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