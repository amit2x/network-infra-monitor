@extends('layouts.app')

@section('title', 'Availability Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line me-2"></i>Availability Report
        </h1>
        <div>
            <button class="btn btn-success" onclick="exportReport('availability')">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
            <button class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Devices</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_devices'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Avg Availability</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['average_availability'] }}%</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">High Availability (99%+)</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['high_availability'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg Response Time</div>
                    <div class="h5 mb-0 font-weight-bold">{{ round($stats['overall_avg_response_time'], 2) }}ms</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        @foreach($allDevices as $dev)
                            <option value="{{ $dev->id }}" {{ request('device_id') == $dev->id ? 'selected' : '' }}>
                                {{ $dev->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="days" class="form-select">
                        <option value="7" {{ $dateRange == 7 ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="14" {{ $dateRange == 14 ? 'selected' : '' }}>Last 14 Days</option>
                        <option value="30" {{ $dateRange == 30 ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="90" {{ $dateRange == 90 ? 'selected' : '' }}>Last 90 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Daily Trend Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Daily Availability Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="dailyTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Device Availability Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="availabilityTable">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>IP Address</th>
                            <th>Total Checks</th>
                            <th>Successful</th>
                            <th>Failed</th>
                            <th>Availability</th>
                            <th>Avg Response</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>
                                <a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a>
                            </td>
                            <td><code>{{ $device->ip_address }}</code></td>
                            <td>{{ $device->total_checks }}</td>
                            <td>{{ $device->successful_checks }}</td>
                            <td>{{ $device->failed_checks }}</td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-{{
                                        $device->availability_percent >= 99 ? 'success' :
                                        ($device->availability_percent >= 95 ? 'warning' : 'danger')
                                    }}" style="width: {{ $device->availability_percent }}%"></div>
                                </div>
                                <small>{{ $device->availability_percent }}%</small>
                            </td>
                            <td>{{ round($device->avg_response_time, 2) }}ms</td>
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
        $('#availabilityTable').DataTable({
            pageLength: 25,
            responsive: true
        });

        // Daily Trend Chart
        new Chart(document.getElementById('dailyTrendChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($dailyTrend, 'date')) !!},
                datasets: [{
                    label: 'Availability %',
                    data: {!! json_encode(array_column($dailyTrend, 'percentage')) !!},
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y'
                }, {
                    label: 'Avg Response Time (ms)',
                    data: {!! json_encode(array_column($dailyTrend, 'avg_response_time')) !!},
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: { display: true, text: 'Availability %' },
                        min: 0,
                        max: 100
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: { display: true, text: 'Response Time (ms)' },
                        grid: { drawOnChartArea: false }
                    }
                }
            }
        });
    });

    function exportReport(type) {
        window.location.href = `/reports/export/${type}`;
    }
</script>
@endpush
@endsection
