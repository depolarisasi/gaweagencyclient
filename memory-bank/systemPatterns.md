# System Patterns

## Architecture Overview
Laravel 12 application dengan comprehensive role-based access control untuk agency client management, payment processing, dan project lifecycle management.

## Key Design Patterns

### 1. Cart Management Pattern
**Implementation:**
- Database-driven cart dengan session fallback
- Cart expiration management (7 days)
- Addon synchronization dengan pricing snapshot
- Migration pattern dari session/cookies ke database

**Usage:**
```php
// Get or create cart
$cart = $this->cartService->getOrCreateCart($request);

// Update cart with template
$this->cartService->updateTemplate($cart, $templateId);

// Sync addons dengan pricing snapshot
$this->cartService->syncAddons($cart, $selectedAddons);

// Get comprehensive cart summary
$summary = $this->cartService->getCartSummary($cart);
```

### 2. Role-Based Access Control (RBAC)
**Implementation:**
- Spatie Laravel Permission package
- Role hierarchy: Admin > Staff > Client
- Granular permissions untuk specific actions
- Middleware-based route protection

**Usage:**
```php
// Role assignment
$user->assignRole('client');

// Permission checking
if ($user->can('manage-projects')) {
    // Allow access
}

// Middleware protection
Route::middleware(['role:admin'])->group(function () {
    // Admin-only routes
});
```

### 3. Service Layer Pattern
**Implementation:**
- Business logic separation dari controllers
- Reusable service classes untuk complex operations
- Dependency injection untuk service management
- Transaction management untuk data consistency

**Key Services:**
- **TripayService**: Payment processing dengan fee calculation
- **CartService**: Comprehensive cart management dengan persistence
- **ProjectService**: Project lifecycle management
- **NotificationService**: System notifications
- **DomainService**: Domain availability checking

**CartService Pattern:**
```php
class CartService
{
    public function getOrCreateCart(Request $request): Cart
    {
        // Session/user-based cart creation dengan fallback mechanism
    }
    
    public function migrateFromSessionAndCookies(Request $request): Cart
    {
        // Migration pattern dari session/cookies ke database
    }
    
    public function getCartSummary(Cart $cart): array
    {
        // Comprehensive calculations dengan fee breakdown
    }
    
    public function syncAddons(Cart $cart, array $addonIds): void
    {
        // Addon synchronization dengan pricing snapshot
    }
}
```

**Benefits:**
- Separation of concerns
- Reusable business logic
- Easier testing dan maintenance
- Clean controller code

### 4. Pricing & Discount Pattern
**Goal:** Konsolidasi perhitungan harga diskon agar konsisten di seluruh alur checkout, cart, order, dan invoice.

**Implementation:**
- Accessor pada model `SubscriptionPlan`: `discounted_price` menghitung harga akhir berdasarkan `discount_percentage`.
- Service layer (`CartService`) selalu menggunakan `discounted_price` untuk `template_amount`/`subscriptionAmount` dan perhitungan subtotal/total.
- Livewire `CheckoutSummaryComponent` mengambil `discounted_price` untuk kalkulasi real-time di UI.
- Blade views (checkout summary, addons) menampilkan breakdown harga: harga asli (strikethrough), persen diskon, harga akhir.

**Usage:**
```php
// Model accessor
$finalPrice = $subscriptionPlan->discounted_price;

// CartService calculations
$summary['subscriptionAmount'] = $subscriptionPlan->discounted_price;
$cart->template_amount = $subscriptionPlan->discounted_price;

// Livewire component
$this->subscriptionAmount = $this->subscriptionPlan->discounted_price;
$this->totalAmount = $this->subscriptionAmount + $this->addonsAmount;
```

**Benefits:**
- Satu sumber kebenaran untuk perhitungan diskon
- Konsistensi antara backend totals dan UI
- Meminimalkan bug akibat duplikasi logika
- Memudahkan testing terfokus di accessor dan service layer

### 3. Livewire Component Pattern
**Implementation:**
- Real-time reactive components dengan comprehensive functionality
- Server-side rendering dengan client-side interactivity
- Event-driven communication antar components
- Form handling dengan real-time validation
- Session dan state management

**Key Components:**
- **ProductShowcase**: Template browsing dengan filtering dan selection
- **CheckoutConfigure**: Subscription plan configuration dengan billing cycles
- **CheckoutSummary**: Comprehensive checkout dengan payment channel selection
- **SubscriptionManager**: Subscription lifecycle management dengan upgrade/renewal
- **DomainSelector**: Real-time domain availability checking
- **CheckoutSummaryComponent**: Advanced cart calculations dengan fee breakdown

**Usage Pattern:**
```php
// Component dengan state management
class CheckoutSummary extends Component
{
    public $cart;
    public $paymentChannels;
    public $selectedPaymentChannel;
    
    protected $listeners = ['cart-updated' => 'refreshCart'];
    
    public function mount()
    {
        $this->loadPaymentChannels();
    }
    
    public function selectPaymentChannel($channelCode)
    {
        $this->selectedPaymentChannel = $channelCode;
        $this->emit('payment-channel-selected', $channelCode);
    }
}
```

**Advantages:**
- Real-time interactivity tanpa complex JavaScript
- Server-side validation
- State management
- Progressive enhancement

### 4. Repository Pattern (Implicit via Eloquent)
**Model-Based Data Access:**
- Eloquent ORM untuk database operations
- Scopes untuk reusable query logic
- Relationships untuk data integrity
- Accessors/Mutators untuk data transformation

## Component Architecture

### Models (9 Core Models)
**Primary Entities:**
- User (role management, relationships)
- Product (pricing, billing cycles)
- Project (lifecycle management, website access)
- Order (payment integration)
- Invoice (payment tracking)

**Supporting Entities:**
- SupportTicket (customer service)
- TicketReply (communication tracking)
- Template (product templates)
- ProductAddon (upselling)
- Payment (Tripay integration)

### Controllers (Organized by Role)
**Admin Controllers:**
- DashboardController (statistics, overview)
- UserController, ProductController, ProjectController
- InvoiceController, SupportTicketController
- TemplateController

**Client Controllers:**
- DashboardController (project overview)
- SupportTicketController (self-service)

**Staff Controllers:**
- DashboardController (task management)

**Core Controllers:**
- CheckoutController (multi-step process)
- PaymentController (Tripay integration)
- TemplateController (public interface)

### Middleware Stack
- Authentication verification
- Role-based authorization
- CSRF protection
- Rate limiting
- Input sanitization

## Database Design

### Core Tables Structure
**User Management:**
- users (role, profile data)
- password_resets, personal_access_tokens

**Product & Ordering:**
- products (pricing, billing cycles)
- product_addons (upselling options)
- orders (customer orders)
- invoices (billing)
- payments (Tripay integration)

**Project Management:**
- projects (lifecycle, website access)
- templates (product templates)

**Support System:**
- support_tickets (customer service)
- ticket_replies (communication)

### Relationship Patterns
**Payment Flow:**
```
User → Order → Invoice → Payment
     ↓
   Project (created after payment)
```

**Support Flow:**
```
User → SupportTicket → TicketReply
     ↑
   Staff (assignment)
```

**Product Flow:**
```
Template → Product → ProductAddon
         ↓
       Order → Project
```

## Security Patterns

### Authentication & Authorization
- Laravel Sanctum untuk API authentication
- Role-based middleware protection
- CSRF token validation
- Password hashing dengan bcrypt

### Payment Security
- Tripay signature validation
- Secure callback handling
- Environment-based API keys
- Transaction logging

### Data Protection
- Input validation via Form Requests
- SQL injection prevention via Eloquent
- XSS protection via Blade escaping
- File upload validation

## Frontend Patterns

### Blade Templating
- Layout inheritance
- Component-based views
- Conditional rendering berdasarkan role
- Data binding dengan security

### Livewire Integration
- Real-time form validation
- Dynamic content updates
- State persistence
- Event handling

### CSS Framework
- Tailwind CSS untuk utility-first styling
- DaisyUI untuk component consistency
- Responsive design patterns
- Dark mode support

### JavaScript Enhancement
- Progressive enhancement
- Minimal JavaScript footprint
- Livewire untuk interactivity
- Alpine.js untuk simple interactions

## Performance Patterns

### Database Optimization
- Eager loading untuk relationships
- Query scopes untuk reusable logic
- Database indexing pada foreign keys
- Pagination untuk large datasets

### Caching Strategy
- Route caching
- View caching
- Configuration caching
- Query result caching (planned)

### Asset Optimization
- Vite untuk asset bundling
- CSS/JS minification
- Image optimization
- CDN integration (planned)

## Error Handling Patterns

### Exception Management
- Custom exception classes
- Graceful error pages
- Logging untuk debugging
- User-friendly error messages

### Payment Error Handling
- Tripay callback validation
- Transaction failure recovery
- Retry mechanisms
- Comprehensive logging

### Validation Patterns
- Form Request validation
- Client-side validation via Livewire
- Database constraint validation
- Business rule validation