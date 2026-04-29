@extends('layouts.app')

@section('title', 'Monitoring Logs')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-chart-line me-2"></i>Monitoring Logs
        </h1>
        <button class="btn btn-success" onclick="runMonitoring()">
            <i class="fas fa-play me-1"></i> Run Monitoring Now
        </button>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <select name="device_id" class="form-select">
                        <option value="">All Devices</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}" {{ request('device_id') == $device->id ? 'selected' : '' }}>
                                {{ $device->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="event_type" class="form-select">
                        <option value="">All Events</option>
                        <option value="ping_check" {{ request('event_type') == 'ping_check' ? 'selected' : '' }}>Ping Check</option>
                        <option value="status_change" {{ request('event_type') == 'status_change' ? 'selected' : '' }}>Status Change</option>
                        <option value="error" {{ request('event_type') == 'error' ? 'selected' : '' }}>Error</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                        <option value="failure" {{ request('status') == 'failure' ? 'selected' : '' }}>Failure</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="logsTable">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Device</th>
                            <th>Event Type</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d-M-Y H:i:s') }}</td>
                            <td>{{ $log->device->name }}</td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $log->event_type)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>
                                @if($log->response_time_ms)
                                    {{ number_format($log->response_time_ms, 2) }} ms
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{ $log->message }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{ $logs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>

@push('scripts')
<script>

    function runMonitoring() {
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Running...';

        $.ajax({
            url: '{{ route("monitoring.run") }}',
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    title: 'Monitoring Complete',
                    html: `
                        <div class="text-start">
                            <p>Devices Checked: ${response.data.checked}</p>
                            <p>Online: ${response.data.online}</p>
                            <p>Offline: ${response.data.offline}</p>
                            <p>Status Changes: ${response.data.status_changes}</p>
                            <p>Alerts Generated: ${response.data.alerts_generated}</p>
                        </div>
                    `,
                    icon: 'success'
                }).then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire('Error', 'Monitoring cycle failed', 'error');
            },
            complete: function() {
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        });
    }
</script>
@endpush
@endsection
