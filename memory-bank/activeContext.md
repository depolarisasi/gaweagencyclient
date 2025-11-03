# Active Context

## Current Focus
Aplikasi GaweClient telah mencapai tahap development yang sangat matang dengan sistem yang komprehensif dan terintegrasi. Sistem cart dan checkout flow telah diimplementasi dengan sempurna menggunakan database-driven approach.

## Recent Changes (Latest Scan - January 2025)
- **Cart System Implementation**: Database-driven cart dengan model Cart dan CartAddon
- **Advanced Checkout Flow**: Multi-step checkout dengan session dan cookie fallback
- **Livewire Components**: ProductShowcase, CheckoutConfigure, CheckoutSummary, SubscriptionManager, DomainSelector
- **CartService**: Comprehensive service untuk cart management dengan migration capabilities
- **Enhanced Testing**: Comprehensive test coverage untuk checkout flow dan subscription management
- **Domain Management**: Advanced domain selection dan validation system
- **Payment Integration**: Fully functional Tripay integration dengan fee calculation
 - **Security Hardening (Support Tickets - Admin)**: Sanitasi HTML pada `Admin\SupportTicketController` (fields `description` dan `message`) diselaraskan dengan client-side untuk mencegah XSS.
 - **Support Tickets Visibility**: Balasan internal (`is_internal = true`) kini disembunyikan dari tampilan klien (client tickets show), namun tetap terlihat di tampilan admin.
 - **Support Tickets Reopen**: Admin dapat membuka kembali tiket yang berstatus `closed` melalui endpoint `admin.tickets.reopen` dan UI tombol di halaman show serta aksi dropdown di index.
 - **Subscription Plans Discount (Restored)**: Fitur diskon pada paket langganan dipulihkan: validasi `discount_percentage` di controller (store/update) dan tampilan di views (kolom Diskon di index, input Diskon di create/edit, serta informasi Diskon di show).
 - **Checkout & Invoice Discount Integration**: Perhitungan harga berlangganan di checkout dan invoice kini memakai harga setelah diskon melalui accessor `SubscriptionPlan::discounted_price`. CartService dan Livewire Checkout Summary menghitung `subscriptionAmount`, `subtotal`, dan `totalAmount` dengan nilai diskon, serta UI menampilkan breakdown harga (harga asli, persen diskon, harga akhir).

### Checkout Flow Update (November 2025)
 - Urutan langkah checkout disederhanakan menjadi: **Domain (1) → Template (2) → Info Personal (3) → Paket & Add-ons (4) → Ringkasan (5) → Pembayaran (6)**.
 - Indikator langkah di views telah diperbarui: `domain.blade.php`, `step1.blade.php`, `personal-info.blade.php`, `configure.blade.php`, `addon.blade.php`, `addons.blade.php`, `summary.blade.php`, `billing.blade.php`.
 - Routes & `CheckoutController` masih menggunakan urutan lama (Domain sebagai langkah 4) dan akan diselaraskan pada tahap berikutnya.
 - `DomainSelector` (Livewire) ditingkatkan:
   - Radio `domainType`: `new` (daftarkan baru) atau `existing` (punya domain sendiri). WHOIS hanya dijalankan untuk `new`.
   - Radio TLD: `.com`, `.net`, `.org`, `.id`, `.co.id` dengan tampilan harga per tahun dari `DomainService::getDomainPrices()`.
- Session payload `checkout.domain` diperluas: `type`, `name`, `own_domain`, `is_available`, `tld`, `price`.
   - Ringkasan domain terpilih menyesuaikan label berdasarkan `domainType`.
 - `CartService` tetap set `domain_amount` ke `0` (harga domain termasuk dalam subscription).
 - Dampak: Beberapa tests/E2E yang mengandalkan urutan lama perlu disesuaikan.

### UI Feedback Improvements (November 2025)
 - Menambahkan hint/alert informatif di halaman `checkout/domain` untuk panduan pemilihan dan verifikasi domain.
 - Menampilkan alert error umum (`$errors->any()`) pada halaman `checkout/domain` dan `checkout/personal-info` untuk visibilitas validasi.
 - Integrasi notifikasi kecil (toast DaisyUI) untuk `session('success')` dan `session('error')` di kedua halaman.
 - Menambahkan toast dinamis saat event `domainUpdated` di halaman domain agar pengguna mendapat umpan balik instan saat domain diperbarui.
 - `CheckoutController@domain` menambahkan flash success pada redirect ke `checkout.personal-info` (`Domain berhasil disimpan.`).

## Current State Analysis

### Database & Models (Fully Implemented)
**Core Models:**
- User (dengan role management dan relationships)
- Product (dengan pricing dan billing cycles)
- Project (dengan status tracking dan website access)
- Order (dengan payment integration)
- Invoice (dengan payment status dan fee fields)
- SupportTicket (dengan priority dan assignment)
- Template (dengan categorization)
- TicketReply (dengan internal/public replies)
- ProductAddon (dengan pricing tiers)
- Payment (dengan Tripay integration)
- **Cart** (database-driven shopping cart dengan expiration)
- **CartAddon** (cart addon relationships dengan pricing snapshot)

**Key Relationships:**
- User → Orders → Invoices → Payments (payment flow)
- User → Projects (client ownership & staff assignment)
- Order → Project (project creation after payment)
- SupportTicket → TicketReply (ticket management)
- Product → ProductAddon (upselling system)

### Livewire Components (Fully Implemented)
**Frontend Components:**
- **ProductShowcase**: Template dan product display dengan selection
- **CheckoutConfigure**: Subscription plan dan billing cycle configuration
- **CheckoutSummary**: Final checkout summary dengan payment channel selection
- **SubscriptionManager**: User subscription management dengan upgrade/renewal
- **DomainSelector**: Domain availability checking dan selection
- **CheckoutSummaryComponent**: Comprehensive checkout summary calculations

### Payment Integration (Fully Functional)
**TripayService Features:**
- Payment channel management
- Transaction creation & tracking
- Fee calculation (merchant & customer fees)
- Callback handling dengan signature validation
- Sandbox/production mode support
- Comprehensive error logging

### Cart System (Database-Driven)
**CartService Features:**
- Session dan user-based cart management
- Migration dari session/cookies ke database
- Cart expiration management (7 days)
- Comprehensive cart summary calculations
- Addon synchronization dengan pricing snapshot
- Fallback mechanism untuk session issues
- Complete cart validation untuk checkout
 - Discount-aware calculations untuk subscription plan (memakai `discounted_price`)

**Payment Flow:**
1. Checkout process dengan template selection
2. Customer registration/login
3. Payment method selection
4. Tripay transaction creation
5. Payment callback processing
6. Project activation after successful payment
 7. Discount reflected di order & invoice amounts (subtotal/total)

### Controller Architecture (Well Organized)
**Admin Controllers:**
- DashboardController (statistics & overview)
- UserController, ProductController, ProjectController
- InvoiceController, SupportTicketController, TemplateController

**Client Controllers:**
- DashboardController (project overview)
- SupportTicketController (ticket management)

**Staff Controllers:**
- DashboardController (task management)

**Core Controllers:**
- CheckoutController (multi-step checkout process)
- PaymentController (Tripay integration)
- TemplateController (public template display)

### Frontend Components (Livewire)
**Implemented Components:**
- ProductShowcase (template selection)
- CheckoutConfigure (product configuration)
- CheckoutSummary (order finalization)
- InvoiceShow (payment processing)

### Recent Database Changes
- Added domain field to projects table (2025-10-13)
- Website access fields (admin_url, admin_username, admin_password)
- Additional access JSON field for flexible data storage

## Next Steps
1. **Testing & Quality Assurance**
   - Comprehensive testing of payment flows
   - User acceptance testing for all roles
   - Performance optimization
   - Tambahkan feature tests untuk: visibilitas balasan internal di client, dan aksi reopen oleh admin.

2. **Documentation & Deployment**
   - API documentation
   - User manuals for different roles
   - Production deployment preparation

3. **Advanced Features**
   - Advanced reporting & analytics
   - Automated project provisioning
   - Enhanced notification system

## Active Decisions
- Laravel 12 dengan PHP 8.2 untuk modern features
- Livewire 3 untuk reactive components
- DaisyUI + Tailwind untuk consistent design
- Tripay sebagai payment gateway utama
- Role-based access control yang granular
- RESTful API structure untuk semua operations

## Technical Debt & Improvements
- Beberapa TODO comments di controllers yang perlu diselesaikan
- Optimization untuk query performance
- Enhanced error handling di beberapa areas
- Automated testing coverage yang lebih comprehensive
### Integrasi TLD & Harga (November 2025)
- Menambahkan hidden inputs `domain_tld` dan `domain_price` pada `resources/views/checkout/domain.blade.php` agar fallback POST menyertakan TLD dan harga.
- Memperbarui sinkronisasi JavaScript (`updateHiddenInputs`) untuk mengisi `type`, `name`, `tld`, dan `price` dari event `domainUpdated` Livewire.
- Memodifikasi `CheckoutController@domain` untuk menyimpan struktur domain lengkap (`type`, `name`, `tld`, `price`, `own_domain`, `is_available`) ke `CartService::updateDomainData` dan cookies via `CheckoutCookieHelper::storeDomain`.
- Update kebijakan harga: `Cart::calculateTotals` dan `CartService::getCartSummary` kini memasukkan `domain_amount` ke `subtotal` dan `total_amount` untuk **domain baru**; nilai `domain_amount` ditentukan dari `domain_data.price` atau mapping TLD dari `DomainService::getDomainPrices()`.
- Ringkasan & Billing UI:
  - `resources/views/livewire/checkout-summary-component.blade.php` menampilkan TLD dan harga domain; total mencakup `domainAmount`.
  - `resources/views/checkout/billing.blade.php` menampilkan item domain dengan TLD dan baris "Subtotal Domain"; `CheckoutController@billing` meneruskan `domainAmount` ke view.
- Catatan: Domain existing tidak menambah biaya. Komentar lama di `DomainController` tentang harga domain termasuk subscription perlu diperbarui untuk konsistensi.