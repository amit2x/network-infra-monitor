@extends('layouts.app')

@section('title', 'Expiry Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-alt me-2"></i>Expiry Report
        </h1>
        <div>
            <button class="btn btn-success" onclick="exportReport('expiry')">
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
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Expiring</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_expiring'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Warranty Expired</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['warranty_expired'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">AMC Expired</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['amc_expired'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Critical</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['critical_expiring'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Expiry Type</label>
                    <select name="expiry_type" class="form-select">
                        <option value="">All</option>
                        <option value="warranty" {{ request('expiry_type') == 'warranty' ? 'selected' : '' }}>Warranty</option>
                        <option value="amc" {{ request('expiry_type') == 'amc' ? 'selected' : '' }}>AMC</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Days Range</label>
                    <select name="days" class="form-select">
                        <option value="30" {{ $daysFilter == 30 ? 'selected' : '' }}>Next 30 Days</option>
                        <option value="60" {{ $daysFilter == 60 ? 'selected' : '' }}>Next 60 Days</option>
                        <option value="90" {{ $daysFilter == 90 ? 'selected' : '' }} selected>Next 90 Days</option>
                        <option value="180" {{ $daysFilter == 180 ? 'selected' : '' }}>Next 180 Days</option>
                        <option value="365" {{ $daysFilter == 365 ? 'selected' : '' }}>Next 365 Days</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Expiry Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="expiryTable">
                    <thead>
                        <tr>
                            <th>Device</th>
                            <th>Type</th>
                            <th>Location</th>
                            <th>Warranty Expiry</th>
                            <th>Days Left (Warranty)</th>
                            <th>AMC Expiry</th>
                            <th>Days Left (AMC)</th>
                            <th>Critical</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $device)
                        <tr>
                            <td>
                                <a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a>
                            </td>
                            <td><span class="badge bg-primary">{{ ucfirst($device->type) }}</span></td>
                            <td>{{ $device->location->full_path ?? 'N/A' }}</td>
                            <td>
                                @if($device->warranty_expiry)
                                    <span class="text-{{ $device->warranty_days_left < 0 ? 'danger' : ($device->warranty_days_left < 30 ? 'warning' : 'success') }}">
                                        {{ $device->warranty_expiry->format('d-M-Y') }}
                                    </span>
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($device->warranty_days_left !== null)
                                    @if($device->warranty_days_left < 0)
                                        <span class="badge bg-danger">Expired {{ abs($device->warranty_days_left) }} days ago</span>
                                    @elseif($device->warranty_days_left <= 30)
                                        <span class="badge bg-warning text-dark">{{ $device->warranty_days_left }} days</span>
                                    @else
                                        <span class="badge bg-success">{{ $device->warranty_days_left }} days</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($device->amc_expiry)
                                    {{ $device->amc_expiry->format('d-M-Y') }}
                                @else
                                    N/A
                                @endif
                            </td>
                            <td>
                                @if($device->amc_days_left !== null)
                                    @if($device->amc_days_left < 0)
                                        <span class="badge bg-danger">Expired</span>
                                    @elseif($device->amc_days_left <= 30)
                                        <span class="badge bg-warning text-dark">{{ $device->amc_days_left }} days</span>
                                    @else
                                        <span class="badge bg-success">{{ $device->amc_days_left }} days</span>
                                    @endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($device->is_critical)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
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
        $('#expiryTable').DataTable({
            pageLength: 25,
            responsive: true,
            order: [[3, 'asc']]
        });
    });

    function exportReport(type) {
        window.location.href = `/reports/export/${type}`;
    }
</script>
@endpush
@endsection
