@extends('layouts.app')

@section('title', 'Audit Trail Detail - Presensia')

@section('content')
<div class="max-w-5xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="bg-white overflow-hidden shadow rounded-lg mb-6">
        <div class="px-4 py-5 sm:p-6">
            <h1 class="text-xl font-bold text-gray-900 mb-4">Detail Audit Trail</h1>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div>
                    <div class="text-gray-500">ID</div>
                    <div class="font-medium">{{ $auditTrail->id }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Waktu</div>
                    <div class="font-medium">{{ $auditTrail->created_at->format('Y-m-d H:i:s') }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Sekolah</div>
                    <div class="font-medium">{{ $auditTrail->school->name ?? 'N/A' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">User</div>
                    <div class="font-medium">{{ $auditTrail->user->name ?? 'Guest' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Aksi</div>
                    <div class="font-medium">{{ $auditTrail->formatted_action }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Status</div>
                    <div class="font-medium">{{ $auditTrail->formatted_status }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Resource</div>
                    <div class="font-medium">{{ $auditTrail->resource_type }} #{{ $auditTrail->resource_id ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">IP / MAC</div>
                    <div class="font-medium">{{ $auditTrail->ip_address }} {{ $auditTrail->mac_address ? ' / '.$auditTrail->mac_address : '' }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-500">User Agent</div>
                    <div class="font-medium break-all">{{ $auditTrail->user_agent }}</div>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-500">Deskripsi</div>
                    <div class="font-medium">{{ $auditTrail->description }}</div>
                </div>
            </div>
            <div class="mt-6">
                <a href="{{ route('super-admin.audit-trails.index') }}" class="px-4 py-2 rounded border text-sm">Kembali</a>
            </div>
        </div>
    </div>
</div>
@endsection












