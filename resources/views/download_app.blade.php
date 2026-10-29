<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Unduh Aplikasi Presensia Mobile - SMPN 14 Surabaya</title>
    <meta name="description" content="Unduh aplikasi resmi Presensia Mobile SMP Negeri 14 Surabaya untuk presensi siswa, guru, dan pegawai.">
    <link rel="icon" href="{{ asset('icons/favicon-96x96.png') }}" type="image/png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #023C69;
            --primary-light: #2563EB;
            --primary-dark: #012542;
            --accent-gold: #D97706;
            --bg-page: #F8FAFC;
            --card-bg: #FFFFFF;
            --text-main: #0F172A;
            --text-sub: #475569;
            --text-muted: #94A3B8;
            --border: #E2E8F0;
            --success: #10B981;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.6;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .header {
            background: linear-gradient(135deg, #023C69 0%, #1E3A8A 100%);
            color: white;
            padding: 2.5rem 1.25rem 3.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -30px;
            left: 0;
            right: 0;
            height: 60px;
            background: var(--bg-page);
            border-radius: 50% 50% 0 0;
        }

        .badge-school {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 1rem;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .app-icon-wrapper {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.25rem;
            background: white;
            border-radius: 24px;
            box-shadow: 0 16px 32px rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid rgba(255, 255, 255, 0.8);
        }

        .app-icon-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .header h1 {
            font-size: 1.85rem;
            font-weight: 800;
            letter-spacing: -0.5px;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #E2E8F0;
            font-size: 0.95rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .main-container {
            max-width: 540px;
            width: 100%;
            margin: -2rem auto 2rem;
            padding: 0 1rem;
            position: relative;
            z-index: 10;
        }

        .download-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.75rem 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(2, 60, 105, 0.08), 0 8px 10px -6px rgba(2, 60, 105, 0.04);
            border: 1px solid var(--border);
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .meta-pills {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-bottom: 1.25rem;
            flex-wrap: wrap;
        }

        .pill {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
        }

        .pill-version {
            background: #EFF6FF;
            color: #1D4ED8;
            border: 1px solid #BFDBFE;
        }

        .pill-android {
            background: #ECFDF5;
            color: #047857;
            border: 1px solid #A7F3D0;
        }

        .pill-size {
            background: #F8FAFC;
            color: #64748B;
            border: 1px solid #E2E8F0;
        }

        .btn-download {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
            color: white;
            text-decoration: none;
            padding: 1rem 1.5rem;
            border-radius: 14px;
            font-size: 1.05rem;
            font-weight: 700;
            box-shadow: 0 10px 20px -3px rgba(37, 99, 235, 0.35);
            transition: all 0.2s ease;
            margin-bottom: 0.75rem;
        }

        .btn-download:hover {
            transform: translateY(-2px);
            box-shadow: 0 14px 24px -3px rgba(37, 99, 235, 0.45);
        }

        .btn-download:active {
            transform: translateY(1px);
        }

        .download-notice {
            font-size: 0.78rem;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 1.5rem;
        }

        .feature-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 1rem;
            text-align: left;
        }

        .feature-icon {
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .feature-card h3 {
            font-size: 0.88rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-main);
        }

        .feature-card p {
            font-size: 0.75rem;
            color: var(--text-sub);
            line-height: 1.4;
        }

        .guide-box {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .step-item {
            display: flex;
            gap: 14px;
            margin-bottom: 1.25rem;
            position: relative;
        }

        .step-item:last-child {
            margin-bottom: 0;
        }

        .step-item:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 30px;
            left: 14px;
            bottom: -15px;
            width: 2px;
            background: #E2E8F0;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #EFF6FF;
            color: #2563EB;
            font-weight: 800;
            font-size: 0.85rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border: 2px solid #BFDBFE;
            position: relative;
            z-index: 2;
        }

        .step-content h4 {
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
            color: var(--text-main);
        }

        .step-content p {
            font-size: 0.82rem;
            color: var(--text-sub);
            line-height: 1.45;
        }

        .alert-safe {
            background: #FEF3C7;
            border: 1px solid #FDE68A;
            border-radius: 12px;
            padding: 0.85rem 1rem;
            font-size: 0.8rem;
            color: #92400E;
            margin-top: 0.5rem;
            line-height: 1.4;
        }

        .footer {
            margin-top: auto;
            text-align: center;
            padding: 2rem 1rem;
            font-size: 0.8rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            background: white;
        }

        .footer strong {
            color: var(--text-sub);
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="badge-school">
            🏫 SMP NEGERI 14 SURABAYA
        </div>
        <div class="app-icon-wrapper">
            <img src="{{ asset('icons/icon-512x512.png') }}" alt="Logo Presensia SMPN 14">
        </div>
        <h1>Presensia Mobile</h1>
        <p>Aplikasi Resmi Presensi Mandiri Siswa, Guru & Pegawai SMP Negeri 14 Surabaya</p>
    </header>

    <main class="main-container">

        <!-- KARTU UTAMA UNDUH APK -->
        <div class="download-card">
            <div class="meta-pills">
                <span class="pill pill-version">Rilis Resmi v1.0.0</span>
                <span class="pill pill-android">Android 8.0+</span>
                <span class="pill pill-size">Ukuran: ~{{ $apkSize }}</span>
            </div>

            <a href="{{ $downloadUrl }}" class="btn-download" id="downloadBtn">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
                <span>Unduh File APK Langsung</span>
            </a>

            <div class="download-notice">
                <span>🛡️ Terverifikasi Aman • Bebas Iklan • Server Lokal Sekolah</span>
            </div>
        </div>

        <!-- FITUR UTAMA -->
        <div class="section-title">
            <span>✨ Fitur Unggulan Aplikasi</span>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📸</div>
                <h3>Presensi Cepat</h3>
                <p>Verifikasi wajah selfie dan scan QR Code presensi gerbang.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Peringkat Disiplin</h3>
                <p>Klasemen kedisiplinan kelas dan ksatria disiplin peraih XP.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📍</div>
                <h3>Validasi Geofence</h3>
                <p>Otomatis memastikan siswa & guru berada di lingkungan sekolah.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Riwayat Kehadiran</h3>
                <p>Pantau rekapan absensi harian dan status tepat waktu secara transparan.</p>
            </div>
        </div>

        <!-- PANDUAN CARA INSTAL -->
        <div class="guide-box">
            <div class="section-title" style="margin-bottom: 1.25rem;">
                <span>📲 Panduan Cara Pasang (Instalasi)</span>
            </div>

            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-content">
                    <h4>Unduh File APK</h4>
                    <p>Klik tombol <strong>Unduh File APK Langsung</strong> di atas.</p>
                    <div class="alert-safe">
                        💡 <strong>Tips Browser Android:</strong> Jika muncul pemberitahuan <em>"File might be harmful"</em> atau <em>"File mungkin berbahaya"</em>, pilih <strong>Tetap Download / Download Anyway</strong>. Ini adalah peringatan standar Android untuk file yang diunduh langsung di luar Play Store.
                    </div>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-content">
                    <h4>Buka & Izinkan Pemasangan</h4>
                    <p>Setelah selesai, buka file APK dari bilah notifikasi atau folder <em>Download</em> HP Anda. Jika muncul permintaan izin, aktifkan <strong>"Izinkan dari sumber ini"</strong>.</p>
                </div>
            </div>

            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-content">
                    <h4>Login & Mulai Gunakan</h4>
                    <p>Buka aplikasi <strong>Presensia</strong>, berikan izin Kamera & Lokasi, lalu login menggunakan <strong>NIS</strong> (Siswa) atau <strong>NIP/Email</strong> (Guru & Pegawai).</p>
                </div>
            </div>
        </div>

    </main>

    <footer class="footer">
        <p><strong>SMP Negeri 14 Surabaya</strong></p>
        <p>Jl. Benowo No. 2, Surabaya, Jawa Timur</p>
        <p style="margin-top: 6px;">Sistem Informasi Presensi Digital Mandiri © 2026</p>
    </footer>

</body>
</html>
