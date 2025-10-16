@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi - Presensia')

@section('content')
<div>
    <!-- Success Message -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif
    
    <!-- Error Message -->
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif
    
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
                <form action="{{ route('tenant.banner.update') }}" method="POST" enctype="multipart/form-data" id="banner-form">
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
                        
                        <!-- Interactive Photo Position Controls -->
                        <div class="mt-4">
                            <h5 class="text-sm font-medium text-gray-700 mb-3">Posisi Foto (Drag untuk mengatur)</h5>
                            <div class="relative bg-gray-100 rounded-lg p-4" style="height: 240px;">
                                <div id="photo-position-area" class="relative w-full h-full border-2 border-dashed border-gray-300 rounded overflow-hidden">
                                    <!-- Background layer for photo (controls opacity/scale/position) - now draggable -->
                                    <div id="photo-bg" class="absolute inset-0 bg-no-repeat bg-cover cursor-move transition-all duration-150 ease-out" style="opacity: {{ ($tenantSettings->school_photo_opacity ?? 10) / 100 }};"></div>
                                    
                                    <div class="absolute inset-0 flex items-center justify-center text-gray-400 text-sm pointer-events-none">
                                        Drag foto untuk mengatur posisi
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Posisi: <span id="position-display">Tengah, Tengah</span>
                            </div>
                            <!-- Hidden inputs for form submission -->
                            <input type="hidden" name="school_photo_position_x" id="school_photo_position_x" value="{{ $tenantSettings->school_photo_position_x ?? 'center' }}">
                            <input type="hidden" name="school_photo_position_y" id="school_photo_position_y" value="{{ $tenantSettings->school_photo_position_y ?? 'center' }}">
                        </div>
                        
                        <!-- Photo Scale Control -->
                        <div class="mt-4">
                            <label for="school_photo_scale" class="block text-sm font-medium text-gray-700 mb-2">Skala Foto (%)</label>
                            <input type="range" name="school_photo_scale" id="school_photo_scale" min="50" max="200" value="{{ old('school_photo_scale', $tenantSettings->school_photo_scale ?? 100) }}"
                                   class="block w-full" oninput="updateScale(this.value)">
                            <div class="flex justify-between text-xs text-gray-500 mt-1">
                                <span>50%</span>
                                <span id="scale-value">{{ $tenantSettings->school_photo_scale ?? 100 }}%</span>
                                <span>200%</span>
                            </div>
                        </div>
                        </div>
                        
                        <!-- Preview terintegrasi pada kanvas posisi, blok terpisah dihapus -->
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
                        <button type="submit" id="save-button"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            <span id="save-text">Simpan Pengaturan Banner</span>
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
        console.log('School photo selected:', input.files[0].name);
        const reader = new FileReader();
        reader.onload = function(e) {
            const bg = document.getElementById('photo-bg');
            if (bg) {
                const opacity = document.getElementById('school_photo_opacity').value;
                const posX = document.getElementById('school_photo_position_x').value;
                const posY = document.getElementById('school_photo_position_y').value;
                const scale = document.getElementById('school_photo_scale').value;
                bg.style.backgroundImage = `url('${e.target.result}')`;
                bg.style.opacity = opacity/100;
                bg.style.backgroundPosition = `${posX} ${posY}`;
                bg.style.backgroundSize = `${scale}%`;
                
                console.log('Photo loaded in preview area');
                
                // Re-initialize drag functionality for the new image
                initializeDrag();
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Update opacity function
function updateOpacity(value) {
    document.getElementById('opacity-value').textContent = value + '%';
    updatePreview();
}

// Drag and drop functionality
let isDragging = false;
let dragArea = null;
let startX = 0;
let startY = 0;
let currentX = 0;
let currentY = 0;

function initializeDrag() {
    dragArea = document.getElementById('photo-position-area');
    const bg = document.getElementById('photo-bg');
    
    if (dragArea && bg) {
        // Remove existing event listeners to prevent duplicates
        bg.removeEventListener('mousedown', startDrag);
        bg.removeEventListener('touchstart', startDrag);
        
        // Add new event listeners
        bg.addEventListener('mousedown', startDrag);
        bg.addEventListener('touchstart', startDrag);
        
        console.log('Drag functionality initialized');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    dragArea = document.getElementById('photo-position-area');
    const bg = document.getElementById('photo-bg');
    
    if (dragArea && bg) {
        // Initialize background image of drag area if existing photo
        @if($tenantSettings->school_photo)
            bg.style.backgroundImage = "url('{{ asset('storage/' . $tenantSettings->school_photo) }}')";
            bg.style.backgroundPosition = "{{ $tenantSettings->school_photo_position_x ?? 'center' }} {{ $tenantSettings->school_photo_position_y ?? 'center' }}";
            bg.style.backgroundSize = "{{ $tenantSettings->school_photo_scale ?? 100 }}%";
            bg.style.opacity = {{ ($tenantSettings->school_photo_opacity ?? 10) / 100 }};
        @endif
        
        // Initialize drag functionality
        initializeDrag();
        
        // Global mouse/touch events
        document.addEventListener('mousemove', drag);
        document.addEventListener('mouseup', stopDrag);
        document.addEventListener('touchmove', drag);
        document.addEventListener('touchend', stopDrag);
    }
    
    // Handle form submission
    const form = document.getElementById('banner-form');
    const saveButton = document.getElementById('save-button');
    const saveText = document.getElementById('save-text');
    
    if (form && saveButton && saveText) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitted');
            
            // Check if files are selected
            const bannerFile = document.getElementById('banner_image').files[0];
            const schoolPhotoFile = document.getElementById('school_photo').files[0];
            
            if (bannerFile) {
                console.log('Banner file selected:', bannerFile.name, bannerFile.size, 'Type:', bannerFile.type);
            }
            if (schoolPhotoFile) {
                console.log('School photo file selected:', schoolPhotoFile.name, schoolPhotoFile.size, 'Type:', schoolPhotoFile.type);
            }
            
            // Check form data
            const formData = new FormData(form);
            console.log('Form data entries:');
            for (let [key, value] of formData.entries()) {
                if (value instanceof File) {
                    console.log(key, ':', value.name, value.size, value.type);
                } else {
                    console.log(key, ':', value);
                }
            }
            
            saveButton.disabled = true;
            saveText.textContent = 'Menyimpan...';
            saveButton.classList.add('opacity-75', 'cursor-not-allowed');
        });
    }
});

function startDrag(e) {
    isDragging = true;
    e.preventDefault();
    
    const bg = document.getElementById('photo-bg');
    if (bg) {
        bg.style.cursor = 'grabbing';
        
        // Get initial position
        const rect = dragArea.getBoundingClientRect();
        const clientX = e.clientX || (e.touches && e.touches[0].clientX);
        const clientY = e.clientY || (e.touches && e.touches[0].clientY);
        
        startX = clientX - rect.left;
        startY = clientY - rect.top;
        
        console.log('Drag started on photo at:', startX, startY);
    }
}

function drag(e) {
    if (!isDragging) return;
    
    e.preventDefault();
    
    const rect = dragArea.getBoundingClientRect();
    const clientX = e.clientX || (e.touches && e.touches[0].clientX);
    const clientY = e.clientY || (e.touches && e.touches[0].clientY);
    
    // Calculate relative position within the drag area
    const x = Math.max(0, Math.min(100, ((clientX - rect.left) / rect.width) * 100));
    const y = Math.max(0, Math.min(100, ((clientY - rect.top) / rect.height) * 100));
    
    // Update background position smoothly
    const bg = document.getElementById('photo-bg');
    if (bg) {
        bg.style.backgroundPosition = `${x}% ${y}%`;
    }
    
    // Update position values
    updatePositionFromDrag(x, y);
}

function stopDrag() {
    isDragging = false;
    const bg = document.getElementById('photo-bg');
    if (bg) bg.style.cursor = 'move';
}

function updatePositionFromDrag(x, y) {
    // Convert percentage to position values
    let positionX = 'center';
    let positionY = 'center';
    
    if (x < 30) positionX = 'left';
    else if (x > 70) positionX = 'right';
    
    if (y < 30) positionY = 'top';
    else if (y > 70) positionY = 'bottom';
    
    // Update hidden inputs
    document.getElementById('school_photo_position_x').value = positionX;
    document.getElementById('school_photo_position_y').value = positionY;
    
    // Update display
    const display = document.getElementById('position-display');
    if (display) {
        const xText = positionX === 'left' ? 'Kiri' : (positionX === 'right' ? 'Kanan' : 'Tengah');
        const yText = positionY === 'top' ? 'Atas' : (positionY === 'bottom' ? 'Bawah' : 'Tengah');
        display.textContent = `${xText}, ${yText}`;
    }
    
    updatePreview();
}

// Update scale function
function updateScale(value) {
    document.getElementById('scale-value').textContent = value + '%';
    updatePreview();
}

// Update preview with all settings
function updatePreview() {
    const bg = document.getElementById('photo-bg');
    if (bg && bg.style.backgroundImage) {
        const opacity = document.getElementById('school_photo_opacity').value;
        const positionX = document.getElementById('school_photo_position_x').value;
        const positionY = document.getElementById('school_photo_position_y').value;
        const scale = document.getElementById('school_photo_scale').value;
        bg.style.opacity = opacity/100;
        bg.style.backgroundPosition = `${positionX} ${positionY}`;
        bg.style.backgroundSize = `${scale}%`;
    }
}

// Initialize position display on page load
document.addEventListener('DOMContentLoaded', function() {
    const positionX = document.getElementById('school_photo_position_x').value;
    const positionY = document.getElementById('school_photo_position_y').value;
    const display = document.getElementById('position-display');
    
    if (display) {
        const xText = positionX === 'left' ? 'Kiri' : (positionX === 'right' ? 'Kanan' : 'Tengah');
        const yText = positionY === 'top' ? 'Atas' : (positionY === 'bottom' ? 'Bawah' : 'Tengah');
        display.textContent = `${xText}, ${yText}`;
    }
});

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

