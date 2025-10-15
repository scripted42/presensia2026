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
- Dashboard dengan statistik real-time
- Report absensi dengan filter bulan/tahun
- Export PDF, Excel, CSV
- Grafik dan chart interaktif

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
- **Charts**: Chart.js

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

6. **Build assets**
```bash
npm run build
```

7. **Start development server**
```bash
php artisan serve
```

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
- `roles` - Role pengguna
- `permissions` - Permission sistem
- `model_has_roles` - Relasi user-role
- `model_has_permissions` - Relasi user-permission

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


