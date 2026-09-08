@extends('layouts.app')

@section('title', 'QR Code Management - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">QR Code Management</h1>
                <p class="text-gray-600 mt-1">Kelola dan cetak QR siswa untuk kartu pelajar (format: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-xs text-blue-700 font-semibold">NIS|Nama</code>).</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('qr.excel', request()->all()) }}" 
                   class="inline-flex items-center bg-emerald-600 hover:bg-emerald-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm hover:shadow transition-all duration-150 text-sm"
                   title="Download daftar siswa, kelas, dan nama file QR dalam format Excel untuk vendor cetak">
                    <i class="fas fa-file-excel mr-2 text-emerald-200"></i>
                    Download Excel (.xlsx)
                </a>
                <a href="{{ route('qr.zip', request()->all()) }}" 
                   class="inline-flex items-center bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium px-4 py-2.5 rounded-xl shadow-sm hover:shadow-md transition-all duration-150 text-sm"
                   title="Download seluruh file PNG QR Code (dilengkapi file Excel di dalamnya)">
                    <i class="fas fa-file-archive mr-2 text-blue-200"></i>
                    Download Massal (.zip)
                </a>
            </div>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <x-summary-cards :cards="$summaryCards ?? []" />

    <!-- Filter -->
    <x-filter-bar 
        :action="route('qr.index')" 
        :reset-url="route('qr.index')" 
        :active-filters="$activeFilters ?? []"
        title="Filter QR Code Siswa"
        badge-class="badge badge-info badge-sm text-xs font-medium"
    >
        <div>
            <label for="q" class="block text-sm text-gray-500 font-normal">Pencarian</label>
            <input type="text" name="q" id="q" value="{{ request('q') }}" 
                   placeholder="NIS atau Nama Siswa..." 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>

        @if(isset($levels) && $levels->isNotEmpty())
        <div>
            <label for="level" class="block text-sm text-gray-500 font-normal">Tingkat</label>
            <select name="level" id="level" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Tingkat</option>
                @foreach($levels as $lvl)
                    <option value="{{ $lvl }}" {{ request('level') == $lvl ? 'selected' : '' }}>Kelas {{ $lvl }}</option>
                @endforeach
            </select>
        </div>
        @endif

        @if(isset($classes) && $classes->isNotEmpty())
        <div>
            <label for="class_id" class="block text-sm text-gray-500 font-normal">Kelas</label>
            <select name="class_id" id="class_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Kelas</option>
                @foreach($classes as $c)
                    <option value="{{ $c->id }}" {{ request('class_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label for="per_page" class="block text-sm text-gray-500 font-normal">Tampilkan</label>
            <select name="per_page" id="per_page" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 baris</option>
                <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 baris</option>
                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50 baris</option>
                <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100 baris</option>
                <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>Semua Data</option>
            </select>
        </div>
    </x-filter-bar>

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">NIS</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Siswa</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Kelas</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24">Preview</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($students as $s)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-sm text-gray-900 font-medium whitespace-nowrap">{{ $s->nis ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $s->name }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                            @if($s->studentClasses && $s->studentClasses->isNotEmpty())
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $s->studentClasses->pluck('name')->join(', ') }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400 italic">Belum ada</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                            <button onclick="showQRPreview('{{ $s->nis ?? 'NIS' }}', '{{ addslashes($s->name) }}', '{{ route('qr.view', $s) }}', '{{ route('qr.download', $s) }}')" 
                                    class="inline-flex items-center px-2.5 py-1 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-md text-xs font-medium transition-colors">
                                <i class="fas fa-eye mr-1"></i>View
                            </button>
                        </td>
                        <td class="px-4 py-3 text-sm text-right whitespace-nowrap">
                            <a class="inline-flex items-center px-2.5 py-1 bg-gray-50 hover:bg-gray-100 text-gray-700 rounded-md text-xs font-medium border border-gray-200 transition-colors" href="{{ route('qr.download', $s) }}">
                                <i class="fas fa-download mr-1 text-gray-500"></i>Download (.png)
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                            <i class="fas fa-qrcode text-3xl text-gray-300 mb-2 block"></i>
                            Tidak ada siswa yang sesuai dengan filter.
                        </td>
                    </tr>
                    @endforelse
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
                    <button onclick="closeQRModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 transition-colors">
                        Tutup
                    </button>
                    <button onclick="downloadQR()" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors">
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

function showQRPreview(nis, name, viewUrl, downloadUrl) {
    const qrData = nis + '|' + name;
    
    // Update modal content
    document.getElementById('modalNIS').textContent = nis;
    document.getElementById('modalName').textContent = name;
    document.getElementById('qrData').textContent = qrData;
    
    // Langsung ambil gambar PNG asli dari server agar 100% identik dengan file download
    document.getElementById('qrPreview').src = viewUrl;
    
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


