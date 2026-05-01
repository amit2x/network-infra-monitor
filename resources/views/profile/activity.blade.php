@extends('layouts.app')

@section('title', 'My Activity Log')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-history me-2"></i>My Activity Log
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('profile.edit') }}">Profile</a></li>
                    <li class="breadcrumb-item active">Activity Log</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Profile
            </a>
            <button class="btn btn-outline-danger" onclick="clearActivity()">
                <i class="fas fa-trash me-1"></i> Clear My Activity
            </button>
        </div>
    </div>

    <!-- Activity Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Activities</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Today</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['today'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">This Week</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['this_week'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">This Month</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['this_month'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Filters -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-filter me-2"></i>Filter Activities
                    </h6>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('profile.activity') }}" id="filterForm">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label for="log_name" class="form-label small">Activity Type</label>
                                <select name="log_name" id="log_name" class="form-select form-select-sm">
                                    <option value="">All Types</option>
                                    @foreach($activityTypes as $type)
                                        <option value="{{ $type }}" {{ request('log_name') == $type ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="date_from" class="form-label small">From Date</label>
                                <input type="date" name="date_from" id="date_from"
                                       class="form-control form-control-sm" value="{{ request('date_from') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="date_to" class="form-label small">To Date</label>
                                <input type="date" name="date_to" id="date_to"
                                       class="form-control form-control-sm" value="{{ request('date_to') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="search" class="form-label small">Search</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" id="search"
                                           class="form-control" placeholder="Search activities..."
                                           value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Timeline -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-stream me-2"></i>Activity Timeline
                    </h6>
                    <span class="badge bg-primary">{{ $activities->total() }} records</span>
                </div>
                <div class="card-body">
                    @if($activities->count() > 0)
                        <div class="timeline">
                            @foreach($activities as $activity)
                                <div class="timeline-item mb-4">
                                    <div class="d-flex">
                                        <!-- Activity Icon -->
                                        <div class="timeline-icon me-3">
                                            @php
                                                $iconClass = match($activity->log_name) {
                                                    'device' => 'fa-server',
                                                    'profile' => 'fa-user-edit',
                                                    'auth' => 'fa-sign-in-alt',
                                                    'port' => 'fa-plug',
                                                    'location' => 'fa-map-marker-alt',
                                                    'alert' => 'fa-bell',
                                                    'report' => 'fa-file-alt',
                                                    'settings' => 'fa-cog',
                                                    'account' => 'fa-user-times',
                                                    'monitoring' => 'fa-chart-line',
                                                    default => 'fa-circle'
                                                };

                                                $bgClass = match($activity->log_name) {
                                                    'device', 'port' => 'primary',
                                                    'profile', 'account' => 'info',
                                                    'auth' => 'success',
                                                    'alert' => 'danger',
                                                    'report' => 'warning',
                                                    'settings' => 'secondary',
                                                    'monitoring' => 'dark',
                                                    default => 'light'
                                                };
                                            @endphp
                                            <span class="badge bg-{{ $bgClass }} p-2 rounded-circle"
                                                  style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                <i class="fas {{ $iconClass }}"></i>
                                            </span>
                                        </div>

                                        <!-- Activity Content -->
                                        <div class="timeline-content flex-grow-1">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $activity->description }}</h6>
                                                    <div class="d-flex flex-wrap align-items-center text-muted small">
                                                        <span class="badge bg-{{ $bgClass }} bg-opacity-10 text-{{ $bgClass }} me-2">
                                                            {{ ucfirst(str_replace('_', ' ', $activity->log_name)) }}
                                                        </span>
                                                        <span class="me-2">
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ $activity->created_at->format('d-M-Y H:i:s') }}
                                                        </span>
                                                        <span>
                                                            <i class="far fa-clock me-1"></i>
                                                            {{ $activity->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <button class="btn btn-sm btn-light"
                                                        onclick="viewDetails({{ $activity->id }})"
                                                        title="View Details">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </div>

                                            <!-- Activity Properties (Expandable) -->
                                            @if($activity->properties)
                                            <div class="mt-2" id="properties-{{ $activity->id }}" style="display: none;">
                                                <div class="bg-light rounded p-2">
                                                    <pre class="mb-0 small"><code>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</code></pre>
                                                </div>
                                            </div>
                                            @endif

                                            <!-- IP and User Agent -->
                                            @if($activity->properties && isset($activity->properties['ip']))
                                            <div class="mt-1 text-muted small">
                                                <i class="fas fa-globe me-1"></i>
                                                {{ $activity->properties['ip'] ?? 'Unknown IP' }}
                                                @if(isset($activity->properties['user_agent']))
                                                    <span class="mx-1">•</span>
                                                    <i class="fas fa-laptop me-1"></i>
                                                    {{ \Illuminate\Support\Str::limit($activity->properties['user_agent'], 60) }}
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        <div class="d-flex justify-content-center mt-4">
                            {{ $activities->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-4x text-muted mb-3"></i>
                            <h5>No Activities Found</h5>
                            <p class="text-muted">
                                @if(request()->hasAny(['log_name', 'date_from', 'date_to', 'search']))
                                    No activities match your filters.
                                    <a href="{{ route('profile.activity') }}">Clear filters</a>
                                @else
                                    Your activities will appear here as you use the system.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Activity Summary -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-pie me-2"></i>Activity Summary
                    </h6>
                </div>
                <div class="card-body">
                    @if(count($stats['by_type']) > 0)
                        <canvas id="activityTypeChart" height="250"></canvas>
                    @else
                        <p class="text-muted text-center">No data to display</p>
                    @endif
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clock me-2"></i>Recent Activities
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 400px; overflow-y: auto;">
                        @forelse($recentActivities as $activity)
                            <div class="list-group-item px-3 py-2">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="small flex-grow-1">
                                        <span class="text-dark">{{ $activity->description }}</span>
                                        <br>
                                        <small class="text-muted">
                                            <i class="far fa-clock me-1"></i>
                                            {{ $activity->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                    <span class="badge bg-{{
                                        $activity->log_name === 'auth' ? 'success' :
                                        ($activity->log_name === 'device' ? 'primary' :
                                        ($activity->log_name === 'alert' ? 'danger' : 'secondary'))
                                    }} ms-2">
                                        {{ ucfirst($activity->log_name) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                <p>No recent activities</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Your Profile</h6>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 80px; height: 80px; background-color: #4e73df;">
                        <span style="font-size: 2rem; color: white; line-height: 80px;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted small mb-2">{{ $user->email }}</p>
                    <div class="mb-2">
                        @foreach($user->roles as $role)
                            <span class="badge bg-primary">{{ ucfirst($role->name) }}</span>
                        @endforeach
                    </div>
                    <a href="{{ route('profile.edit') }}" class="btn btn-sm btn-outline-primary">
                        <i class="fas fa-edit me-1"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Detail Modal -->
<div class="modal fade" id="activityDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Activity Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="activityDetailContent">
                <!-- Loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 17px;
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
    }

    .avatar-circle {
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .bg-opacity-10 {
        --bs-bg-opacity: 0.1;
    }

    .hover-card {
        transition: all 0.3s ease;
    }

    .hover-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize activity type chart
        @if(count($stats['by_type']) > 0)
        const ctx = document.getElementById('activityTypeChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($stats['by_type'])) !!}.map(type =>
                    type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())
                ),
                datasets: [{
                    data: {!! json_encode(array_values($stats['by_type'])) !!},
                    backgroundColor: [
                        '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e',
                        '#e74a3b', '#858796', '#5a5c69', '#6f42c1'
                    ],
                    hoverBackgroundColor: [
                        '#2e59d9', '#17a673', '#2c9faf', '#dda20a',
                        '#be2617', '#6c707e', '#46484f', '#59339d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: {
                                size: 11
                            }
                        }
                    }
                }
            }
        });
        @endif

        // Auto-refresh recent activities every 2 minutes
        setInterval(function() {
            location.reload();
        }, 120000);
    });

    // Toggle activity properties
    function viewDetails(id) {
        const propertiesDiv = document.getElementById(`properties-${id}`);
        if (propertiesDiv) {
            if (propertiesDiv.style.display === 'none') {
                propertiesDiv.style.display = 'block';
            } else {
                propertiesDiv.style.display = 'none';
            }
        }
    }

    // Clear user activities
    function clearActivity() {
        Swal.fire({
            title: 'Clear Activity Log?',
            text: "This will permanently delete all your activity records. This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, clear all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ route("profile.clear-activity") }}',
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Cleared!',
                            text: response.message || 'All your activity records have been deleted.',
                            icon: 'success'
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to clear activity log.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
