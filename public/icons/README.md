# PWA Icons

Folder ini berisi icon-icon untuk PWA (Progressive Web App) Presensia.

## Icon yang Dibutuhkan:

### Main App Icons:
- `icon-16x16.png` - Favicon kecil
- `icon-32x32.png` - Favicon standar
- `icon-72x72.png` - Android home screen
- `icon-96x96.png` - Android home screen
- `icon-128x128.png` - Android home screen
- `icon-144x144.png` - Windows tile
- `icon-152x152.png` - iOS home screen
- `icon-192x192.png` - Android home screen
- `icon-384x384.png` - Android splash screen
- `icon-512x512.png` - Android splash screen

### Shortcut Icons:
- `checkin-96x96.png` - Absensi Masuk shortcut
- `checkout-96x96.png` - Absensi Keluar shortcut
- `reports-96x96.png` - Laporan shortcut

## Cara Membuat Icon:

1. **Buat icon utama** dengan ukuran 512x512px
2. **Resize** ke berbagai ukuran yang dibutuhkan
3. **Simpan** dengan nama sesuai daftar di atas
4. **Format**: PNG dengan transparansi
5. **Style**: Konsisten dengan brand Presensia

## Tools yang Bisa Digunakan:

- **Online**: https://realfavicongenerator.net/
- **Online**: https://www.favicon-generator.org/
- **Desktop**: GIMP, Photoshop, Figma
- **Command Line**: ImageMagick

## Contoh Command ImageMagick:

```bash
# Resize dari icon utama 512x512 ke berbagai ukuran
convert icon-512x512.png -resize 16x16 icon-16x16.png
convert icon-512x512.png -resize 32x32 icon-32x32.png
convert icon-512x512.png -resize 72x72 icon-72x72.png
convert icon-512x512.png -resize 96x96 icon-96x96.png
convert icon-512x512.png -resize 128x128 icon-128x128.png
convert icon-512x512.png -resize 144x144 icon-144x144.png
convert icon-512x512.png -resize 152x152 icon-152x152.png
convert icon-512x512.png -resize 192x192 icon-192x192.png
convert icon-512x512.png -resize 384x384 icon-384x384.png
```

## Catatan:

- Icon harus **persegi** (1:1 ratio)
- Gunakan **warna brand** Presensia (#3b82f6)
- **Konsisten** dengan logo aplikasi
- **Test** di berbagai device untuk memastikan kualitas
