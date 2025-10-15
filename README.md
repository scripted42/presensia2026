# Presensia - Sistem Manajemen Sekolah

Aplikasi manajemen sekolah berbasis web dengan PWA yang menyediakan solusi lengkap untuk:
- Manajemen data pegawai dan siswa
- Sistem absensi digital dengan QR Code dan GPS
- Report dan dashboard analytics
- Sistem izin dan approval
- Multi-tenant untuk model SaaS

## Fitur Utama

### 🏫 Manajemen Sekolah
- Multi-tenant architecture untuk multiple schools
- Manajemen data pegawai dan siswa lengkap
- Manajemen kelas dan promosi siswa
- Upload data massal Excel/CSV

### 👥 Role Management
- **Admin**: Full access ke semua fitur
- **Guru**: Absensi pribadi dan siswa
- **Tata Usaha**: Manajemen data
- **BK/Kesiswaan**: Monitoring siswa
- **Siswa**: Data pribadi dan absensi

### 📱 Sistem Absensi
- **Absensi Pegawai**: Selfie + QR Code + GPS validation
- **Absensi Siswa**: Bulk scan QR Code
- Real-time QR Code generation (10 detik)
- GPS validation dengan radius 100m
- Offline sync untuk absensi siswa

### 📊 Dashboard & Reports
- Dashboard baru dengan metrik agregat (gauge): Penggunaan Absensi, KPI Absensi, Kelengkapan Data Pegawai/Siswa
- Komponen ringkas "Absensi Hari Ini" (stepper Masuk/Keluar) untuk pengguna non-manajerial
- Report absensi dengan filter periode, export PDF/Excel/CSV
- Insight operasional: Missed Checkout, Non-user periode, Profil tidak lengkap

### 📋 Sistem Izin
- Request izin: Sakit, Cuti, Dinas Luar
- Upload evidence dan approval workflow
- Notifikasi real-time

## Technology Stack

- **Backend**: Laravel 12.x
- **Database**: MySQL 8.0+
- **Frontend**: Blade + TailwindCSS
- **PWA**: Service Worker + Manifest
- **Maps**: Google Maps API
- **QR Code**: QRCode.js
- **Charts**: ApexCharts (CDN)

## Installation

### Prerequisites
- PHP 8.2+
- MySQL 8.0+
- Composer
- Node.js & NPM

### Setup

1. **Clone repository**
```bash
git clone <repository-url>
cd presensia-v2/starter-kit
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database setup**
```bash
# Create MySQL database
mysql -u root -p
CREATE DATABASE presensia;
exit

# Update .env file
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=presensia
DB_USERNAME=root
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

> Catatan: repo ini menyertakan migrasi perbaikan role (normalisasi nama role + kolom `display_name`). Jika Anda mengubah nama role melalui UI dan terjadi 403, jalankan lagi `php artisan migrate` untuk menormalkan slug teknis peran.

6. **Build assets**
```bash
npm run build
```

7. **Start development server**
```bash
php artisan serve
```

> Catatan: sebagian kecil gaya UI (hover dan banner dashboard) menggunakan inline CSS sehingga tidak bergantung pada build Vite untuk berfungsi. Grafik menggunakan ApexCharts via CDN, jadi tidak memerlukan bundling.

## Demo Credentials

Setelah menjalankan seeder, Anda dapat login dengan:

- **Admin**: admin@presensia.com / password
- **Guru**: guru@presensia.com / password
- **TU**: tu@presensia.com / password
- **BK**: bk@presensia.com / password
- **Kesiswaan**: kesiswaan@presensia.com / password

## Database Structure

### Core Tables
- `users` - Pegawai dan siswa
- `schools` - Data sekolah (multi-tenant)
- `classes` - Data kelas
- `class_students` - Relasi siswa-kelas
- `attendances` - Data absensi
- `leave_requests` - Permohonan izin
- `attendance_settings` - Pengaturan absensi
- `qr_codes` - QR Code aktif

### Role & Permission System
- `roles` - Role pengguna (slug teknis pada kolom `name`, label tampilan pada kolom `display_name`)
- `permissions` - Permission sistem
- `model_has_roles` - Relasi user-role
- `model_has_permissions` - Relasi user-permission

#### Kebijakan Role
- Slug teknis (name) untuk peran sistem bersifat terkunci: `admin`, `teacher`, `headmaster`, `tu`, `bk`, `kesiswaan`, `student`, `super-admin`.
- UI mengizinkan perubahan `display_name` (mis. Admin, Guru, Tata Usaha, BK, Kepala Sekolah) tanpa mengubah slug.
- Tombol normalisasi tersedia melalui migrasi perbaikan (lihat bagian setup) untuk memulihkan slug jika terlanjur berubah.

## API Endpoints

### Authentication
- `POST /login` - Login user
- `POST /logout` - Logout user

### Dashboard
- `GET /dashboard` - Dashboard utama

### Attendance
- `GET /attendance/check-in` - Form absensi masuk
- `POST /attendance/check-in` - Proses absensi masuk
- `GET /attendance/qr-code` - Generate QR Code
- `POST /attendance/student-scan` - Scan siswa

### Reports
- `GET /attendance/reports` - Laporan absensi
- `GET /attendance/reports/export` - Export laporan

## PWA Features

- Service Worker untuk offline caching
- Manifest untuk install sebagai app
- Responsive design untuk mobile
- Push notifications

## Security Features

- HTTPS encryption
- CSRF protection
- XSS protection
- SQL injection protection
- File upload validation
- Rate limiting

## Performance Optimization

- Redis caching
- Database indexing
- Image optimization
- Lazy loading
- CDN support

## Deployment

### Production Setup
1. Setup web server (Nginx/Apache)
2. Configure PHP-FPM
3. Setup MySQL dengan replication
4. Configure Redis
5. Setup SSL certificate
6. Configure backup strategy

### Docker Deployment
```bash
docker-compose up -d
```

## Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## License

MIT License - see LICENSE file for details

## Support

Untuk support dan pertanyaan:
- Email: support@presensia.com
- Documentation: [docs.presensia.com](https://docs.presensia.com)
- Issues: [GitHub Issues](https://github.com/presensia/issues)

## Roadmap

### Phase 2
- Parent Portal
- Mobile App (iOS/Android)
- Advanced Analytics
- Integration dengan SIS/LMS

### Phase 3
- Multi-language support
- API Marketplace
- White-label solution
- Enterprise features


