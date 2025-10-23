@extends('layouts.app')

@section('title', 'Import Hari Libur Nasional - Presensia')

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Import Hari Libur Nasional</h1>
                    <p class="text-gray-600 mt-1">Import hari libur nasional secara otomatis dari data pemerintah</p>
                </div>
                <a href="{{ route('admin.holidays.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Import Form -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <form method="POST" action="{{ route('admin.holidays.import.store') }}" id="importForm">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Year Selection -->
                    <div>
                        <label for="year" class="block text-sm font-medium text-gray-700 mb-2">
                            Pilih Tahun <span class="text-red-500">*</span>
                        </label>
                        <select name="year" id="year" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach($years as $yearOption)
                            <option value="{{ $yearOption }}" {{ $yearOption == now()->year ? 'selected' : '' }}>
                                {{ $yearOption }}
                            </option>
                            @endforeach
                        </select>
                        @error('year')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    
                    <!-- Preview Button -->
                    <div class="flex items-end">
                        <button type="button" id="previewBtn" class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-eye mr-2"></i>Preview Hari Libur
                        </button>
                    </div>
                </div>
                
                <!-- Preview Results -->
                <div id="previewResults" class="mt-6 hidden">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Preview Hari Libur Nasional</h3>
                    <div id="previewContent" class="bg-gray-50 rounded-lg p-4">
                        <!-- Preview content will be loaded here -->
                    </div>
                </div>
                
                <!-- Confirmation -->
                <div class="mt-6">
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="confirm" id="confirm" class="focus:ring-blue-500 h-4 w-4 text-blue-600 border-gray-300 rounded" required>
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="confirm" class="font-medium text-gray-700">
                                Saya yakin ingin mengimport hari libur nasional untuk tahun yang dipilih
                            </label>
                            <p class="text-gray-500">Hari libur yang sudah ada akan dilewati dan tidak akan diubah.</p>
                        </div>
                    </div>
                    @error('confirm')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Submit Button -->
                <div class="mt-6 flex justify-end space-x-3">
                    <a href="{{ route('admin.holidays.index') }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                        Batal
                    </a>
                    <button type="submit" id="importBtn" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-download mr-2"></i>Import Hari Libur
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Information -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi Import</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Data hari libur nasional diambil dari API pemerintah Indonesia</li>
                        <li>Hari libur yang sudah ada di database akan dilewati</li>
                        <li>Import hanya menambahkan hari libur nasional, tidak mengubah hari libur sekolah</li>
                        <li>Proses import dapat memakan waktu beberapa detik</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const yearSelect = document.getElementById('year');
    const previewBtn = document.getElementById('previewBtn');
    const previewResults = document.getElementById('previewResults');
    const previewContent = document.getElementById('previewContent');
    const importBtn = document.getElementById('importBtn');
    const confirmCheckbox = document.getElementById('confirm');
    
    // Preview button click
    previewBtn.addEventListener('click', function() {
        const year = yearSelect.value;
        if (!year) {
            alert('Silakan pilih tahun terlebih dahulu');
            return;
        }
        
        previewBtn.disabled = true;
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Loading...';
        
        fetch(`{{ route('admin.holidays.preview') }}?year=${year}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let html = `<div class="mb-4">
                        <p class="text-sm text-gray-600">Ditemukan <strong>${data.count}</strong> hari libur nasional untuk tahun ${year}</p>
                    </div>`;
                    
                    if (data.holidays.length > 0) {
                        html += '<div class="overflow-x-auto"><table class="min-w-full divide-y divide-gray-200">';
                        html += '<thead class="bg-gray-50"><tr>';
                        html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>';
                        html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hari</th>';
                        html += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Nama Hari Libur</th>';
                        html += '</tr></thead><tbody class="bg-white divide-y divide-gray-200">';
                        
                        data.holidays.forEach(holiday => {
                            html += '<tr>';
                            html += `<td class="px-4 py-2 text-sm text-gray-900">${holiday.date}</td>`;
                            html += `<td class="px-4 py-2 text-sm text-gray-500">${holiday.day_name}</td>`;
                            html += `<td class="px-4 py-2 text-sm text-gray-900">${holiday.holiday_name}</td>`;
                            html += '</tr>';
                        });
                        
                        html += '</tbody></table></div>';
                    }
                    
                    previewContent.innerHTML = html;
                    previewResults.classList.remove('hidden');
                } else {
                    alert('Gagal mengambil data hari libur: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat mengambil data hari libur');
            })
            .finally(() => {
                previewBtn.disabled = false;
                previewBtn.innerHTML = '<i class="fas fa-eye mr-2"></i>Preview Hari Libur';
            });
    });
    
    // Enable/disable import button based on confirmation
    confirmCheckbox.addEventListener('change', function() {
        importBtn.disabled = !this.checked;
    });
    
    // Form submission
    document.getElementById('importForm').addEventListener('submit', function(e) {
        if (!confirmCheckbox.checked) {
            e.preventDefault();
            alert('Silakan centang konfirmasi terlebih dahulu');
            return;
        }
        
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Importing...';
    });
});
</script>
@endpush
