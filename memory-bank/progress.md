# Progress

Rekap status implementasi dan pengujian alur checkout.

## Status
- Alur baru berjalan: `domain → template → personal-info → configure (paket + add-ons) → summary`.
- Perlu penyesuaian minor pada beberapa test untuk redirect `configure POST → summary`.
- Dokumentasi aktif (`activeContext.md`) diperbarui; `techContext.md` tetap relevan untuk setup Playwright/Laravel.
 - Penamaan halaman Template diseragamkan (`checkout.template`), rute POST menggunakan `checkout.template.post`.
 - Guest fresh kini diarahkan benar ke `/checkout/` (Domain) saat klik Cart; fallback reuse keranjang tamu lain dihapus.

## Sudah Berfungsi
- POST `configure`: menyimpan `subscription_plan_id`, `billing_cycle`, `selected_addons[]` dan redirect ke `checkout.summary`.
- View `checkout/configure.blade.php`: UI paket & add-ons dalam satu form, dengan preselect add-ons dari cart.
- Auto-isi `customer_info` saat user login melalui `CartService::migrateFromSessionAndCookies`.
- `POST /checkout/domain` menyimpan `domain_type`, `domain_name`, `domain_tld`, `domain_price` dan redirect ke `/checkout/template`.
- Validasi `personal-info` pada e2e comprehensive berjalan setelah langkah domain.
- Dokumentasi aktif diperbarui untuk alur baru; server dev berjalan (`php artisan serve`), preview dibuka untuk review UI.
 - View `checkout/template.blade.php`: tombol Back ke langkah Domain + autoselect kartu dari `cart/cookie/session`.
 - Controller `template()`: mengirim `selectedTemplateId` dan me-render view `checkout.template` (rename dari `step1`).
 - `checkout/billing.blade.php`: perbaikan Blade syntax (`@php ... @endphp`) mengatasi ParseError token `else`.
 - Admin SubscriptionPlan: durasi readonly dan disinkronkan otomatis dari `billing_cycle` di UI.
 - Admin SubscriptionPlan: UI hanya select (tanpa input manual) dengan opsi `monthly`, `6_months`, `annually`, `2_years`, `3_years`.
 - Admin SubscriptionPlan: mapping `cycle_months` dilakukan di controller saat store/update (1/6/12/24/36) + normalisasi MySQL (`annual→annually`, `semi_annual→6_months`).
 - Email `order_created_with_invoice` stabil terhadap `user` null (menggunakan fallback).
 - Model label diperluas: `6_months`, `annually`, `2_years`, `3_years` untuk konsistensi tampilan.
 - Feature tests `AdminSubscriptionPlanControllerTest` lulus (mapping `cycle_months` tervalidasi).
 - View diselaraskan: Admin Orders (create/edit), Livewire Product Management, Admin Subscription Plans (index filter dan show), Livewire Subscription Manager memakai `billing_cycle_label`.
 - Migration `fix_subscription_plans_billing_cycle_enum` diterapkan: enum `subscription_plans.billing_cycle` → `monthly`,`quarterly`,`6_months`,`annually`,`2_years`,`3_years` dengan normalisasi nilai lama.
 - Server dev berjalan (`php artisan serve`), preview dibuka untuk review UI.

## Yang Belum
- Update ekspektasi di `CheckoutFlowTest`/`CheckoutEndToEndTest` untuk alur tanpa halaman `addon` terpisah.
- Tambahan skenario e2e untuk variasi domain baru vs existing (opsional).
- Audit minor referensi lama `checkout.addons` (low risk) bila masih ada.
 - Selaraskan factory `SubscriptionPlanFactory` dan label di `OrderAddon` untuk nilai baru (opsional, non-blocking).