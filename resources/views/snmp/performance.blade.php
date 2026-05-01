@extends('layouts.app')

@section('title', 'SNMP Performance - ' . $device->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-line me-2"></i>Performance Metrics - {{ $device->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('snmp.dashboard') }}">SNMP Monitoring</a></li>
                    <li class="breadcrumb-item active">Performance</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success" onclick="refreshData()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Metric Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Avg CPU</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $metrics['avg_cpu'] ?? 'N/A' }}%</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Avg Memory</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $metrics['avg_memory'] ?? 'N/A' }}%</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Max CPU</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $metrics['max_cpu'] ?? 'N/A' }}%</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Data Points</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $metrics['data_points'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">CPU Usage Trend (24h)</h6>
                </div>
                <div class="card-body">
                    <canvas id="cpuChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Memory Usage Trend (24h)</h6>
                </div>
                <div class="card-body">
                    <canvas id="memoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        renderCharts();
    });

    function renderCharts() {
        const cpuData = @json($metrics['cpu_trend'] ?? []);
        const memoryData = @json($metrics['memory_trend'] ?? []);

        if (Object.keys(cpuData).length > 0) {
            new Chart(document.getElementById('cpuChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(cpuData),
                    datasets: [{
                        label: 'CPU %',
                        data: Object.values(cpuData),
                        borderColor: '#4e73df',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }

        if (Object.keys(memoryData).length > 0) {
            new Chart(document.getElementById('memoryChart'), {
                type: 'line',
                data: {
                    labels: Object.keys(memoryData),
                    datasets: [{
                        label: 'Memory %',
                        data: Object.values(memoryData),
                        borderColor: '#1cc88a',
                        backgroundColor: 'rgba(28, 200, 138, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { beginAtZero: true, max: 100 }
                    }
                }
            });
        }
    }

    function refreshData() {
        location.reload();
    }
</script>
@endpush
@endsection