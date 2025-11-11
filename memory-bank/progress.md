# Progress

## 2025-11-11 — TLD Pricing, DomainService, & Admin markAsPaid
- CRUD TLD Pricing: resource route `admin.tld-pricings.*` ditambahkan; UI admin mendapat menu "TLD Pricing" di sidebar (Management).
- Database: migrasi `tld_pricings` dijalankan dan seeder awal dimasukkan (harga berbagai TLD), siap dipakai oleh DomainService.
- DomainService: integrasi harga domain dari DB (`tld_pricings`) dengan fallback statik; `getSupportedTlds()` menyatukan default + TLD aktif dari DB.
- Admin InvoiceController@markAsPaid: diselaraskan aktivasi order & project saat invoice ditandai `paid` menggunakan `ActivationService::activateOrderAndProjectFromInvoice(..., sendNotification:true)`, menghindari duplikasi logic. `next_due_date` ditetapkan konsisten (pakai `invoice.billing_period_end` bila ada, fallback ke `Order::calculateNextDueDate()`); project aktif/terbuat dengan `start_date`. Notifikasi PaymentSuccessful dikirim ke klien (best-effort) dengan lampiran PDF bila tersedia.
- Verifikasi: preview halaman `http://127.0.0.1:8000/admin/tld-pricings` dibuka untuk memastikan UI CRUD TLD Pricing berfungsi.

## 2025-11-11 — Refactor Aktivasi via Service
- Service baru: `ActivationService` dibuat untuk mengkapsulasi aktivasi order/project dan pengiriman notifikasi sukses pembayaran dari invoice berstatus `paid`.
- Integrasi awal: Admin `InvoiceController@markAsPaid` memanggil service ini.
- Rencana berikutnya: Checkout/Payment akan di-refactor untuk memakai service yang sama.

## 2025-11-11 — Checkout/Payment gunakan ActivationService
- CheckoutController: blok aktivasi saat status `PAID` di `checkTripayStatus` diganti menjadi panggilan `ActivationService::activateOrderAndProjectFromInvoice($invoice, false)` agar tidak mengirim notifikasi ganda.
- PaymentController: pada callback Tripay dan fallback polling, ketika transisi ke `paid`, memanggil `ActivationService::activateOrderAndProjectFromInvoice($invoice, true)` untuk aktivasi dan pengiriman notifikasi `PaymentSuccessful` secara terpusat.
- Konsistensi: `order.next_due_date` ditetapkan memakai `invoice.billing_period_end` (fallback `Order::calculateNextDueDate()`), project diaktifkan atau dibuat otomatis bila belum ada.

## 2025-11-11 — Cleanup & Audit Aktivasi
- Menghapus metode privat lama di `PaymentController`: `activateOrder`, `activateProject`, `generateProjectName` (telah diganti oleh `ActivationService`).
- Audit duplikasi aktivasi: jalur pasca pembayaran terpusat ke `ActivationService` (Admin markAsPaid, Payment callback & polling, Checkout status final). Tidak ada blok aktivasi tersisa di controller lain.
- Audit konsistensi `next_due_date`: penetapan hanya dilakukan oleh `ActivationService` untuk jalur paid; tidak ditemukan penetapan lain yang konflik.
- Catatan legacy: `Livewire\\CheckoutSummary` membuat `Project` pra-pembayaran (status `pending`) dan bukan bagian jalur paid saat ini; dibiarkan apa adanya.

## 2025-11-11 — Kebijakan Invoice & Reminders
- Menghapus command `CancelExpiredInvoices` dan `CancelUnpaidInvoices` dari codebase (tidak dijadwalkan) untuk mencegah konflik kebijakan dengan penandaan `overdue` dan suspend H+14.
- Reminders idempoten: menambahkan kolom JSON `invoices.reminders` untuk penanda per-offset (H-7/H-1/H+3/H+7/H+14); sistem skip pengiriman jika sudah bertanda, dan logging jumlah terkirim per-batch diperkuat.
- Harmonisasi siklus penagihan: generator recurring mendukung enum `6_months` berdampingan dengan `semi_annually` (kompatibilitas lintas model/enum), tanpa mengubah `order.next_due_date` saat generate.

Selesai (Plan B/C/D — 2025-11-10)
- Admin InvoiceController: kirim email (`send()` → status `sent` + `InvoiceSentMail`) dan dukungan inline print melalui `download()`; respons JSON untuk JS view.
- Email Template: `emails/invoice-sent.blade.php` ditambahkan menampilkan nomor, total, dan jatuh tempo.
- Admin Invoice View: baris `Tripay Reference` ditampilkan bila ada.
- Client Orders View: kartu “Paket Subscription” (nama, harga, siklus) dan baris “Periode” dari invoice terbaru.
- Client DashboardController: alur cancel/uncancel add-on diverifikasi sesuai (recurring: `cancel_at_period_end`; one-time: batal segera), redirect & pesan konfirmasi siap.
- Eager load pada `showOrder`: relasi `subscriptionPlan`, `template`, `orderAddons.productAddon`, dan `invoices` tersedia untuk tampilan.
-
- Support Ticket: notifikasi dibuat & terintegrasi
  - Notifikasi: `SupportTicketCreatedNotification`, `SupportTicketRepliedNotification`, `SupportTicketStatusUpdatedNotification` + template markdown.
  - Integrasi: Client `store()` kirim email ke pembuat; Admin `store/reply/status` kirim sesuai aksi (reply non-internal, assign/markInProgress/resolve/close/reopen).
  - Ketahanan: semua pengiriman dibungkus `try/catch` + `Log::error` agar UI tidak gagal saat SMTP error.
  - Pengujian: `SupportTicketNotificationsTest` lulus untuk jalur create, reply, dan status update.

Verifikasi (2025-11-10)
- Login klien berhasil (`john@example.com` / `password123`), halaman `/client/orders` dapat diakses dan ditampilkan.
- Tombol "Details" mengarah ke `client.orders.show`; UI detail menampilkan status add-ons dan tombol batal sesuai tipe (recurring vs one-time).
- Preview UI dilakukan dan diabadikan melalui screenshot untuk referensi.
- Diketahui isu minor saat `db:seed`: duplikasi email admin; tidak mengganggu jalur preview klien.

Rekap status implementasi dan pengujian alur checkout.

## Status
- Alur baru berjalan: `domain → template → personal-info → configure (paket + add-ons) → summary`.
- Perlu penyesuaian minor pada beberapa test untuk redirect `configure POST → summary`.
- Dokumentasi aktif (`activeContext.md`) diperbarui; `techContext.md` tetap relevan untuk setup Playwright/Laravel.
 - Penamaan halaman Template diseragamkan (`checkout.template`), rute POST menggunakan `checkout.template.post`.
 - Guest fresh kini diarahkan benar ke `/checkout/` (Domain) saat klik Cart; fallback reuse keranjang tamu lain dihapus.
 - Stabilisasi recurring & reminders aktif: generator recurring 14 hari, status `sent/overdue`, penjadwalan mark-overdue & send-reminders, dan suspend overdue H+14.

## Sudah Berfungsi
- Generator recurring (`invoices:generate-recurring`): memilih order dengan `next_due_date` ≤ 14 hari, membuat invoice `sent` dengan `due_date` sama, tanpa mengubah `next_due_date` order saat generate.
- Model `Invoice`: tambah `fillable` & `casts` (is_renewal, billing_period_start/end, payment_url/code/instructions, payment_expired_at) dan scope `sent`.
- Commands baru: `invoices:mark-overdue` (menandai `sent` yang lewat jatuh tempo → `overdue`) dan `invoices:send-reminders` (H-7/H-1 untuk `sent`, H+3/H+7/H+14 untuk `overdue`).
- Scheduler (`Console\Kernel`): mengganti `cancel-expired` dengan `mark-overdue`, menambahkan `send-reminders`, dan menonaktifkan `cancel-unpaid`.
- Suspend overdue (`projects:suspend-overdue`): kini memproses invoice renewal `overdue` H+14, suspend order & project, dan cancel invoice.
- PaymentController: dedup notifikasi (hanya kirim saat transisi ke `paid`), aktifkan order dan set `next_due_date` ke `billing_period_end` invoice (fallback aman bila null).
- Email pembayaran sukses: tanggal memakai `paid_date` atau fallback `tripay_data.paid_at`, jumlah dibayar memakai `tripay_data.amount_received` bila tersedia.
- Mapping Tripay (`config/tripay.php`): `UNPAID → sent`, `EXPIRED → overdue`, `FAILED/REFUND → cancelled` untuk konsistensi UI & controller.
- UI Client Invoices: `index.blade.php` dan `show.blade.php` diselaraskan ke status `sent/overdue`; auto-refresh & countdown hanya untuk `sent`.
 - UI Admin Invoices: `admin/invoices/index.blade.php` menyelaraskan label untuk status `sent` (badge "Sent"), dan kartu statistik berubah dari "Pending Payment" menjadi "Sent" namun tetap menghitung `status='sent'`.
 - Client Dashboard: `DashboardController` kini menghitung jumlah invoice belum dibayar menggunakan `status='sent'` (menggantikan referensi lama ke `pending`).
 - Client Add-ons: Uncancel (undo cancel at period end) berfungsi—route, controller, dan UI tombol konfirmasi siap pakai.
 - Commands (audit edge-case):
   - GenerateRecurringInvoices: filter `order_type='subscription'` dan guard idempoten per-periode (cek invoice existing dengan `billing_period_start == next_due_date`).
   - MarkOverdueInvoices: tambah `whereNotNull('due_date')` untuk keamanan.
   - SendInvoiceReminders: guard due_date tidak null + try/catch per notifikasi untuk ketahanan batch.
   - SuspendOverdueProjects: suspend `order` dan cancel invoice meski project tidak ada; logging diperluas.
  - Commands (refinement skalabilitas):
    - GenerateRecurringInvoices: hapus filter `created_at >= 30 hari` yang berpotensi salah-positif; tambah `whereNotNull('next_due_date')` dan proses dengan `chunkById(100)`.
    - SendInvoiceReminders: gunakan `chunkById(100)` untuk H-7/H-1 dan H+3/H+7/H+14; laporkan jumlah terkirim per batch.
    - SuspendOverdueProjects: batasi hanya pada orders `order_type='subscription'` dan gunakan `chunkById(100)`.
- POST `configure`: menyimpan `subscription_plan_id`, `billing_cycle`, `selected_addons[]` dan redirect ke `checkout.summary`.
- View `checkout/configure.blade.php`: UI paket & add-ons dalam satu form, dengan preselect add-ons dari cart.
- Auto-isi `customer_info` saat user login melalui `CartService::migrateFromSessionAndCookies`.
- `POST /checkout/domain` menyimpan `domain_type`, `domain_name`, `domain_tld`, `domain_price` dan redirect ke `/checkout/template`.
- Validasi `personal-info` pada e2e comprehensive berjalan setelah langkah domain.
- Dokumentasi aktif diperbarui untuk alur baru; server dev berjalan (`php artisan serve`), preview dibuka untuk review UI.
 - View `checkout/template.blade.php`: tombol Back ke langkah Domain + autoselect kartu dari `cart/cookie/session`.
 - Controller `template()`: mengirim `selectedTemplateId` dan me-render view `checkout.template` (rename dari `step1`).
- `checkout/billing.blade.php`: perbaikan Blade syntax (`@php ... @endphp`) mengatasi ParseError token `else`.
- Billing: normalisasi tipe domain di controller (map `register_new` → `new`, dukung `transfer`) dan view menampilkan harga domain untuk `transfer`; fallback nama domain kini mempertimbangkan key `domain`.
 - Billing: subtotal Add-ons kini konsisten tampil. Ditambahkan fallback perhitungan `addonsAmount` di controller dengan menjumlahkan harga dari relasi add-ons (pivot `price`) ketika nilai dari Order/Cart nol atau tidak tersedia.
 - Admin SubscriptionPlan: durasi readonly dan disinkronkan otomatis dari `billing_cycle` di UI.
 - Admin SubscriptionPlan: UI hanya select (tanpa input manual) dengan opsi `monthly`, `6_months`, `annually`, `2_years`, `3_years`.
 - Admin SubscriptionPlan: mapping `cycle_months` dilakukan di controller saat store/update (1/6/12/24/36) + normalisasi MySQL (`annual→annually`, `semi_annual→6_months`).
 - Email `order_created_with_invoice` stabil terhadap `user` null (menggunakan fallback).
 - Model label diperluas: `6_months`, `annually`, `2_years`, `3_years` untuk konsistensi tampilan.
 - Feature tests `AdminSubscriptionPlanControllerTest` lulus (mapping `cycle_months` tervalidasi).
 - View diselaraskan: Admin Orders (create/edit), Livewire Product Management, Admin Subscription Plans (index filter dan show), Livewire Subscription Manager memakai `billing_cycle_label`.
 - Migration `fix_subscription_plans_billing_cycle_enum` diterapkan: enum `subscription_plans.billing_cycle` → `monthly`,`quarterly`,`6_months`,`annually`,`2_years`,`3_years` dengan normalisasi nilai lama.
- Server dev berjalan (`php artisan serve`), preview dibuka untuk review UI.
 - Invoice Show: breakdown biaya kini ditampilkan (Subscription, Domain, Add-ons). Subtotal = `invoice->amount` (tanpa fee), Fee Tripay = `invoice->fee_customer`, Total Bayar = Subtotal + Fee Tripay.
 - Checkout → Tripay payload: amount dikirim sebagai subtotal, `order_items` mengandung item terpisah untuk subscription, domain, dan add-ons agar sesuai dokumentasi Tripay.
 - Konsistensi biaya: hilangkan penambahan biaya platform 3% di `CartService::getCartSummary` dan `Cart::calculateTotals`; biarkan Tripay menghitung `fee_customer` sesuai metode pembayaran.
 - TripayService diperbarui: `formatTransactionData()` gunakan `invoice->amount` dan route `invoice.show`; `createPaymentUrl()` ditambahkan untuk membaca `tripay_data.checkout_url`.
 - PaymentController diselaraskan dengan skema Invoice: referensi pembayaran memakai `tripay_reference` (bukan `payment_reference`), validasi create payment untuk status `sent`, update status callback menyimpan `paid_date`.
- Client Invoice: referensi ditampilkan dari `tripay_reference`, jumlah bayar fallback dari `tripay_data.amount_received`, tombol "Cetak PDF Invoice" ditambahkan dengan auto-print sederhana.
 - Client Invoice: referensi ditampilkan dari `tripay_reference`, jumlah bayar fallback dari `tripay_data.amount_received`, tombol "Cetak PDF Invoice" ditambahkan dengan auto-print sederhana. Tombol "Check Payment Status" di `client/invoices/show.blade.php` kini memakai `checkInvoicePaymentStatus(this)` dengan spinner/disable selama cek.
 - Client Invoice Total: konsolidasi tampilan subtotal/tax/total memakai `invoice->amount`, `invoice->tax_amount`, dan menampilkan "Biaya Admin (Customer)" (`invoice->fee_customer`). "Biaya Admin (Merchant)" dihilangkan dari tampilan.
- Polling status pembayaran: `client/payment/instructions.blade.php` dan `client/invoices/show.blade.php` memanggil route `client.invoices.payment.status`. Backend `PaymentController@checkPaymentStatus` kini menyinkronkan status invoice ke `paid/expired/failed/refunded` (mapping Tripay) saat polling, menyetel `paid_date`, mengaktifkan project, dan mengirim notifikasi `PaymentSuccessful` jika `PAID`.
 - Halaman Instruksi Pembayaran: fungsi copy VA diperbaiki menjadi `copyToClipboard(text, this)` agar tidak bergantung pada `event` global; tombol "Check Status" kini menampilkan spinner dan mendisable saat request berlangsung. Countdown diperbaiki untuk memakai timestamp Tripay (detik → ms).
- TripayService::createTransaction mengirim request sebagai JSON (`asJson`) dan menerapkan fallback `callback_url` (config atau `route('payment.callback')`), `return_url` (home), serta `customer_phone` default bila kosong.
- CheckoutController::submit menyertakan `callback_url` ke payload dan fallback `customer_phone`, serta guard error yang memunculkan pesan dari Tripay ketika `success=false` atau respons null.
- Perbaikan error handling Tripay: `TripayService::createTransaction` kini mengembalikan struktur standar `{success:false,message,data:null}` pada respons non-2xx (mencoba parse JSON error atau fallback snippet body), sehingga controller tidak menerima `null`. `CheckoutController::submit` diperbaiki untuk melempar `Exception($message)` alih-alih `Exception($transaction)` agar pesan jelas tampil di UI/log.
- Perbaikan inkonsistensi Tripay (`order_items != amount`): `CheckoutController::submit` kini menormalkan itemisasi add-on memakai harga pivot (`addon->pivot->price`), merehitung `totalAmount` dari komponen (subscription + domain + add-ons pivot), lalu menambahkan sanity check—bila jumlah item tidak sama dengan `amount`, nilai `amount` dinormalisasi dan `order->amount` disinkronkan sebelum membuat invoice.
- Pencegahan back dari Billing: route `POST /checkout/reset` tersedia dan dipanggil dari script di `checkout/billing.blade.php` saat user menekan tombol Back (popstate) atau mengklik link kembali ke `summary`. Endpoint ini menghapus cart (user/session), membersihkan seluruh session checkout, dan menghapus semua cookies checkout termasuk data pembayaran (`clearAllForce`). Setelah reset, user diarahkan ke awal alur (`checkout.index`).
- Guard server-side pada `CheckoutController@summary`: jika ditemukan `tripay_reference` atau `tripay_transaction` (cookie/session), redirect ke `checkout.index` agar tetap aman meski JS dimatikan.

Aktivasi Order & Project + UI Orders
- Saat invoice `PAID`, order diaktifkan (`status=active`, `activated_at`, `next_due_date`) dan project diaktifkan/ dibuat otomatis bila belum ada. Field start project diseragamkan ke `start_date` untuk konsistensi dengan migration dan view.
- Model `Order` kini memiliki accessor singular `invoice` dan `project` agar tampilan dapat mengakses entitas tunggal dengan aman.
- Client Dashboard `orders()` melakukan eager load `subscriptionPlan` dan `template`.
- View `client/orders.blade.php` menampilkan produk dengan fallback ke `subscriptionPlan->name` dan template bila tersedia (tidak lagi `N/A`).

Admin Orders Pricing Breakdown & Total (2025-11-10)
- Order total dihitung dari `subscription_amount + addons_amount + setup_fee` melalui accessor `Order::getTotalAmountAttribute`.
- Halaman `admin/orders/show.blade.php` menampilkan "Total Amount" dari `total_amount` dan Subtotal dari `subscription + addons`, sesuai rencana perbaikan.

Aktivasi via Admin Update (2025-11-10)
- `Admin\OrderController@update` kini secara otomatis membuat project ketika status berubah ke `active`, menyelaraskan jalur edit dengan jalur `activate` dan pembayaran sukses.

Client Orders — Invoice Terkait & UX Proteksi (2025-11-10)
- Controller `Client\DashboardController@showOrder` menambahkan eager load relasi `invoices`.
- View `client/orders/show.blade.php` kini memiliki seksi "Invoice Terkait" berisi daftar invoice order dan tautan ke detail.
- Tombol Cancel/Undo Add-on didisable sementara setelah konfirmasi untuk mencegah double submit.

Client Orders Detail & Cancel Add-ons (baru)
- Route `client.orders.show` untuk halaman detail order; tombol "Details" ditambahkan di daftar orders.
- Route `client.orders.addons.cancel` memungkinkan client membatalkan add-on:
  - Recurring: ditandai `cancel_at_period_end = true`.
  - One-time: dibatalkan segera (`status='cancelled'`, `cancelled_at=now`, `next_due_date=null`).
- View `client/orders/show.blade.php` menampilkan tabel add-ons dengan badge status, label billing, indikator "Cancel at period end", serta periode `started_at` dan `next_due_date` jika tersedia.

Uncancel Add-ons (baru)
- Route `client.orders.addons.uncancel` (POST) untuk menghapus penandaan `cancel_at_period_end`.
- Controller `DashboardController::uncancelAddon()` melakukan otorisasi, menolak undo untuk `status='cancelled'`, dan mengembalikan add-on recurring ke aktif.
- UI: tombol "Undo cancel at period end" dengan konfirmasi SweetAlert; indikator teks kecil menampilkan "Akan dibatalkan pada: {Next Due}" saat `cancel_at_period_end==true`.

Scheduler Add-ons
- Command `invoices:generate-recurring-addons` (harian 06:10), `addons:cancel-overdue` (07:10), dan `addons:apply-cancel-at-period-end` (07:15) telah ditambahkan ke `Console\\Kernel`.

Perbaikan Terbaru: Kolom Project yang Hilang
- Menambahkan migration `add_template_started_completed_to_projects_table` yang menambah `template_id` (FK `templates`, set null on delete), `started_at`, dan `completed_at` (semua nullable).
- Migrasi berhasil dijalankan; aktivasi manual order (admin) kini membuat Project tanpa error `Unknown column 'template_id'`.

Verifikasi
- Server dev berjalan dan preview dibuka di `http://127.0.0.1:8000/` untuk review UI. Error ORB eksternal tidak mempengaruhi fungsi halaman.

UI Billing — Copy & Polling Status
- Copy: di `checkout/billing.blade.php` dan `checkout/billing-cycle.blade.php`, tombol Copy diperbaiki agar tidak bergantung pada `event` global. Pemanggilan diubah menjadi `copyToClipboard(text, this)` dan fungsi menerima `buttonEl` untuk menampilkan feedback tersalin.
- Polling: endpoint `/api/payment/status/{reference}` (CheckoutController::checkTripayStatus) kini melakukan sinkronisasi status invoice ketika status final (`PAID/EXPIRED/FAILED/REFUND`) terdeteksi, menyetel `paid_date`, mengaktifkan project terkait, dan mengirim notifikasi sukses pembayaran jika `PAID`. Tombol JS diperbaiki menjadi `checkPaymentStatus(this)` dengan spinner yang aman.

Update Implementasi (2025-11-10 — Invoice & PDF)
- DashboardController diperbarui untuk meload relasi invoice lengkap (`items`, `order.product`, `order.template`, `order.orderAddons.productAddon`).
- Client Invoice View dirombak: memprioritaskan `invoice.items` sebagai sumber itemisasi, fallback ke `order_details` bila kosong; fee merchant tidak ditampilkan; subtotal memakai `invoice.amount`.
- Add-ons diselaraskan ke siklus bulanan: `billing_cycle='monthly'` saat pembuatan `OrderAddon`, dan perhitungan `next_due_date` disederhanakan ke `+1 bulan`.
- Label billing add-on disederhanakan: “Sekali” dan “Per Bulan”.
- InvoicePdfService kini meload relasi tambahan agar PDF menampilkan konteks lengkap.
- Template PDF invoice diganti ke layout profesional dengan header brand, metadata, tabel item (deskripsi, periode, jumlah), pajak, dan total.
- Migrasi DB berhasil: penambahan kolom pada `order_addons`, kolom `renewal_type` dan `items_snapshot` pada `invoices`, serta pembuatan tabel `invoice_items`. Satu migration diperbaiki dengan penghapusan ketergantungan `after('is_renewal')` secara kondisional.
- Server dev dijalankan dan preview tersedia di `http://127.0.0.1:8000/`. Error ORB eksternal pada resource pihak ketiga (Google) tidak memengaruhi fungsionalitas inti.

## 2025-11-11 — Checkout E2E + Add-on Recurring
- Checkout end-to-end berhasil hingga halaman Billing.
- Add-on terverifikasi: `SSL Certificate` (recurring). Subtotal Add-ons: `Rp 150.000` sesuai Ringkasan Pesanan.
- Bukti visual: screenshot `billing-page` dan `client-orders-index` (akses orders tanpa login diarahkan ke login).
- Catatan: verifikasi detail order via Billing karena `/client/orders` membutuhkan autentikasi.
- Langkah berikut: uji alur pembayaran Tripay hingga status `PAID`, lalu konfirmasi aktivasi order & project; tambah variasi add-on (≥2) untuk memvalidasi subtotal multi-add-on.

## 2025-11-11 — Normalisasi Add-on Recurring
- Normalisasi pembuatan `OrderAddon` di `CheckoutController@submit`:
  - `billing_cycle` diset ke `'monthly'` hanya untuk add-on `billing_type='recurring'`.
  - `next_due_date` dihitung `+1 bulan` hanya untuk add-on recurring; one-time → `next_due_date=null`.
  - Harga add-on pada OrderAddon menggunakan `pivot->price` bila tersedia untuk konsistensi dengan keranjang/itemisasi Tripay.
- Dampak UI: label billing add-on tetap konsisten (`Sekali` bila `billing_cycle=null`, `Per Bulan` bila `'monthly'`).
- Verifikasi: alur checkout tetap berjalan, dan OrderAddon yang one-time tidak memiliki `billing_cycle`/`next_due_date`.

## Yang Belum
- Penyesuaian test untuk status baru `sent/overdue` di area invoice & scheduler.
- Audit referensi sisa ke `pending` di command lama `CancelExpiredInvoices`/`CancelUnpaidInvoices` (tidak dijadwalkan, low priority).
- Update ekspektasi di `CheckoutFlowTest`/`CheckoutEndToEndTest` untuk alur tanpa halaman `addon` terpisah.
- Tambahan skenario e2e untuk variasi domain baru vs existing (opsional).
- Audit minor referensi lama `checkout.addons` (low risk) bila masih ada.
 - Tambah test untuk skenario ≥2 add-ons memastikan subtotal add-ons muncul dan sesuai.
 - Selaraskan factory `SubscriptionPlanFactory` dan label di `OrderAddon` untuk nilai baru (opsional, non-blocking).
- Model `Order` ditambah accessor `customer_info`, `domain_info`, `domain_amount` sehingga billing saat Order sudah terbentuk menampilkan data konsisten (termasuk domain price) tanpa bergantung ke Cart.
- Sinkronisasi field pembayaran lama: rujukan di view/controller yang masih memakai `payment_reference` akan dihapus/diubah ke `tripay_reference` (ongoing audit ringan).
 - Tambah test untuk fallback polling agar tidak terjadi double-processing ketika callback dan polling terjadi berdekatan (mitigasi diarahkan via idempotency update).
- Pertimbangkan guard server-side di `CheckoutController@summary` untuk mendeteksi state billing (mis. keberadaan `tripay_reference`) dan arahkan ulang ke `checkout.index` agar robust meski JS dimatikan.
 - Tambah E2E test untuk Cancel/Undo flow pada Client Orders.

Tambahan
- Tambah tests untuk accessor `Order::invoice` dan `Order::project` (latest/active, null-safe).
- Tambah e2e verifikasi invoice `PAID` → order aktif → project aktif dengan `start_date` terset.
Support Tickets — Attachments (2025-11-10)
- Admin UI: form balasan diperbarui dengan input `attachments[]` (multiple, accept: pdf/png/jpg/jpeg/gif) dan `enctype` multipart.
- Admin Thread: lampiran per-reply dirender di view menggunakan `Storage::url($file['path'])`.
- Admin Controller: `SupportTicketController@reply` memvalidasi dan menyimpan lampiran ke `public/ticket_attachments/{ticket_id}` lalu meneruskan metadata ke `addReply(...)`.
- Notifikasi: tetap mengirim `SupportTicketRepliedNotification` untuk balasan non-internal.
- Environment: `php artisan storage:link` dijalankan; catatan alternatif junction (`mklink /J`) ditambahkan untuk Windows bila symlink gagal.
- Logging: penanganan error upload ditingkatkan dengan `Log::warning/error` saat file invalid/gagal simpan.
## 2025-11-11 — PDF Invoice Redesign & Helper Terbilang
- InvoicePdfService: mengaktifkan `isRemoteEnabled` agar PDF dapat memuat resource eksternal.
- Template PDF `pdf/invoice.blade.php`: tabel item menjadi 4 kolom (Deskripsi, QTY, Harga Satuan, Jumlah); domain dan add-ons ditampilkan dengan ringkas; Pajak & Total memakai `formatIDR`.
- Footer PDF: menampilkan baris “Terbilang: {teks} rupiah” bila `terbilang_idr()` tersedia.
- Helper: `terbilang_idr()` ditambahkan di `app/helpers.php` untuk konversi angka ke teks bahasa Indonesia.
- Debug route: `GET /debug/invoice/{invoice?}` tersedia di environment local untuk preview PDF tanpa autentikasi.
- Verifikasi: server dev berjalan (`php artisan serve`), preview dibuka di `http://127.0.0.1:8000/debug/invoice` dan layout baru terkonfirmasi.

## Tertutup
- PDF invoice redesain dan helper terbilang telah diterapkan serta diverifikasi di environment lokal.