# Prompt Restyle UI - Aplikasi Presensia (PWA Absensi Sekolah)

## KONTEKS PROYEK
Aplikasi PWA bernama "Presensia" untuk manajemen absensi sekolah (SMA Negeri 1 Jakarta,
skala hingga 1000 siswa). Saat ini tampilan mobile hanya hasil responsive shrink dari
desktop admin panel — banyak tabel HTML, input native browser, dan icon yang tidak
ter-render dengan baik di WebView mobile.

**Tujuan:** restyle tampilan agar terasa seperti aplikasi mobile native, TANPA mengubah
struktur data, API, routing, atau logic bisnis yang sudah ada. Ini murni perubahan
presentation layer.

## ATURAN KETAT (JANGAN DILANGGAR)
1. JANGAN ubah state management, API calls, atau data fetching logic yang sudah ada
2. JANGAN ubah struktur routing/navigasi antar halaman
3. JANGAN hapus fitur atau data yang sudah tampil, hanya ubah CARA menampilkannya
4. Pertahankan semua nama field, endpoint, dan komponen fungsional yang ada
5. Kerjakan bertahap per komponen, jangan rewrite seluruh app sekaligus — mulai dari
   komponen yang paling sering dipakai (Dashboard) dulu, minta konfirmasi, baru lanjut

## STACK & LIBRARY YANG DIIZINKAN
- Tetap gunakan framework frontend yang sudah ada di project ini (cek package.json dulu)
- Tambahkan salah satu (pilih yang paling kompatibel dengan stack yang ada):
  - Framework7 (jika project vanilla JS/tidak pakai framework berat)
  - Konsta UI (jika sudah pakai Tailwind CSS)
  - shadcn/ui + custom mobile styling (jika sudah pakai React)
- Icon set: Lucide Icons atau Heroicons (ganti SEMUA icon placeholder/kotak kosong)
- Untuk gesture (swipe): Hammer.js atau built-in touch handler framework yang dipakai

## DAFTAR PERUBAHAN SPESIFIK PER KOMPONEN

### 1. Dashboard Utama
- Ubah grid statistik 2x2 (Total Pegawai, Total Siswa, Absensi Hari Ini, Izin Pending)
  menjadi horizontal scrollable cards ATAU stack vertikal dengan tipografi lebih besar
- Ganti SEMUA icon kotak kosong dengan icon Lucide yang sesuai konteks
  (contoh: users untuk Total Pegawai, graduation-cap untuk Total Siswa)
- Ganti warna merah otomatis pada nilai 0% menjadi abu-abu netral (0% = belum ada data,
  bukan error/alert)
- Ganti input date range native (`<input type="date">`) dengan bottom-sheet date picker
  custom yang muncul dari bawah layar saat di-tap

### 2. Ringkasan Performa (Circular Gauge KPI)
- Perbesar circular progress chart, satu per baris (bukan 2 sejajar berdesakan)
- Ubah list sub-metric (Tepat Waktu, Keaktifan User, Kelengkapan Data, Konsistensi
  Absensi) dari format teks rata-kanan menjadi mini horizontal progress bar dengan
  label di atas

### 3. Tabel → Card List (PRIORITAS TINGGI)
Terapkan ke SEMUA tabel berikut, ubah dari `<table>` menjadi vertical card list:
- "Masuk tanpa Keluar (Leak)" — tabel Nama/Tanggal/Check In
- "Profil Tidak Lengkap" — tabel Nama/Tipe/Field Kosong
- "KPI Absensi Terlambat" — list Terlambat Terbaru

Pola card yang diinginkan:
```
[Avatar/Icon] Nama Orang                    [Badge angka merah]
              Subtext (tipe: Pegawai/Siswa)
```
- Badge angka (11, 9, dst) tetap merah/warna sesuai urgency, tapi dalam bentuk pill/chip
  di pojok kanan card, bukan kolom tabel
- Tambahkan swipe-to-action jika relevan (misal swipe card untuk export data individual)

### 4. Menu Grid
- Ganti icon kotak kosong dengan icon Lucide yang sesuai (Manajemen User → users-cog,
  Import Data → upload, Pengaturan → settings, Laporan → file-text, Manajemen Izin →
  calendar-check)
- Pertahankan warna background pastel per kategori (ini sudah bagus), tapi tambahkan
  sedikit shadow/elevation agar terasa seperti tombol native, bukan flat div

### 5. Navigasi
- Tambahkan BOTTOM TAB BAR fixed untuk 4-5 menu utama (Dashboard, Absensi, Laporan,
  Menu) — cek dulu apakah routing yang ada mendukung ini tanpa refactor besar
- Header atas disederhanakan: cukup avatar + nama + logout icon, hilangkan elemen yang
  terasa seperti top bar admin desktop

### 6. Action Buttons
- Tombol "Export" dan "Terapkan" yang solid besar → ubah jadi:
  - Floating Action Button (FAB) untuk aksi utama per halaman, ATAU
  - Icon button kecil kontekstual di pojok card/section terkait

### 7. Spacing & Tipografi
- Tambah padding antar section (minimal 16px, idealnya 20-24px)
- Hierarki font: judul section bold 18-20px, subtext abu-abu 13-14px
- Pastikan tap target minimal 44x44px untuk semua elemen interaktif

## MANIFEST PWA (cek dan perbaiki jika belum sesuai)
```json
{
  "display": "standalone",
  "theme_color": "#4F46E5",
  "background_color": "#FFFFFF",
  "icons": [...]
}
```
Pastikan safe-area-inset untuk notch/punch-hole ditangani dengan CSS
`env(safe-area-inset-*)`.

## URUTAN PENGERJAAN YANG DIMINTA
1. Scan struktur project dulu (framework, komponen existing, routing) — laporkan
   temuan sebelum mulai edit
2. Restyle Dashboard utama dulu sebagai contoh, tunjukkan hasilnya
3. Setelah dikonfirmasi, lanjut ke Ringkasan Performa
4. Setelah dikonfirmasi, lanjut ke konversi tabel → card list (bagian paling penting)
5. Terakhir: Menu grid + bottom nav + PWA manifest check

## OUTPUT YANG DIHARAPKAN
Di setiap tahap, tunjukkan:
- File apa saja yang diubah
- Screenshot/preview hasil jika tool mendukung
- Apakah ada breaking change terhadap logic yang sudah ada (harus dilaporkan, bukan
  diam-diam diubah)