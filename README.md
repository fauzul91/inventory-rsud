# Backend Inventory RSD Balung

<p align="center">
  <strong>Sistem Manajemen Inventaris Rumah Sakit Balung</strong><br>
  Dikembangkan oleh Tim Capstone Fasilkom UNEJ
</p>

---

## 📋 Daftar Isi

- [Overview Proyek](#overview-proyek)
- [Fitur Utama](#fitur-utama)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Requirements](#requirements)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Menjalankan Aplikasi](#menjalankan-aplikasi)
- [Struktur Proyek](#struktur-proyek)
- [Tim Pengembang](#tim-pengembang)

---

## 📱 Overview Proyek

**Backend Inventory RSD Balung** adalah sistem backend API yang dirancang untuk mengelola inventaris di Rumah Sakit Umum Daerah (RSUD) Balung. Aplikasi ini menggunakan framework Laravel 11 dan menyediakan RESTful API untuk manajemen inventaris, pemesanan barang, penerimaan barang, dan monitoring stok secara real-time.

### Tujuan Proyek

- Mengoptimalkan pengelolaan inventaris rumah sakit
- Meningkatkan efisiensi tracking stok barang
- Menyediakan sistem pemesanan barang yang terstruktur
- Memberikan laporan dan analisis stok secara akurat

---

## ✨ Fitur Utama

1. **Manajemen Stok**
    - Monitoring stok barang real-time
    - Tracking pergerakan barang
    - Kategori dan satuan barang

2. **Pemesanan Barang**
    - Sistem pemesanan barang terstruktur
    - Tracking status pemesanan
    - Detail pemesanan per item

3. **Penerimaan Barang**
    - Pencatatan penerimaan barang
    - Verifikasi barang masuk
    - Penerimaan per pegawai
    - Berita Acara Serah Terima (BAST)

4. **Pengelolaan Pengguna**
    - Manajemen user dengan role-based access control
    - Manajemen jabatan pegawai
    - Notifikasi sistem

5. **Pelaporan**
    - Export data pengeluaran ke Excel
    - Laporan inventory
    - Dashboard monitoring

6. **Integrasi SSO**
    - Integrasi dengan sistem Single Sign-On eksternal
    - Keamanan dengan Laravel Sanctum

---

## 🛠 Teknologi yang Digunakan

| Komponen           | Teknologi                 |
| ------------------ | ------------------------- |
| Framework Backend  | Laravel 11.31             |
| Bahasa Pemrograman | PHP 8.2+                  |
| Database           | MySQL/PostgreSQL          |
| Authentication     | Laravel Sanctum           |
| Package Manager    | Composer                  |
| Build Tool         | Vite                      |
| ORM                | Eloquent                  |
| API Documentation  | Dedoc Scramble            |
| Export Excel       | Maatwebsite Excel         |
| PDF Generation     | DomPDF                    |
| Permission         | Spatie Laravel Permission |
| Testing            | PHPUnit                   |

---

## 📦 Requirements

Sebelum melakukan instalasi, pastikan sistem Anda memiliki:

- **PHP 8.2** atau lebih tinggi
- **Composer** (untuk manajemen package PHP)
- **Node.js 18+** dan **npm** atau **yarn** (untuk asset frontend)
- **MySQL 8.0+** atau **PostgreSQL 12+**
- **Git**

### Verifikasi Instalasi

```bash
php --version      # Cek versi PHP
composer --version # Cek versi Composer
node --version     # Cek versi Node.js
npm --version      # Cek versi npm
```

---

## 🚀 Instalasi

### 1. Clone Repository

```bash
git clone https://github.com/fauzul91/inventory-rsud.git
cd inventory-rsud
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
# atau
yarn install
```

### 4. Setup Environment File

```bash
# Copy file .env.example menjadi .env
cp .env.example .env

# atau untuk Windows
copy .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Buat Database

Buat database baru di MySQL/PostgreSQL:

```sql
CREATE DATABASE inventory_rsud;
```

### 7. Konfigurasi Database

Edit file `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_rsud
DB_USERNAME=root
DB_PASSWORD=
```

### 8. Jalankan Database Migrations

```bash
php artisan migrate
```

### 9. Jalankan Database Seeding (Opsional)

```bash
php artisan db:seed
```

---

## ⚙️ Konfigurasi

### Konfigurasi Environment

File `.env` yang penting untuk diperhatikan:

```env
# App Configuration
APP_NAME="Inventory RSUD"
APP_ENV=local
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8001
FRONTEND_URL=http://localhost:5173

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=inventory_rsud
DB_USERNAME=root
DB_PASSWORD=

# Auth Configuration (SSO)
PASSPORT_HOST=http://localhost:8000
PASSPORT_CLIENT_ID=your_client_id
PASSPORT_CLIENT_SECRET=your_client_secret
PASSPORT_REDIRECT_URI=http://localhost:8001/api/sso/callback
SSO_LOGOUT_URL=http://localhost:8000/logout

# Mail Configuration (Opsional)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_FROM_ADDRESS=noreply@inventoryrsud.com
```

### Konfigurasi Permission

File `config/permission.php` mengatur role dan permission dalam sistem.

---

## ▶️ Menjalankan Aplikasi

### Development Mode

#### 1. Jalankan Server Laravel

```bash
php artisan serve
# Server akan berjalan di http://localhost:8000
```

#### 2. Compile Assets (di terminal baru)

```bash
npm run dev
# atau dengan Vite watcher
npm run dev
```

#### 3. Optional: Jalankan Telescope (untuk debugging)

```bash
# Akses di http://localhost:8000/telescope
```

### Production Mode

```bash
# Compile assets untuk production
npm run build

# Jalankan server production
php artisan serve --env=production
```

---

## 📁 Struktur Proyek

```
inventory-rsud/
├── app/
│   ├── Console/           # Artisan Commands
│   ├── Enum/              # Enums (Value Objects)
│   ├── Exceptions/        # Custom Exceptions
│   ├── Exports/           # Excel Exports
│   ├── Helpers/           # Helper Functions
│   ├── Http/
│   │   ├── Controllers/   # API Controllers
│   │   ├── Middleware/    # HTTP Middleware
│   │   └── Requests/      # Form Requests (Validation)
│   ├── Imports/           # Excel Imports
│   ├── Interfaces/        # Contracts/Interfaces
│   ├── Models/            # Database Models
│   ├── Repositories/      # Repository Pattern
│   ├── Services/          # Business Logic Services
│   └── Providers/         # Service Providers
├── bootstrap/             # Bootstrap files
├── config/                # Configuration files
├── database/
│   ├── migrations/        # Database Migrations
│   ├── seeders/           # Database Seeders
│   └── factories/         # Model Factories
├── public/                # Public assets
├── resources/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── views/             # Blade templates
├── routes/                # Route definitions
│   ├── api/               # API routes
│   ├── web.php            # Web routes
│   └── console.php        # Console routes
├── storage/               # File storage
├── tests/                 # Unit & Feature tests
├── vendor/                # Composer dependencies
├── .env.example           # Example environment file
├── artisan                # Artisan CLI
├── composer.json          # PHP dependencies
├── package.json           # Node dependencies
├── phpunit.xml            # PHPUnit configuration
├── tailwind.config.js     # Tailwind CSS configuration
├── vite.config.js         # Vite configuration
└── README.md              # Documentation
```

---

## 🔑 Key Models

- **User**: Pengguna sistem
- **Pegawai**: Data pegawai rumah sakit
- **Stok**: Inventaris barang
- **Pemesanan**: Data pemesanan barang
- **Penerimaan**: Data penerimaan barang
- **Category**: Kategori barang
- **Satuan**: Satuan pengukuran barang
- **Jabatan**: Posisi/jabatan pegawai
- **Notifikasi**: Sistem notifikasi

---

## 🧪 Testing

Jalankan unit tests:

```bash
php artisan test
```

Jalankan test dengan coverage:

```bash
php artisan test --coverage
```

---

## 📚 API Documentation

Dokumentasi API dapat diakses melalui Scramble di:

```
http://localhost:8001/api/documentation
```

---

## 📝 Kontribusi

Untuk berkontribusi pada proyek ini:

1. Fork repository
2. Buat branch fitur (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

---

## 👥 Tim Pengembang

Proyek ini dikembangkan oleh **Tim Capstone Fasilkom UNEJ** (Fakultas Ilmu Komputer - Universitas Negeri Jember)

---

## 📄 Lisensi

Proyek ini dilisensikan di bawah lisensi MIT. Lihat file `LICENSE` untuk detail lebih lanjut.

---

## 📞 Kontak & Support

Untuk pertanyaan atau masalah, silakan buka issue di repository ini atau hubungi tim pengembang.

---

**Last Updated**: Februari 2026

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
