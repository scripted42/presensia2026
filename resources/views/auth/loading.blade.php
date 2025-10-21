<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loading - Presensia</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loading-container {
            text-align: center;
            color: #374151;
        }


        .loading-title {
            font-size: 20px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #111827;
        }

        .loading-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 32px;
        }

        .loading-spinner {
            width: 32px;
            height: 32px;
            margin: 0 auto 24px;
            border: 2px solid #e5e7eb;
            border-top: 2px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 13px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="loading-container">
        <!-- Loading Title -->
        <h1 class="loading-title">Loading</h1>
        <p class="loading-subtitle">Menyiapkan dashboard Anda...</p>

        <!-- Loading Spinner -->
        <div class="loading-spinner"></div>

        <!-- Loading Text -->
        <p class="loading-text">Mohon tunggu sebentar</p>
    </div>

    <script>
        // Redirect to dashboard after loading
        setTimeout(function() {
            window.location.href = '{{ route("dashboard") }}';
        }, 3000); // 3 seconds total loading time
    </script>
</body>
</html>
