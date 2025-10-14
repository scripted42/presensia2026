# System Architecture - Aplikasi Manajemen Sekolah (Presensia)

## 1. Overview Sistem

Aplikasi Presensia adalah sistem manajemen sekolah berbasis web dengan PWA yang mencakup:
- Manajemen pegawai dan siswa
- Sistem absensi dengan QR Code dan GPS
- Report dan dashboard
- Sistem izin dan approval
- Multi-tenant untuk SaaS

## 2. Technology Stack

### Backend
- **Framework**: Laravel 12.x
- **Database**: MySQL 8.0+
- **Cache**: Redis (untuk session dan cache)
- **Queue**: Redis Queue (untuk background jobs)
- **Storage**: Local/Cloud Storage untuk file

### Frontend
- **Framework**: Laravel Blade + TailwindCSS
- **PWA**: Service Worker + Manifest
- **Maps**: Google Maps API / Leaflet
- **QR Code**: QRCode.js
- **Charts**: Chart.js

### Mobile Features
- **Camera**: WebRTC untuk selfie
- **GPS**: Geolocation API
- **Offline**: Service Worker untuk sync offline

## 3. Database Architecture

### Core Tables
```
users (pegawai/siswa)
├── id, name, email, password, role, qr_code
├── phone, address, birth_date, gender
├── photo, is_active, created_at, updated_at

schools (multi-tenant)
├── id, name, address, phone, email
├── settings (JSON), created_at, updated_at

classes
├── id, school_id, name, level, year
├── teacher_id, created_at, updated_at

class_students
├── id, class_id, student_id, status
├── created_at, updated_at

attendances
├── id, user_id, date, check_in, check_out
├── status, location, photo, qr_code_used
├── created_at, updated_at

leave_requests
├── id, user_id, type, start_date, end_date
├── reason, evidence, status, approved_by
├── created_at, updated_at

attendance_settings
├── id, school_id, check_in_time, check_out_time
├── location_lat, location_lng, radius
├── qr_code_duration, created_at, updated_at
```

## 4. System Components

### 4.1 Authentication & Authorization
- **Multi-role**: Admin, Guru, TU, BK, Kesiswaan, Siswa
- **JWT Token**: Untuk API authentication
- **Permission System**: Spatie Laravel Permission

### 4.2 Absensi System
- **QR Code Generator**: Otomatis setiap 10 detik
- **GPS Validation**: Radius 100m dari titik lokasi
- **Selfie Capture**: WebRTC untuk foto absensi
- **Bulk Scan**: Untuk absensi siswa massal

### 4.3 Report System
- **Monthly Report**: Tabel dengan status warna
- **Filter**: Bulan, tahun, pegawai/siswa
- **Export**: PDF, Excel, CSV
- **Dashboard**: Statistik real-time

### 4.4 Multi-tenant Architecture
- **Tenant Isolation**: Data terpisah per sekolah
- **Shared Database**: Dengan tenant_id
- **Customization**: Settings per sekolah

## 5. API Endpoints

### Authentication
```
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
```

### Absensi
```
POST /api/attendance/check-in
POST /api/attendance/check-out
GET /api/attendance/qr-code
POST /api/attendance/bulk-scan
```

### Reports
```
GET /api/reports/attendance
GET /api/reports/export
GET /api/dashboard/stats
```

## 6. Security Features

- **HTTPS**: SSL/TLS encryption
- **CSRF Protection**: Laravel CSRF tokens
- **XSS Protection**: Input sanitization
- **SQL Injection**: Eloquent ORM protection
- **File Upload**: Validation dan scanning
- **Rate Limiting**: API rate limiting

## 7. Performance Optimization

- **Caching**: Redis untuk session dan data
- **CDN**: Static assets delivery
- **Image Optimization**: WebP format
- **Database Indexing**: Optimized queries
- **Lazy Loading**: Component lazy loading
- **Service Worker**: Offline caching

## 8. Deployment Architecture

### Production
```
Load Balancer (Nginx)
├── App Server 1 (Laravel + PHP-FPM)
├── App Server 2 (Laravel + PHP-FPM)
├── Database Server (MySQL Master-Slave)
├── Cache Server (Redis Cluster)
└── File Storage (Local/Cloud)
```

### Development
```
Local Development
├── Laravel Valet/Sail
├── MySQL 8.0
├── Redis
└── Node.js (Vite)
```

## 9. Monitoring & Logging

- **Application Logs**: Laravel Log
- **Error Tracking**: Sentry integration
- **Performance**: New Relic/Blackfire
- **Uptime**: Health checks
- **Analytics**: Google Analytics

## 10. Backup & Recovery

- **Database Backup**: Daily automated backup
- **File Backup**: Cloud storage sync
- **Disaster Recovery**: Multi-region deployment
- **Data Retention**: Configurable retention policy

