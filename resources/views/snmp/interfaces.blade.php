@extends('layouts.app')

@section('title', 'SNMP Interfaces - ' . $device->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plug me-2"></i>Interface Statistics - {{ $device->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('snmp.dashboard') }}">SNMP Monitoring</a></li>
                    <li class="breadcrumb-item active">Interfaces</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success" onclick="refreshInterfaces()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <div id="interfacesContainer">
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading interfaces...</span>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        loadInterfaces();
    });

    function loadInterfaces() {
        $.get('/api/snmp/devices/{{ $device->id }}/interfaces', function(response) {
            if (response.success) {
                renderInterfaces(response.data);
            }
        });
    }

    function renderInterfaces(interfaces) {
        let html = '';
        
        interfaces.forEach((iface, index) => {
            const statusColor = iface.oper_status === 'up' ? 'success' : 'danger';
            const adminColor = iface.admin_status === 'up' ? 'success' : 'secondary';
            
            html += `
                <div class="card shadow mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <h6 class="mb-1">${iface.description || 'Interface ' + iface.index}</h6>
                                <small class="text-muted">Index: ${iface.index}</small>
                            </div>
                            <div class="col-md-2">
                                <span class="badge bg-${statusColor}">Oper: ${iface.oper_status}</span>
                                <span class="badge bg-${adminColor} ms-1">Admin: ${iface.admin_status}</span>
                            </div>
                            <div class="col-md-2">
                                <small>Speed: ${iface.speed}</small><br>
                                <small>MAC: ${iface.mac}</small>
                            </div>
                            <div class="col-md-2">
                                <small>In: ${formatBytes(iface.in_octets)}</small><br>
                                <small>Out: ${formatBytes(iface.out_octets)}</small>
                            </div>
                            <div class="col-md-2">
                                <small class="text-danger">Errors In: ${iface.in_errors || 0}</small><br>
                                <small class="text-danger">Errors Out: ${iface.out_errors || 0}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });

        $('#interfacesContainer').html(html);
    }

    function formatBytes(bytes) {
        if (bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function refreshInterfaces() {
        $('#interfacesContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
            </div>
        `);
        loadInterfaces();
    }
</script>
@endpush
@endsection