# PT TRS Finance System — Backend API

REST API untuk sistem informasi keuangan **PT Trisena Rekainova Sinergi**. Bagian dari project skripsi: backend mencatat transaksi penjualan + dashboard untung-rugi + audit trail.

Frontend (React) ada di repo terpisah → [`project-frontend-trs`](../project-frontend-trs).

---

## 🧱 Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Framework | Laravel 11+ |
| Bahasa | PHP 8.2+ |
| Database | MySQL / MariaDB |
| Auth | Laravel Sanctum (token-based) |
| Audit Trail | Spatie Laravel Activity Log |
| Architecture | REST API |

---

## 📋 Prerequisites

- **PHP 8.2+** dengan extensions: `pdo_mysql`, `mbstring`, `openssl`, `bcmath`, `curl`
- **Composer 2.x**
- **MySQL 5.7+** atau **MariaDB 10.4+**
- (Disarankan) Laragon untuk Windows — bundle PHP + MySQL + Apache

---

## 🚀 Quick Setup

### 1. Clone repository

```bash
git clone <url-repo> project-api-trs
cd project-api-trs
```

### 2. Install dependencies

```bash
composer install
```

### 3. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — set database config:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=trs
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Create database

Buat database bernama `trs` di phpMyAdmin (atau via CLI):
```bash
mysql -u root -e "CREATE DATABASE trs;"
```

### 5. Jalankan migration

```bash
php artisan migrate
```

Output yang diharapkan: ~15 migration berhasil (users, products, sales_transactions, customers, expeditions, activity_log, dst). Migration `create_expeditions_table` otomatis seed 3 default ekspedisi (JNE, JNT, SiCepat).

### 6. Run development server

```bash
php artisan serve
```

API base URL: **http://127.0.0.1:8000/api**

---

## 🔐 Default Credentials (untuk testing)

Belum ada seeder default untuk user. Setelah migrate, **register manual** via API.

**Via curl:**
```bash
curl -X POST http://127.0.0.1:8000/api/register ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Owner Test\",\"email\":\"owner@trs.local\",\"password\":\"password123\",\"role\":\"owner\"}"
```

Lalu untuk akun finance:
```bash
curl -X POST http://127.0.0.1:8000/api/register ^
  -H "Content-Type: application/json" ^
  -d "{\"name\":\"Finance Test\",\"email\":\"finance@trs.local\",\"password\":\"password123\",\"role\":\"finance\"}"
```

**Suggested test credentials:**

| Role | Email | Password |
|------|-------|----------|
| Owner | `owner@trs.local` | `password123` |
| Finance | `finance@trs.local` | `password123` |

> ⚠️ Ganti password sebelum deploy production. Endpoint `/register` sebaiknya di-disable atau di-restrict pasca-setup awal (hanya owner via `/api/users`).

---

## 🌟 Key Features

- ✅ **Auth** — login, logout, register, profile self-service (change password dengan verify current)
- ✅ **CRUD lengkap** — User, Customer, Product, ProductType, Ekspedisi, SalesTransaction (+ nested TransactionCost)
- ✅ **Transaction lifecycle** — status workflow (quotation → pre_order → processing → shipping → completed/cancelled) dengan edit-lock & state machine validation
- ✅ **Master Ekspedisi** — kurir pengiriman dengan flag `is_active` untuk disable tanpa hapus
- ✅ **Customer master** — foreign key ke transaksi untuk invoice yang bermakna
- ✅ **Role-based access** — `role:owner` middleware untuk endpoint sensitive (User mgmt, Master Product/Ekspedisi CRUD, DELETE apapun)
- ✅ **Dashboard endpoints** — total, weekly/monthly/yearly snapshot, monthly sales/profit, top products, financial report dengan filter rentang tanggal
- ✅ **Audit trail** — Spatie Activity Log: catat siapa, kapan, ubah apa untuk 6 model (User, Customer, Product, ProductType, SalesTransaction, Expedition)
- ✅ **Search & Pagination** — endpoint list support `?search=`, `?start_date`, `?end_date`, `?page=`
- ✅ **DB Indexing** — index di `sales_transactions.transaction_date` & `status` untuk performance saat data besar

---

## 📂 Folder Structure (highlights)

```
project-api-trs/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/BaseController.php          ← shared response helpers
│   │   │   ├── AuthController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── UserController.php               (owner only)
│   │   │   ├── CustomerController.php
│   │   │   ├── ProductController.php
│   │   │   ├── ProductTypeController.php
│   │   │   ├── ExpeditionController.php
│   │   │   ├── SalesTransactionController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── AnalyticsController.php
│   │   │   └── ActivityLogController.php        (owner only)
│   │   └── Middleware/
│   │       └── RoleMiddleware.php               ← role:owner enforcement
│   └── Models/
│       ├── User.php
│       ├── Customer.php
│       ├── Product.php
│       ├── ProductType.php
│       ├── Expedition.php
│       ├── SalesTransaction.php
│       └── TransactionCost.php
├── database/migrations/                         ← 15+ migration files
├── routes/api.php                               ← all API routes (auth & role middleware)
└── .env                                         ← config (DB, app key, etc)
```

---

## 🔗 API Endpoints Overview

Base URL: `http://127.0.0.1:8000/api`. Semua endpoint kecuali `register` & `login` butuh header `Authorization: Bearer <token>`.

### Public
- `POST /register` — register user (role: `owner` | `finance`)
- `POST /login` — login (return token + user)

### Authenticated (semua user login)
- `POST /logout`, `GET /me`
- `PUT /profile` (update name/email), `PUT /profile/password` (ganti password)
- `GET|POST|PUT /customers`, `GET /customers/dropdown`
- `GET /products`, `GET /product-types`, `GET /expeditions`, `GET /expeditions/dropdown`
- `GET|POST|PUT|PATCH /sales-transactions` (+ `/history`, `/{id}/status`)
- `GET /dashboard`, `GET /dashboard/financial`
- `GET /analytics/{monthly-sales,monthly-profit,top-products}`

### Owner only (`role:owner` middleware)
- `GET|POST|PUT|DELETE /users`
- `POST|PUT|DELETE /products`, `/product-types`, `/expeditions`
- `DELETE /customers/{id}`, `DELETE /sales-transactions/{id}`
- `GET /activity-logs`

Untuk detail request/response shape: lihat `routes/api.php` + controller masing-masing.

---

## 🛠️ Development Notes

- **Response shape**: semua endpoint (kecuali AuthController) pakai envelope `{ success, message, data }` lewat `BaseController::successResponse()` / `paginatedResponse()` / `errorResponse()`.
- **Route ordering**: route `/dropdown` & `/history` HARUS di-define **sebelum** `/{id}` di route file supaya tidak ke-catch dynamic route.
- **FK strategy**: `customer_id` & `expedition_id` di `sales_transactions` pakai `restrictOnDelete()` — tidak boleh hapus master kalau masih dipakai. Alternatif: set `is_active = false` (Expedition).
- **Activity Log**: trait `LogsActivity` di-attach ke Model + `getActivitylogOptions()` configure field yang di-track. Owner-only access via middleware.
- **Transaction Edit Lock**: hanya status `quotation` yang bisa di-edit lewat `PUT /sales-transactions/{id}`. Status lain hanya bisa transition via `PATCH /{id}/status` (state machine).

---

## 🐛 Known Issues / Future Work

- [ ] Belum ada UserSeeder default — register manual via API
- [ ] `/register` masih public — restrict ke owner setelah setup awal
- [ ] Integrasi e-commerce (auto-create transaction dari order) — post-skripsi
- [ ] Soft delete belum diimplementasi (data dihapus permanen)
- [ ] Test suite (PHPUnit) belum ditulis

---

## 📖 Related Docs

- 📊 **[ERD.md](./ERD.md)** — Entity Relationship Diagram + per-table breakdown + design decisions (snapshot pattern, state machine, polymorphic activity log, FK strategy). **Wajib baca** sebelum modify struktur DB.
- 📄 **[Frontend CARA_BACA_CODE.md](../project-frontend-trs/CARA_BACA_CODE.md)** — panduan baca code frontend.

---

## 📄 License

Project akademik (skripsi). Internal PT Trisena Rekainova Sinergi.
