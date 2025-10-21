# 🏢 FLOW SAAS PRESENSIA

## 📋 **ARQUITECTURA SAAS**

### **1. SUPER ADMIN (Pemilik SaaS)**
- **Login**: `superadmin@presensia.com` / `password`
- **Database**: Shared database dengan isolasi per `school_id`
- **Fungsi**: 
  - Kelola semua sekolah
  - Buat sekolah baru dengan admin
  - Kustomisasi branding per sekolah
  - Monitor semua tenant

### **2. ADMIN SEKOLAH (Per Sekolah)**
- **Login**: Email yang dibuat saat sekolah dibuat
- **Database**: Isolated data berdasarkan `school_id`
- **Fungsi**:
  - Kelola data sekolah mereka saja
  - Kustomisasi aplikasi sekolah
  - Kelola user sekolah mereka

## 🔄 **FLOW PENGGUNAAN SAAS**

### **STEP 1: SUPER ADMIN BUAT SEKOLAH**
1. **Login sebagai Super Admin**
   - URL: `http://localhost:8000/login`
   - Email: `superadmin@presensia.com`
   - Password: `password`

2. **Akses Super Admin Dashboard**
   - URL: `http://localhost:8000/super-admin`
   - Klik "Tambah Sekolah"

3. **Isi Form Sekolah Baru**
   - **Informasi Sekolah**: Nama, alamat, telepon, email, website
   - **Informasi Admin**: Nama admin, email admin, password admin
   - **Branding**: Nama aplikasi, warna tema

4. **Sekolah Berhasil Dibuat**
   - Sistem otomatis membuat admin untuk sekolah
   - Menampilkan informasi login admin
   - **SIMPAN INFORMASI LOGIN ADMIN!**

### **STEP 2: ADMIN SEKOLAH LOGIN**
1. **Login sebagai Admin Sekolah**
   - URL: `http://localhost:8000/login`
   - Email: (Email yang ditampilkan saat sekolah dibuat)
   - Password: (Password yang ditampilkan saat sekolah dibuat)

2. **Akses Dashboard Sekolah**
   - URL: `http://localhost:8000/dashboard`
   - Hanya melihat data sekolah mereka
   - Dapat kustomisasi aplikasi sekolah

## 🔒 **KEAMANAN DATABASE**

### **1. SHARED DATABASE DENGAN ISOLASI**
- **Satu Database**: Semua sekolah dalam satu database
- **Isolasi Data**: Setiap data memiliki `school_id`
- **Middleware**: `SchoolIsolationMiddleware` memastikan isolasi
- **Keamanan**: User hanya bisa akses data sekolah mereka

### **2. CONTOH ISOLASI DATA**
```sql
-- Users table
id | school_id | name | email
1  | 1         | Admin SMPN 14 | admin@smpn14.com
2  | 1         | Guru SMPN 14  | guru@smpn14.com
3  | 2         | Admin SMPN 10 | admin@smpn10.com
4  | 2         | Guru SMPN 10  | guru@smpn10.com
```

### **3. QUERY ISOLASI**
```php
// User hanya bisa akses data sekolah mereka
$users = User::where('school_id', auth()->user()->school_id)->get();

// Admin sekolah tidak bisa akses data sekolah lain
$otherSchoolUsers = User::where('school_id', '!=', auth()->user()->school_id)->get(); // BLOCKED
```

## 🎨 **KUSTOMISASI PER SEKOLAH**

### **1. BRANDING YANG BERBEDA**
- **SMPN 14**: "Presensia SMPN 14" dengan tema hijau
- **SMPN 10**: "Etrack SMPN 10" dengan tema merah
- **SMA 1**: "SchoolTrack SMA 1" dengan tema ungu

### **2. FITUR YANG DAPAT DIKUSTOMISASI**
- **Nama Aplikasi**: Setiap sekolah punya nama berbeda
- **Warna Tema**: Primer, sekunder, aksen
- **Logo**: Upload logo sekolah
- **Favicon**: Icon browser custom
- **Fitur**: Enable/disable fitur per sekolah

## 📱 **CONTOH PENGGUNAAN**

### **SCENARIO 1: Sekolah A (SMPN 14)**
1. **Super Admin** buat sekolah "SMPN 14" dengan admin "admin@smpn14.com"
2. **Admin SMPN 14** login dengan email tersebut
3. **Admin SMPN 14** kustomisasi aplikasi menjadi "Presensia SMPN 14" dengan tema hijau
4. **User SMPN 14** login dan melihat aplikasi dengan branding hijau

### **SCENARIO 2: Sekolah B (SMPN 10)**
1. **Super Admin** buat sekolah "SMPN 10" dengan admin "admin@smpn10.com"
2. **Admin SMPN 10** login dengan email tersebut
3. **Admin SMPN 10** kustomisasi aplikasi menjadi "Etrack SMPN 10" dengan tema merah
4. **User SMPN 10** login dan melihat aplikasi dengan branding merah

## 🚀 **KEUNTUNGAN ARQUITECTURA INI**

### **1. KEAMANAN**
- ✅ Data terisolasi per sekolah
- ✅ User tidak bisa akses data sekolah lain
- ✅ Super Admin bisa monitor semua sekolah

### **2. KUSTOMISASI**
- ✅ Setiap sekolah punya branding berbeda
- ✅ Fitur dapat dienable/disable per sekolah
- ✅ Identitas visual yang unik

### **3. SKALABILITAS**
- ✅ Mudah tambah sekolah baru
- ✅ Shared infrastructure
- ✅ Cost effective

### **4. MAINTENANCE**
- ✅ Satu aplikasi untuk semua sekolah
- ✅ Update sekali, berlaku untuk semua
- ✅ Centralized management

## 🔧 **TECHNICAL IMPLEMENTATION**

### **1. MIDDLEWARE ISOLASI**
```php
// SchoolIsolationMiddleware
public function handle(Request $request, Closure $next)
{
    $user = auth()->user();
    if ($user->school_id) {
        $request->merge(['school_id' => $user->school_id]);
    }
    return $next($request);
}
```

### **2. MODEL SCOPING**
```php
// User model
public function scopeForSchool($query, $schoolId)
{
    return $query->where('school_id', $schoolId);
}
```

### **3. CONTROLLER FILTERING**
```php
// UserController
public function index()
{
    $users = User::where('school_id', auth()->user()->school_id)->get();
    return view('users.index', compact('users'));
}
```

## 📊 **DATABASE STRUCTURE**

### **1. TABLES DENGAN SCHOOL_ID**
- `users` - User per sekolah
- `classes` - Kelas per sekolah
- `attendances` - Absensi per sekolah
- `leave_requests` - Izin per sekolah
- `tenant_settings` - Pengaturan per sekolah

### **2. TABLES GLOBAL**
- `schools` - Data sekolah
- `roles` - Role global
- `permissions` - Permission global
- `super_admins` - Super admin

## 🎯 **KESIMPULAN**

**Sistem SaaS Presensia menggunakan:**
- ✅ **Shared Database** dengan isolasi per `school_id`
- ✅ **Multi-tenant Architecture** yang aman
- ✅ **Custom Branding** per sekolah
- ✅ **Feature Toggle** per sekolah
- ✅ **Centralized Management** untuk Super Admin

**Flow yang benar:**
1. **Super Admin** buat sekolah → dapat info login admin
2. **Admin Sekolah** login dengan info tersebut
3. **Admin Sekolah** kustomisasi aplikasi sekolah mereka
4. **User Sekolah** login dan melihat aplikasi dengan branding sekolah mereka








