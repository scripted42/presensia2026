# 🗑️ Cara Menghapus PWA (Progressive Web App)

Jika Anda tidak menyukai fitur PWA dan ingin menghapusnya, ikuti langkah-langkah berikut:

## 📋 File yang Perlu Dihapus:

### 1. File PWA Utama:
```bash
rm public/manifest.json
rm public/sw.js
rm public/offline.html
rm public/icons/browserconfig.xml
rm public/icons/README.md
```

### 2. Folder Icons (opsional):
```bash
rm -rf public/icons/
```

## 🔧 File yang Perlu Dimodifikasi:

### 1. Hapus PWA Meta Tags dari `resources/views/layouts/app.blade.php`:

**Hapus baris 9-40** (PWA Meta Tags):
```html
<!-- PWA Meta Tags -->
<meta name="application-name" content="Presensia">
<meta name="apple-mobile-web-app-capable" content="yes">
<!-- ... dst ... -->
```

**Hapus baris 563-706** (PWA JavaScript):
```html
<!-- PWA JavaScript -->
<script>
// ... semua kode PWA JavaScript ...
</script>
```

### 2. Hapus PWA JavaScript dari Layout:

Cari dan hapus semua kode JavaScript PWA yang dimulai dari:
```html
<!-- PWA JavaScript -->
<script>
```

Sampai dengan:
```html
</script>
```

## 🚀 Langkah-langkah Penghapusan:

### 1. Hapus File PWA:
```bash
cd starter-kit
rm public/manifest.json
rm public/sw.js
rm public/offline.html
rm public/icons/browserconfig.xml
rm public/icons/README.md
```

### 2. Edit Layout File:
```bash
# Buka file layout
nano resources/views/layouts/app.blade.php

# Hapus baris 9-40 (PWA Meta Tags)
# Hapus baris 563-706 (PWA JavaScript)
```

### 3. Clear Cache:
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### 4. Commit Perubahan:
```bash
git add .
git commit -m "remove: Remove PWA functionality

- Remove manifest.json and service worker
- Remove PWA meta tags from layout
- Remove PWA JavaScript code
- Clean up PWA-related files"
```

## ⚠️ Catatan Penting:

1. **Backup dulu** sebelum menghapus
2. **Test aplikasi** setelah penghapusan
3. **Clear cache browser** untuk menghapus PWA yang sudah terinstall
4. **Restart server** jika diperlukan

## 🔄 Rollback PWA:

Jika ingin mengembalikan PWA:
```bash
git checkout HEAD~1 -- public/manifest.json
git checkout HEAD~1 -- public/sw.js
git checkout HEAD~1 -- public/offline.html
git checkout HEAD~1 -- resources/views/layouts/app.blade.php
```

## 📱 Menghapus PWA dari Device:

### Android Chrome:
1. Buka Chrome
2. Menu > Settings > Apps
3. Cari "Presensia"
4. Uninstall

### iOS Safari:
1. Long press icon Presensia
2. Tap "Remove App"

### Desktop:
1. Buka browser
2. Settings > Apps
3. Cari "Presensia"
4. Remove/Uninstall

---

**PWA telah dihapus sepenuhnya!** 🎉
