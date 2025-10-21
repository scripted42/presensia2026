@extends('layouts.app')

@section('title', 'Security Log Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.index') }}">Super Admin</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('super-admin.security.index') }}">Security</a></li>
                        <li class="breadcrumb-item active">Security Log #{{ $securityLog->id }}</li>
                    </ol>
                </div>
                <h4 class="page-title">Security Log Details</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Security Log #{{ $securityLog->id }}</h5>
                        <div>
                            @if(!$securityLog->is_blocked)
                                <form method="POST" action="{{ route('super-admin.security.block', $securityLog) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to block this security log?')">
                                        <i class="fas fa-ban mr-1"></i> Block
                                    </button>
                                </form>
                            @else
                                <span class="badge badge-danger">Blocked</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted">Basic Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>ID:</strong></td>
                                    <td>{{ $securityLog->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>School:</strong></td>
                                    <td>{{ $securityLog->school ? $securityLog->school->name : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td>
                                        <code>{{ $securityLog->ip_address }}</code>
                                        @if($securityLog->ip_address)
                                            <a href="https://ipinfo.io/{{ $securityLog->ip_address }}" target="_blank" class="btn btn-sm btn-outline-info ml-2">
                                                <i class="fas fa-external-link-alt"></i> Lookup
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>MAC Address:</strong></td>
                                    <td>{{ $securityLog->mac_address ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>User Agent:</strong></td>
                                    <td>
                                        @if($securityLog->user_agent)
                                            <small class="text-muted">{{ Str::limit($securityLog->user_agent, 100) }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted">Attack Details</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Attack Type:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $securityLog->severity === 'critical' ? 'danger' : ($securityLog->severity === 'high' ? 'warning' : 'info') }}">
                                            {{ $securityLog->formatted_attack_type }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Severity:</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $securityLog->severity === 'critical' ? 'danger' : ($securityLog->severity === 'high' ? 'warning' : 'info') }}">
                                            {{ ucfirst($securityLog->severity) }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Attempt Count:</strong></td>
                                    <td>{{ $securityLog->attempt_count }}</td>
                                </tr>
                                <tr>
                                    <td><strong>First Attempt:</strong></td>
                                    <td>{{ $securityLog->first_attempt ? $securityLog->first_attempt->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Last Attempt:</strong></td>
                                    <td>{{ $securityLog->last_attempt ? $securityLog->last_attempt->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($securityLog->is_blocked)
                                            <span class="badge badge-danger">Blocked</span>
                                        @else
                                            <span class="badge badge-success">Active</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($securityLog->description)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-muted">Description</h6>
                                <div class="alert alert-info">
                                    {{ $securityLog->description }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($securityLog->block_reason)
                        <div class="row mt-3">
                            <div class="col-12">
                                <h6 class="text-muted">Block Reason</h6>
                                <div class="alert alert-warning">
                                    {{ $securityLog->block_reason }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-muted">Timestamps</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Created At:</strong></td>
                                    <td>{{ $securityLog->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Updated At:</strong></td>
                                    <td>{{ $securityLog->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('super-admin.security.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Security Logs
                        </a>
                        <div>
                            @if($securityLog->ip_address)
                                <a href="{{ route('super-admin.security.banned-ips') }}?ip={{ $securityLog->ip_address }}" class="btn btn-outline-warning">
                                    <i class="fas fa-ban mr-1"></i> View IP Bans
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








