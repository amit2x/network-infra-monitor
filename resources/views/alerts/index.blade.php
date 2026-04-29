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
                <i class="fas fa-check-double me-1"></i> Resolve All Unresolved
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="severity" class="form-select">
                        <option value="">All Severity</option>
                        <option value="critical" {{ request('severity') == 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('severity') == 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('severity') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('severity') == 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="device_down" {{ request('type') == 'device_down' ? 'selected' : '' }}>Device Down</option>
                        <option value="warranty_expiry" {{ request('type') == 'warranty_expiry' ? 'selected' : '' }}>Warranty Expiry</option>
                        <option value="amc_expiry" {{ request('type') == 'amc_expiry' ? 'selected' : '' }}>AMC Expiry</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="resolved" class="form-select">
                        <option value="">All Status</option>
                        <option value="0" {{ request('resolved') === '0' ? 'selected' : '' }}>Unresolved</option>
                        <option value="1" {{ request('resolved') === '1' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts List -->
    <div class="card shadow">
        <div class="card-body">
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
                                <input type="checkbox" class="alert-checkbox" value="{{ $alert->id }}">
                            </td>
                            <td>
                                <span class="badge bg-{{
                                    $alert->severity === 'critical' ? 'danger' :
                                    ($alert->severity === 'high' ? 'warning' :
                                    ($alert->severity === 'medium' ? 'info' : 'secondary'))
                                }}">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('alerts.show', $alert->id) }}" class="text-decoration-none">
                                    {{ $alert->title }}
                                </a>
                                @if(!$alert->is_read)
                                    <span class="badge bg-danger ms-1">NEW</span>
                                @endif
                            </td>
                            <td>{{ $alert->device->name ?? 'System' }}</td>
                            <td>
                                <span title="{{ $alert->created_at }}">
                                    {{ $alert->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                @if($alert->is_resolved)
                                    <span class="badge bg-success">Resolved</span>
                                @else
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('alerts.show', $alert->id) }}" class="btn btn-info" title="View">
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
            {{ $alerts->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Select All
        $('#selectAll').on('click', function() {
            $('.alert-checkbox').prop('checked', this.checked);
        });
    });

    function resolveAlert(id) {
        $.post(`/alerts/${id}/resolve`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire('Resolved', 'Alert has been resolved', 'success')
                .then(() => location.reload());
        });
    }

    function resolveAll() {
        const selectedAlerts = $('.alert-checkbox:checked').map(function() {
            return $(this).val();
        }).get();

        if (selectedAlerts.length === 0) {
            Swal.fire('Info', 'Please select alerts to resolve', 'info');
            return;
        }

        $.post('/alerts/bulk-resolve', {
            _token: '{{ csrf_token() }}',
            alert_ids: selectedAlerts
        }, function(response) {
            Swal.fire('Success', response.message, 'success')
                .then(() => location.reload());
        });
    }

    function deleteAlert(id) {
        Swal.fire({
            title: 'Delete Alert?',
            text: 'This cannot be undone',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Delete'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/alerts/${id}`,
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function() {
                        Swal.fire('Deleted', '', 'success')
                            .then(() => location.reload());
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
