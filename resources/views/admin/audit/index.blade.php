@extends('layouts.app')

@section('title', 'Audit Trail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history me-2"></i>Audit Trail
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Audit Trail</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.audit.export', request()->query()) }}" class="btn btn-success">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
            <button class="btn btn-danger" onclick="cleanAudit()">
                <i class="fas fa-broom me-1"></i> Clean Old Records
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['today'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Success</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['success_count'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Failed</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['failed_count'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" id="filterForm">
                <div class="row g-2">
                    <div class="col-md-2">
                        <select name="action" class="form-select form-select-sm">
                            <option value="">All Actions</option>
                            @foreach($actions as $action)
                                <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $action)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="module" class="form-select form-select-sm">
                            <option value="">All Modules</option>
                            @foreach($modules as $module)
                                <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                    {{ $module }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="user_id" class="form-select form-select-sm">
                            <option value="">All Users</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control form-control-sm"
                               value="{{ request('date_from') }}" placeholder="From Date">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control form-control-sm"
                               value="{{ request('date_to') }}" placeholder="To Date">
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="search" class="form-control form-control-sm"
                               value="{{ request('search') }}" placeholder="Search...">
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="fas fa-filter me-1"></i> Apply Filters
                        </button>
                        <a href="{{ route('admin.audit.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Audit Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="auditTable">
                    <thead>
                        <tr>
                            <th>Date/Time</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Details</th>
                            <th>IP Address</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($activities as $activity)
                        <tr>
                            <td>
                                <span title="{{ $activity->performed_at }}">
                                    {{ $activity->performed_at->format('d-M-Y H:i:s') }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-circle me-2" style="width: 30px; height: 30px; min-width: 30px; background-color: #4e73df;">
                                        <span style="font-size: 12px; color: white;">
                                            {{ $activity->user_name ? strtoupper(substr($activity->user_name, 0, 2)) : 'S' }}
                                        </span>
                                    </div>
                                    <div>
                                        <div class="small">{{ $activity->user_name ?? 'System' }}</div>
                                        @if($activity->user_role)
                                            <small class="text-muted">{{ ucfirst($activity->user_role) }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $activity->action_color }}">
                                    <i class="fas {{ $activity->action_icon }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                </span>
                            </td>
                            <td>
                                <i class="fas {{ $activity->module_icon }} me-1 text-muted"></i>
                                {{ $activity->module }}
                            </td>
                            <td>
                                <a href="{{ route('admin.audit.show', $activity->id) }}" class="text-decoration-none">
                                    {{ $activity->description }}
                                </a>
                                @if($activity->module_name)
                                    <br>
                                    <small class="text-muted">ID: {{ $activity->module_id }} | {{ $activity->module_name }}</small>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="fas fa-globe me-1"></i>
                                    {{ $activity->ip_address }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $activity->status === 'success' ? 'success' : 'danger' }}">
                                    {{ ucfirst($activity->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-history fa-3x text-muted mb-3"></i>
                                <h5>No Audit Records Found</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $activities->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

<!-- Clean Audit Modal -->
<div class="modal fade" id="cleanAuditModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Clean Old Audit Records</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="cleanDays" class="form-label">Delete records older than (days)</label>
                    <select id="cleanDays" class="form-select">
                        <option value="30">30 days</option>
                        <option value="60">60 days</option>
                        <option value="90">90 days</option>
                        <option value="180">180 days</option>
                        <option value="365">1 year</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="confirmClean()">
                    <i class="fas fa-broom me-1"></i> Clean Records
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

    function cleanAudit() {
        const modal = new bootstrap.Modal(document.getElementById('cleanAuditModal'));
        modal.show();
    }

    function confirmClean() {
        const days = $('#cleanDays').val();

        Swal.fire({
            title: 'Are you sure?',
            text: `This will delete all audit records older than ${days} days.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete them!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("admin.audit.clean") }}',
                    method: 'POST',
                    data: {
                        days: days,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire('Cleaned!', response.message, 'success')
                            .then(() => location.reload());
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
