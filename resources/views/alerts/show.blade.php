@extends('layouts.app')

@section('title', 'Alert Details - ' . $alert->title)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-bell me-2"></i>Alert Details
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('alerts.index') }}">Alerts</a></li>
                    <li class="breadcrumb-item active">Alert #{{ $alert->id }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            @if(!$alert->is_resolved)
            <button class="btn btn-success" onclick="resolveAlert()">
                <i class="fas fa-check me-1"></i> Resolve
            </button>
            @endif
            <button class="btn btn-danger" onclick="deleteAlert()">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            <a href="{{ route('alerts.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Alert Details Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Alert Information
                    </h6>
                    <div>
                        @if(!$alert->is_read)
                            <span class="badge bg-danger me-1">UNREAD</span>
                        @endif
                        @if($alert->is_resolved)
                            <span class="badge bg-success">RESOLVED</span>
                        @else
                            <span class="badge bg-warning text-dark">PENDING</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <!-- Alert Header -->
                    <div class="alert alert-{{ 
                        $alert->severity === 'critical' ? 'danger' : 
                        ($alert->severity === 'high' ? 'warning' : 
                        ($alert->severity === 'medium' ? 'info' : 'secondary')) 
                    }} mb-4">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <i class="fas fa-{{ 
                                    $alert->type === 'device_down' ? 'server' : 
                                    ($alert->type === 'device_up' ? 'check-circle' : 
                                    ($alert->type === 'warranty_expiry' ? 'calendar' : 
                                    ($alert->type === 'amc_expiry' ? 'file-contract' : 'bell'))) 
                                }} fa-2x"></i>
                            </div>
                            <div>
                                <h5 class="alert-heading mb-1">{{ $alert->title }}</h5>
                                <p class="mb-0">{{ $alert->message }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Details -->
                    <div class="row mb-4">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Alert Type</label>
                            <div>
                                <span class="badge bg-info px-3 py-2">
                                    <i class="fas fa-tag me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $alert->type)) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Severity</label>
                            <div>
                                <span class="badge bg-{{ 
                                    $alert->severity === 'critical' ? 'danger' : 
                                    ($alert->severity === 'high' ? 'warning' : 
                                    ($alert->severity === 'medium' ? 'info' : 'secondary')) 
                                }} px-3 py-2">
                                    <i class="fas fa-{{ 
                                        $alert->severity === 'critical' ? 'exclamation-triangle' : 
                                        ($alert->severity === 'high' ? 'exclamation-circle' : 'info-circle') 
                                    }} me-1"></i>
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Created</label>
                            <div>
                                <i class="far fa-clock me-1 text-muted"></i>
                                {{ $alert->created_at->format('d-M-Y H:i:s') }}
                                <br>
                                <small class="text-muted">({{ $alert->created_at->diffForHumans() }})</small>
                            </div>
                        </div>
                    </div>

                    <!-- Device Information -->
                    @if($alert->device)
                    <hr>
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-server me-2"></i>Related Device
                    </h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Device Name</label>
                            <div>
                                <a href="{{ route('devices.show', $alert->device_id) }}" class="text-decoration-none">
                                    <i class="fas fa-server me-1"></i>
                                    {{ $alert->device->name }}
                                </a>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">IP Address</label>
                            <div>
                                <code>{{ $alert->device->ip_address }}</code>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Type</label>
                            <div>
                                <span class="badge bg-primary">{{ ucfirst($alert->device->type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location</label>
                            <div>
                                <i class="fas fa-map-marker-alt me-1 text-muted"></i>
                                {{ $alert->device->location->full_path ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Status</label>
                            <div>
                                <span class="badge bg-{{ $alert->device->status === 'online' ? 'success' : 'danger' }}">
                                    {{ ucfirst($alert->device->status) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Serial Number</label>
                            <div>
                                <code>{{ $alert->device->serial_number }}</code>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Device Quick Actions -->
                    <div class="mt-2">
                        <a href="{{ route('devices.show', $alert->device_id) }}" class="btn btn-sm btn-info me-2">
                            <i class="fas fa-eye me-1"></i> View Device
                        </a>
                        <a href="{{ route('devices.edit', $alert->device_id) }}" class="btn btn-sm btn-warning me-2">
                            <i class="fas fa-edit me-1"></i> Edit Device
                        </a>
                        <button class="btn btn-sm btn-primary" onclick="pingDevice({{ $alert->device_id }})">
                            <i class="fas fa-broadcast-tower me-1"></i> Ping Now
                        </button>
                    </div>
                    @endif

                    <!-- Resolution Information -->
                    @if($alert->is_resolved)
                    <hr>
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-check-circle me-2 text-success"></i>Resolution Details
                    </h6>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Resolved By</label>
                            <div>
                                <i class="fas fa-user me-1"></i>
                                {{ $alert->resolvedBy->name ?? 'Unknown' }}
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Resolved At</label>
                            <div>
                                <i class="far fa-calendar-check me-1"></i>
                                {{ $alert->resolved_at ? $alert->resolved_at->format('d-M-Y H:i:s') : 'N/A' }}
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small">Time to Resolve</label>
                            <div>
                                <i class="far fa-clock me-1"></i>
                                @if($alert->resolved_at)
                                    {{ $alert->created_at->diffForHumans($alert->resolved_at, true) }}
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Additional Data -->
                    @if($alert->additional_data)
                    <hr>
                    <h6 class="font-weight-bold mb-3">
                        <i class="fas fa-database me-2"></i>Additional Information
                    </h6>
                    <div class="bg-light p-3 rounded">
                        <pre class="mb-0"><code>{{ json_encode($alert->additional_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Related Alerts -->
            @if($alert->device)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Related Alerts for {{ $alert->device->name }}
                    </h6>
                </div>
                <div class="card-body">
                    @php
                        $relatedAlerts = \App\Models\Alert::where('device_id', $alert->device_id)
                            ->where('id', '!=', $alert->id)
                            ->orderBy('created_at', 'desc')
                            ->take(5)
                            ->get();
                    @endphp
                    
                    @if($relatedAlerts->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($relatedAlerts as $related)
                            <a href="{{ route('alerts.show', $related->id) }}" 
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="badge bg-{{ 
                                            $related->severity === 'critical' ? 'danger' : 
                                            ($related->severity === 'high' ? 'warning' : 'info') 
                                        }} me-2">
                                            {{ ucfirst($related->severity) }}
                                        </span>
                                        {{ $related->title }}
                                    </div>
                                    <div>
                                        <small class="text-muted">{{ $related->created_at->diffForHumans() }}</small>
                                        @if($related->is_resolved)
                                            <span class="badge bg-success ms-1">Resolved</span>
                                        @else
                                            <span class="badge bg-warning ms-1">Pending</span>
                                        @endif
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-center text-muted py-3 mb-0">
                            <i class="fas fa-check-circle text-success me-1"></i>
                            No other alerts for this device
                        </p>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- Alert Status Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Alert Status</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($alert->is_resolved)
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h5 class="text-success">Resolved</h5>
                            <p class="text-muted">This alert has been resolved</p>
                        @else
                            <i class="fas fa-exclamation-circle fa-4x text-warning mb-3"></i>
                            <h5 class="text-warning">Pending Action</h5>
                            <p class="text-muted">This alert requires attention</p>
                        @endif
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        @if(!$alert->is_resolved)
                        <button class="btn btn-success btn-lg" onclick="resolveAlert()">
                            <i class="fas fa-check me-1"></i> Resolve Alert
                        </button>
                        @endif
                        @if($alert->device)
                        <a href="{{ route('devices.show', $alert->device_id) }}" class="btn btn-info">
                            <i class="fas fa-server me-1"></i> View Device
                        </a>
                        @endif
                        <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i> All Alerts
                        </a>
                    </div>
                </div>
            </div>

            <!-- Alert Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-stream me-2"></i>Alert Timeline
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <!-- Alert Created -->
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon me-3">
                                    <span class="badge bg-info rounded-circle p-2">
                                        <i class="fas fa-bell"></i>
                                    </span>
                                </div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $alert->created_at->format('d-M-Y H:i:s') }}</small>
                                    <p class="mb-0 fw-bold">Alert Created</p>
                                    <small>{{ $alert->title }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Alert Read (if applicable) -->
                        @if($alert->is_read)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon me-3">
                                    <span class="badge bg-primary rounded-circle p-2">
                                        <i class="fas fa-eye"></i>
                                    </span>
                                </div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $alert->updated_at->format('d-M-Y H:i:s') }}</small>
                                    <p class="mb-0 fw-bold">Alert Read</p>
                                    <small>Alert was viewed</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Alert Resolved (if applicable) -->
                        @if($alert->is_resolved)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-icon me-3">
                                    <span class="badge bg-success rounded-circle p-2">
                                        <i class="fas fa-check"></i>
                                    </span>
                                </div>
                                <div class="timeline-content">
                                    <small class="text-muted">{{ $alert->resolved_at->format('d-M-Y H:i:s') }}</small>
                                    <p class="mb-0 fw-bold">Alert Resolved</p>
                                    <small>Resolved by {{ $alert->resolvedBy->name ?? 'Unknown' }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Actions Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if(!$alert->is_resolved)
                        <button class="btn btn-outline-success" onclick="resolveAlert()">
                            <i class="fas fa-check-circle me-1"></i> Mark as Resolved
                        </button>
                        @endif
                        <button class="btn btn-outline-primary" onclick="markAsRead()">
                            <i class="fas fa-envelope-open me-1"></i> Mark as Read
                        </button>
                        <button class="btn btn-outline-danger" onclick="deleteAlert()">
                            <i class="fas fa-trash me-1"></i> Delete Alert
                        </button>
                        <a href="{{ route('alerts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 25px;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 12px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    .timeline-item {
        position: relative;
    }
    .timeline-icon {
        position: relative;
        z-index: 1;
    }
    .timeline-content {
        flex: 1;
        padding: 10px;
        background: #f8f9fc;
        border-radius: 5px;
    }
</style>
@endpush

@push('scripts')
<script>
    function resolveAlert() {
        Swal.fire({
            title: 'Resolve Alert?',
            text: 'This will mark the alert as resolved.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, resolve it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('{{ route("alerts.resolve", $alert->id) }}', {
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    Swal.fire({
                        title: 'Resolved!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }).fail(function() {
                    Swal.fire('Error!', 'Failed to resolve alert.', 'error');
                });
            }
        });
    }

    function markAsRead() {
        $.post('{{ route("alerts.read", $alert->id) }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire({
                title: 'Done!',
                text: response.message,
                icon: 'success',
                timer: 1500,
                showConfirmButton: false
            }).then(() => location.reload());
        }).fail(function() {
            Swal.fire('Error!', 'Failed to mark as read.', 'error');
        });
    }

    function deleteAlert() {
        Swal.fire({
            title: 'Delete Alert?',
            text: 'This action cannot be undone!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("alerts.destroy", $alert->id) }}',
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'Alert has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.href = '{{ route("alerts.index") }}';
                        });
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete alert.', 'error');
                    }
                });
            }
        });
    }

    function pingDevice(deviceId) {
        Swal.fire({
            title: 'Pinging Device...',
            html: 'Please wait...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.post(`/devices/${deviceId}/ping`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            const icon = response.data.success ? 'success' : 'error';
            const title = response.data.success ? 'Device Reachable' : 'Device Unreachable';
            const message = `Response Time: ${response.data.response_time?.toFixed(2) || 'N/A'}ms`;
            
            Swal.fire({
                title: title,
                text: message,
                icon: icon,
                timer: 3000,
                showConfirmButton: false
            });
        }).fail(function() {
            Swal.fire('Error!', 'Failed to ping device.', 'error');
        });
    }
</script>
@endpush
@endsection