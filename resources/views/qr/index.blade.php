@extends('layouts.app')

@section('title', 'QR Code Management - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">QR Code Management</h1>
                <p class="text-gray-600 mt-1">Kelola dan cetak QR siswa (format: NIS|Nama). Unduhan massal tersedia.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('qr.zip') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Download Massal (.zip)</a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 overflow-x-auto">
            <form method="GET" action="{{ route('qr.index') }}" class="mb-4 flex items-center gap-3">
                <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Cari NIS atau Nama..." class="border-gray-300 rounded-md">
                <select name="per_page" class="border-gray-300 rounded-md">
                    <option value="10" {{ ($perPageParam ?? '10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPageParam ?? '')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPageParam ?? '')=='50' ? 'selected' : '' }}>50</option>
                    <option value="all" {{ ($perPageParam ?? '')=='all' ? 'selected' : '' }}>All</option>
                </select>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
            </form>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">NIS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">View QR</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-48">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-sm text-gray-900 font-medium">{{ $s->nis ?? '-' }}</td>
                        <td class="px-3 py-2 text-sm text-gray-900">{{ $s->name }}</td>
                        <td class="px-3 py-2 text-sm">
                            <button onclick="showQRPreview('{{ $s->nis ?? 'NIS' }}', '{{ $s->name }}', '{{ route('qr.download', $s) }}')" 
                                    class="bg-blue-100 text-blue-700 px-3 py-1 rounded-md text-xs font-medium hover:bg-blue-200 transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </td>
                        <td class="px-3 py-2 text-sm">
                            <div class="flex flex-col space-y-1">
                                <a class="text-blue-600 hover:text-blue-800 text-xs font-medium" href="{{ route('qr.download', $s) }}">
                                    <i class="fas fa-download mr-1"></i>Download QR (.png)
                                </a>
                                <a class="text-green-600 hover:text-green-800 text-xs font-medium" href="{{ route('qr.card', $s) }}">
                                    <i class="fas fa-id-card mr-1"></i>Kartu Pelajar (.png)
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>

    <!-- QR Preview Modal -->
    <div id="qrModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <!-- Modal Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Preview QR Code</h3>
                    <button onclick="closeQRModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Modal Body -->
                <div class="text-center">
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">NIS: <span id="modalNIS" class="font-medium"></span></p>
                        <p class="text-sm text-gray-600 mb-4">Nama: <span id="modalName" class="font-medium"></span></p>
                    </div>
                    
                    <!-- QR Code Preview -->
                    <div class="bg-gray-50 p-4 rounded-lg mb-4">
                        <img id="qrPreview" src="" alt="QR Code Preview" class="mx-auto" style="max-width: 200px; max-height: 200px;">
                    </div>
                    
                    <!-- QR Code Data -->
                    <div class="bg-blue-50 p-3 rounded-lg mb-4">
                        <p class="text-xs text-blue-600 font-medium">Data QR Code:</p>
                        <p class="text-sm text-blue-800 font-mono" id="qrData"></p>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex justify-end space-x-2 mt-4">
                    <button onclick="closeQRModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">
                        Tutup
                    </button>
                    <button onclick="downloadQR()" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
                        <i class="fas fa-download mr-1"></i>Download
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<!-- Zxing-js Library for QR Generation -->
<script src="https://unpkg.com/@zxing/library@latest/umd/index.min.js"></script>
<script>
let currentQRData = '';
let currentDownloadUrl = '';

function showQRPreview(nis, name, downloadUrl) {
    const qrData = nis + '|' + name;
    
    // Update modal content
    document.getElementById('modalNIS').textContent = nis;
    document.getElementById('modalName').textContent = name;
    document.getElementById('qrData').textContent = qrData;
    
    // Generate QR code using Zxing-js
    try {
        const qrCode = new ZXing.QRCodeWriter();
        const bitMatrix = qrCode.encode(qrData, ZXing.BarcodeFormat.QR_CODE, 200, 200);
        
        // Create canvas and draw QR code
        const canvas = document.createElement('canvas');
        canvas.width = 200;
        canvas.height = 200;
        const ctx = canvas.getContext('2d');
        
        // Fill white background
        ctx.fillStyle = '#ffffff';
        ctx.fillRect(0, 0, 200, 200);
        
        // Draw QR code
        ctx.fillStyle = '#000000';
        for (let x = 0; x < 200; x++) {
            for (let y = 0; y < 200; y++) {
                if (bitMatrix.get(x, y)) {
                    ctx.fillRect(x, y, 1, 1);
                }
            }
        }
        
        // Convert canvas to data URL
        const qrUrl = canvas.toDataURL('image/png');
        document.getElementById('qrPreview').src = qrUrl;
        
    } catch (error) {
        console.error('QR generation failed:', error);
        // Fallback to external service
        const qrUrl = `https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(qrData)}`;
        document.getElementById('qrPreview').src = qrUrl;
    }
    
    // Store for download
    currentQRData = qrData;
    currentDownloadUrl = downloadUrl;
    
    // Show modal
    document.getElementById('qrModal').classList.remove('hidden');
}

function closeQRModal() {
    document.getElementById('qrModal').classList.add('hidden');
}

function downloadQR() {
    if (currentDownloadUrl) {
        window.open(currentDownloadUrl, '_blank');
    }
}

// Close modal when clicking outside
document.getElementById('qrModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeQRModal();
    }
});
</script>
@endpush


