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
                <button onclick="showTab('branding')" id="branding-tab" class="tab-button active py-4 px-1 border-b-2 border-blue-500 font-medium text-sm text-blue-600">
                    <i class="fas fa-palette mr-2"></i>
                    Branding
                </button>
                <button onclick="showTab('features')" id="features-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-cogs mr-2"></i>
                    Fitur
                </button>
                <button onclick="showTab('colors')" id="colors-tab" class="tab-button py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300">
                    <i class="fas fa-paint-brush mr-2"></i>
                    Warna
                </button>
            </nav>
        </div>
    </div>

    <!-- Branding Tab -->
    <div id="branding-content" class="tab-content">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Branding</h3>
                <form action="{{ route('tenant.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="app_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Aplikasi *</label>
                            <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $tenantSettings->app_name) }}" required 
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                   placeholder="Nama aplikasi">
                            @error('app_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label for="app_logo" class="block text-sm font-medium text-gray-700 mb-2">Logo Aplikasi</label>
                            <input type="file" name="app_logo" id="app_logo" accept="image/*"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @if($tenantSettings->app_logo)
                                <p class="mt-1 text-sm text-gray-500">Logo saat ini: {{ $tenantSettings->app_logo }}</p>
                            @endif
                        </div>
                        
                        <div>
                            <label for="app_favicon" class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
                            <input type="file" name="app_favicon" id="app_favicon" accept="image/*"
                                   class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            @if($tenantSettings->app_favicon)
                                <p class="mt-1 text-sm text-gray-500">Favicon saat ini: {{ $tenantSettings->app_favicon }}</p>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Branding
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Features Tab -->
    <div id="features-content" class="tab-content hidden">
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pengaturan Fitur</h3>
                <form action="{{ route('tenant.features.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @php
                            $features = $tenantSettings->features ?? \App\Models\TenantSetting::getDefaultFeatures();
                        @endphp
                        
                        @foreach($features as $key => $enabled)
                            <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg">
                                <div>
                                    <h4 class="text-sm font-medium text-gray-900">{{ ucwords(str_replace('_', ' ', $key)) }}</h4>
                                    <p class="text-xs text-gray-500">
                                        @switch($key)
                                            @case('attendance')
                                                Sistem absensi pegawai dan siswa
                                                @break
                                            @case('leave_management')
                                                Manajemen izin dan cuti
                                                @break
                                            @case('reports')
                                                Laporan dan export data
                                                @break
                                            @case('qr_codes')
                                                QR code untuk absensi
                                                @break
                                            @case('bulk_import')
                                                Import data massal
                                                @break
                                            @case('rbac')
                                                Role-based access control
                                                @break
                                            @case('notifications')
                                                Sistem notifikasi
                                                @break
                                            @case('api_access')
                                                Akses API untuk integrasi
                                                @break
                                            @case('custom_branding')
                                                Kustomisasi branding
                                                @break
                                        @endswitch
                                    </p>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="features[{{ $key }}]" value="1" {{ $enabled ? 'checked' : '' }}
                                           class="sr-only peer">
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Fitur
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
</script>
@endsection

