@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi - Presensia')

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pengaturan Aplikasi</h1>
                    <p class="text-gray-600 mt-1">Kustomisasi branding dan fitur aplikasi untuk {{ $school->name }}</p>
    </div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                <button onclick="showTab('banner')" id="banner-tab" class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    <i class="fas fa-image mr-2"></i>
                    Banner & Layout
                </button>
                <button onclick="showTab('colors')" id="colors-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-paint-brush mr-2"></i>
                    Warna
                </button>
            </nav>
        </div>
    </div>

    <!-- Banner & Layout Tab -->
    <div id="banner-content" class="tab-content">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Banner & Layout</h3>
                <form action="{{ route('tenant.banner.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <!-- Banner Settings -->
                    <div class="mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Banner Utama</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="banner_image" class="block text-sm font-medium text-gray-700 mb-2">Gambar Banner</label>
                                <input type="file" name="banner_image" id="banner_image" accept="image/*" onchange="previewBanner(this)"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                
                                @if($tenantSettings->banner_image)
                                    <div class="mt-2 flex items-center justify-between">
                                        <p class="text-sm text-gray-500">Banner saat ini: {{ basename($tenantSettings->banner_image) }}</p>
                                        <button type="button" onclick="removeBanner()" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <label for="banner_text" class="block text-sm font-medium text-gray-700 mb-2">Teks Banner</label>
                                <input type="text" name="banner_text" id="banner_text" value="{{ old('banner_text', $tenantSettings->banner_text ?? '') }}"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="Teks yang akan ditampilkan di banner">
                            </div>
                        </div>
                        
                        <!-- Banner Preview -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview Banner:</label>
                            <div class="relative w-full h-48 bg-gray-100 rounded-lg overflow-hidden border-2 border-dashed border-gray-300" id="banner-preview">
                                <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                                    <div class="text-center">
                                        <i class="fas fa-image text-4xl mb-2"></i>
                                        <p>Preview banner akan muncul di sini</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- School Photo Settings -->
                    <div class="mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Foto Sekolah (Overlay Banner)</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="school_photo" class="block text-sm font-medium text-gray-700 mb-2">Foto Sekolah</label>
                                <input type="file" name="school_photo" id="school_photo" accept="image/*" onchange="previewSchoolPhoto(this)"
                                       class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                                
                                @if($tenantSettings->school_photo)
                                    <div class="mt-2 flex items-center justify-between">
                                        <p class="text-sm text-gray-500">Foto sekolah saat ini: {{ basename($tenantSettings->school_photo) }}</p>
                                        <button type="button" onclick="removeSchoolPhoto()" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i>Hapus
                                        </button>
                                    </div>
                                @endif
                            </div>
                            
                            <div>
                                <label for="school_photo_opacity" class="block text-sm font-medium text-gray-700 mb-2">Transparansi (%)</label>
                                <input type="range" name="school_photo_opacity" id="school_photo_opacity" min="0" max="100" value="{{ old('school_photo_opacity', $tenantSettings->school_photo_opacity ?? 10) }}"
                                       class="block w-full" oninput="updateOpacity(this.value)">
                                <div class="flex justify-between text-xs text-gray-500 mt-1">
                                    <span>0%</span>
                                    <span id="opacity-value">{{ $tenantSettings->school_photo_opacity ?? 10 }}%</span>
                                    <span>100%</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- School Photo Preview -->
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preview Foto Sekolah:</label>
                            <div class="relative w-full h-32 bg-gray-100 rounded-lg overflow-hidden border-2 border-dashed border-gray-300" id="school-photo-preview">
                                <div class="absolute inset-0 flex items-center justify-center text-gray-500">
                                    <div class="text-center">
                                        <i class="fas fa-school text-2xl mb-1"></i>
                                        <p class="text-sm">Preview foto sekolah akan muncul di sini</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Topbar Announcement -->
                    <div class="mb-8">
                        <h4 class="text-md font-medium text-gray-900 mb-4">Pengumuman Topbar</h4>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="topbar_announcement" class="block text-sm font-medium text-gray-700 mb-2">Teks Pengumuman</label>
                                <textarea name="topbar_announcement" id="topbar_announcement" rows="3"
                                          class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Masukkan teks pengumuman yang akan ditampilkan di topbar...">{{ old('topbar_announcement', $tenantSettings->topbar_announcement ?? '') }}</textarea>
                            </div>
                            
                            <div class="flex items-center">
                                <input type="checkbox" name="show_announcement" id="show_announcement" value="1" {{ ($tenantSettings->show_announcement ?? false) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="show_announcement" class="ml-2 block text-sm text-gray-900">
                                    Tampilkan pengumuman di topbar
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Pengaturan Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- Colors Tab -->
    <div id="colors-content" class="tab-content hidden">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Warna</h3>
                <form action="{{ route('tenant.settings.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="primary_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Primer *</label>
                            <input type="color" name="primary_color" id="primary_color" required 
                                   class="block w-full h-12 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ old('primary_color', $tenantSettings->primary_color) }}">
                            @error('primary_color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="secondary_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Sekunder *</label>
                            <input type="color" name="secondary_color" id="secondary_color" required 
                                   class="block w-full h-12 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ old('secondary_color', $tenantSettings->secondary_color) }}">
                            @error('secondary_color')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="accent_color" class="block text-sm font-medium text-gray-700 mb-2">Warna Aksen</label>
                            <input type="color" name="accent_color" id="accent_color" 
                                   class="block w-full h-12 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   value="{{ old('accent_color', $tenantSettings->accent_color) }}">
                        </div>
                    </div>
                    
                    <!-- Color Preview -->
                    <div class="mt-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Preview Warna:</h4>
                        <div class="flex space-x-4">
                            <div class="w-16 h-16 rounded-lg border" id="primary-preview" style="background-color: {{ $tenantSettings->primary_color }}"></div>
                            <div class="w-16 h-16 rounded-lg border" id="secondary-preview" style="background-color: {{ $tenantSettings->secondary_color }}"></div>
                            <div class="w-16 h-16 rounded-lg border" id="accent-preview" style="background-color: {{ $tenantSettings->accent_color }}"></div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Warna
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active', 'border-blue-500', 'text-blue-600');
        button.classList.add('border-transparent', 'text-gray-500');
    });
    
    // Show selected tab content
    document.getElementById(tabName + '-content').classList.remove('hidden');
    
    // Add active class to selected tab
    const activeTab = document.getElementById(tabName + '-tab');
    activeTab.classList.add('active', 'border-blue-500', 'text-blue-600');
    activeTab.classList.remove('border-transparent', 'text-gray-500');
}

// Color preview update
document.getElementById('primary_color').addEventListener('input', function() {
    document.getElementById('primary-preview').style.backgroundColor = this.value;
});

document.getElementById('secondary_color').addEventListener('input', function() {
    document.getElementById('secondary-preview').style.backgroundColor = this.value;
});

document.getElementById('accent_color').addEventListener('input', function() {
    document.getElementById('accent-preview').style.backgroundColor = this.value;
});

// Banner preview function
function previewBanner(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('banner-preview');
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" alt="Banner preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// School photo preview function
function previewSchoolPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('school-photo-preview');
            const opacity = document.getElementById('school_photo_opacity').value;
            preview.innerHTML = `<img src="${e.target.result}" class="w-full h-full object-cover" style="opacity: ${opacity/100}" alt="School photo preview">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update opacity function
function updateOpacity(value) {
    document.getElementById('opacity-value').textContent = value + '%';
    const preview = document.getElementById('school-photo-preview');
    const img = preview.querySelector('img');
    if (img) {
        img.style.opacity = value/100;
    }
}

// Remove banner function
function removeBanner() {
    if (confirm('Apakah Anda yakin ingin menghapus banner?')) {
        fetch('{{ route("tenant.banner.remove", ["type" => "banner"]) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus banner: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus banner');
        });
    }
}

// Remove school photo function
function removeSchoolPhoto() {
    if (confirm('Apakah Anda yakin ingin menghapus foto sekolah?')) {
        fetch('{{ route("tenant.banner.remove", ["type" => "school_photo"]) }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Gagal menghapus foto sekolah: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menghapus foto sekolah');
        });
    }
}
</script>
@endsection

