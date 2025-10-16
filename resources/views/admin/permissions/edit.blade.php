@extends('layouts.app')

@section('title', 'Edit Permission - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Permission: {{ $permission->name }}</h1>
                    <p class="text-gray-600 mt-1">Ubah informasi permission dan assign ke role</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <a href="{{ route('admin.permissions.show', $permission) }}" 
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
            <form action="{{ route('admin.permissions.update', $permission) }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Permission Name -->
                <div class="mb-6">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Permission *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required 
                           class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Masukkan nama permission">
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Permission Group -->
                <div class="mb-6">
                    <label for="group" class="block text-sm font-medium text-gray-700 mb-2">Grup Permission *</label>
                    <select name="group" id="group" required 
                            class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih grup permission</option>
                        @foreach($permissionGroups as $key => $label)
                            <option value="{{ $key }}" {{ old('group', explode('-', $permission->name)[0]) === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('group')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea name="description" id="description" rows="3"
                              class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Deskripsi permission (opsional)">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Roles Assignment -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-4">Assign ke Role</label>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($roles as $role)
                            <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer {{ $permission->roles->contains($role->id) ? 'bg-blue-50 border-blue-300' : '' }}">
                                <input type="checkbox" name="roles[]" value="{{ $role->id }}" 
                                       {{ $permission->roles->contains($role->id) ? 'checked' : '' }}
                                       class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ ucfirst($role->name) }}</div>
                                    <div class="text-xs text-gray-500">{{ $role->permissions->count() }} permissions</div>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3">
                    <a href="{{ route('admin.permissions.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Permission
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection


