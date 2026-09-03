# Prompt Restyle - Perbaikan Layout Desktop (Sistemik, Semua Halaman)

## KONTEKS PENTING
Masalah pemanfaatan ruang kosong (whitespace berlebihan) terjadi di **SEMUA menu/halaman**
aplikasi Presensia versi desktop (Dashboard, Laporan Absensi, Manajemen Izin, Pengaturan
Absensi, dan kemungkinan semua halaman lain yang belum di-screenshot). Ini indikasi kuat
bahwa masalahnya BUKAN per-halaman, melainkan di level:
- Layout wrapper/template utama (kemungkinan ada `max-width` container yang terlalu
  sempit diterapkan global)
- ATAU sistem grid yang tidak punya breakpoint khusus untuk layar desktop lebar
  (>1440px), sehingga semua halaman "mentok" di lebar yang didesain untuk tablet/laptop
  kecil

**Prioritas:** cari dan perbaiki akar masalah di layout container global DULU, baru
sesuaikan komponen per halaman jika masih diperlukan.

## ATURAN KETAT
1. JANGAN ubah struktur data, routing, atau logic bisnis apapun
2. JANGAN ubah sidebar kiri — lebar dan struktur menu sudah proporsional, pertahankan
3. JANGAN ubah versi mobile/PWA yang sudah direstyle sebelumnya — perubahan ini KHUSUS
   untuk breakpoint desktop (biasanya di atas 1024px atau 1280px, sesuaikan dengan
   breakpoint yang sudah ada di project)
4. Kerjakan bertahap: cari akar masalah dulu, laporkan temuan, baru eksekusi perbaikan

## LANGKAH 1: Diagnosis Akar Masalah (WAJIB DILAKUKAN LEBIH DULU)
Sebelum mengubah kode apapun, lakukan pengecekan berikut dan laporkan hasilnya:
1. Cari file layout utama/template wrapper (biasanya bernama `Layout.jsx`,
   `MainLayout.vue`, `app.html`, `_layout.tsx`, atau sejenisnya tergantung framework)
2. Cek apakah ada `max-width` fixed (misal `max-width: 768px` atau `1024px`) pada
   container konten utama (bukan pada sidebar) yang membatasi lebar area konten
   meskipun layar browser jauh lebih lebar
3. Cek apakah CSS framework yang dipakai (Tailwind/Bootstrap/custom) punya container
   class (misal `.container`, `max-w-3xl`, dsb) yang diterapkan secara default ke
   semua halaman tanpa override untuk breakpoint desktop besar
4. Cek apakah komponen tabel/card menggunakan lebar fixed dalam pixel (bukan
   persentase/fr/flex) yang menyebabkan sisa ruang tidak terisi

## LANGKAH 2: Perbaikan Container Utama
- Ubah `max-width` container konten dari nilai sempit (jika ditemukan) menjadi lebih
  lebar, atau ganti ke `max-width: 100%` dengan padding proporsional (misal
  `padding: 24px 32px`)
- Jika pakai CSS Grid/Flexbox, pastikan container utama punya `width: 100%` relatif
  terhadap sisa ruang setelah sidebar, bukan lebar tetap dalam pixel
- Tambahkan breakpoint khusus desktop besar (misal `@media (min-width: 1440px)`)
  untuk halaman-halaman yang butuh penyesuaian lebih (contoh: tabel dengan banyak
  kolom bisa dapat lebih banyak ruang per kolom)

## LANGKAH 3: Perbaikan Pola Komponen (terapkan konsisten di semua halaman)

### Tabel (contoh: Manajemen Izin, Laporan Absensi)
- Kolom-kolom harus proporsional terhadap kontennya, gunakan lebar relatif (`fr` unit
  di CSS Grid atau `flex-grow` proporsional), BUKAN lebar sama rata yang membuat kolom
  seperti "Status" (butuh sedikit ruang) sama lebar dengan kolom "Pengguna" (butuh
  banyak ruang)
- Saat data sedikit (misal cuma 1 baris seperti di Manajemen Izin), JANGAN biarkan
  whitespace vertikal kosong sampai ke bawah — batasi tinggi container tabel sesuai
  jumlah baris data aktual, atau tambahkan elemen pendukung di bawah/samping tabel
  (lihat Langkah 4)
- Tambahkan **sticky header** pada tabel jika nanti data banyak, agar scroll tetap
  nyaman di layar lebar

### Form (contoh: Pengaturan Absensi)
- Kelompokkan input field dalam grid multi-kolom yang proporsional dengan lebar layar
  (misal 3-4 kolom untuk desktop besar, bukan 2 kolom yang menyisakan ruang kosong)
- Section seperti "Pengaturan Lokasi" yang cuma pakai 2 kolom padahal ada 4 field
  (Nama Lokasi, Radius, Latitude, Longitude) — susun jadi grid 2x2 yang mengisi lebar
  penuh, bukan 2 kolom lalu sisa kosong di kanan

### Card/Dashboard (contoh: Dashboard KPI cards)
- Grid 4 kolom untuk KPI card sudah benar, pertahankan pola ini
- Untuk halaman dengan konten pendek yang menyisakan ruang kosong di bawah (viewport
  tidak terisi penuh), pertimbangkan menambah elemen pendukung yang relevan alih-alih
  membiarkan kosong, contoh:
  - Dashboard: tambahkan grafik tren mini (line chart 7/30 hari terakhir) di ruang
    kosong setelah Ringkasan Performa
  - Manajemen Izin: tambahkan summary card kecil di atas tabel (Total Pending, Total
    Disetujui, Total Ditolak) yang mengisi lebar penuh sebelum tabel data

### Empty State
- Saat data kosong ("Tidak ada data untuk periode ini", "Belum ada permohonan"),
  JANGAN biarkan empty state mengambang sendirian di area sempit dengan whitespace
  masif di sekitarnya
- Perbesar sedikit ukuran icon dan teks empty state agar proporsional dengan luas
  area yang tersedia, dan pastikan container tetap centered secara visual terhadap
  SELURUH lebar konten (bukan cuma sebagian)

## LANGKAH 4: Elemen Pendukung untuk Mengisi Ruang (opsional, terapkan jika relevan)
Untuk halaman yang secara natural kontennya sedikit (misal Manajemen Izin dengan 1
data), tambahkan elemen yang memberi value tambahan alih-alih sekadar dekorasi:
- Summary/stat cards ringkas di bagian atas sebelum tabel utama
- Quick filter chips (selain dropdown yang sudah ada) untuk akses cepat
- Recent activity log kecil di sidebar kanan (jika layout memungkinkan kolom kedua)

## URUTAN PENGERJAAN
1. Lakukan diagnosis Langkah 1, laporkan temuan akar masalah SEBELUM mengubah kode
2. Perbaiki container utama (Langkah 2) — ini akan berdampak ke SEMUA halaman sekaligus
3. Setelah container diperbaiki, screenshot ulang 2-3 halaman untuk verifikasi apakah
   masalah sudah teratasi secara global
4. Baru lanjutkan ke penyesuaian pola komponen spesifik (Langkah 3) di halaman yang
   masih terlihat kurang optimal
5. Terakhir, tambahkan elemen pendukung (Langkah 4) jika masih ada halaman dengan
   konten natural sedikit

## OUTPUT YANG DIHARAPKAN
- Laporan diagnosis: file/komponen mana yang jadi akar masalah
- Screenshot before-after minimal untuk Dashboard dan Manajemen Izin (dua kasus
  paling kontras) setelah perbaikan container
- Konfirmasi bahwa perubahan container TIDAK merusak tampilan mobile/PWA yang sudah
  direstyle sebelumnya (cek breakpoint tidak saling tabrakan)