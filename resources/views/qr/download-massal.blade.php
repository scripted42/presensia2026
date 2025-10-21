@extends('layouts.app')

@section('title', 'Download Massal QR Codes')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">Download Massal QR Codes</h1>
                    <p class="text-gray-600">Download semua QR code siswa dalam satu file ZIP atau secara individual</p>
                </div>
                <a href="{{ route('qr.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke QR Management
                </a>
            </div>
        </div>

        <!-- Download Options -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- ZIP Download -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-file-archive text-3xl text-purple-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Download ZIP</h3>
                            <p class="text-sm text-gray-600">Semua QR code dalam satu file ZIP</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Semua QR code dalam satu file</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Download cepat dan mudah</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Format: PNG 600x600px</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="{{ route('qr.zip') }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download ZIP ({{ count($downloadLinks) }} files)
                        </a>
                    </div>
                </div>
            </div>

            <!-- Individual Downloads -->
            <div class="bg-white rounded-lg shadow-md border border-gray-200">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="flex-shrink-0">
                            <i class="fas fa-list text-3xl text-blue-600"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-gray-900">Download Individual</h3>
                            <p class="text-sm text-gray-600">Download QR code satu per satu</p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Pilih QR code yang diinginkan</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Download sesuai kebutuhan</span>
                        </div>
                        <div class="flex items-center text-sm text-gray-600">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Kontrol penuh atas file</span>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button onclick="scrollToIndividual()" 
                                class="w-full inline-flex items-center justify-center px-4 py-3 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-list mr-2"></i>
                            Lihat Daftar Individual
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Individual Downloads List -->
        <div id="individual-downloads" class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Download Individual QR Codes</h2>
                <p class="text-sm text-gray-600 mt-1">Klik tombol download untuk mendapatkan QR code individual</p>
            </div>
            
            <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                @foreach($downloadLinks as $index => $link)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50">
                    <div class="flex-1">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-medium text-blue-600">{{ $index + 1 }}</span>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-gray-900">{{ $link['name'] }}</h3>
                                <p class="text-sm text-gray-500">NIS: {{ $link['nis'] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ $link['download_url'] }}" 
                           class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Download
                        </a>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Informasi Download</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>QR code menggunakan format data: <code>NIS|Nama</code></li>
                            <li>Ukuran file: 600x600 pixels (PNG)</li>
                            <li>Kompatibel dengan semua scanner QR code</li>
                            <li>File ZIP berisi semua QR code dalam satu folder</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function scrollToIndividual() {
    document.getElementById('individual-downloads').scrollIntoView({ 
        behavior: 'smooth' 
    });
}
</script>
@endsection
