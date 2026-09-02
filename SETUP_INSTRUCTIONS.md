# AutoDealer ERP — Setup Instructions

Complete Automotive Dealership ERP/CRM SaaS Platform for Pakistan.
Laravel 12 · Multi-Tenant · 23 Modules.

---

## 1. Requirements

- PHP 8.3+
- Composer 2.x
- MySQL 8.0+
- Redis (cache, sessions, queues)
- Node.js 18+ (only if you add asset bundling later — not required, CDN-based UI)

---

## 2. Installation

```bash
# 1. Extract the zip
unzip automotive-erp-complete.zip
cd automotive-erp

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
cp .env.example .env
php artisan key:generate

# 4. Configure .env — set these at minimum:
#    DB_DATABASE, DB_USERNAME, DB_PASSWORD
#    REDIS_HOST (or switch CACHE_DRIVER/SESSION_DRIVER/QUEUE_CONNECTION to 'database' or 'file' for local dev)
#    CENTRAL_DOMAINS=platform.test   (for local dev with valet/herd, or platform.com in production)
#    FILESYSTEM_DISK=public          (for local dev; use 's3' in production)

# 5. Create storage symlink (for local file access)
php artisan storage:link

# 6. Run migrations (creates all 33 tables in dependency order)
php artisan migrate

# 7. Seed platform-level data (plans + 60 permissions + Pakistan vehicle catalogue)
php artisan db:seed
```

---

## 3. Register Service Provider (Laravel 11/12 style)

Open `bootstrap/providers.php` (create if it doesn't exist) and add:

```php
<?php
return [
    App\Providers\AppServiceProvider::class,
    App\Providers\InventoryServiceProvider::class,
];
```

---

## 4. Create Your First Tenant (Dealership)

Since this is a multi-tenant SaaS, you need to create at least one tenant to log in.
Use `php artisan tinker`:

```php
php artisan tinker

$service = app(\App\Services\TenantService::class);
$tenant = $service->create([
    'subdomain'    => 'demo',
    'company_name' => 'Demo Motors',
    'owner_name'   => 'Ali Khan',
    'email'        => 'admin@demo.com',
    'phone'        => '03001234567',
    'city'         => 'Lahore',
    'password'     => 'password123',
    'plan_slug'    => 'professional',
]);

echo $tenant->subdomain;
```

This single call automatically:
- Creates the tenant + main branch
- Seeds 10 default roles (Dealer Owner, Sales Manager, Accountant, etc.) with correct permissions
- Creates the owner user account
- Seeds the tenant's Chart of Accounts (19 accounts)
- Seeds default expense categories
- Seeds all 18 document templates (affidavit, sale agreement, etc.)
- Creates a 14-day trial subscription

---

## 5. Local Development — Tenant Resolution

In production, tenants are resolved by subdomain (`demo.platform.com`).
For local development without subdomain DNS setup, the app falls back to a query parameter:

```
http://127.0.0.1:8000/login?tenant=demo
```

Or better, add to your `/etc/hosts`:
```
127.0.0.1   demo.platform.test
```
And set `CENTRAL_DOMAINS=platform.test` in `.env`, then visit `http://demo.platform.test:8000`.

---

## 6. Start the Application

```bash
# Start the dev server
php artisan serve

# In a separate terminal — start the queue worker (required for QR codes, documents, WhatsApp)
php artisan queue:work redis

# In a separate terminal — start the scheduler (for installment reminders, overdue marking)
php artisan schedule:work
```

Visit `http://demo.platform.test:8000/login` (or with `?tenant=demo` locally) and log in with the
owner email/password you set in step 4.

---

## 7. Production Deployment Checklist

```bash
APP_ENV=production
APP_DEBUG=false
SESSION_SECURE_COOKIE=true
FILESYSTEM_DISK=s3
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
```

- Set up a real S3 bucket (or Wasabi/DigitalOcean Spaces) for `FILESYSTEM_DISK`
- Set up SendGrid/SES for `MAIL_*` variables
- Configure a wildcard SSL certificate for `*.yourplatform.com`
- Set up Laravel Horizon for queue monitoring (optional but recommended)
- Add cron entry for the scheduler:
  ```
  * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
  ```
- Configure WhatsApp Business API (Meta Cloud API) — see Module 22 below

---

## 8. Module Overview (23 Modules — All Complete)

| # | Module | Status |
|---|--------|--------|
| M01 | Multi-Tenant SaaS Platform | ✅ |
| M02 | Authentication & Security (2FA) | ✅ |
| M03 | User, Role & Permission Management | ✅ |
| M04 | Branch Management | ✅ |
| M05 | Vehicle Inventory Management | ✅ |
| M06 | Vehicle Import Cost Tracking | ✅ |
| M07 | Vehicle Digital File System | ✅ |
| M08 | QR Code System | ✅ |
| M09 | Customer CRM | ✅ |
| M10 | Lead Management | ✅ |
| M11 | Sales Workflow (Quotation→Invoice) | ✅ |
| M12 | Trade-In Management | ✅ |
| M13 | Booking & Reservation System | ✅ |
| M14 | Document Automation Engine (18 docs) | ✅ |
| M15 | Deal File Management | ✅ |
| M16 | Chart of Accounts & Ledger | ✅ |
| M17 | Accounts Receivable | ✅ |
| M18 | Accounts Payable (Vendors) | ✅ |
| M19 | Installment Finance & Recovery | ✅ |
| M20 | Expense Management | ✅ |
| M21 | Commission Management | ✅ |
| M22 | WhatsApp CRM (Shared Inbox) | ✅ |
| M23 | Excel Import/Export | ✅ |

---

## 9. Key Workflows to Test

1. **Add Inventory**: Vehicles → Add Vehicle → fill details → vehicle gets stock number + QR code automatically
2. **Create a Customer**: Customers → Add Customer
3. **Create a Lead**: Leads → Add Lead → assign to salesperson → log activity
4. **Quotation → Booking → Invoice**: Sales workflow chains through these three steps; each one locks/reserves the vehicle appropriately
5. **Generate Affidavit**: From any Sale Invoice → "Generate Documents" panel → click "Affidavit" → PDF auto-fills from customer + vehicle + invoice data
6. **Installments**: Create an invoice with payment_type=installment → redirects to installment plan creation → auto-generates payment schedule
7. **Accounting**: Every sale automatically posts a balanced double-entry journal entry (Cash/Receivable DR, Sales Revenue CR, COGS DR, Inventory CR)
8. **WhatsApp**: Configure WhatsApp Settings with Meta Cloud API credentials → webhook receives messages → shared inbox → convert to lead

---

## 10. WhatsApp Business API Setup (Module 22)

1. Create a Meta Business App at developers.facebook.com
2. Add the WhatsApp product, get a phone number ID and permanent access token
3. In the app: WhatsApp CRM → Settings → enter phone number + access token
4. Set the webhook URL in Meta's dashboard to: `https://yourplatform.com/webhook/whatsapp`
5. Set `WHATSAPP_VERIFY_TOKEN` in `.env` to match what you enter in Meta's webhook config

---

## 11. Known Limitations / Next Steps (Not in This Build)

These were intentionally deferred per the lean V1 scope:
- Workshop / Job Cards module
- Full HR & Payroll
- Bank Reconciliation
- PakWheels/OLX API integrations
- Public dealership website
- Customer self-service portal
- Post-dated cheque register (tracked via Payments module generically instead)

---

## 12. Support Files Included

- `composer.json` — all dependencies pinned
- `.env.example` — full environment template
- `bootstrap/app.php` — middleware registration
- `config/permission.php` — Spatie team-based permissions config
- 33 migrations in strict dependency order
- 44 Eloquent models
- 18 controllers across 8 namespaces
- 17 service classes (business logic layer)
- 78 Blade views
- 7 seeders
- 4 scheduled console commands

**Total: 213 files, fully wired — routes, controllers, and views were cross-validated with zero missing references.**
