# Tech Context

## Technology Stack

### Backend
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Database**: MySQL 8.0
- **Authentication**: Laravel Sanctum
- **Queue**: Database driver (default)
- **Payment Gateway**: Tripay Integration
- **ORM**: Eloquent

### Frontend
- **Templating**: Blade Templates
- **CSS Framework**: Tailwind CSS + DaisyUI
- **JavaScript**: Livewire 3 + Alpine.js
- **Build Tool**: Vite
- **Icons**: Heroicons (via DaisyUI)
- **Interactive Components**: Livewire Components (ProductShowcase, CheckoutConfigure, CheckoutSummary, SubscriptionManager, DomainSelector)
- **State Management**: Session-based dengan database persistence

### Development Environment
- **Server**: XAMPP (Apache + MySQL + PHP)
- **OS**: Windows 11
- **Terminal**: PowerShell 7+
- **Package Manager**: Composer (PHP), NPM (Node.js)
- **Version Control**: Git

## Key Dependencies

### PHP Packages (composer.json)
**Core Framework:**
- laravel/framework: ^12.0
- laravel/sanctum: ^4.0
- laravel/tinker: ^2.9
- livewire/livewire: ^3.0

**Additional Packages:**
- guzzlehttp/guzzle (HTTP client untuk Tripay)
- intervention/image (image processing)
- spatie/laravel-permission (role management)
- barryvdh/laravel-debugbar (development debugging)

### Node Packages (package.json)
**CSS & Build Tools:**
- @tailwindcss/forms
- @tailwindcss/typography
- daisyui
- autoprefixer
- postcss
- tailwindcss
- vite
- laravel-vite-plugin

**JavaScript Libraries:**
- alpinejs
- axios
- @alpinejs/persist
- @alpinejs/focus

## Configuration

### Environment Variables
**Application:**
- APP_ENV=local
- APP_DEBUG=true
- APP_KEY=[generated]
- APP_URL=http://localhost:8000

**Database:**
- DB_CONNECTION=mysql
- DB_HOST=127.0.0.1
- DB_PORT=3306
- DB_DATABASE=gaweagencyclient
- DB_USERNAME=root
- DB_PASSWORD=

**Payment Gateway (Tripay):**
- TRIPAY_MERCHANT_CODE
- TRIPAY_API_KEY
- TRIPAY_PRIVATE_KEY
- TRIPAY_MODE=sandbox
- TRIPAY_BASE_URL

**Mail Configuration:**
- MAIL_MAILER=smtp
- MAIL_HOST=smtp.gmail.com
- MAIL_PORT=587
- MAIL_USERNAME
- MAIL_PASSWORD

### Key Config Files
- config/app.php (application settings)
- config/database.php (database configuration)
- config/auth.php (authentication settings)
- config/services.php (third-party services)
- tailwind.config.js (CSS framework)
- vite.config.js (build tool)

## Development Setup

### System Requirements
- PHP 8.2+ dengan extensions:
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
- Composer 2.0+
- Node.js 18+ & NPM
- MySQL 8.0+
- XAMPP (recommended for Windows)

### Installation Steps
1. Clone repository
2. Run `composer install`
3. Run `npm install`
4. Copy `.env.example` to `.env`
5. Configure database dan payment gateway settings
6. Run `php artisan key:generate`
7. Run `php artisan migrate --seed`
8. Run `php artisan storage:link`
9. Run `php artisan serve`
10. Run `npm run dev` (for asset compilation)

## Database Schema

### Core Tables (12 Tables)
**User Management:**
- users (authentication, roles, profile)
- password_resets
- personal_access_tokens

**Product & Commerce:**
- products (service offerings, pricing)
- product_addons (upselling options)
- templates (product templates)
- orders (purchase tracking)
- invoices (billing management dengan fee fields)
- payments (Tripay integration)
- carts (database-driven shopping cart)
- cart_addons (cart addon relationships)

**Project & Support:**
- projects (client work, website access, domain management)
- support_tickets (customer service)
- ticket_replies (communication tracking)

### Recent Schema Changes
**2025-01-13: Projects Enhancement**
- Added `domain` field untuk website domain tracking
- Added website access fields:
  - `website_url`
  - `admin_url`
  - `admin_username`
  - `admin_password`
  - `additional_access` (JSON field)

**2025-10-27: Cart System Implementation**
- Created `carts` table dengan comprehensive cart management:
  - Session dan user-based cart tracking
  - Template dan subscription plan configuration
  - JSON fields untuk template_config dan domain_data
  - Amount calculations (template, addons, domain, fees, total)
  - Cart expiration management
- Created `cart_addons` table untuk addon relationships:
  - Cart-addon linking dengan price snapshot
  - Unique constraint untuk duplicate prevention

**2025-10-26: Invoice Enhancement**
- Added fee calculation fields ke invoices table:
  - `fee_merchant` (decimal)
  - `fee_customer` (decimal)
  - `total_fee` (decimal)

### Migration Status
- All core tables created dan up-to-date
- Foreign key relationships established
- Indexes applied untuk performance optimization
- Soft deletes implemented pada critical tables

## Payment Integration

### Tripay Gateway
**Features Implemented:**
- Payment channel management
- Transaction creation & tracking
- Fee calculation
- Callback signature validation
- Sandbox/production mode support
- Comprehensive error logging

**API Endpoints Used:**
- GET /merchant/payment-channel
- POST /transaction/create
- GET /transaction/detail/{reference}
- GET /payment/instruction
- POST /callback (webhook)

**Security Measures:**
- HMAC signature validation
- Environment-based API keys
- Secure callback handling
- Transaction logging

## Security Considerations

### Authentication & Authorization
- Laravel Sanctum untuk API authentication
- Role-based access control (admin, client, staff)
- Password hashing dengan bcrypt
- CSRF protection enabled
- Session security configured
- Rate limiting implemented

### Payment Security
- Tripay signature validation
- Secure API key management
- Environment-based configuration
- Transaction integrity checks
- Callback verification

### Data Protection
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade escaping)
- CSRF token validation
- Input validation via Form Requests
- File upload security
- Secure password storage

### API Security
- Sanctum token authentication
- Rate limiting
- CORS configuration
- Request validation
- Error handling tanpa information disclosure

## Performance Optimization

### Database Performance
- Query optimization dengan eager loading
- Database indexing pada foreign keys
- Pagination untuk large datasets
- Query scopes untuk reusable logic

### Frontend Performance
- Vite untuk fast asset bundling
- CSS/JS minification
- Lazy loading untuk images
- Component-based architecture dengan Livewire 3
- Real-time reactive components dengan minimal JavaScript
- Server-side rendering dengan client-side interactivity
- Event-driven communication antar components
- Session state management dengan database persistence

### Caching Strategy
- Route caching
- View caching
- Configuration caching
- Query result caching (planned)
- Redis integration (planned)

## Development Tools

### Debugging & Testing
- Laravel Debugbar untuk development
- Laravel Telescope (planned)
- PHPUnit untuk testing dengan comprehensive test coverage:
  - CheckoutEndToEndTest (cart system validation)
  - ProjectManagementTest (project lifecycle)
  - SubscriptionTest (subscription management)
- Browser testing dengan Dusk (planned)

### Business Logic Services
- **CartService**: Comprehensive cart management dengan session/database persistence
- **TripayService**: Payment processing dengan fee calculation
- **DomainService**: Domain availability checking
- **ProjectService**: Project lifecycle management
- **NotificationService**: System notifications

### Code Quality
- PHP CS Fixer untuk code formatting
- PHPStan untuk static analysis (planned)
- ESLint untuk JavaScript (planned)

### Deployment
- Environment-based configuration
- Asset compilation
- Database migration automation
- Queue worker setup (planned)

## Third-Party Integrations

### Payment Gateway
- **Tripay**: Primary payment processor dengan comprehensive integration
- **Supported Methods**: Bank transfer, e-wallet, virtual account
- **Features**: Real-time callback, fee calculation (merchant & customer), multi-channel
- **Cart Integration**: Database-driven cart dengan Tripay fee calculation
- **Checkout Flow**: Multi-step checkout dengan payment channel selection

### Email Services
- **SMTP Configuration**: Gmail/custom SMTP
- **Features**: Transactional emails, notifications
- **Templates**: Blade-based email templates

### File Storage
- **Local Storage**: Default untuk development
- **Cloud Storage**: AWS S3/DigitalOcean Spaces (planned)
- **Features**: File upload, image processing

## Monitoring & Logging

### Application Logging
- Laravel Log facade
- Daily log rotation
- Error tracking
- Performance monitoring

### Payment Logging
- Transaction logging dengan comprehensive tracking
- Callback logging dengan signature validation
- Error tracking untuk payment failures
- Audit trail untuk transaction history
- Cart activity logging
- Checkout flow monitoring
- Fee calculation tracking

### Security Logging
- Authentication attempts
- Authorization failures
- Suspicious activities
- API access logs
## Pricing & Discount Integration

### Overview
- Sistem harga berlangganan kini mengintegrasikan diskon secara konsisten end-to-end pada model, service, komponen Livewire, dan tampilan Blade.

### Model Accessor
- `SubscriptionPlan::discounted_price` menghitung harga akhir menggunakan `discount_percentage`.
- Rumus: `final = price - (price * discount_percentage / 100)` dengan normalisasi 2 desimal dan penjagaan agar tidak negatif.
- Tujuan: Menjadi satu sumber kebenaran untuk semua kalkulasi diskon.

### Service Layer
- `CartService` menggunakan `discounted_price` untuk mengisi `template_amount` dan `subscriptionAmount`.
- Perhitungan `subtotal` dan `total_amount` otomatis ikut merefleksikan harga pasca-diskon.

### Livewire Components
- `CheckoutSummaryComponent` mengkalkulasi `subscriptionAmount`, `subtotal`, dan `totalAmount` menggunakan `discounted_price` sehingga UI dan backend selaras.

### Blade Views
- `resources/views/livewire/checkout-summary-component.blade.php`: Menampilkan breakdown dengan harga asli (strikethrough), persen diskon, dan harga akhir.
- `resources/views/checkout/addons.blade.php`: Menampilkan harga diskon untuk subscription plan di halaman addons.

### Order & Invoice
- Nilai yang digunakan pada pembuatan `Order` dan `Invoice` di alur checkout berasal dari total checkout yang telah memakai `discounted_price` melalui `CartService`/`CheckoutSummaryComponent`.

### Testing Recommendations
- Unit test untuk `SubscriptionPlan::discounted_price` (kasus 0%, >0%, dan edge cases).
- Feature/integration test untuk kalkulasi `CartService` dan `CheckoutSummaryComponent` agar subtotal/total konsisten.
- Validasi tampilan breakdown di Blade dengan snapshot/DOM assertion.
- Pastikan akurasi jumlah pada `Invoice`/`Order` terhadap hasil perhitungan checkout.

### Display & Rounding
- Penyajian harga menggunakan format 2 desimal konsisten dengan tipe `decimal:2` pada model terkait.
- Gunakan helper/formatting yang ada (`CurrencyHelper`) untuk tampilan harga, bila tersedia.