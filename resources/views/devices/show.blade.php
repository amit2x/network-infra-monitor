@extends('layouts.app')

@section('title', 'Device Details - ' . $device->name)

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-server me-2"></i>{{ $device->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
                    <li class="breadcrumb-item active">{{ $device->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <button class="btn btn-success" onclick="pingDevice({{ $device->id }})">
                <i class="fas fa-broadcast-tower me-1"></i> Ping Now
            </button>
            <a href="{{ route('devices.edit', $device->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button class="btn btn-danger" onclick="deleteDevice({{ $device->id }})">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <!-- Device Status Banner -->
    <div class="alert alert-{{ $device->status === 'online' ? 'success' : ($device->status === 'offline' ? 'danger' : 'warning') }} shadow mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-{{ $device->status === 'online' ? 'check-circle' : 'exclamation-triangle' }} fa-2x"></i>
            </div>
            <div>
                <h5 class="alert-heading mb-1">Device is {{ ucfirst($device->status) }}</h5>
                <p class="mb-0">Last Updated: {{ $device->updated_at->diffForHumans() }}</p>
            </div>
            @if($device->status === 'offline')
            <div class="ms-auto">
                <a href="{{ route('alerts.index') }}?device_id={{ $device->id }}" class="btn btn-light btn-sm">
                    <i class="fas fa-exclamation-circle me-1"></i> View Alerts
                </a>
            </div>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Device Information -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Device Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Device Code</label>
                            <div class="fw-bold">{{ $device->device_code }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Type</label>
                            <div>
                                <span class="badge bg-primary">{{ ucfirst(str_replace('_', ' ', $device->type)) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Vendor</label>
                            <div class="fw-bold">{{ $device->vendor }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Model</label>
                            <div class="fw-bold">{{ $device->model }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Serial Number</label>
                            <div class="text-monospace">{{ $device->serial_number }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">IP Address</label>
                            <div class="text-monospace">{{ $device->ip_address }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">MAC Address</label>
                            <div class="text-monospace">{{ $device->mac_address ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Firmware Version</label>
                            <div>{{ $device->firmware_version ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Deployment -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location & Deployment</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location</label>
                            <div>
                                <i class="fas fa-map-marker-alt me-2"></i>
                                {{ $device->location->full_path }}
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location Type</label>
                            <div>
                                <span class="badge bg-info">{{ ucfirst($device->location->type) }}</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Procurement Date</label>
                            <div>{{ $device->procurement_date ? $device->procurement_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Installation Date</label>
                            <div>{{ $device->installation_date ? $device->installation_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lifecycle Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lifecycle Management</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Warranty Expiry</label>
                            <div>
                                @if($device->warranty_expiry)
                                    @php
                                        $warrantyDays = now()->diffInDays($device->warranty_expiry, false);
                                    @endphp
                                    <span class="fw-bold {{ $warrantyDays <= 30 ? 'text-danger' : '' }}">
                                        {{ $device->warranty_expiry->format('d-M-Y') }}
                                    </span>
                                    @if($warrantyDays <= 30 && $warrantyDays > 0)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Expiring in {{ $warrantyDays }} days
                                        </span>
                                    @elseif($warrantyDays <= 0)
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">AMC Expiry</label>
                            <div>
                                @if($device->amc_expiry)
                                    @php
                                        $amcDays = now()->diffInDays($device->amc_expiry, false);
                                    @endphp
                                    <span class="fw-bold {{ $amcDays <= 30 ? 'text-danger' : '' }}">
                                        {{ $device->amc_expiry->format('d-M-Y') }}
                                    </span>
                                    @if($amcDays <= 30 && $amcDays > 0)
                                        <span class="badge bg-warning text-dark ms-2">
                                            Expiring in {{ $amcDays }} days
                                        </span>
                                    @elseif($amcDays <= 0)
                                        <span class="badge bg-danger ms-2">Expired</span>
                                    @endif
                                @else
                                    N/A
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">End of Life Date</label>
                            <div>{{ $device->eol_date ? $device->eol_date->format('d-M-Y') : 'N/A' }}</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Critical Device</label>
                            <div>
                                @if($device->is_critical)
                                    <span class="badge bg-danger">Yes</span>
                                @else
                                    <span class="badge bg-secondary">No</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Remarks -->
            @if($device->remarks)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Remarks</h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $device->remarks }}</p>
                </div>
            </div>
            @endif
            
                    <!-- SNMP Configuration & Testing Section -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-project-diagram me-2"></i>SNMP Monitoring
                    </h6>
                    <div>
                        @if($device->snmp_enabled)
                            <span class="badge bg-success me-2">
                                <i class="fas fa-check-circle me-1"></i> Enabled
                            </span>
                        @else
                            <span class="badge bg-secondary me-2">
                                <i class="fas fa-times-circle me-1"></i> Disabled
                            </span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(!$device->snmp_enabled)
                        <!-- SNMP Not Configured -->
                        <div class="text-center py-4">
                            <i class="fas fa-project-diagram fa-3x text-muted mb-3"></i>
                            <h5>SNMP Not Configured</h5>
                            <p class="text-muted">Enable SNMP monitoring to collect CPU, memory, and bandwidth metrics from this device.</p>
                            <div class="mt-3">
                                <a href="{{ route('devices.edit', $device->id) }}#snmp-section" class="btn btn-primary">
                                    <i class="fas fa-cog me-1"></i> Configure SNMP Now
                                </a>
                                <button class="btn btn-outline-info" onclick="quickEnableSNMP({{ $device->id }})">
                                    <i class="fas fa-magic me-1"></i> Quick Enable (Default Settings)
                                </button>
                            </div>
                            <div class="mt-3 alert alert-info small text-start">
                                <i class="fas fa-info-circle me-1"></i>
                                <strong>Default SNMP Settings:</strong> Community: <code>public</code>, Port: <code>161</code>, Version: <code>v2c</code>
                            </div>
                        </div>
                    @else
                        <!-- SNMP Configured - Show Test & Status -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="font-weight-bold mb-3">
                                        <i class="fas fa-cog me-2"></i>SNMP Configuration
                                    </h6>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" width="40%">Status</td>
                                            <td>
                                                <span id="snmpStatusBadge" class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i> Not Tested
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Community</td>
                                            <td>
                                                <code id="displayCommunity">******</code>
                                                <button class="btn btn-sm btn-outline-secondary ms-1" onclick="toggleCommunity()">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Version</td>
                                            <td><span class="badge bg-info">{{ $device->snmp_version ?? '2c' }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Port</td>
                                            <td>{{ $device->snmp_port ?? 161 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Timeout</td>
                                            <td>{{ $device->snmp_timeout ?? 1 }}s</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Polling</td>
                                            <td>
                                                @if($device->snmp_polling_enabled)
                                                    <span class="badge bg-success">Enabled</span>
                                                    <small class="text-muted ms-1">(Every {{ $device->snmp_polling_interval }}s)</small>
                                                @else
                                                    <span class="badge bg-secondary">Disabled</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                    <div class="mt-2">
                                        <a href="{{ route('devices.edit', $device->id) }}#snmp-section" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit me-1"></i> Edit Configuration
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <h6 class="font-weight-bold mb-3">
                                        <i class="fas fa-vial me-2"></i>Connection Test
                                    </h6>
                                    <div id="testResults" class="mb-3">
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-question-circle fa-2x mb-2"></i>
                                            <p>Test SNMP connectivity to verify configuration.</p>
                                        </div>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-success" onclick="testSNMPConnection({{ $device->id }})" id="testBtn">
                                            <i class="fas fa-vial me-1"></i> Test SNMP Connection
                                        </button>
                                        <button class="btn btn-outline-info" onclick="getLiveSNMPData({{ $device->id }})" id="liveDataBtn">
                                            <i class="fas fa-sync-alt me-1"></i> Get Live SNMP Data
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live SNMP Data Display -->
                        <div id="liveDataSection" style="display: none;">
                            <hr>
                            <h6 class="font-weight-bold mb-3">
                                <i class="fas fa-chart-line me-2"></i>Live SNMP Data
                            </h6>
                            <div class="row" id="liveDataCards">
                                <!-- Filled dynamically -->
                            </div>
                        </div>

                        <!-- Historical Data Link -->
                        @if($device->snmpData()->exists())
                        <div class="text-center mt-3">
                            <a href="{{ route('snmp.performance', $device->id) }}" class="btn btn-outline-primary">
                                <i class="fas fa-chart-bar me-1"></i> View Historical SNMP Data
                            </a>
                            <a href="{{ route('snmp.interfaces', $device->id) }}" class="btn btn-outline-info ms-2">
                                <i class="fas fa-plug me-1"></i> View Interface Statistics
                            </a>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Total Ports</div>
                                <div class="h4 mb-0 text-primary">{{ $device->ports->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Active Ports</div>
                                <div class="h4 mb-0 text-success">{{ $device->ports->where('status', 'active')->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Monitoring</div>
                                <div class="h4 mb-0">
                                    <span class="text-{{ $device->monitoring_enabled ? 'success' : 'danger' }}">
                                        {{ $device->monitoring_enabled ? 'On' : 'Off' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-2 text-center">
                                <div class="text-muted small">Created</div>
                                <div class="small mb-0">{{ $device->created_at->format('d-M-Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Monitoring Logs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Monitoring</h6>
                    <a href="{{ route('monitoring.logs', ['device_id' => $device->id]) }}" class="btn btn-sm btn-light">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="max-height: 300px; overflow-y: auto;">
                        @forelse($monitoringLogs as $log)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <span class="badge bg-{{ $log->status === 'success' ? 'success' : 'danger' }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    @if($log->response_time_ms)
                                    <small class="text-muted ms-2">{{ number_format($log->response_time_ms, 2) }}ms</small>
                                    @endif
                                </div>
                                <small class="text-muted">{{ $log->created_at->diffForHumans() }}</small>
                            </div>
                            <small class="text-muted">{{ $log->message }}</small>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No monitoring data available
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Recent Alerts -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Alerts</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($alerts as $alert)
                        <a href="{{ route('alerts.show', $alert->id) }}" class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between">
                                <span class="badge bg-{{ $alert->severity === 'critical' ? 'danger' : ($alert->severity === 'high' ? 'warning' : 'info') }}">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                                <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                            </div>
                            <small>{{ $alert->title }}</small>
                        </a>
                        @empty
                        <div class="list-group-item text-center text-muted">
                            No alerts
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Delete Device</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete <strong>{{ $device->name }}</strong>?</p>
                <p class="text-danger"><i class="fas fa-exclamation-triangle me-2"></i>This action cannot be undone. All associated data including ports, logs, and alerts will be permanently deleted.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" action="{{ route('devices.destroy', $device->id) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Device</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Store actual community for testing
    const actualCommunity = '{{ $device->snmp_community ?? "public" }}';
    let communityVisible = false;

    // Toggle community string visibility
    function toggleCommunity() {
        const displayEl = $('#displayCommunity');
        if (communityVisible) {
            displayEl.text('******');
            communityVisible = false;
        } else {
            displayEl.text(actualCommunity);
            communityVisible = true;
            // Auto-hide after 10 seconds
            setTimeout(() => {
                displayEl.text('******');
                communityVisible = false;
            }, 10000);
        }
    }

    // Quick enable SNMP with default settings
    function quickEnableSNMP(deviceId) {
        Swal.fire({
            title: 'Quick Enable SNMP?',
            html: `
                <div class="text-start">
                    <p>This will enable SNMP with default settings:</p>
                    <ul>
                        <li>Community: <code>public</code></li>
                        <li>Port: <code>161</code></li>
                        <li>Version: <code>v2c</code></li>
                        <li>Timeout: <code>1 second</code></li>
                        <li>Polling Interval: <code>5 minutes</code></li>
                    </ul>
                    <p class="text-warning small">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        Make sure your device allows SNMP access with these credentials.
                    </p>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-magic me-1"></i> Enable SNMP',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#3085d6'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/devices/${deviceId}`,
                    method: 'PUT',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    data: {
                        name: '{{ $device->name }}',
                        type: '{{ $device->type }}',
                        vendor: '{{ $device->vendor }}',
                        model: '{{ $device->model }}',
                        serial_number: '{{ $device->serial_number }}',
                        ip_address: '{{ $device->ip_address }}',
                        status: '{{ $device->status }}',
                        location_id: '{{ $device->location_id }}',
                        snmp_enabled: true,
                        snmp_community: 'public',
                        snmp_version: '2c',
                        snmp_port: 161,
                        snmp_timeout: 1,
                        snmp_polling_enabled: true,
                        snmp_polling_interval: 300,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Success!',
                            text: 'SNMP has been enabled with default settings.',
                            icon: 'success',
                            timer: 2000
                        }).then(() => location.reload());
                    },
                    error: function(xhr) {
                        Swal.fire('Error', 'Failed to enable SNMP. Please try manual configuration.', 'error');
                    }
                });
            }
        });
    }

    // Test SNMP connection
    function testSNMPConnection(deviceId) {
        const testBtn = $('#testBtn');
        const statusBadge = $('#snmpStatusBadge');
        const testResults = $('#testResults');

        // Update UI to testing state
        testBtn.prop('disabled', true);
        testBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Testing...');
        statusBadge.attr('class', 'badge bg-info');
        statusBadge.html('<i class="fas fa-spinner fa-spin me-1"></i> Testing...');
        testResults.html(`
            <div class="text-center py-3">
                <div class="spinner-border text-primary mb-2" role="status">
                    <span class="visually-hidden">Testing...</span>
                </div>
                <p class="text-muted mb-0">Connecting to {{ $device->ip_address }}:{{ $device->snmp_port ?? 161 }}...</p>
            </div>
        `);

        $.ajax({
            url: `/snmp/devices/${deviceId}/test`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    // Success
                    statusBadge.attr('class', 'badge bg-success');
                    statusBadge.html('<i class="fas fa-check-circle me-1"></i> Connected');

                    const data = response.data;
                    testResults.html(`
                        <div class="alert alert-success mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-check-circle me-1"></i> 
                                SNMP Connection Successful!
                            </h6>
                            <hr>
                            <div class="row small">
                                <div class="col-6">
                                    <strong>Device Name:</strong><br>
                                    <span class="text-muted">${data.system_info?.name || 'N/A'}</span>
                                </div>
                                <div class="col-6">
                                    <strong>Uptime:</strong><br>
                                    <span class="text-muted">${data.system_info?.uptime || 'N/A'}</span>
                                </div>
                                <div class="col-12 mt-2">
                                    <strong>Description:</strong><br>
                                    <span class="text-muted">${data.system_info?.description || 'N/A'}</span>
                                </div>
                                <div class="col-6 mt-2">
                                    <strong>Response Time:</strong><br>
                                    <span class="text-success">${data.response_time_ms}ms</span>
                                </div>
                                <div class="col-6 mt-2">
                                    <strong>Location:</strong><br>
                                    <span class="text-muted">${data.system_info?.location || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    `);

                    Swal.fire({
                        title: 'Connection Successful!',
                        text: `SNMP is working on ${response.data.connection_params.host}`,
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false
                    });
                } else {
                    // Failed
                    statusBadge.attr('class', 'badge bg-danger');
                    statusBadge.html('<i class="fas fa-times-circle me-1"></i> Failed');

                    testResults.html(`
                        <div class="alert alert-danger mb-0">
                            <h6 class="alert-heading">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                SNMP Connection Failed
                            </h6>
                            <hr>
                            <p class="mb-2 small">${response.message}</p>
                            <p class="mb-0 small text-muted">Response time: ${response.data?.response_time_ms || 'N/A'}ms</p>
                            <hr>
                            <p class="mb-0 small">
                                <strong>Troubleshooting Tips:</strong>
                                <ul class="small mb-0">
                                    <li>Verify the community string is correct</li>
                                    <li>Check if SNMP service is running on the device</li>
                                    <li>Ensure firewall allows SNMP (UDP port 161)</li>
                                    <li>Verify the device is reachable from this server</li>
                                </ul>
                            </p>
                        </div>
                    `);

                    Swal.fire({
                        title: 'Connection Failed',
                        html: response.message + '<br><br><small>Check SNMP configuration and try again.</small>',
                        icon: 'error'
                    });
                }
            },
            error: function(xhr) {
                // Error
                statusBadge.attr('class', 'badge bg-danger');
                statusBadge.html('<i class="fas fa-times-circle me-1"></i> Error');

                testResults.html(`
                    <div class="alert alert-warning mb-0">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Connection Error
                        </h6>
                        <p class="mb-0 small">${xhr.responseJSON?.message || 'An unexpected error occurred.'}</p>
                    </div>
                `);

                Swal.fire('Error', xhr.responseJSON?.message || 'Failed to test SNMP connection', 'error');
            },
            complete: function() {
                // Reset button
                testBtn.prop('disabled', false);
                testBtn.html('<i class="fas fa-vial me-1"></i> Test SNMP Connection');
            }
        });
    }

    // Get live SNMP data
    function getLiveSNMPData(deviceId) {
        const liveDataBtn = $('#liveDataBtn');
        const liveDataSection = $('#liveDataSection');
        const liveDataCards = $('#liveDataCards');

        // Update UI
        liveDataBtn.prop('disabled', true);
        liveDataBtn.html('<span class="spinner-border spinner-border-sm me-1"></span> Fetching...');
        liveDataSection.show();
        liveDataCards.html(`
            <div class="col-12 text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 text-muted">Fetching live SNMP data...</p>
            </div>
        `);

        $.ajax({
            url: `/api/snmp/devices/${deviceId}/performance`,
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            },
            data: {
                hours: 1 // Get last hour data
            },
            success: function(response) {
                if (response.success) {
                    const metrics = response.data.metrics;
                    
                    let html = '';
                    
                    // CPU Card
                    if (metrics.cpu) {
                        const cpuUsage = metrics.cpu.current?.['5sec'] || metrics.cpu.avg || 0;
                        const cpuColor = cpuUsage > 80 ? 'danger' : cpuUsage > 60 ? 'warning' : 'success';
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-${cpuColor} shadow h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-${cpuColor} text-uppercase mb-1">
                                            <i class="fas fa-microchip me-1"></i> CPU Usage
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <div class="h4 mb-0 font-weight-bold">${cpuUsage}%</div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <i class="fas fa-microchip fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-${cpuColor}" style="width: ${cpuUsage}%"></div>
                                        </div>
                                        ${metrics.cpu.current?.['1min'] ? `
                                            <small class="text-muted mt-1 d-block">
                                                1min: ${metrics.cpu.current['1min']}% | 5min: ${metrics.cpu.current['5min'] || 'N/A'}%
                                            </small>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // Memory Card
                    if (metrics.memory) {
                        const memUsage = metrics.memory.current?.usage_percent || metrics.memory.avg || 0;
                        const memColor = memUsage > 80 ? 'danger' : memUsage > 60 ? 'warning' : 'success';
                        
                        html += `
                            <div class="col-md-6 mb-3">
                                <div class="card border-left-${memColor} shadow h-100">
                                    <div class="card-body">
                                        <div class="text-xs font-weight-bold text-${memColor} text-uppercase mb-1">
                                            <i class="fas fa-memory me-1"></i> Memory Usage
                                        </div>
                                        <div class="row align-items-center">
                                            <div class="col-8">
                                                <div class="h4 mb-0 font-weight-bold">${memUsage}%</div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <i class="fas fa-memory fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                        <div class="progress mt-2" style="height: 6px;">
                                            <div class="progress-bar bg-${memColor}" style="width: ${memUsage}%"></div>
                                        </div>
                                        ${metrics.memory.current?.total ? `
                                            <small class="text-muted mt-1 d-block">
                                                Used: ${formatBytes(metrics.memory.current.used)} / Total: ${formatBytes(metrics.memory.current.total)}
                                            </small>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    // System Info Card
                    if (metrics.system) {
                        html += `
                            <div class="col-12 mb-3">
                                <div class="card shadow">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold text-primary mb-3">
                                            <i class="fas fa-info-circle me-1"></i> System Information
                                        </h6>
                                        <div class="row small">
                                            <div class="col-md-6">
                                                <strong>Contact:</strong> ${metrics.system.contact || 'N/A'}
                                            </div>
                                            <div class="col-md-6">
                                                <strong>Uptime:</strong> ${metrics.system.uptime || 'N/A'}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    if (!html) {
                        html = `
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle me-1"></i>
                                    No live data available. The device may not support these SNMP OIDs or may need additional configuration.
                                </div>
                            </div>
                        `;
                    }

                    liveDataCards.html(html);
                }
            },
            error: function(xhr) {
                liveDataCards.html(`
                    <div class="col-12">
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Failed to fetch SNMP data: ${xhr.responseJSON?.message || 'Unknown error'}
                        </div>
                    </div>
                `);
            },
            complete: function() {
                liveDataBtn.prop('disabled', false);
                liveDataBtn.html('<i class="fas fa-sync-alt me-1"></i> Get Live SNMP Data');
            }
        });
    }

    // Format bytes helper
    function formatBytes(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const k = 1024;
        const sizes = ['B', 'KB', 'MB', 'GB', 'TB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Existing ping function
    function pingDevice(id) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Pinging...';
        
        $.ajax({
            url: `/devices/${id}/ping`,
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            success: function(response) {
                Swal.fire({
                    title: response.data.success ? 'Device Reachable' : 'Device Unreachable',
                    html: `<p>Response Time: ${response.data.response_time?.toFixed(2) || 'N/A'}ms</p>`,
                    icon: response.data.success ? 'success' : 'error',
                    timer: 3000
                });
                setTimeout(() => location.reload(), 2000);
            }
        }).always(() => {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    // Delete device function
    function deleteDevice(id) {
        Swal.fire({
            title: 'Delete Device?',
            text: "This action cannot be undone!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/devices/${id}`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
@endsection
