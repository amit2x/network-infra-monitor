@extends('layouts.app')

@section('title', 'Inventory Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-clipboard-list me-2"></i>Inventory Report
        </h1>
        <div>
            <button class="btn btn-success" onclick="exportReport('inventory')">
                <i class="fas fa-download me-1"></i> Export CSV
            </button>
            <button class="btn btn-info" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
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
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['active_ports'] }} / {{ $stats['total_ports'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Critical Devices</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['critical_devices'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Monitored</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['monitored_devices'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        @foreach($deviceTypes as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                {{ ucfirst($type) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="online" {{ request('status') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('status') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="maintenance" {{ request('status') == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="vendor" class="form-select">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor }}" {{ request('vendor') == $vendor ? 'selected' : '' }}>
                                {{ $vendor }}
                            </option>
                        @endforeach
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

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Devices by Type</h6>
                </div>
                <div class="card-body">
                    <canvas id="deviceTypeChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Devices by Status</h6>
                </div>
                <div class="card-body">
                    <canvas id="deviceStatusChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Device List</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="inventoryTable">
                    <thead>
                        <tr>
                            <th>Device Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Vendor/Model</th>
                            <th>IP Address</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Ports</th>
                            <th>Warranty</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td><small class="text-muted">{{ $device->device_code }}</small></td>
                            <td>
                                <a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a>
                            </td>
                            <td><span class="badge bg-primary">{{ ucfirst($device->type) }}</span></td>
                            <td>{{ $device->vendor }} {{ $device->model }}</td>
                            <td><code>{{ $device->ip_address }}</code></td>
                            <td>
                                <span class="badge bg-{{ $device->status === 'online' ? 'success' : 'danger' }}">
                                    {{ ucfirst($device->status) }}
                                </span>
                            </td>
                            <td>{{ $device->location->full_path ?? 'N/A' }}</td>
                            <td>
                                {{ $device->ports->where('status', 'active')->count() }}/{{ $device->ports->count() }}
                            </td>
                            <td>
                                @if($device->warranty_expiry)
                                    @php $daysLeft = now()->diffInDays($device->warranty_expiry, false); @endphp
                                    <span class="text-{{ $daysLeft < 0 ? 'danger' : ($daysLeft < 30 ? 'warning' : 'success') }}">
                                        {{ $device->warranty_expiry->format('d-M-Y') }}
                                    </span>
                                @else
                                    N/A
                                @endif
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
        $('#inventoryTable').DataTable({
            pageLength: 25,
            responsive: true,
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print']
        });

        // Device Type Chart
        new Chart(document.getElementById('deviceTypeChart'), {
            type: 'pie',
            data: {
                labels: {!! json_encode(array_keys($stats['by_type']->toArray())) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['by_type']->toArray())) !!},
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Device Status Chart
        new Chart(document.getElementById('deviceStatusChart'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode(array_keys($stats['by_status']->toArray())) !!},
                datasets: [{
                    data: {!! json_encode(array_values($stats['by_status']->toArray())) !!},
                    backgroundColor: ['#1cc88a', '#e74a3b', '#f6c23e', '#858796']
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
