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

### Core Tables (10 Tables)
**User Management:**
- users (authentication, roles, profile)
- password_resets
- personal_access_tokens

**Product & Commerce:**
- products (service offerings, pricing)
- product_addons (upselling options)
- templates (product templates)
- orders (purchase tracking)
- invoices (billing management)
- payments (Tripay integration)

**Project & Support:**
- projects (client work, website access)
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
- Component-based architecture

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
- PHPUnit untuk testing
- Browser testing dengan Dusk (planned)

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
- **Tripay**: Primary payment processor
- **Supported Methods**: Bank transfer, e-wallet, virtual account
- **Features**: Real-time callback, fee calculation, multi-channel

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
- Transaction logging
- Callback logging
- Error tracking
- Audit trail

### Security Logging
- Authentication attempts
- Authorization failures
- Suspicious activities
- API access logs