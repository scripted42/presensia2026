<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Absensi - Presensia</title>
    <style>
        html, body { height: 100%; margin: 0; background: #0b1220; font-family: system-ui, Arial; }
        header { text-align: center; padding: 20px 12px 6px; color: #fff; }
        header h1 { margin: 0; font-size: 20px; font-weight: 700; letter-spacing: .3px; }
        #timer { margin-top: 6px; font-size: 14px; opacity: .85; }
        .wrap { height: calc(100% - 76px); display: flex; align-items: center; justify-content: center; }
        canvas { background: #fff; padding: 12px; border-radius: 12px; }
    </style>
    <meta http-equiv="refresh" content="600"> <!-- refresh hard tiap 10 menit sebagai fallback -->
    <script>
        let counter = 10;
        const timerEl = () => document.getElementById('timer');
        const canvasEl = () => document.getElementById('qr');

        function render(code) {
            const size = Math.floor(Math.min(window.innerWidth, window.innerHeight) * 0.7);
            const canvas = canvasEl();
            const ctx = canvas.getContext('2d');
            canvas.width = size; canvas.height = size;
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0,0,size,size);
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => { ctx.drawImage(img, 0, 0, size, size); };
            // gunakan layanan QR PNG agar tidak butuh library di sisi klien
            const url = 'https://api.qrserver.com/v1/create-qr-code/?size='+size+'x'+size+'&data='+encodeURIComponent(code);
            img.src = url;
        }

        async function refreshQr() {
            const url = '{{ route('attendance.qr-code') }}';
            const res = await fetch(url, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            if (data && data.qr_code) {
                render(data.qr_code);
            }
        }

        async function tick() {
            if (counter === 10) {
                await refreshQr();
            }
            timerEl().innerText = 'Refresh dalam ' + counter + ' dtk';
            counter -= 1;
            if (counter < 0) {
                counter = 10; // reset pas bareng refresh berikutnya
            }
        }

        window.addEventListener('load', () => {
            counter = 10;
            tick();
            setInterval(tick, 1000);
        });
        window.addEventListener('resize', () => { counter = 1; }); // paksa redraw cepat saat resize
    </script>
    </head>
<body>
    <header>
        <h1>QR Code Absensi Pegawai</h1>
        <div id="timer">Refresh dalam 0 dtk</div>
    </header>
    <div class="wrap">
        <canvas id="qr"></canvas>
    </div>
</body>
</html>


