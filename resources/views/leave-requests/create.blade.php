@extends('layouts.app')

@section('title', 'Ajukan Izin - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Ajukan Izin</h1>
                    <p class="text-gray-600 mt-1">Formulir permohonan izin, cuti, sakit, atau dinas luar</p>
                </div>
                <a href="{{ route('leave-requests.index') }}" 
                   class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('leave-requests.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <!-- Target User (hidden for self, visible for others) -->
                @if($targetUser->id !== Auth::id())
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Pengguna</label>
                        <div class="bg-gray-50 p-4 rounded-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $targetUser->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $targetUser->email }} - {{ ucfirst($targetUser->user_type) }}</div>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="user_id" value="{{ $targetUser->id }}">
                    </div>
                @else
                    <input type="hidden" name="user_id" value="{{ Auth::id() }}">
                @endif

                <!-- Type -->
                <div class="mb-6">
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Jenis Izin *</label>
                    <select name="type" id="type" required 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih jenis izin</option>
                        <option value="sick" {{ old('type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                        <option value="leave" {{ old('type') == 'leave' ? 'selected' : '' }}>Cuti</option>
                        <option value="duty" {{ old('type') == 'duty' ? 'selected' : '' }}>Dinas Luar</option>
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
                               value="{{ old('start_date', date('Y-m-d')) }}"
                               class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Selesai *</label>
                        <input type="date" name="end_date" id="end_date" required 
                               value="{{ old('end_date') }}"
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
                              placeholder="Jelaskan alasan permohonan izin...">{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Evidence -->
                <div class="mb-6">
                    <label for="evidence" class="block text-sm font-medium text-gray-700 mb-2">Dokumen Pendukung</label>
                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                            <p class="text-sm text-gray-600 mb-2">Upload dokumen pendukung (maksimal 5 file)</p>
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
                    <a href="{{ route('leave-requests.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-paper-plane mr-2"></i>
                        Ajukan Izin
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

