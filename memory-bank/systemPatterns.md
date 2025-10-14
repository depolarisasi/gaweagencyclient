# System Patterns

## Architecture Overview
Laravel 12 application dengan comprehensive role-based access control untuk agency client management, payment processing, dan project lifecycle management.

## Key Design Patterns

### 1. Role-Based Access Control (RBAC)
**Implementation:**
- User roles: admin, client, staff
- Middleware untuk proteksi route berdasarkan role
- Role-specific dashboards dengan functionality yang berbeda
- Granular permissions untuk setiap action

**Pattern Usage:**
```php
// Middleware protection
Route::middleware(['auth', 'role:admin'])->group(function () {
    // Admin-only routes
});

// Role-specific controllers
Admin\DashboardController
Client\DashboardController  
Staff\DashboardController
```

### 2. Service Layer Pattern
**TripayService Implementation:**
- Centralized payment gateway logic
- API integration dengan error handling
- Transaction management dan callback processing
- Environment-based configuration (sandbox/production)

**Benefits:**
- Separation of concerns
- Reusable business logic
- Easier testing dan maintenance
- Clean controller code

### 3. Livewire Component Pattern
**Interactive Components:**
- ProductShowcase (template selection)
- CheckoutConfigure (product configuration)
- CheckoutSummary (order processing)
- InvoiceShow (payment interface)

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