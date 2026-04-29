@extends('layouts.app')

@section('title', 'Devices')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-server me-2"></i>Devices
        </h1>
        <div>
            <a href="{{ route('devices.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Device
            </a>
            <button class="btn btn-success" onclick="exportDevices()">
                <i class="fas fa-download me-1"></i> Export
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('devices.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                            <input type="text" name="search" class="form-control"
                                   placeholder="Search name, IP, or serial..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($deviceTypes as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                            <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                            <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="location_id" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->full_path }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Device Cards Grid -->
    <div class="row" id="devicesGrid">
        @forelse($devices as $device)
        <div class="col-xl-3 col-md-4 col-sm-6 mb-4">
            <div class="card shadow border-left-{{
                $device->status === 'online' ? 'success' :
                ($device->status === 'offline' ? 'danger' :
                ($device->status === 'maintenance' ? 'warning' : 'secondary'))
            }} h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="card-title mb-1">
                                <a href="{{ route('devices.show', $device->id) }}" class="text-decoration-none">
                                    {{ $device->name }}
                                </a>
                            </h6>
                            <small class="text-muted">{{ $device->device_code }}</small>
                        </div>
                        <span class="badge bg-{{
                            $device->status === 'online' ? 'success' :
                            ($device->status === 'offline' ? 'danger' : 'warning')
                        }}">
                            {{ ucfirst($device->status) }}
                        </span>
                    </div>

                    <div class="mb-2">
                        <small class="text-muted d-block">
                            <i class="fas fa-network-wired me-1"></i> {{ $device->ip_address }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-tag me-1"></i> {{ $device->vendor }} {{ $device->model }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="fas fa-map-marker-alt me-1"></i> {{ $device->location->full_path }}
                        </small>
                    </div>

                    @if($device->ports_count > 0)
                    <div class="mt-3">
                        <small class="text-muted">Port Utilization</small>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-{{
                                $device->port_utilization_percent > 80 ? 'danger' :
                                ($device->port_utilization_percent > 50 ? 'warning' : 'success')
                            }}" role="progressbar"
                                 style="width: {{ $device->port_utilization_percent }}%">
                            </div>
                        </div>
                        <small class="text-muted float-end">
                            {{ $device->active_ports_count }}/{{ $device->ports_count }}
                        </small>
                    </div>
                    @endif

                    <div class="mt-3">
                        <div class="btn-group w-100">
                            <button class="btn btn-sm btn-outline-primary" onclick="pingDevice({{ $device->id }})">
                                <i class="fas fa-broadcast-tower"></i> Ping
                            </button>
                            <a href="{{ route('devices.ports.index', $device->id) }}"
                               class="btn btn-sm btn-outline-info">
                                <i class="fas fa-plug"></i> Ports
                            </a>
                            <a href="{{ route('devices.edit', $device->id) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-server fa-4x text-muted mb-3"></i>
                <h4>No Devices Found</h4>
                <p class="text-muted">Start monitoring by adding your first network device.</p>
                <a href="{{ route('devices.create') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-plus me-1"></i> Add First Device
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $devices->links('pagination::bootstrap-5') }}
    </div>
</div>

@push('scripts')
<script>
    function pingDevice(id) {
        const button = event.target.closest('button');
        const originalHtml = button.innerHTML;

        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Pinging...';

        $.ajax({
            url: `/devices/${id}/ping`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                const status = response.data.success ? 'success' : 'danger';
                const icon = response.data.success ? 'fa-check-circle' : 'fa-times-circle';
                const message = response.data.message;

                Swal.fire({
                    title: response.data.success ? 'Device Reachable' : 'Device Unreachable',
                    text: `Response Time: ${response.data.response_time.toFixed(2)}ms`,
                    icon: response.data.success ? 'success' : 'error',
                    confirmButtonText: 'OK'
                });

                // Reload page after 2 seconds to update status
                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: 'Failed to ping device',
                    icon: 'error'
                });
            },
            complete: function() {
                button.disabled = false;
                button.innerHTML = originalHtml;
            }
        });
    }


</script>
@endpush
@endsection
