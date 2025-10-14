@extends('layouts.app')

@section('title', 'QR Code Management - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">QR Code Management</h1>
                <p class="text-gray-600 mt-1">Kelola dan cetak QR siswa (format: NIS|Nama). Unduhan massal tersedia.</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('qr.zip') }}" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Download Massal (.zip)</a>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 overflow-x-auto">
            <form method="GET" action="{{ route('qr.index') }}" class="mb-4 flex items-center gap-3">
                <input type="text" name="q" value="{{ $query ?? '' }}" placeholder="Cari NIS atau Nama..." class="border-gray-300 rounded-md">
                <select name="per_page" class="border-gray-300 rounded-md">
                    <option value="10" {{ ($perPageParam ?? '10')=='10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ ($perPageParam ?? '')=='25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ ($perPageParam ?? '')=='50' ? 'selected' : '' }}>50</option>
                    <option value="all" {{ ($perPageParam ?? '')=='all' ? 'selected' : '' }}>All</option>
                </select>
                <button class="bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
            </form>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">NIS</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($students as $s)
                    <tr>
                        <td class="px-3 py-2 text-sm text-gray-900">{{ $s->nis ?? '-' }}</td>
                        <td class="px-3 py-2 text-sm text-gray-900">{{ $s->name }}</td>
                        <td class="px-3 py-2 text-sm">
                            <a class="text-blue-600 hover:underline" href="{{ route('qr.download', $s) }}">Download QR (.png)</a>
                            <span class="mx-2">|</span>
                            <a class="text-green-600 hover:underline" href="{{ route('qr.card', $s) }}">Kartu Pelajar (.png)</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4">
                {{ $students->links() }}
            </div>
        </div>
    </div>
@endsection


