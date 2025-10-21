@extends('layouts.app')

@section('title', 'Edit Jadwal Khusus')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Jadwal Khusus</h1>
                    <p class="text-gray-600 mt-1">Edit jadwal khusus untuk absensi</p>
                </div>
                <a href="{{ route('admin.special-schedules.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('admin.special-schedules.update', $specialSchedule) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Jadwal -->
                    <div class="md:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Jadwal <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $specialSchedule->name) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                               placeholder="Contoh: Upacara Senin, Rapat Guru, dll"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                            Deskripsi
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
                                  placeholder="Jelaskan detail jadwal khusus ini">{{ old('description', $specialSchedule->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Hari dalam Seminggu -->
                    <div>
                        <label for="day_of_week" class="block text-sm font-medium text-gray-700 mb-2">
                            Hari dalam Seminggu <span class="text-red-500">*</span>
                        </label>
                        <select name="day_of_week" 
                                id="day_of_week"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('day_of_week') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Hari</option>
                            <option value="monday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'monday' ? 'selected' : '' }}>Senin</option>
                            <option value="tuesday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'tuesday' ? 'selected' : '' }}>Selasa</option>
                            <option value="wednesday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'wednesday' ? 'selected' : '' }}>Rabu</option>
                            <option value="thursday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'thursday' ? 'selected' : '' }}>Kamis</option>
                            <option value="friday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'friday' ? 'selected' : '' }}>Jumat</option>
                            <option value="saturday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'saturday' ? 'selected' : '' }}>Sabtu</option>
                            <option value="sunday" {{ old('day_of_week', $specialSchedule->day_of_week) == 'sunday' ? 'selected' : '' }}>Minggu</option>
                        </select>
                        @error('day_of_week')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Waktu Maksimal Check-in -->
                    <div>
                        <label for="max_check_in_time" class="block text-sm font-medium text-gray-700 mb-2">
                            Waktu Maksimal Check-in <span class="text-red-500">*</span>
                        </label>
                        <input type="time" 
                               name="max_check_in_time" 
                               id="max_check_in_time"
                               value="{{ old('max_check_in_time', $specialSchedule->max_check_in_time ? $specialSchedule->max_check_in_time->format('H:i') : '07:30') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('max_check_in_time') border-red-500 @enderror"
                               required>
                        @error('max_check_in_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Mulai -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Mulai
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="start_date"
                               value="{{ old('start_date', $specialSchedule->start_date ? $specialSchedule->start_date->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Berakhir -->
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Berakhir
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="end_date"
                               value="{{ old('end_date', $specialSchedule->end_date ? $specialSchedule->end_date->format('Y-m-d') : '') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('end_date') border-red-500 @enderror">
                        @error('end_date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role yang Terkena Dampak -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role yang Terkena Dampak
                        </label>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @php
                                $roles = [
                                    'teacher' => 'Guru',
                                    'student' => 'Siswa',
                                    'employee' => 'Pegawai',
                                    'admin' => 'Admin',
                                    'headmaster' => 'Kepala Sekolah',
                                    'tu' => 'Tata Usaha',
                                    'bk' => 'Bimbingan Konseling',
                                    'kesiswaan' => 'Kesiswaan'
                                ];
                                $selectedRoles = old('affected_roles', $specialSchedule->affected_roles ?? []);
                            @endphp
                            
                            @foreach($roles as $roleKey => $roleName)
                                <label class="flex items-center">
                                    <input type="checkbox" 
                                           name="affected_roles[]" 
                                           value="{{ $roleKey }}"
                                           {{ in_array($roleKey, $selectedRoles) ? 'checked' : '' }}
                                           class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                                    <span class="ml-2 text-sm text-gray-700">{{ $roleName }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('affected_roles')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="md:col-span-2">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $specialSchedule->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-700">Jadwal Aktif</span>
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('admin.special-schedules.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-save mr-2"></i>
                        Update Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
