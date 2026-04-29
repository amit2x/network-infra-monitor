@extends('layouts.app')

@section('title', 'Device Details - ' . $device->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-server me-2"></i>{{ $device->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
                    <li class="breadcrumb-item active">{{ $device->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button class="btn btn-success" onclick="pingDevice({{ $device->id }})">
                <i class="fas fa-broadcast-tower me-1"></i> Ping Now
            </button>
            <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button class="btn btn-danger" onclick="deleteDevice({{ $device->id }})">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <!-- Device Status Banner -->
    <div class="alert alert-{{ $device->status === 'online' ? 'success' : ($device->status === 'offline' ? 'danger' : 'warning') }} shadow mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-{{ $device->status === 'online' ? 'check-circle' : 'exclamation-triangle' }} fa-2x"></i>
            </div>
            <div>
                <h5 class="alert-heading mb-1">Device is {{ ucfirst($device->status) }}</h5>
                <p class="mb-0">Last Updated: {{ $device->updated_at->diffForHumans() }}</p>
            </div>
            @if($device->status === 'offline')
            <div class="ms-auto">
                <a href="{{ route('alerts.index') }}?device_id={{ $device->id }}" class="btn btn-light btn-sm">
                    <i class="fas fa-exclamation-circle me-1"></i> View Alerts
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Device Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Device Code</label>
                            <div class="fw-bold">{{ $device->device_code }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Type</label>
                            <div>
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $device->type)) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Vendor</label>
                            <div class="fw-bold">{{ $device->vendor }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Model</label>
                            <div class="fw-bold">{{ $device->model }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Serial Number</label>
                            <div class="text-monospace">{{ $device->serial_number }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">IP Address</label>
                            <div class="text-monospace">{{ $device->ip_address }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">MAC Address</label>
                            <div class="text-monospace">{{ $device->mac_address ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Firmware Version</label>
                            <div>{{ $device->firmware_version ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Deployment -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location & Deployment</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location</label>
                            <div>
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $device->location->full_path }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location Type</label>
                            <div>
                                <span class="badge bg-info">{{ ucfirst($device->location->type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Procurement Date</label>
                            <div>{{ $device->procurement_date ? $device->procurement_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Installation Date</label>
                            <div>{{ $device->installation_date ? $device->installation_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lifecycle Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lifecycle Management</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Warranty Expiry</label>
                            <div>
                                @if($device->warranty_expiry)
                                    @php
                                        $warrantyDays = now()->diffInDays($device->warranty_expiry, false);
                                    @endphp
                                    <span class="fw-bold {{ $warrantyDays <= 30 ? 'text-danger' : '' }}">
                                        {{ $device->warranty_expiry->format('d-M-Y') }}
                                    </span>
                                    @if($warrantyDays <= 30 && $warrantyDays > 0)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Expiring in {{ $warrantyDays }} days
                                        </span>
                                    @elseif($warrantyDays <= 0)
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">AMC Expiry</label>
                            <div>
                                @if($device->amc_expiry)
                                    @php
                                        $amcDays = now()->diffInDays($device->amc_expiry, false);
                                    @endphp
                                    <span class="fw-bold {{ $amcDays <= 30 ? 'text-danger' : '' }}">
                                        {{ $device->amc_expiry->format('d-M-Y') }}
                                    </span>
                                    @if($amcDays <= 30 && $amcDays > 0)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Expiring in {{ $amcDays }} days
                                        </span>
                                    @elseif($amcDays <= 0)
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">End of Life Date</label>
                            <div>{{ $device->eol_date ? $device->eol_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Critical Device</label>
                            <div>
                                @if($device->is_critical)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            @if($device->remarks)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Remarks</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $device->remarks }}</p>
                </div>
            </div>
            @endif
        </div>

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Total Ports</div>
                                <div class="h4 mb-0 text-primary">{{ $device->ports->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Active Ports</div>
                                <div class="h4 mb-0 text-success">{{ $device->ports->where('status', 'active')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Monitoring</div>
                                <div class="h4 mb-0">
                                    <span class="text-{{ $device->monitoring_enabled ? 'success' : 'danger' }}">
                                        {{ $device->monitoring_enabled ? 'On' : 'Off' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Created</div>
                                <div class="small mb-0">{{ $device->created_at->format('d-M-Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Monitoring Logs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Monitoring</h6>
                    <a href="{{ route('monitoring.logs', ['device_id' => $device->id]) }}" class="btn btn-sm btn-light">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @forelse($monitoringLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    @if($log->response_time_ms)
                                    <small class="text-muted ms-2">{{ number_format($log->response_time_ms, 2) }}ms</small>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">{{ $log->message }}</small>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No monitoring data available
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Alerts</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($alerts as $alert)
                        <a href="{{ route('alerts.show', $alert->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                                <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                            </div>
                            <small>{{ $alert->title }}</small>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No alerts
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Device</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $device->name }}</strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone. All associated data including ports, logs, and alerts will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('devices.destroy', $device->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Device</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function pingDevice(id) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Pinging...';

        $.ajax({
            url: `/devices/${id}/ping`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                const status = response.data.success ? 'success' : 'danger';
                Swal.fire({
                    title: response.data.success ? 'Device Reachable' : 'Device Unreachable',
                    html: `
                        <div class="text-start">
                            <p><strong>Status:</strong> ${response.data.message}</p>
                            <p><strong>Response Time:</strong> ${response.data.response_time.toFixed(2)}ms</p>
                        </div>
                    `,
                    icon: response.data.success ? 'success' : 'error',
                    timer: 3000,
                    showConfirmButton: false
                });

                setTimeout(() => location.reload(), 2000);
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to ping device', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        });
    }

    function deleteDevice(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        modal.show();
    }
</script>
@endpush
@endsection
