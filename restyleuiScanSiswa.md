# Prompt Restyle UI - Halaman Scan QR Code Absensi (Lanjutan)

## KONTEKS
Ini lanjutan dari restyle sebelumnya (Dashboard, Ringkasan Performa, tabel → card list).
Bottom tab bar (Home, Absensi, Izin, Menu) sudah berhasil diimplementasikan — pertahankan
pola ini. Fokus sekarang: halaman "Absensi" yang berisi fitur Scan QR Code Siswa.

**Kondisi saat ini:** area kamera berupa placeholder abu-abu kosong dengan icon kamera
kecil, ada duplikasi tombol sync (satu kecil di atas, satu full-width di bawah), input
manual masih berbentuk form desktop standar.

## ATURAN KETAT (JANGAN DILANGGAR)
1. JANGAN ubah logic scanning QR yang sudah ada (library scanner, format data
   NIS|Nama, proses sync ke server)
2. JANGAN ubah struktur data siswa yang sudah discan/tersimpan
3. JANGAN hapus fitur input manual — hanya ubah cara presentasinya
4. Pertahankan bottom tab bar yang sudah ada, jangan diubah
5. Kerjakan bertahap: mulai dari area kamera dulu, konfirmasi, baru lanjut ke sync
   button dan input manual

## DAFTAR PERUBAHAN SPESIFIK

### 1. Area Kamera (PRIORITAS UTAMA)
- Ganti placeholder abu-abu kosong dengan **live camera preview yang aktif langsung**
  saat halaman dibuka (minta izin kamera saat pertama kali, bukan menunggu tombol
  "Mulai Scan" ditekan)
- Jika alasan kamera tidak auto-aktif adalah performa/baterai, minimal ubah placeholder
  jadi lebih menarik: ikon kamera lebih besar di tengah dengan animasi subtle (pulse),
  bukan statis
- Tambahkan **overlay scan frame**: kotak dengan 4 sudut bergaya scanner (garis di
  pojok kiri-atas, kanan-atas, kiri-bawah, kanan-bawah), bukan kotak solid
- Tambahkan **animasi garis scan** yang bergerak naik-turun di dalam frame saat kamera
  aktif, memberi kesan aplikasi sedang aktif mencari QR code
- Tombol "Mulai Scan" tetap ada tapi ubah posisi jadi floating di bawah area kamera,
  full-width, dengan icon kamera di sampingnya

### 2. Kontrol Kamera Tambahan
- Tambahkan row kecil di atas area kamera (atau overlay di pojok kamera) berisi:
  - Toggle flash on/off (icon flashlight)
  - Switch kamera depan/belakang (icon refresh/camera-flip)
- Gunakan icon Lucide: `flashlight`, `flashlight-off`, `switch-camera`

### 3. Hilangkan Duplikasi Tombol Sync
- Pertahankan HANYA SATU tombol sync — rekomendasi: tombol kecil di header dekat
  "0 Record" (yang sudah ada), ubah label jadi icon-only (icon `refresh-cw`) dengan
  badge notifikasi jumlah data yang belum sync jika ada
- HAPUS tombol "Synchronize" full-width di bagian bawah
- Jika tim ingin tetap ada CTA sync yang jelas terlihat, ganti dengan floating action
  button (FAB) kecil di pojok kanan bawah, bukan full-width button yang makan tempat

### 4. Input Manual → Bottom Sheet
- Ganti section "Input Manual" yang menempel di halaman utama menjadi:
  - Tombol kecil/chip "Input Manual" dengan icon `keyboard` di dekat header
  - Saat ditap, muncul **bottom sheet modal** dari bawah layar berisi:
    - Text input untuk QR Code (format NIS|Nama tetap dipertahankan)
    - Helper text format contoh tetap ditampilkan
    - Tombol "Tambahkan" full-width di dalam bottom sheet
    - Bottom sheet bisa ditutup dengan swipe down atau tap area luar
- Ini mengurangi kepadatan halaman utama karena fitur sekunder (manual input) tidak
  selalu terlihat, hanya saat dibutuhkan

### 5. List "Siswa yang Sudah Diabsensi"
- Pertahankan empty state yang sudah ada (icon orang + teks "Belum ada siswa yang
  diabsensi") — ini sudah baik
- Saat data mulai terisi, tampilkan sebagai card list (bukan tabel):
  ```
  [Avatar/Inisial] Nama Siswa              [Waktu: 07:15]
                    NIS: SISWA001
  ```
- Tambahkan swipe-to-delete pada card (swipe kiri = hapus, untuk kasus salah scan)
  dengan konfirmasi sebelum data terhapus permanen
- Tambahkan subtle animation (fade-in/slide-in) saat card baru ditambahkan ke list agar
  terasa responsif

### 6. Elemen Merah/Alert yang Terpotong
- Cek konten alert/warning berwarna merah-muda yang terpotong di bagian paling bawah
  halaman (terlihat di screenshot terakhir) — pastikan konten ini tetap ditampilkan
  dengan jelas, ubah ke gaya notification banner mobile (icon warning + teks singkat +
  tombol dismiss) jika ini pesan error/peringatan penting

### 7. Header "Daftar Absensi Siswa"
- Sederhanakan row header: judul "Daftar Absensi Siswa" + badge jumlah record (sudah
  bagus, pertahankan style pill birunya) + icon sync (setelah tombol sync bawah dihapus)
- Hilangkan teks instruksi "Swipe ke atas untuk melihat daftar" jika daftar sudah bisa
  di-scroll natural — teks instruksi seperti ini biasanya tanda UX belum cukup intuitif
  tanpa penjelasan tambahan

## URUTAN PENGERJAAN
1. Restyle area kamera + overlay scan frame + kontrol flash/switch camera dulu,
   tunjukkan hasilnya
2. Setelah dikonfirmasi, hilangkan duplikasi tombol sync
3. Setelah dikonfirmasi, ubah input manual jadi bottom sheet
4. Terakhir: card list untuk siswa yang sudah diabsensi + swipe-to-delete + cek elemen
   alert yang terpotong

## OUTPUT YANG DIHARAPKAN
Di setiap tahap, laporkan:
- File/komponen apa yang diubah
- Apakah ada dependency baru yang perlu diinstall (misal untuk bottom sheet component
  atau animasi)
- Apakah ada perubahan pada permission handling kamera yang perlu izin ulang dari user