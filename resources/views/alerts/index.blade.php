@extends('layouts.app')

@section('title', 'Alerts')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-bell me-2"></i>Alerts
        </h1>
        <div>
            <button class="btn btn-success" onclick="resolveAll()">
                <i class="fas fa-check-double me-1"></i> Resolve All Selected
            </button>
        </div>
    </div>

    <!-- Alert Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Alerts</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Critical</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['critical'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Unresolved</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['unresolved'] ?? 0 }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Today</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['today'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('alerts.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Severity</label>
                        <select name="severity" class="form-select" onchange="this.form.submit()">
                            <option value="">All Severity</option>
                            <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                            <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                            <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" class="form-select" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="device_down" {{ request('type') == 'device_down' ? 'selected' : '' }}>Device Down</option>
                            <option value="device_up" {{ request('type') == 'device_up' ? 'selected' : '' }}>Device Up</option>
                            <option value="warranty_expiry" {{ request('type') == 'warranty_expiry' ? 'selected' : '' }}>Warranty Expiry</option>
                            <option value="amc_expiry" {{ request('type') == 'amc_expiry' ? 'selected' : '' }}>AMC Expiry</option>
                            <option value="port_down" {{ request('type') == 'port_down' ? 'selected' : '' }}>Port Down</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="resolved" class="form-select" onchange="this.form.submit()">
                            <option value="">All Status</option>
                            <option value="0" {{ request('resolved') === '0' ? 'selected' : '' }}>Unresolved</option>
                            <option value="1" {{ request('resolved') === '1' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <a href="{{ route('alerts.index') }}" class="btn btn-secondary w-100">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Alert List
            </h6>
            <span class="badge bg-primary">{{ $alerts->total() }} alerts</span>
        </div>
        <div class="card-body">
            @if($alerts->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover" id="alertsTable">
                    <thead>
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAll">
                            </th>
                            <th>Severity</th>
                            <th>Title</th>
                            <th>Device</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alerts as $alert)
                        <tr class="{{ !$alert->is_read ? 'table-active fw-bold' : '' }}">
                            <td>
                                @if(!$alert->is_resolved)
                                <input type="checkbox" class="alert-checkbox" value="{{ $alert->id }}">
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{
                                    $alert->severity === 'critical' ? 'danger' :
                                    ($alert->severity === 'high' ? 'warning' :
                                    ($alert->severity === 'medium' ? 'info' : 'secondary'))
                                }} px-2 py-1">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('alerts.show', $alert->id) }}" class="text-decoration-none">
                                    {{ $alert->title }}
                                </a>
                                @if(!$alert->is_read)
                                    <span class="badge bg-danger ms-1" style="font-size: 0.65rem;">NEW</span>
                                @endif
                            </td>
                            <td>
                                @if($alert->device)
                                    <a href="{{ route('devices.show', $alert->device_id) }}" class="text-decoration-none">
                                        {{ $alert->device->name }}
                                    </a>
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <span title="{{ $alert->created_at->format('d-M-Y H:i:s') }}" data-bs-toggle="tooltip">
                                    {{ $alert->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @if($alert->is_resolved)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Resolved
                                    </span>
                                    @if($alert->resolvedBy)
                                        <br><small class="text-muted">by {{ $alert->resolvedBy->name }}</small>
                                    @endif
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('alerts.show', $alert->id) }}" class="btn btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if(!$alert->is_resolved)
                                    <button class="btn btn-success" onclick="resolveAlert({{ $alert->id }})" title="Resolve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-danger" onclick="deleteAlert({{ $alert->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted small">
                    Showing {{ $alerts->firstItem() ?? 0 }} to {{ $alerts->lastItem() ?? 0 }} of {{ $alerts->total() }} alerts
                </div>
                {{ $alerts->links('pagination::bootstrap-5') }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="fas fa-bell-slash fa-4x text-muted mb-3"></i>
                <h5>No Alerts Found</h5>
                <p class="text-muted">
                    @if(request()->hasAny(['severity', 'type', 'resolved']))
                        No alerts match your filters. 
                        <a href="{{ route('alerts.index') }}">Clear filters</a>
                    @else
                        All clear! No alerts at this time.
                    @endif
                </p>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Select All checkbox
        $('#selectAll').on('click', function() {
            $('.alert-checkbox').prop('checked', this.checked);
        });
        
        // Uncheck Select All if any individual checkbox is unchecked
        $('.alert-checkbox').on('click', function() {
            if (!$(this).prop('checked')) {
                $('#selectAll').prop('checked', false);
            }
            
            if ($('.alert-checkbox:checked').length === $('.alert-checkbox').length) {
                $('#selectAll').prop('checked', true);
            }
        });
    });

    function resolveAlert(id) {
        Swal.fire({
            title: 'Resolve Alert?',
            text: 'This will mark the alert as resolved.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, resolve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`/alerts/${id}/resolve`, {
                    _token: '{{ csrf_token() }}'
                }, function(response) {
                    Swal.fire({
                        title: 'Resolved!',
                        text: response.message,
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }).fail(function() {
                    Swal.fire('Error!', 'Failed to resolve alert.', 'error');
                });
            }
        });
    }

    function resolveAll() {
        const selectedAlerts = $('.alert-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedAlerts.length === 0) {
            Swal.fire({
                title: 'No Selection',
                text: 'Please select alerts to resolve by checking the boxes.',
                icon: 'info',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        Swal.fire({
            title: 'Resolve Selected Alerts?',
            text: `You are about to resolve ${selectedAlerts.length} alert(s).`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            confirmButtonText: 'Yes, resolve them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('/alerts/bulk-resolve', {
                    _token: '{{ csrf_token() }}',
                    alert_ids: selectedAlerts
                }, function(response) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }).fail(function() {
                    Swal.fire('Error!', 'Failed to resolve alerts.', 'error');
                });
            }
        });
    }

    function deleteAlert(id) {
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
                    url: `/alerts/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'Alert has been deleted.',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    },
                    error: function() {
                        Swal.fire('Error!', 'Failed to delete alert.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection