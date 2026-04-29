@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-cogs me-2"></i>System Settings
        </h1>
        <button class="btn btn-info" onclick="showSystemInfo()">
            <i class="fas fa-info-circle me-1"></i> System Info
        </button>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Monitoring Settings -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Monitoring Configuration</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.settings.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monitoring_interval" class="form-label">
                                    Monitoring Interval (minutes)
                                </label>
                                <select name="monitoring_interval" id="monitoring_interval" class="form-select">
                                    <option value="1" {{ $settings['monitoring_interval'] == 1 ? 'selected' : '' }}>1 minute</option>
                                    <option value="5" {{ $settings['monitoring_interval'] == 5 ? 'selected' : '' }}>5 minutes</option>
                                    <option value="10" {{ $settings['monitoring_interval'] == 10 ? 'selected' : '' }}>10 minutes</option>
                                    <option value="15" {{ $settings['monitoring_interval'] == 15 ? 'selected' : '' }}>15 minutes</option>
                                    <option value="30" {{ $settings['monitoring_interval'] == 30 ? 'selected' : '' }}>30 minutes</option>
                                    <option value="60" {{ $settings['monitoring_interval'] == 60 ? 'selected' : '' }}>1 hour</option>
                                </select>
                                <div class="form-text">How often to ping devices</div>
                            </div>
                            <div class="col-md-6">
                                <label for="ping_timeout" class="form-label">
                                    Ping Timeout (seconds)
                                </label>
                                <input type="range" name="ping_timeout" id="ping_timeout"
                                       class="form-range" min="1" max="10"
                                       value="{{ $settings['ping_timeout'] }}"
                                       oninput="document.getElementById('timeoutValue').textContent = this.value + 's'">
                                <div class="text-center">
                                    <span class="badge bg-primary" id="timeoutValue">{{ $settings['ping_timeout'] }}s</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="log_retention_days" class="form-label">
                                    Log Retention (days)
                                </label>
                                <input type="number" name="log_retention_days" id="log_retention_days"
                                       class="form-control" min="7" max="365"
                                       value="{{ $settings['log_retention_days'] }}">
                                <div class="form-text">Monitoring logs older than this will be deleted</div>
                            </div>
                            <div class="col-md-6">
                                <label for="default_port_count" class="form-label">
                                    Default Switch Port Count
                                </label>
                                <select name="default_port_count" id="default_port_count" class="form-select">
                                    <option value="8" {{ $settings['default_port_count'] == 8 ? 'selected' : '' }}>8 Ports</option>
                                    <option value="16" {{ $settings['default_port_count'] == 16 ? 'selected' : '' }}>16 Ports</option>
                                    <option value="24" {{ $settings['default_port_count'] == 24 ? 'selected' : '' }}>24 Ports</option>
                                    <option value="48" {{ $settings['default_port_count'] == 48 ? 'selected' : '' }}>48 Ports</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="alert_email_enabled"
                                       name="alert_email_enabled" value="1"
                                       {{ $settings['alert_email_enabled'] ? 'checked' : '' }}>
                                <label class="form-check-label" for="alert_email_enabled">
                                    Enable Email Notifications
                                </label>
                            </div>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold mb-3">Email Settings</h6>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">SMTP Host</label>
                                <input type="text" class="form-control" value="{{ $settings['smtp_host'] }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">SMTP Port</label>
                                <input type="text" class="form-control" value="{{ $settings['smtp_port'] }}" readonly>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">From Address</label>
                                <input type="text" class="form-control" value="{{ $settings['from_address'] }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">From Name</label>
                                <input type="text" class="form-control" value="{{ $settings['from_name'] }}" readonly>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Email settings can be modified in the <code>.env</code> file
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-1"></i> Save Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- System Info Card -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">System Information</h6>
                </div>
                <div class="card-body" id="systemInfo">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-warning" onclick="clearCache()">
                            <i class="fas fa-broom me-1"></i> Clear Cache
                        </button>
                        <button class="btn btn-info" onclick="runMonitoring()">
                            <i class="fas fa-play me-1"></i> Run Monitoring Now
                        </button>
                        <button class="btn btn-danger" onclick="cleanLogs()">
                            <i class="fas fa-trash me-1"></i> Clean Old Logs
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- System Info Modal -->
<div class="modal fade" id="systemInfoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">System Information</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="systemInfoContent">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        loadSystemInfo();
    });

    function loadSystemInfo() {
        $.get('{{ route("admin.settings") }}/system-info', function(data) {
            const html = `
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between">
                        <span>PHP Version</span>
                        <span class="badge bg-primary">${data.php_version}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Laravel Version</span>
                        <span class="badge bg-primary">${data.laravel_version}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Server OS</span>
                        <span class="badge bg-info">${data.server_os}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Database</span>
                        <span class="badge bg-info">${data.database}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Database Size</span>
                        <span class="badge bg-warning text-dark">${data.database_size}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between">
                        <span>Free Disk Space</span>
                        <span class="badge bg-success">${Math.round(data.disk_free / 1024 / 1024 / 1024)} GB</span>
                    </li>
                </ul>
            `;
            $('#systemInfo').html(html);
        });
    }

    function showSystemInfo() {
        const modal = new bootstrap.Modal(document.getElementById('systemInfoModal'));
        $.get('{{ route("admin.settings") }}/system-info', function(data) {
            $('#systemInfoContent').html(`<pre class="mb-0">${JSON.stringify(data, null, 2)}</pre>`);
        });
        modal.show();
    }

    function clearCache() {
        $.post('{{ route("admin.settings.clear-cache") }}', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire('Success', 'Cache cleared successfully', 'success');
        });
    }
</script>
@endpush
@endsection
