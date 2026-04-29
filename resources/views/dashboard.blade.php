@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['total'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Online Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['online'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Offline Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['offline'] }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Port Utilization</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $portUtilization }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plug fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Status Overview</h6>
                </div>
                <div class="card-body">
                    <canvas id="deviceStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="deviceTypeChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Alerts & Activity -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Alerts</h6>
                    <a href="{{ route('alerts.index') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentAlerts as $alert)
                                <tr class="{{ $alert->is_read ? '' : 'table-active' }}">
                                    <td>{{ $alert->device->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $alert->type === 'device_down' ? 'danger' : 'warning' }}">
                                            {{ str_replace('_', ' ', $alert->type) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'high' ? 'warning' : 'info') }}">
                                            {{ $alert->severity }}
                                        </span>
                                    </td>
                                    <td>{{ $alert->created_at->diffForHumans() }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('devices.create') }}" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-plus-circle mb-2 d-block" style="font-size: 2rem;"></i>
                                Add New Device
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('alerts.index') }}" class="btn btn-warning btn-lg w-100">
                                <i class="fas fa-bell mb-2 d-block" style="font-size: 2rem;"></i>
                                View Alerts
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('reports.inventory') }}" class="btn btn-info btn-lg w-100">
                                <i class="fas fa-clipboard-list mb-2 d-block" style="font-size: 2rem;"></i>
                                Generate Report
                            </a>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="{{ route('locations.create') }}" class="btn btn-success btn-lg w-100">
                                <i class="fas fa-location-dot mb-2 d-block" style="font-size: 2rem;"></i>
                                Add Location
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        // Device Status Chart
        const statusCtx = document.getElementById('deviceStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Online', 'Offline', 'Maintenance', 'Decommissioned'],
                datasets: [{
                    data: [
                        {{ $deviceStats['online'] ?? 0 }},
                        {{ $deviceStats['offline'] ?? 0 }},
                        {{ $deviceStats['maintenance'] ?? 0 }},
                        {{ $deviceStatusDistribution['decommissioned'] ?? 0 }}
                    ],
                    backgroundColor: ['#28a745', '#dc3545', '#ffc107', '#6c757d'],
                    hoverBackgroundColor: ['#218838', '#c82333', '#e0a800', '#5a6268']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Device Type Chart
        const typeCtx = document.getElementById('deviceTypeChart').getContext('2d');
        new Chart(typeCtx, {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($deviceStats['by_type'] ?? [])) !!},
                datasets: [{
                    data: {!! json_encode(array_values($deviceStats['by_type'] ?? [])) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
