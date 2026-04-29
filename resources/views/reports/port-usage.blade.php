@extends('layouts.app')

@section('title', 'Port Usage Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-plug me-2"></i>Port Usage Report
        </h1>
        <div>
            <button class="btn btn-success" onclick="exportReport('port-usage')">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Overall Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_ports'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_active'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Down Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_down'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Utilization</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['overall_utilization'] }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Usage Chart -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Port Utilization by Device</h6>
                </div>
                <div class="card-body">
                    <canvas id="utilizationChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Port Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="portDistributionChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Usage Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="portUsageTable">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Location</th>
                            <th>Total Ports</th>
                            <th>Active</th>
                            <th>Free</th>
                            <th>Down</th>
                            <th>Disabled</th>
                            <th>Utilization</th>
                            <th>Copper/SFP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>
                                <a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a>
                            </td>
                            <td>{{ $device->location->full_path ?? 'N/A' }}</td>
                            <td>{{ $device->total_ports }}</td>
                            <td><span class="badge bg-success">{{ $device->active_ports }}</span></td>
                            <td><span class="badge bg-secondary">{{ $device->free_ports }}</span></td>
                            <td><span class="badge bg-danger">{{ $device->down_ports }}</span></td>
                            <td><span class="badge bg-dark">{{ $device->disabled_ports }}</span></td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{
                                        $device->utilization_percent > 80 ? 'danger' :
                                        ($device->utilization_percent > 50 ? 'warning' : 'success')
                                    }}" style="width: {{ $device->utilization_percent }}%"></div>
                                </div>
                                <small>{{ $device->utilization_percent }}%</small>
                            </td>
                            <td>
                                <small>Cu: {{ $device->copper_ports }}</small><br>
                                <small>SFP: {{ $device->sfp_ports }}</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('#portUsageTable').DataTable({
            pageLength: 25,
            responsive: true
        });

        // Utilization Chart
        new Chart(document.getElementById('utilizationChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($devices->pluck('name')) !!},
                datasets: [{
                    label: 'Utilization %',
                    data: {!! json_encode($devices->pluck('utilization_percent')) !!},
                    backgroundColor: '#4e73df'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Port Distribution Chart
        new Chart(document.getElementById('portDistributionChart'), {
            type: 'doughnut',
            data: {
                labels: ['Active', 'Free', 'Down', 'Disabled'],
                datasets: [{
                    data: [
                        {{ $stats['total_active'] }},
                        {{ $stats['total_free'] }},
                        {{ $stats['total_down'] }},
                        {{ $devices->sum('disabled_ports') }}
                    ],
                    backgroundColor: ['#1cc88a', '#858796', '#e74a3b', '#5a5c69']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });

    function exportReport(type) {
        window.location.href = `/reports/export/${type}`;
    }
</script>
@endpush
@endsection
