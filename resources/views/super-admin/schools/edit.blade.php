@extends('layouts.app')

@section('title', 'Edit Sekolah - Super Admin')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Sekolah: {{ $school->name }}</h1>
                    <p class="text-gray-600 mt-1">Ubah informasi sekolah dan pengaturan tenant</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('super-admin.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <a href="{{ route('super-admin.schools.show', $school) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-eye mr-2"></i>
                        Lihat Detail
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            <form action="{{ route('super-admin.schools.update', $school) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- School Information -->
                <div class="mb-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sekolah</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Sekolah *</label>
                            <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $school->name) }}" required 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nama sekolah">
                            @error('school_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="school_phone" class="block text-sm font-medium text-gray-700 mb-2">Telepon</label>
                            <input type="text" name="school_phone" id="school_phone" value="{{ old('school_phone', $school->phone) }}" 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="031-1234567">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="school_address" class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea name="school_address" id="school_address" rows="3"
                                      class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Alamat lengkap sekolah">{{ old('school_address', $school->address) }}</textarea>
                        </div>
                        
                        <div>
                            <label for="school_email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" name="school_email" id="school_email" value="{{ old('school_email', $school->email) }}" 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="info@sekolah.sch.id">
                        </div>
                        
                        <div>
                            <label for="school_website" class="block text-sm font-medium text-gray-700 mb-2">Website</label>
                            <input type="url" name="school_website" id="school_website" value="{{ old('school_website', $school->website) }}" 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="https://sekolah.sch.id">
                        </div>
                        
                        <div class="md:col-span-2">
                            <label for="school_logo" class="block text-sm font-medium text-gray-700 mb-2">Logo Sekolah</label>
                            
                            <!-- Current Logo Display -->
                            @if($school->logo)
                                <div class="mb-4" id="current-logo-container">
                                    <p class="text-sm text-gray-600 mb-2">Logo saat ini:</p>
                                    <div class="flex items-center space-x-4">
                                        <img src="{{ asset('storage/' . $school->logo) }}" alt="{{ $school->name }}" class="h-16 w-auto border border-gray-300 rounded">
                                        <div>
                                            <p class="text-sm text-gray-500">File: {{ basename($school->logo) }}</p>
                                            <button type="button" onclick="removeCurrentLogo()" class="text-red-600 hover:text-red-800 text-sm">
                                                <i class="fas fa-trash mr-1"></i>Hapus Logo
                                            </button>
                                        </div>
                                    </div>
                                    <input type="hidden" name="current_logo" value="{{ $school->logo }}">
                                </div>
                            @endif
                            
                            <!-- Upload New Logo -->
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="school_logo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload file baru</span>
                                            <input id="school_logo" name="school_logo" type="file" accept="image/*" class="sr-only">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">PNG, JPG, GIF hingga 10MB</p>
                                </div>
                            </div>
                            @error('school_logo')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- App Branding -->
                @if($school->tenantSettings)
                    <div class="mb-8">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Branding Aplikasi</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi *</label>
                                <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $school->tenantSettings->app_name) }}" required 
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Nama aplikasi">
                                @error('app_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Primer *</label>
                                <input type="color" name="primary_color" id="primary_color" value="{{ old('primary_color', $school->tenantSettings->primary_color) }}" required 
                                       class="block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('primary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Sekunder *</label>
                                <input type="color" name="secondary_color" id="secondary_color" value="{{ old('secondary_color', $school->tenantSettings->secondary_color) }}" required 
                                       class="block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                @error('secondary_color')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="accent_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Aksen</label>
                                <input type="color" name="accent_color" id="accent_color" value="{{ old('accent_color', $school->tenantSettings->accent_color) }}" 
                                       class="block w-full h-10 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('super-admin.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Sekolah
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function removeCurrentLogo() {
    if (confirm('Apakah Anda yakin ingin menghapus logo saat ini?')) {
        const form = document.querySelector('form');
        
        // Check if remove_logo input already exists
        let removeInput = form.querySelector('input[name="remove_logo"]');
        if (!removeInput) {
            // Add hidden input to indicate logo should be removed
            removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove_logo';
            removeInput.value = '1';
            form.appendChild(removeInput);
        }
        
        // Hide current logo display
        const currentLogoDiv = document.getElementById('current-logo-container');
        if (currentLogoDiv) {
            currentLogoDiv.style.display = 'none';
        }
        
        // Clear file input if any
        const fileInput = document.getElementById('school_logo');
        if (fileInput) {
            fileInput.value = '';
        }
    }
}
</script>
@endsection

