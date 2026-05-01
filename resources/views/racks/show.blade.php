@extends('layouts.app')

@section('title', 'Rack View - ' . $rack->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-server me-2"></i>{{ $rack->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('racks.index') }}">Racks</a></li>
                    <li class="breadcrumb-item active">{{ $rack->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button class="btn btn-success" onclick="addDevice()">
                <i class="fas fa-plus me-1"></i> Mount Device
            </button>
            <a href="{{ route('racks.edit', $rack->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Main Rack View -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server me-2"></i>Rack View
                    </h6>
                    <div>
                        <button class="btn btn-sm btn-outline-primary" onclick="switchSide('front')" id="frontBtn">
                            <i class="fas fa-eye me-1"></i> Front
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="switchSide('rear')" id="rearBtn">
                            <i class="fas fa-arrow-right me-1"></i> Rear
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="text-center bg-dark text-white py-2">
                        <strong>{{ $rack->name }}</strong> ({{ $rack->total_units }}U) - 
                        <span id="currentSide">Front View</span>
                    </div>
                    
                    <!-- Front Rack View -->
                    <div id="rackFrontView" class="rack-visualization">
                        @include('racks.partials.rack-grid', ['side' => 'front'])
                    </div>
                    
                    <!-- Rear Rack View -->
                    <div id="rackRearView" class="rack-visualization" style="display:none;">
                        @include('racks.partials.rack-grid', ['side' => 'rear'])
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between small text-muted">
                        <span>
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $rack->location->full_path ?? $rack->location->name }}
                        </span>
                        <span>
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $rack->rackItems->count() }} device(s) mounted
                        </span>
                        <span>
                            <i class="fas fa-qrcode me-1"></i>
                            {{ $rack->rack_code }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Rack Info -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rack Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td class="text-muted">Name</td><td>{{ $rack->name }}</td></tr>
                        <tr><td class="text-muted">Code</td><td><span class="badge bg-secondary">{{ $rack->rack_code }}</span></td></tr>
                        <tr><td class="text-muted">Location</td><td>{{ $rack->location->name ?? 'N/A' }}</td></tr>
                        <tr><td class="text-muted">Size</td><td>{{ $rack->total_units }}U</td></tr>
                        <tr>
                            <td class="text-muted">Utilization</td>
                            <td>
                                @php
                                    $usedUnits = $rack->rackItems->sum('unit_height');
                                    $utilization = $rack->total_units > 0 ? round(($usedUnits / $rack->total_units) * 100, 1) : 0;
                                @endphp
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $utilization > 80 ? 'danger' : ($utilization > 50 ? 'warning' : 'success') }}"
                                         style="width: {{ $utilization }}%"></div>
                                </div>
                                <small>{{ $usedUnits }}/{{ $rack->total_units }}U ({{ $utilization }}%)</small>
                            </td>
                        </tr>
                        @if($rack->description)
                        <tr><td class="text-muted">Description</td><td>{{ $rack->description }}</td></tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Mounted Devices -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Mounted Devices</h6>
                </div>
                <div class="card-body p-0" style="max-height: 400px; overflow-y: auto;">
                    @forelse($rack->rackItems as $item)
                    <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
                        <div>
                            <a href="{{ route('devices.show', $item->device_id) }}" class="text-decoration-none">
                                <i class="fas fa-server me-1"></i>{{ $item->device->name }}
                            </a>
                            <br>
                            <small class="text-muted">
                                U{{ $item->unit_start }}-U{{ $item->unit_start + $item->unit_height - 1 }} 
                                ({{ $item->side }})
                            </small>
                        </div>
                        <button class="btn btn-sm btn-outline-danger" 
                                onclick="removeDevice({{ $item->device_id }}, '{{ $item->device->name }}')"
                                title="Remove from rack">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    @empty
                    <div class="text-center py-3 text-muted">
                        <small>No devices mounted</small>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Other Racks in Same Location -->
            @if($locationRacks->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Nearby Racks</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($locationRacks as $nearbyRack)
                        <a href="{{ route('racks.show', $nearbyRack->id) }}" 
                           class="list-group-item list-group-item-action">
                            <i class="fas fa-server me-2"></i>{{ $nearbyRack->name }}
                            <span class="badge bg-secondary float-end">{{ $nearbyRack->total_units }}U</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Device Modal -->
<div class="modal fade" id="addDeviceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mount Device in Rack</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addDeviceForm">
                    <div class="mb-3">
                        <label for="deviceSelect" class="form-label">Select Device</label>
                        <select id="deviceSelect" class="form-select" required>
                            <option value="">Choose device...</option>
                            @foreach($availableDevices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->type }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="unitStart" class="form-label">Starting U Position</label>
                            <input type="number" id="unitStart" class="form-control" 
                                   min="1" max="{{ $rack->total_units }}" required>
                        </div>
                        <div class="col-md-6">
                            <label for="unitHeight" class="form-label">Height (U)</label>
                            <input type="number" id="unitHeight" class="form-control" 
                                   value="1" min="1" max="{{ $rack->total_units }}" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="side" class="form-label">Side</label>
                        <select id="side" class="form-select" required>
                            <option value="front">Front</option>
                            <option value="rear">Rear</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="confirmAddDevice()">
                    <i class="fas fa-plus me-1"></i> Mount Device
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .rack-visualization {
        padding: 10px;
        background: #2c3e50;
    }
    .rack-row {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        border-bottom: 1px solid #34495e;
        cursor: pointer;
        transition: all 0.2s;
    }
    .rack-row:hover {
        background: rgba(255,255,255,0.1);
    }
    .rack-row.occupied {
        font-weight: bold;
    }
    .rack-unit-number {
        width: 50px;
        text-align: center;
        color: #95a5a6;
        font-size: 12px;
    }
    .rack-unit-content {
        flex: 1;
        padding: 5px 10px;
        color: white;
        font-size: 12px;
    }
</style>
@endpush

@push('scripts')
<script>
    let currentSide = 'front';

    function switchSide(side) {
        currentSide = side;
        if (side === 'front') {
            $('#rackFrontView').show();
            $('#rackRearView').hide();
            $('#frontBtn').removeClass('btn-outline-primary').addClass('btn-primary');
            $('#rearBtn').removeClass('btn-secondary').addClass('btn-outline-secondary');
            $('#currentSide').text('Front View');
        } else {
            $('#rackFrontView').hide();
            $('#rackRearView').show();
            $('#rearBtn').removeClass('btn-outline-secondary').addClass('btn-secondary');
            $('#frontBtn').removeClass('btn-primary').addClass('btn-outline-primary');
            $('#currentSide').text('Rear View');
        }
    }

    function addDevice() {
        const modal = new bootstrap.Modal(document.getElementById('addDeviceModal'));
        modal.show();
    }

    function confirmAddDevice() {
        const deviceId = $('#deviceSelect').val();
        const unitStart = $('#unitStart').val();
        const unitHeight = $('#unitHeight').val();
        const side = $('#side').val();

        if (!deviceId || !unitStart) {
            Swal.fire('Error', 'Please fill all fields', 'error');
            return;
        }

        $.ajax({
            url: `/racks/{{ $rack->id }}/devices/${deviceId}/add`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            data: {
                unit_start: unitStart,
                unit_height: unitHeight,
                side: side
            },
            success: function(response) {
                bootstrap.Modal.getInstance(document.getElementById('addDeviceModal')).hide();
                Swal.fire('Success', response.message, 'success')
                    .then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to add device', 'error');
            }
        });
    }

    function removeDevice(deviceId, deviceName) {
        Swal.fire({
            title: 'Remove Device?',
            text: `Remove "${deviceName}" from rack?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/racks/{{ $rack->id }}/devices/${deviceId}/remove`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        Swal.fire('Removed', response.message, 'success')
                            .then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', xhr.responseJSON?.message || 'Failed to remove device', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection