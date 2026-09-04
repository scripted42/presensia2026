@extends('layouts.app')

@section('title', 'Preview Import - Presensia')

@section('content')
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Preview Import ({{ $type === 'employee' ? 'Pegawai' : 'Siswa' }})</h1>
                <p class="text-gray-600 mt-1">Periksa data berikut sebelum disimpan ke sistem.</p>
            </div>
            <a href="{{ route('users.import', ['type' => $type]) }}" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg mb-4">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg">
                    <span class="text-gray-600 block text-xs">Akun Baru (Valid)</span>
                    <span class="font-bold text-lg text-green-700">{{ $summary['valid'] }}</span>
                </div>
                <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                    <span class="text-gray-600 block text-xs">Update Kelas Siswa</span>
                    <span class="font-bold text-lg text-blue-700">{{ $summary['update'] ?? 0 }}</span>
                </div>
                <div class="p-3 bg-orange-50 border border-orange-200 rounded-lg">
                    <span class="text-gray-600 block text-xs">Duplikasi di Database</span>
                    <span class="font-bold text-lg text-orange-700">{{ $summary['dbDuplicates'] }}</span>
                </div>
                <div class="p-3 bg-red-50 border border-red-200 rounded-lg">
                    <span class="text-gray-600 block text-xs">Invalid (Error)</span>
                    <span class="font-bold text-lg text-red-700">{{ $summary['invalid'] }}</span>
                </div>
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
                            <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Kelas</th>
                        @endif
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Status</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500">Keterangan / Issues</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($preview as $row)
                        @php
                            if ($row['status'] === 'valid') {
                                $statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Baru</span>';
                            } elseif ($row['status'] === 'update') {
                                $statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">Update Kelas</span>';
                            } else {
                                $statusBadge = '<span class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Invalid</span>';
                            }
                        @endphp
                        <tr>
                            <td class="px-3 py-2 text-sm text-gray-900">{{ $row['line'] }}</td>
                            <td class="px-3 py-2 text-sm font-medium text-gray-900">{{ $row['data']['name'] }}</td>
                            <td class="px-3 py-2 text-sm text-gray-500">{{ $row['data']['email'] }}</td>
                            @if($type === 'employee')
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['nik'] ?? '' }}</td>
                            @else
                                <td class="px-3 py-2 text-sm text-gray-900">{{ $row['data']['nis'] ?? '' }}</td>
                                <td class="px-3 py-2 text-sm text-blue-700 font-semibold">{{ !empty($row['data']['class']) ? $row['data']['class'] : '-' }}</td>
                            @endif
                            <td class="px-3 py-2 text-sm">{!! $statusBadge !!}</td>
                            <td class="px-3 py-2 text-sm text-gray-600">{{ implode('; ', $row['issues']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @php
        $canSubmit = ($summary['valid'] + ($summary['update'] ?? 0)) > 0;
    @endphp

    @if($canSubmit)
        <form method="POST" action="{{ route('users.import') }}" class="mt-4">
            @csrf
            <input type="hidden" name="type" value="{{ $type }}">
            <input type="hidden" name="confirm" value="1">
            <div class="flex flex-col sm:flex-row items-center justify-between p-4 bg-white shadow rounded-lg border border-green-200">
                <div class="mb-3 sm:mb-0 text-sm text-gray-700">
                    Siap memproses 
                    @if($summary['valid'] > 0)
                        <strong class="text-green-700">{{ $summary['valid'] }} akun baru</strong>
                    @endif
                    @if($summary['valid'] > 0 && ($summary['update'] ?? 0) > 0) dan @endif
                    @if(($summary['update'] ?? 0) > 0)
                        <strong class="text-blue-700">{{ $summary['update'] }} penetapan kelas siswa</strong>
                    @endif.
                </div>
                <button type="submit" class="w-full sm:w-auto bg-green-600 hover:bg-green-700 text-white font-medium px-6 py-2.5 rounded-lg shadow transition">
                    <i class="fas fa-check-circle mr-2"></i>Proses Import Sekarang
                </button>
            </div>
        </form>
    @else
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800 flex items-center">
            <i class="fas fa-exclamation-triangle mr-2 text-yellow-600"></i>
            Tidak ada data baru atau update kelas yang dapat diproses. Silakan periksa kembali file Anda.
        </div>
    @endif
@endsection
