@extends('layouts.app')

@section('title', 'Manajemen Izin - Presensia')

@section('content')
<div>
    <!-- Header -->
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Manajemen Izin</h1>
                    <p class="text-xs text-gray-500 mt-1">Kelola permohonan izin, cuti, sakit, dan dinas luar</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('leave-requests.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <i class="fas fa-plus mr-2"></i>
                        Ajukan Izin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metric Cards -->
    <x-summary-cards :cards="$summaryCards ?? []" />

    <!-- Filters -->
    <x-filter-bar 
        :action="route('leave-requests.index')" 
        :reset-url="route('leave-requests.index')" 
        :active-filters="$activeFilters ?? []"
        title="Filter Permohonan Izin"
        badge-class="badge badge-info badge-sm text-xs font-medium"
    >
        <div>
            <label for="q" class="block text-sm text-gray-500 font-normal">Pencarian</label>
            <input type="text" name="q" id="q" value="{{ request('q') }}" 
                   placeholder="Nama pemohon, alasan..." 
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>

        <div>
            <label for="status" class="block text-sm text-gray-500 font-normal">Status</label>
            <select name="status" id="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Persetujuan</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
            </select>
        </div>

        <div>
            <label for="type" class="block text-sm text-gray-500 font-normal">Jenis Izin</label>
            <select name="type" id="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="">Semua Jenis</option>
                <option value="sick" {{ request('type') == 'sick' ? 'selected' : '' }}>Sakit</option>
                <option value="leave" {{ request('type') == 'leave' ? 'selected' : '' }}>Cuti</option>
                <option value="duty" {{ request('type') == 'duty' ? 'selected' : '' }}>Dinas Luar</option>
            </select>
        </div>

        <div>
            <label for="start_date" class="block text-sm text-gray-500 font-normal">Dari Tanggal</label>
            <input type="date" name="start_date" id="start_date" value="{{ request('start_date') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>

        <div>
            <label for="end_date" class="block text-sm text-gray-500 font-normal">Sampai Tanggal</label>
            <input type="date" name="end_date" id="end_date" value="{{ request('end_date') }}"
                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
        </div>

        <div>
            <label for="per_page" class="block text-sm text-gray-500 font-normal">Tampilkan</label>
            <select name="per_page" id="per_page" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 text-sm">
                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 baris</option>
                <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 baris</option>
                <option value="50" {{ request('per_page', 50) == 50 ? 'selected' : '' }}>50 baris</option>
                <option value="100" {{ request('per_page', 100) == 100 ? 'selected' : '' }}>100 baris</option>
            </select>
        </div>
    </x-filter-bar>

    <!-- Leave Requests List -->
    <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
            @if($leaveRequests->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pengguna</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diajukan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($leaveRequests as $request)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                                                    <i class="fas fa-user text-gray-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-900 leading-tight">{{ $request->user->name }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $request->user->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-sm text-xs font-medium
                                            {{ $request->type === 'sick' ? 'badge-error' : '' }}
                                            {{ $request->type === 'leave' ? 'badge-info' : '' }}
                                            {{ $request->type === 'duty' ? 'badge-success' : '' }}">
                                            {{ $request->type_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $request->start_date->format('d/m/Y') }} - {{ $request->end_date->format('d/m/Y') }}
                                        <br>
                                        <span class="text-xs text-gray-500">
                                            {{ $request->start_date->diffInDays($request->end_date) + 1 }} hari
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="badge badge-sm text-xs font-medium
                                            {{ $request->status === 'pending' ? 'badge-warning' : '' }}
                                            {{ $request->status === 'approved' ? 'badge-success' : '' }}
                                            {{ $request->status === 'rejected' ? 'badge-error' : '' }}">
                                            {{ $request->status_label }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $request->created_at->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('leave-requests.show', $request) }}" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            
                                            @if($request->status === 'pending' && $request->user_id === Auth::id())
                                                <a href="{{ route('leave-requests.edit', $request) }}" 
                                                   class="text-yellow-600 hover:text-yellow-900">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('leave-requests.destroy', $request) }}" method="POST" class="inline" 
                                                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus permohonan izin ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif

                                            @if(Auth::user()->hasRole(['admin', 'headmaster']) && $request->status === 'pending')
                                                <form action="{{ route('leave-requests.approve', $request) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-900"
                                                            onclick="return confirm('Apakah Anda yakin ingin menyetujui permohonan izin ini?')">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <button onclick="showRejectModal({{ $request->id }})" 
                                                        class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $leaveRequests->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <i class="fas fa-inbox text-4xl text-gray-400 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada permohonan izin</h3>
                    <p class="text-gray-500">Belum ada permohonan izin yang diajukan.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Tolak Permohonan Izin</h3>
            <form id="rejectForm" method="POST">
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
function showRejectModal(requestId) {
    document.getElementById('rejectForm').action = `/leave-requests/${requestId}/reject`;
    document.getElementById('rejectModal').classList.remove('hidden');
}

function hideRejectModal() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejection_reason').value = '';
}
</script>
@endsection

