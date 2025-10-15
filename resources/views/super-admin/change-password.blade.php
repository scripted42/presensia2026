@extends('layouts.app')

@section('title', 'Ubah Password - Super Admin')

@section('content')
<div class="max-w-3xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-5 border-b">
            <h1 class="text-xl font-bold text-gray-900">Ubah Password Super Admin</h1>
        </div>
        <form method="POST" action="{{ route('super-admin.password.update') }}" class="p-6">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Password Saat Ini</label>
                <input type="password" name="current_password" class="mt-1 block w-full border-gray-300 rounded-md" required>
                @error('current_password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Password Baru</label>
                    <input type="password" name="new_password" class="mt-1 block w-full border-gray-300 rounded-md">
                    @error('new_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" class="mt-1 block w-full border-gray-300 rounded-md">
                </div>
            </div>

            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700">Email Login Super Admin (opsional)</label>
                <input type="email" name="new_email" value="{{ old('new_email', auth()->user()->email) }}" class="mt-1 block w-full border-gray-300 rounded-md">
                <p class="text-xs text-gray-500 mt-1">Jika diubah, nilai APP_SUPER_ADMIN_EMAIL di .env juga akan diperbarui.</p>
                @error('new_email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end">
                <a href="{{ route('super-admin.index') }}" class="px-4 py-2 rounded bg-gray-200 text-gray-700 mr-2">Batal</a>
                <button type="submit" class="px-4 py-2 rounded bg-blue-600 text-white">Simpan</button>
            </div>
        </form>
    </div>
    @if(session('success'))
        <div class="mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
</div>
@endsection


