@extends('layouts.app')

@section('title', 'Preview Import - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Preview Import ({{ $type === 'employee' ? 'Pegawai' : 'Siswa' }})</h1>
                <p class="text-gray-600 mt-1">Periksa data berikut. Baris yang bermasalah ditandai merah/kuning.</p>
            </div>
            <a href="{{ route('users.import', ['type' => $type]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg">Kembali</a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg mb-4">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>Valid: <span class="font-semibold text-green-700">{{ $summary['valid'] }}</span></div>
                <div>Invalid: <span class="font-semibold text-red-700">{{ $summary['invalid'] }}</span></div>
                <div>Duplikasi di file: <span class="font-semibold text-yellow-700">{{ $summary['fileDuplicates'] }}</span></div>
                <div>Duplikasi di database: <span class="font-semibold text-orange-700">{{ $summary['dbDuplicates'] }}</span></div>
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">#</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Nama</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Email</th>
                        @if($type === 'employee')
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">NIK</th>
                        @else
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">NIS</th>
                        @endif
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Issues</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($preview as $row)
                        @php
                            $statusColor = $row['status'] === 'valid' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800';
                        @endphp
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['line'] }}</td>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['name'] }}</td>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['email'] }}</td>
                            @if($type === 'employee')
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['nik'] ?? '' }}</td>
                            @else
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['nis'] ?? '' }}</td>
                            @endif
                            <td class="px-3 py-2 text-sm"><span class="px-2 py-1 rounded {{ $statusColor }}">{{ ucfirst($row['status']) }}</span></td>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ implode('; ', $row['issues']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <form method="POST" action="{{ route('users.import') }}" class="mt-4">
        @csrf
        <input type="hidden" name="type" value="{{ $type }}">
        <input type="hidden" name="confirm" value="1">
        <div class="flex justify-end">
            <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">Submit Import</button>
        </div>
    </form>
@endsection














