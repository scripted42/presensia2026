@extends('layouts.app')

@section('title', 'Detail Izin - Presensia')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Permohonan Izin</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap permohonan izin</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('leave-requests.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    @if($leaveRequest->status === 'pending' && $leaveRequest->user_id === Auth::id())
                        <a href="{{ route('leave-requests.edit', $leaveRequest) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Request Details -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- User Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Pengguna</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 rounded-full bg-gray-300 flex items-center justify-center">
                                    <i class="fas fa-user text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $leaveRequest->user->name }}</div>
                                <div class="text-sm text-gray-500">{{ $leaveRequest->user->email }}</div>
                                <div class="text-sm text-gray-500">{{ ucfirst($leaveRequest->user->user_type) }}</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Info -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Status Permohonan</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $leaveRequest->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                {{ $leaveRequest->status === 'approved' ? 'bg-green-100 text-green-800' : '' }}
                                {{ $leaveRequest->status === 'rejected' ? 'bg-red-100 text-red-800' : '' }}">
                                {{ $leaveRequest->status_label }}
                            </span>
                        </div>
                        @if($leaveRequest->approved_at)
                            <div class="mt-2 flex items-center justify-between">
                                <span class="text-sm text-gray-500">Disetujui oleh:</span>
                                <span class="text-sm font-medium text-gray-900">{{ $leaveRequest->approver->name }}</span>
                            </div>
                            <div class="mt-1 flex items-center justify-between">
                                <span class="text-sm text-gray-500">Tanggal:</span>
                                <span class="text-sm text-gray-900">{{ $leaveRequest->approved_at->format('d/m/Y H:i') }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leave Request Info -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Detail Permohonan</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Jenis Izin</label>
                    <div class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $leaveRequest->type === 'sick' ? 'bg-red-100 text-red-800' : '' }}
                            {{ $leaveRequest->type === 'leave' ? 'bg-blue-100 text-blue-800' : '' }}
                            {{ $leaveRequest->type === 'duty' ? 'bg-green-100 text-green-800' : '' }}">
                            {{ $leaveRequest->type_label }}
                        </span>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Durasi</label>
                    <div class="mt-1 text-sm text-gray-900">
                        {{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} hari
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->format('d F Y') }}</div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <div class="mt-1 text-sm text-gray-900">{{ $leaveRequest->end_date->format('d F Y') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reason -->
    <div class="bg-white shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Alasan</h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $leaveRequest->reason }}</p>
            </div>
        </div>
    </div>

    <!-- Evidence -->
    @if($leaveRequest->evidence && count($leaveRequest->evidence) > 0)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Dokumen Pendukung</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($leaveRequest->evidence as $index => $file)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center">
                                    @if(str_contains($file, '.pdf'))
                                        <i class="fas fa-file-pdf text-red-500 mr-2"></i>
                                    @else
                                        <i class="fas fa-file-image text-blue-500 mr-2"></i>
                                    @endif
                                    <span class="text-sm font-medium text-gray-900">Dokumen {{ $index + 1 }}</span>
                                </div>
                                <a href="{{ Storage::url($file) }}" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                            <div class="text-xs text-gray-500">
                                {{ basename($file) }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Rejection Reason -->
    @if($leaveRequest->status === 'rejected' && $leaveRequest->rejection_reason)
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Alasan Penolakan</h3>
                <div class="bg-red-50 border border-red-200 p-4 rounded-lg">
                    <p class="text-sm text-red-800 whitespace-pre-wrap">{{ $leaveRequest->rejection_reason }}</p>
                </div>
            </div>
        </div>
    @endif

    <!-- Action Buttons for Admin/Headmaster -->
    @if(Auth::user()->hasRole(['admin', 'headmaster']) && $leaveRequest->status === 'pending')
        <div class="bg-white shadow rounded-lg">
            <div class="px-4 py-5 sm:p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Aksi Persetujuan</h3>
                <div class="flex space-x-3">
                    <form action="{{ route('leave-requests.approve', $leaveRequest) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                onclick="return confirm('Apakah Anda yakin ingin menyetujui permohonan izin ini?')">
                            <i class="fas fa-check mr-2"></i>
                            Setujui
                        </button>
                    </form>
                    <button onclick="showRejectModal()" 
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        <i class="fas fa-times mr-2"></i>
                        Tolak
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Permohonan Izin</h3>
            <form action="{{ route('leave-requests.reject', $leaveRequest) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
                    <textarea name="rejection_reason" id="rejection_reason" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Masukkan alasan penolakan..." required></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="hideRejectModal()" 
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700">
                        Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showRejectModal() {
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}
</script>
@endsection




