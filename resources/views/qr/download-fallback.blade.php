@extends('layouts.app')

@section('title', 'Download QR Codes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Download QR Codes</h1>
            <p class="text-gray-600">ZipArchive tidak tersedia. Silakan download QR code secara individual:</p>
        </div>

        <!-- Info Box -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle text-yellow-600 mr-3"></i>
                <div>
                    <h3 class="text-sm font-medium text-yellow-800">ZipArchive Extension Tidak Tersedia</h3>
                    <p class="text-sm text-yellow-700 mt-1">
                        Untuk mengaktifkan download ZIP, silakan aktifkan PHP ZipArchive extension di server.
                    </p>
                </div>
            </div>
        </div>

        <!-- Download Links -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">QR Code Downloads</h2>
                <p class="text-sm text-gray-600 mt-1">Klik link di bawah untuk download QR code individual</p>
            </div>
            
            <div class="divide-y divide-gray-200">
                @foreach($downloadLinks as $link)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex-1">
                        <h3 class="text-sm font-medium text-gray-900">{{ $link['name'] }}</h3>
                        <p class="text-sm text-gray-500">NIS: {{ $link['nis'] }}</p>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ $link['download_url'] }}" 
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download QR
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-blue-800 mb-2">Cara Mengaktifkan ZipArchive:</h3>
            <div class="text-sm text-blue-700 space-y-1">
                <p><strong>XAMPP:</strong> Uncomment <code>extension=zip</code> di php.ini</p>
                <p><strong>Laragon:</strong> Aktifkan PHP Zip extension di Laragon control panel</p>
                <p><strong>Server:</strong> Install php-zip package</p>
            </div>
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a href="{{ route('qr.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke QR Management
            </a>
        </div>
    </div>
</div>
@endsection
