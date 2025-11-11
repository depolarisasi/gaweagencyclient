# Active Context

Update (Plan B/C/D — 2025-11-10)
- Admin Invoice: `InvoiceController@send` kini mengubah status ke `sent` dan mengirim email ke klien memakai `InvoiceSentMail`. Respons JSON disesuaikan untuk konsumsi JS di view admin.
- Admin PDF: `InvoiceController@download` mendukung inline PDF untuk workflow print di browser. View admin menambahkan tombol Preview/Print yang membuka PDF di tab baru.
- Admin Invoice View: menampilkan `Tripay Reference` bila tersedia agar admin mudah mencocokkan transaksi Tripay.
- Client Orders: halaman detail order menambah kartu “Paket Subscription” (nama, harga, siklus) dan baris “Periode” (start–end) dari invoice terbaru bila ada.
- Client Add-ons: logika `cancelAddon/uncancelAddon` ditinjau dan sesuai—recurring ditandai `cancel_at_period_end`, one-time dibatalkan segera; undo hanya untuk penandaan periode, bukan pembatalan permanen.
- Eager loading: `DashboardController@showOrder` meload `product`, `subscriptionPlan`, `template`, `orderAddons.productAddon`, dan `invoices` agar tampilan kaya konteks.

Verifikasi UI Client Orders (2025-11-10)
- Halaman `/client/orders` diverifikasi tampil normal dan tombol "Details" berfungsi menuju `client.orders.show`.
- Halaman detail order menampilkan add-ons dengan badge status dan aksi batal: recurring ditandai `cancel_at_period_end`, one-time dibatalkan segera.
- Login pengguna uji: `john@example.com` / `password123`. Screenshot indeks orders telah diambil untuk dokumentasi visual.
- Catatan: seeding ulang menghasilkan error duplikat email admin; tidak memblokir login klien untuk preview.

Fokus Saat Ini:
- Gabungkan UI Add-ons ke halaman `configure` dalam satu form.
- `POST /checkout/configure` menyimpan paket, billing cycle, dan add-ons; redirect ke `summary`.
- Auto-isi `customer_info` ketika user login saat migrasi cart.
 - Konsolidasikan penamaan: halaman Template menggunakan `checkout.template` (rename dari `step1`).
 - Prefill pilihan Template dari `cart`/`cookie`/`session` saat kembali ke langkah ini.

Stabilisasi Recurring & Reminders (aktif):
- Konsistensi status invoice: gunakan `sent` untuk belum dibayar, `overdue` untuk lewat jatuh tempo.
- Generator recurring: pilih order dengan `next_due_date ≤ 14 hari`, buat invoice status `sent` dengan `due_date = next_due_date` (tanpa mengubah `next_due_date` order saat generate).
- Penjadwalan: tambah `invoices:mark-overdue` harian untuk menandai `sent` yang lewat jatuh tempo → `overdue`, dan `invoices:send-reminders` harian untuk H-7/H-1 (sent) serta H+3/H+7/H+14 (overdue).
- Penangguhan proyek: `projects:suspend-overdue` menindak invoice renewal `overdue` H+14 dengan suspend project & order, dan cancel invoice.
- Notifikasi: tambahkan `InvoiceGeneratedNotification`, `InvoiceReminderBeforeDueNotification`, `InvoiceReminderAfterDueNotification`.

Tambahan Terbaru:
- Model `Order` kini memiliki accessor `customer_info`, `domain_info`, dan `domain_amount` untuk kompatibilitas dengan `CheckoutController@billing` dan view `checkout/billing.blade.php`. Ini menyederhanakan pengambilan data ketika sumbernya dari Order/Invoice, bukan Cart.
 - PaymentController diselaraskan: pakai `tripay_reference` (bukan `payment_reference`), validasi pembayaran untuk status `sent`, dan set waktu bayar ke `paid_date` agar konsisten dengan model & tampilan.
- View `client/invoices/show.blade.php`: tampilkan referensi dari `tripay_reference`, jumlah bayar fallback dari `tripay_data.amount_received`, dan tombol "Cetak PDF Invoice" dengan auto-print sederhana.
- Fallback polling status: `PaymentController@checkPaymentStatus` kini menyinkronkan status invoice (PAID/EXPIRED/FAILED/REFUND) ketika polling UI mendeteksi status final dari Tripay, termasuk set `paid_date`, aktivasi project, dan pengiriman notifikasi `PaymentSuccessful` jika `PAID`.

Penyesuaian Status & UI Invoice (terbaru):
- Mapping Tripay di `config/tripay.php`: `UNPAID → sent`, `EXPIRED → overdue`, `FAILED/REFUND → cancelled`.
- PaymentController: dedup pengiriman notifikasi (hanya saat transisi ke `paid`), aktifkan order dan set `next_due_date` ke `billing_period_end` invoice.
- View `client/invoices/index.blade.php`: label/aksi untuk status `sent` menggantikan `pending`.
- View `client/invoices/show.blade.php`: ganti semua `isPending()` menjadi cek `status==='sent'`, tampilkan badge `Overdue` untuk status `overdue`, auto-refresh & countdown hanya untuk `sent` yang masih aktif.
 - Konsolidasi total invoice klien: tampilkan Subtotal (`invoice->amount`), Pajak (`invoice->tax_amount`), dan Biaya Admin (Customer) (`invoice->fee_customer`); Total memakai `invoice->total_amount`. Baris Biaya Admin (Merchant) tidak ditampilkan.
- Email `payment_successful.blade.php`: perbaiki tanggal (pakai `paid_date` atau fallback `tripay_data.paid_at`) dan jumlah dibayar (pakai `tripay_data.amount_received` bila ada).
- View `admin/invoices/index.blade.php`: badge untuk status `sent` diganti dari "Pending" menjadi "Sent"; kartu statistik berubah dari "Pending Payment" menjadi "Sent" namun tetap menghitung `status='sent'`.

Audit & Patch Command (Edge-case):
- GenerateRecurringInvoices: tambah filter `order_type='subscription'` dan guard idempoten untuk periode yang sama (cek `billing_period_start == order.next_due_date` agar tidak duplikat). Tetap tidak mengubah `order.next_due_date` saat generate.
- MarkOverdueInvoices: tambahkan guard `whereNotNull('due_date')` agar aman terhadap data tidak lengkap.
- SendInvoiceReminders: tambahkan guard `whereNotNull('due_date')` dan try/catch pada pengiriman notifikasi supaya batch tidak gagal ketika sebagian invoice error.
- SuspendOverdueProjects: bila project tidak ada, tetap suspend `order` dan cancel invoice; logging diperluas dan idempoten.

Refinement Tambahan (Skalabilitas & Akurasi):
- GenerateRecurringInvoices: hapus filter `created_at >= 30 hari` yang berisiko melewatkan order valid; tambahkan `whereNotNull('next_due_date')` dan proses dengan `chunkById(100)` untuk skala besar.
- SendInvoiceReminders: proses H-7/H-1 dan H+3/H+7/H+14 menggunakan `chunkById(100)` dan menghitung jumlah terkirim per-batch agar aman di dataset besar.
- SuspendOverdueProjects: batasi hanya pada `order_type='subscription'` dan proses `chunkById(100)`; tetap suspend order dan batalkan invoice meskipun project tidak ada.

Fix Terbaru: Client Invoice — Tombol Status & Instruksi
- Halaman `client/invoices/show.blade.php`: tombol "Check Payment Status" diperbaiki untuk mengirim elemen tombol ke fungsi `checkInvoicePaymentStatus(this)`, menampilkan spinner kecil, dan mendisable tombol saat request berlangsung. Fungsi JS kini menangani status `PAID`, `EXPIRED`, `FAILED`, dan `REFUND` dengan feedback yang jelas.
- Halaman `client/payment/instructions.blade.php`: tombol salin VA menggunakan `copyToClipboard(text, this)` sehingga tidak bergantung pada `event` global. Tombol "Check Status" di halaman instruksi juga diperbaiki agar menampilkan spinner dan mendisable tombol saat pengecekan; countdown diperbaiki untuk memakai `expired_time` Tripay (detik → milidetik).

Perubahan Terbaru:
- Controller `CheckoutController@configure` meng-handle GET+POST dengan integrasi add-ons.
- View `checkout/configure.blade.php` menampilkan paket + add-ons, tombol "Lanjut ke Ringkasan".
- `CartService::syncAddons` digunakan untuk sinkronisasi add-ons dari POST.
 - Controller `CheckoutController@template` kini me-render `checkout.template` dan mengirim `selectedTemplateId` dari `cart/cookie/session/query`.
 - View `checkout/template.blade.php` menambahkan tombol Back ke langkah Domain dan autoselect kartu Template.
 - Link Back di Configure diarahkan ke `route('checkout.template')` (sebelumnya `checkout.step1`).
- Memperbaiki ParseError di `checkout/billing.blade.php` dengan mengganti `@php(...)` menjadi blok `@php ... @endphp`.
 - Normalisasi tipe domain di `CheckoutController@billing`: memetakan `register_new` → `new`, mendukung `transfer`, dan menambahkan fallback nama domain dari key `domain`. View `checkout/billing.blade.php` diperbarui untuk menampilkan harga domain pada tipe `transfer` (atau `register_new`) sama seperti `new`, sementara `existing` tetap "Tidak dikenakan biaya".
 - Fallback subtotal add-ons di `CheckoutController@billing`: ketika `addonsAmount` dari Order/Cart bernilai 0 atau tidak tersedia, sistem kini menghitung ulang dengan menjumlahkan harga dari relasi add-ons (pivot `price`). Ini memastikan "Subtotal Add-ons" muncul konsisten saat ada ≥2 add-ons.
 - Menonaktifkan fallback pengambilan keranjang tamu lain di `CartService::getOrCreateCart` agar guest baru selalu mulai dari `/checkout/` (Domain), mencegah redirect ke `/checkout/personal-info` saat Cart diklik.
 - View `livewire/invoice-show.blade.php`: breakdown biaya ditampilkan jelas (Subscription, Domain, Add-ons), Subtotal = `invoice->amount`, Fee Tripay = `invoice->fee_customer`, dan Total Bayar = Subtotal + Fee Tripay.
 - Tripay payload diperbaiki: `CheckoutController@submit` dan `TripayService::formatTransactionData` kini mengirim `amount = subtotal` (tanpa fee platform), serta `order_items` berisi item terpisah untuk subscription, domain, dan tiap add-on.
 - TripayService::createTransaction kini mengirim request sebagai JSON dan menambahkan fallback `callback_url` (config atau `route('payment.callback')`), `return_url` (home) dan `customer_phone` default untuk mencegah kegagalan validasi.
 - CheckoutController::submit menambahkan `callback_url` ke payload, fallback `customer_phone`, serta memperketat guard: bila respons Tripay `success=false` atau null, tampilkan pesan error dari Tripay.
 - `CartService::getCartSummary` dan `Cart::calculateTotals` diselaraskan untuk tidak menambahkan fee 3% ke `total_amount` (fee pelanggan dipasrahkan ke Tripay).
 - `TripayService::createPaymentUrl` ditambahkan untuk mengambil `checkout_url` dari `invoice->tripay_data` agar tombol "Lanjutkan ke Pembayaran" berfungsi.
 - PaymentController: perbaiki pencarian invoice pada callback Tripay memakai `tripay_reference`; pengecekan status di `createPayment` memakai `sent` (bukan `pending`); `updateInvoiceStatus` menyimpan `paid_date` (bukan `paid_at`).
- Pencegahan navigasi balik dari halaman Billing: ditambahkan endpoint `POST /checkout/reset` (nama route `checkout.reset`) yang menghapus cart (user/session), membersihkan seluruh session checkout, dan menghapus semua cookies termasuk data pembayaran via `CheckoutCookieHelper::clearAllForce()`. Ditambahkan script di `checkout/billing.blade.php` untuk intercept `popstate` (tombol back) dan klik link menuju `checkout.summary`, memanggil reset lalu redirect ke `checkout.index`.
 - Guard server-side di `CheckoutController@summary`: bila terdeteksi `tripay_reference` atau `tripay_transaction` (di cookie/session), user diarahkan ke `checkout.index` untuk mencegah akses kembali ke ringkasan saat sudah di tahap billing meski JS dimatikan.

Next Steps:
- Audit referensi lama ke halaman `addon` dan sesuaikan tests yang bergantung pada flow lama.
- Tambahkan e2e variasi untuk domain baru vs existing (opsional).
 - Tambah smoke test untuk skenario polling tanpa callback (verifikasi invoice `paid_date` & project aktif).
 - Tambah test UI untuk memastikan back dari Billing memicu reset state dan kembali ke awal checkout.
 - Tambah test untuk memastikan subtotal add-ons tampil saat memiliki ≥2 add-ons dan nilai dihitung dari relasi.

Fix Terbaru: Aktivasi Order & Project + UI Orders
- Aktivasi otomatis: ketika invoice berubah ke `PAID`, order diaktifkan (`status=active`, `activated_at`, `next_due_date`) dan project diaktifkan/ dibuat bila belum ada. Field start project diseragamkan ke `start_date` untuk konsistensi dengan migration dan view.
- Otomatis buat Project: pada pembayaran sukses, sistem membuat project baru terkait order bila belum ada, dengan nama yang dihasilkan dari detail order (product/subscription/template/domain).
- Accessor singular di `Order`: ditambahkan `invoice` dan `project` agar view dapat mengakses entitas tunggal dengan aman (`$order->invoice`, `$order->project`), mencegah error di tampilan yang mengharapkan satu entitas.
- UI Client Orders: perbaikan fallback produk—tampilkan nama `product` bila ada, fallback ke `subscriptionPlan->name` bila `product` kosong, dan tampilkan `template->name` bila tersedia (atau deskripsi produk sebagai fallback). Controller Client Dashboard menambahkan eager load `subscriptionPlan` dan `template` agar data konsisten.

Perubahan Terkait Controller
- `PaymentController` dan `CheckoutController`: menyelaraskan proses aktivasi order & project saat invoice `PAID`, memakai `start_date` untuk project, dan membuat project otomatis bila belum ada.
- `CheckoutController@checkTripayStatus`: selain sinkronisasi status invoice dan notifikasi, kini memastikan order aktif dan project aktif/konsisten.

Dampak & Verifikasi
- Server dev dijalankan (`php artisan serve`), preview dibuka pada `http://127.0.0.1:8000/` untuk verifikasi visual.
- Halaman `/client/orders` kini tidak lagi menampilkan `N/A` untuk produk pada order yang valid.
- Konsistensi data project meningkat dengan penyelarasan `start_date` dan pembuatan otomatis saat bayar.

Next Steps (Tambahan)
- Tambahkan test untuk memastikan accessor `Order::invoice` dan `Order::project` mengembalikan entitas yang tepat (latest/active) dan aman terhadap null.
- Tambahkan e2e verifikasi bahwa invoice `PAID` mengaktifkan order dan membuat/ mengaktifkan project dengan `start_date` terset.

Fix Terbaru: Tripay order_items vs amount
- Gejala: Tripay menolak transaksi dengan pesan "Inconsistent: order_items != amount".
- Akar masalah: itemisasi add-on di `CheckoutController@submit` memakai `ProductAddon->price` (bukan pivot `price`), sementara `total_amount` berasal dari penjumlahan pivot `price` di Cart. Akibatnya jumlah item tidak sama dengan `amount` yang dikirim.
- Solusi:
  - Gunakan harga pivot untuk setiap add-on saat membangun `order_items`.
  - Rehitung `totalAmount = subscriptionAmount + domainAmount + addonsAmount (pivot)` sebelum membuat Order.
  - Tambahkan sanity check setelah itemisasi: jika `sum(order_items) != amount`, normalisasi `amount` ke jumlah item dan sinkronkan `order->amount` sebelum membuat invoice.
  - Tetap kirim `amount` ke Tripay sebagai subtotal (tanpa `fee_customer`), fee akan dihitung oleh Tripay.
- Dampak: Payload Tripay konsisten, tidak memunculkan error inkonsistensi lagi; invoice & order menyimpan subtotal yang identik dengan itemisasi.

Fix Terbaru: UI Billing — Copy & Polling Status
- Gejala: Klik tombol Copy di `checkout/billing.blade.php` memunculkan error `TypeError: Cannot read properties of undefined (reading 'target')` karena fungsi JS bergantung pada `event` global.
- Perbaikan: Ganti pemanggilan menjadi `copyToClipboard(text, this)` dan ubah fungsi `copyToClipboard(text, buttonEl)` untuk menggunakan elemen tombol yang dikirim, bukan `event.target`. Perubahan serupa diterapkan di `checkout/billing-cycle.blade.php`.
- Gejala lain: Tombol "Cek Status Pembayaran" melakukan polling ke `/api/payment/status/{reference}` namun tidak menyinkronkan status invoice.
- Perbaikan: `CheckoutController::checkTripayStatus` kini menyelaraskan status invoice saat menerima status final dari Tripay (`PAID/EXPIRED/FAILED/REFUND`), menyetel `paid_date` jika `PAID`, mengaktifkan project terkait, dan mengirim notifikasi `PaymentSuccessful`. JS di billing diperbaiki untuk memanggil `checkPaymentStatus(this)` agar tidak bergantung pada `event` global.

Investigasi: Recurring Add-ons
- Sistem sudah memiliki scheduler `invoices:generate-recurring` (harian 06:00) yang membuat invoice perpanjangan untuk Order aktif dengan `next_due_date` mendekat.
- Generator saat ini menetapkan `invoice.amount` dari `order.amount` dan tidak melakukan itemisasi; view `invoice-show` menampilkan breakdown Add-ons dari relasi `order->orderAddons`.
- Model `OrderAddon` menyimpan `billing_cycle` (diset dari `subscriptionPlan->billing_cycle` saat checkout), sementara sifat Add-on (`one_time` vs `recurring`) berada di `ProductAddon.billing_type` (tersimpan di `addon_details`).
- Potensi perbaikan: hitung amount renewal dari `subscription_amount + sum(addons recurring)` dan eksklusi komponen one-time (mis. domain). Saat ini tidak ada logika eksplisit untuk mengecualikan one-time add-ons/domain dalam recurring invoice.

Pertimbangan Aktif:
- Jaga KISS: form sederhana, tanpa micro-interactions kompleks yang tidak perlu.
- Pastikan preselect add-ons dari cart berjalan agar UX konsisten.
 - Back dari Billing harus selalu memulai alur baru agar tidak terjadi inkonsistensi cart/cookies.

Fix Darurat: Aktivasi Order → Project (DB Columns)
- Gejala: Aktivasi manual order gagal dengan error `Unknown column 'template_id' in 'field list'` saat membuat Project otomatis.
- Akar masalah: Skema `projects` belum memiliki kolom `template_id` dan `started_at` yang dipakai controller/model.
- Solusi: Tambah migration `add_template_started_completed_to_projects_table` menambahkan kolom `template_id`, `started_at`, dan `completed_at` (nullable, FK `templates` set null on delete). Migrasi dijalankan sukses.
- Dampak: `Admin\OrderController::activate()` dan `createProjectForOrder()` kini berhasil membuat Project dengan field yang sesuai; jalur aktivasi manual aman.

UI Client Orders & Cancel Add-ons (baru)
- Route baru: `client.orders.show` untuk detail order, `client.orders.addons.cancel` untuk pembatalan add-on oleh client.
- Controller `Client\DashboardController` menambahkan `showOrder()` (eager load product, subscriptionPlan, template, orderAddons) dan `cancelAddon()` dengan guard otorisasi.
- Pembatalan add-on:
  - Recurring: set `cancel_at_period_end = true` (tetap aktif hingga akhir periode berjalan).
  - One-time: batalkan segera (`status='cancelled'`, `cancelled_at=now`, `cancel_at_period_end=false`, `next_due_date=null`).
- View `resources/views/client/orders/show.blade.php` menampilkan status add-on (badge), label billing, indikator "Cancel at period end", serta periode `started_at` dan `next_due_date` bila ada. Tombol "Cancel at end of term" tersedia bila add-on belum dibatalkan/diberi tanda cancel.
- Di `client/orders.blade.php` ditambahkan tombol "Details" yang mengarah ke halaman detail order.

Fitur Uncancel Add-on (2025-11-10)
- Route baru: `client.orders.addons.uncancel` (POST) untuk menghapus penandaan `cancel_at_period_end`.
- Controller: `DashboardController::uncancelAddon()` dengan guard otorisasi dan validasi kepemilikan; menolak undo bila `status='cancelled'` (batal permanen).
- View: tombol "Undo cancel at period end" muncul saat add-on belum `cancelled` namun `cancel_at_period_end==true`, lengkap dengan konfirmasi SweetAlert (fallback `confirm()`).
- UX: aksi Cancel/Undo kini dilengkapi dialog konfirmasi agar tidak terjadi perubahan tidak sengaja.

Indikator Tanggal Efektif Pembatalan
- Saat `cancel_at_period_end==true`, UI menampilkan teks kecil "Akan dibatalkan pada: {Next Due}" untuk memperjelas kapan pembatalan berlaku.

Recurring Add-ons — Scheduler
 - Command `invoices:generate-recurring-addons`: membuat invoice gabungan add-ons per order-periode (idempoten), `renewal_type='addons'`, dan `items_snapshot` berisi item per add-on.
 - Command `addons:cancel-overdue`: auto-cancel add-ons jika renewal invoice `overdue` >14 hari (invoice → `cancelled`).
 - Command `addons:apply-cancel-at-period-end`: eksekusi add-ons yang sudah ditandai `cancel_at_period_end` saat `next_due_date` lewat.
 - Penjadwalan `Console\Kernel`: `invoices:generate-recurring-addons` setiap hari 06:10; `addons:cancel-overdue` 07:10; `addons:apply-cancel-at-period-end` 07:15.

Implementasi Rencana Invoice & PDF (2025-11-10)
- DashboardController: meload relasi tambahan untuk invoice (`items`, `order.product`, `order.template`, `order.orderAddons.productAddon`) agar tampilan detail invoice lengkap.

Implementasi PDF Invoice (2025-11-11)
- InvoicePdfService: opsi `isRemoteEnabled=true` diaktifkan untuk memuat resource eksternal (gambar/font) saat render PDF.
- Template `resources/views/pdf/invoice.blade.php`: dirombak ke layout profesional 4 kolom (Deskripsi, QTY, Harga Satuan, Jumlah). Menampilkan periode/siklus di deskripsi, domain & add-ons diringkas, Pajak dan Total konsisten memakai `formatIDR`.
- Helper global: fungsi `terbilang_idr($number)` ditambahkan di `app/helpers.php` untuk konversi angka ke teks bahasa Indonesia; footer PDF menampilkan “Terbilang: … rupiah”.
- Route debug lokal: `GET /debug/invoice/{invoice?}` (env local) untuk preview PDF tanpa auth; fallback membuat sample invoice bila ID tidak diberikan.
- Preview: server dev berjalan (`php artisan serve`), preview dapat diakses di `http://127.0.0.1:8000/debug/invoice`.

Next Steps (PDF):
- Tambahkan informasi brand perusahaan dari `AppServiceProvider` (alamat/telepon/website) ke header/footer PDF.
- Pertimbangkan watermark “PAID/OVERDUE/CANCELLED” sesuai status.
- Uji resource eksternal (logo/font) dengan `isRemoteEnabled` aktif.
- Client Invoice View: itemisasi memprioritaskan `invoice.items` (fallback ke `order_details`), menyembunyikan fee merchant/platform dari tampilan, dan menyelaraskan subtotal ke `invoice.amount`.

Update (Checkout E2E + Verifikasi Add-on — 2025-11-11)

Normalisasi Add-on Recurring (2025-11-11)
- Pada `CheckoutController@submit`, `OrderAddon` kini diset:
  - `billing_cycle='monthly'` hanya untuk add-on dengan `billing_type='recurring'`.
  - `next_due_date=+1 bulan` hanya untuk recurring; one-time memakai `next_due_date=null`.
  - `price` mengambil `pivot->price` bila tersedia untuk konsistensi dengan subtotal & itemisasi Tripay.
- UI label add-on tetap: “Sekali” (`billing_cycle=null`) dan “Per Bulan” (`monthly`).
- Checkout end-to-end berhasil hingga halaman Billing. Add-on recurring terpilih: `SSL Certificate`.
- Ringkasan Pesanan di `checkout/billing.blade.php` menampilkan baris add-on dan `Subtotal Add-ons` sebesar `Rp 150.000` (hasil parsing DOM terverifikasi).
- Halaman `/client/orders` membutuhkan login saat ini; verifikasi detail order dilakukan via Ringkasan Pesanan Billing yang bersumber dari entitas `Order` (aksesors di controller telah diselaraskan).
- Screenshot referensi: `billing-page`, `client-orders-index` (login gate terlihat).

Next Steps (singkat):
- Opsional: uji polling status pembayaran Tripay dari halaman Billing hingga `PAID` untuk memastikan aktivasi order & project berjalan mulus.
- Tambah e2e untuk variasi add-on ≥2 memastikan subtotal add-ons konsisten dan diambil dari harga pivot.
- Add-ons Billing Cycle: seluruh add-on diset ke `monthly` saat `OrderAddon::create`; `calculateAddonNextDueDate(baseDate)` disederhanakan menjadi `+1 bulan` untuk konsistensi.
- Label Billing Cycle Add-on: `OrderAddon::getBillingCycleLabelAttribute` disederhanakan menjadi “Sekali” atau “Per Bulan”.
- InvoicePdfService: meload relasi `items`, `order.template`, dan `order.orderAddons.productAddon` agar PDF memiliki konteks penuh.
- Template PDF Invoice: `resources/views/pdf/invoice.blade.php` diganti ke layout profesional lengkap dengan header brand, metadata tanggal/jatuh tempo/status, tabel item (deskripsi, periode, jumlah), pajak, dan total.
- Migrasi DB: kolom `order_addons.status/started_at/next_due_date/cancel_at_period_end/cancelled_at` ditambahkan; kolom `invoices.renewal_type/items_snapshot` serta tabel `invoice_items` dibuat. Migration diperbaiki untuk menghindari dependensi pada kolom yang belum ada (`is_renewal`/`tripay_data`) dengan penempatan kondisional.

Preview & Verifikasi
- Server dev berjalan di `http://127.0.0.1:8000/`. Verifikasi tampilan invoice di halaman klien (`/client/invoices/{id}`) dan cetak PDF melalui tombol yang tersedia.

Fix Terbaru: Admin Orders — Breakdown & Total (2025-11-10)
- Model `Order::getTotalAmountAttribute` disesuaikan: Total = `subscription_amount + addons_amount + setup_fee`.
- View `admin/orders/show.blade.php`: kartu "Total Amount" memakai `total_amount`, dan Subtotal dihitung dari `subscription_amount + addons_amount`.
- Dampak: breakdown harga konsisten dengan rencana, tidak menjumlahkan `amount` dua kali.

Aktivasi Manual Admin — Buat Project Otomatis (2025-11-10)
- `Admin\OrderController@update`: ketika status berubah ke `active`, sistem memanggil `createProjectForOrder($order)` secara otomatis.
- `Admin\OrderController@activate` sudah membuat project; sekarang jalur `update` juga aman dan konsisten.
- Dampak: aktivasi via halaman edit order tidak lagi lupa membuat project.

Client Orders — Invoice Terkait & Proteksi Double Submit (2025-11-10)
- `Client\DashboardController@showOrder`: tambah eager load `invoices`.
- View `client/orders/show.blade.php`: seksi "Invoice Terkait" ditambahkan dengan tautan ke detail invoice; tombol cancel/undo add-on kini disable sementara setelah konfirmasi untuk mencegah double submit.
- Error ORB eksternal tidak mempengaruhi fungsi halaman.

Support Ticket — Notifikasi & Integrasi (2025-11-10)
- Menambahkan notifikasi: `SupportTicketCreatedNotification`, `SupportTicketRepliedNotification`, `SupportTicketStatusUpdatedNotification` dengan template markdown di `resources/views/emails/support/`.
- Integrasi Client: pada `Client\SupportTicketController@store` mengirim email ke pembuat tiket saat tiket dibuat.
- Integrasi Admin: pada `Admin\SupportTicketController` mengirim email untuk pembuatan tiket (store), balasan non-internal (reply), dan perubahan status (`assign/markInProgress/resolve/close/reopen`).
- Ketahanan: setiap pengiriman notifikasi dibungkus `try/catch` dan `Log::error` dengan konteks `ticket_id`, penerima, dan transisi status untuk mencegah UI gagal bila SMTP bermasalah.
- Pengujian: `tests/Unit/SupportTicketNotificationsTest.php` memverifikasi tiga jalur (create, reply, status update) menggunakan `Notification::fake()` — semua lulus.
Update (Support Tickets — 2025-11-10)
- Admin Tickets: form balasan kini mendukung upload lampiran (input `attachments[]`, `enctype="multipart/form-data"`).
- Admin Thread: setiap reply menampilkan blok "Attachments" dengan tautan `Storage::url(...)`.
- Admin Controller: `SupportTicketController@reply` memvalidasi dan menyimpan file ke `public/ticket_attachments/{ticket_id}`, meneruskan metadata ke `SupportTicket::addReply(...)`.
- Logging: ditambahkan logging untuk kasus file invalid/gagal simpan agar troubleshooting mudah.
- Environment: `php artisan storage:link` dijalankan sukses; dicatat alternatif junction (`mklink /J`) untuk Windows bila symlink gagal.