@extends('layouts.app')

@section('title', 'Edit Izin - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Permohonan Izin</h1>
                    <p class="text-gray-600 mt-1">Ubah informasi permohonan izin</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('leave-requests.show', $leaveRequest) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('leave-requests.update', $leaveRequest) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin *</label>
                    <select name="type" id="type" required 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih jenis izin</option>
                        <option value="sick" {{ old('type', $leaveRequest->type) == 'sick' ? 'selected' : '' }}>Sakit</option>
                        <option value="leave" {{ old('type', $leaveRequest->type) == 'leave' ? 'selected' : '' }}>Cuti</option>
                        <option value="duty" {{ old('type', $leaveRequest->type) == 'duty' ? 'selected' : '' }}>Dinas Luar</option>
                    </select>
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Date Range -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai *</label>
                        <input type="date" name="start_date" id="start_date" required 
                               value="{{ old('start_date', $leaveRequest->start_date->format('Y-m-d')) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input type="date" name="end_date" id="end_date" required 
                               value="{{ old('end_date', $leaveRequest->end_date->format('Y-m-d')) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">Alasan *</label>
                    <textarea name="reason" id="reason" rows="4" required 
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Jelaskan alasan permohonan izin...">{{ old('reason', $leaveRequest->reason) }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Evidence -->
                @if($leaveRequest->evidence && count($leaveRequest->evidence) > 0)
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dokumen Pendukung Saat Ini</label>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($leaveRequest->evidence as $index => $file)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center">
                                            @if(str_contains($file, '.pdf'))
                                                <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                            @else
                                                <i class="fas fa-file-image text-blue-500 mr-2"></i>
                                            @endif
                                            <span class="text-sm font-medium text-gray-900">Dokumen {{ $index + 1 }}</span>
                                        </div>
                                        <a href="{{ Storage::url($file) }}" target="_blank" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ basename($file) }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Upload dokumen baru akan mengganti dokumen yang ada.</p>
                    </div>
                @endif

                <!-- New Evidence -->
                <div class="mb-6">
                    <label for="evidence" class="block text-sm font-medium text-gray-700 mb-2">Dokumen Pendukung Baru</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-sm text-gray-600 mb-2">Upload dokumen pendukung baru (maksimal 5 file)</p>
                            <p class="text-xs text-gray-500 mb-4">Format: JPG, PNG, PDF (maksimal 2MB per file)</p>
                            <input type="file" name="evidence[]" id="evidence" multiple 
                                   accept=".jpg,.jpeg,.png,.pdf"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                    </div>
                    @error('evidence')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    @error('evidence.*')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('leave-requests.show', $leaveRequest) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-set end date when start date changes
document.getElementById('start_date').addEventListener('change', function() {
    const startDate = new Date(this.value);
    const endDateInput = document.getElementById('end_date');
    
    if (startDate && !endDateInput.value) {
        endDateInput.value = this.value;
    }
    
    // Set minimum end date
    endDateInput.min = this.value;
});

// Validate end date is not before start date
document.getElementById('end_date').addEventListener('change', function() {
    const startDate = new Date(document.getElementById('start_date').value);
    const endDate = new Date(this.value);
    
    if (endDate < startDate) {
        alert('Tanggal selesai tidak boleh sebelum tanggal mulai');
        this.value = document.getElementById('start_date').value;
    }
});
</script>
@endsection


