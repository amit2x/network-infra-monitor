@extends('layouts.app')

@section('title', 'Port Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plug me-2"></i>Port {{ $port->port_number }} - {{ $device->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.ports.index', $device->id) }}">Ports</a></li>
                    <li class="breadcrumb-item active">Port {{ $port->port_number }}</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-warning" onclick="openPortModal({{ $device->id }}, {{ $port->id }})">
                <i class="fas fa-edit me-1"></i> Edit Port
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Port Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Port Number</label>
                            <div class="h5">{{ $port->port_number }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Port Type</label>
                            <div>
                                <span class="badge bg-{{ $port->type === 'sfp' ? 'info' : 'warning' }} fs-6">
                                    {{ ucfirst(str_replace('_', ' ', $port->type)) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Status</label>
                            <div>
                                <span class="badge bg-{{
                                    $port->status === 'active' ? 'success' :
                                    ($port->status === 'down' ? 'danger' :
                                    ($port->status === 'disabled' ? 'secondary' : 'light text-dark'))
                                }} fs-6">
                                    {{ ucfirst($port->status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Service Name</label>
                            <div>{{ $port->service_name ?? 'Not configured' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Connected Device</label>
                            <div>{{ $port->connected_device ?? 'Not configured' }}</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">VLAN ID</label>
                            <div>{{ $port->vlan_id ?? 'Not configured' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Speed</label>
                            <div>{{ $port->speed_mbps ? $port->speed_mbps . ' Mbps' : 'Not configured' }}</div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Last Updated</label>
                            <div>{{ $port->updated_at->format('d-M-Y H:i') }}</div>
                        </div>
                    </div>

                    @if($port->description)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Description</label>
                        <p>{{ $port->description }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Device</small>
                        <div><a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">IP Address</small>
                        <div><code>{{ $device->ip_address }}</code></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Location</small>
                        <div>{{ $device->location->full_path }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
