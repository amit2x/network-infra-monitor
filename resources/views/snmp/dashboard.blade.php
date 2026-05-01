@extends('layouts.app')

@section('title', 'SNMP Monitoring Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-project-diagram me-2"></i>SNMP Monitoring Dashboard
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">SNMP Monitoring</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success" onclick="runSNMPMonitoring()">
                <i class="fas fa-play me-1"></i> Run SNMP Now
            </button>
            <button class="btn btn-warning" onclick="discoverDevices()">
                <i class="fas fa-search me-1"></i> Discover Devices
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">SNMP Devices</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['total_snmp_devices'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Online</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['online_snmp_devices'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Data Points Today</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $stats['data_points_today'] }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Avg CPU</div>
                    <div class="h5 mb-0 font-weight-bold" id="avgCpu">--%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- SNMP Devices Grid -->
    <div class="row" id="snmpDevicesGrid">
        @forelse($snmpDevices as $device)
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card shadow h-100 border-left-{{ $device->status === 'online' ? 'success' : 'danger' }}">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server me-2"></i>{{ $device->name }}
                    </h6>
                    <span class="badge bg-{{ $device->status === 'online' ? 'success' : 'danger' }}">
                        {{ ucfirst($device->status) }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-network-wired me-1"></i> {{ $device->ip_address }}:{{ $device->snmp_port ?? 161 }}
                        </small>
                    </div>

                    <!-- CPU Usage -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><i class="fas fa-microchip me-1"></i> CPU Usage</span>
                            <span class="cpu-value" data-device-id="{{ $device->id }}">--%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar cpu-bar" 
                                 data-device-id="{{ $device->id }}"
                                 role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small mb-1">
                            <span><i class="fas fa-memory me-1"></i> Memory Usage</span>
                            <span class="memory-value" data-device-id="{{ $device->id }}">--%</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar memory-bar"
                                 data-device-id="{{ $device->id }}"
                                 role="progressbar" 
                                 style="width: 0%"></div>
                        </div>
                    </div>

                    <!-- Uptime -->
                    <div class="mb-3">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i> Uptime: 
                            <span class="uptime-value" data-device-id="{{ $device->id }}">--</span>
                        </small>
                    </div>

                    <div class="d-flex justify-content-between">
                        <button class="btn btn-sm btn-info" onclick="viewSNMPDetails({{ $device->id }})">
                            <i class="fas fa-chart-line me-1"></i> Details
                        </button>
                        <button class="btn btn-sm btn-outline-success" onclick="refreshDeviceSNMP({{ $device->id }})">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-project-diagram fa-4x text-muted mb-3"></i>
                <h4>No SNMP Devices Configured</h4>
                <p class="text-muted">Enable SNMP on your devices to start monitoring.</p>
                <a href="{{ route('devices.index') }}" class="btn btn-primary">
                    <i class="fas fa-cog me-1"></i> Configure Devices
                </a>
            </div>
        </div>
        @endforelse
    </div>
</div>

<!-- SNMP Device Detail Modal -->
<div class="modal fade" id="snmpDetailModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-chart-line me-2"></i>SNMP Device Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="snmpDetailContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading SNMP data...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Device Discovery Modal -->
<div class="modal fade" id="discoveryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Discover SNMP Devices</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="networkRange" class="form-label">Network Range</label>
                    <input type="text" id="networkRange" class="form-control" 
                           placeholder="192.168.1.0/24">
                </div>
                <div class="mb-3">
                    <label for="communityString" class="form-label">Community String</label>
                    <input type="text" id="communityString" class="form-control" 
                           value="public">
                </div>
                <div id="discoveryResults"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="startDiscovery()">
                    <i class="fas fa-search me-1"></i> Start Discovery
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-refresh SNMP data every 60 seconds
    let refreshInterval;

    $(document).ready(function() {
        loadAllSNMPData();
        refreshInterval = setInterval(loadAllSNMPData, 60000);
    });

    // Load all SNMP data
    function loadAllSNMPData() {
        $('.cpu-value, .memory-value, .uptime-value').each(function() {
            const deviceId = $(this).data('device-id');
            if (deviceId) {
                loadDeviceSNMPData(deviceId);
            }
        });
    }

    // Load SNMP data for a specific device
    function loadDeviceSNMPData(deviceId) {
        $.get(`/api/snmp/devices/${deviceId}/performance`, function(response) {
            if (response.success) {
                const data = response.data;
                
                // Update CPU
                $(`.cpu-value[data-device-id="${deviceId}"]`).text(data.avg_cpu + '%');
                $(`.cpu-bar[data-device-id="${deviceId}"]`)
                    .css('width', data.avg_cpu + '%')
                    .removeClass('bg-success bg-warning bg-danger')
                    .addClass(data.avg_cpu > 80 ? 'bg-danger' : data.avg_cpu > 60 ? 'bg-warning' : 'bg-success');

                // Update Memory
                $(`.memory-value[data-device-id="${deviceId}"]`).text(data.avg_memory + '%');
                $(`.memory-bar[data-device-id="${deviceId}"]`)
                    .css('width', data.avg_memory + '%')
                    .removeClass('bg-success bg-warning bg-danger')
                    .addClass(data.avg_memory > 80 ? 'bg-danger' : data.avg_memory > 60 ? 'bg-warning' : 'bg-success');
            }
        });
    }

    // View SNMP details in modal
    function viewSNMPDetails(deviceId) {
        const modal = new bootstrap.Modal(document.getElementById('snmpDetailModal'));
        modal.show();

        $.get(`/api/snmp/devices/${deviceId}/performance?hours=24`, function(response) {
            if (response.success) {
                renderSNMPDetails(response.data, deviceId);
            }
        });
    }

    // Render SNMP details
    function renderSNMPDetails(data, deviceId) {
        let html = `
            <div class="row">
                <div class="col-md-6">
                    <h6>CPU Usage Trend (24h)</h6>
                    <canvas id="cpuChart" height="200"></canvas>
                </div>
                <div class="col-md-6">
                    <h6>Memory Usage Trend (24h)</h6>
                    <canvas id="memoryChart" height="200"></canvas>
                </div>
            </div>
            <hr>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <div class="text-muted small">Avg CPU</div>
                        <div class="h4 text-primary">${data.avg_cpu}%</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <div class="text-muted small">Avg Memory</div>
                        <div class="h4 text-info">${data.avg_memory}%</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-3 text-center">
                        <div class="text-muted small">Data Points</div>
                        <div class="h4 text-success">${data.data_points}</div>
                    </div>
                </div>
            </div>
        `;

        $('#snmpDetailContent').html(html);

        // Render charts
        if (data.cpu_trend && Object.keys(data.cpu_trend).length > 0) {
            const cpuLabels = Object.keys(data.cpu_trend);
            const cpuValues = Object.values(data.cpu_trend);
            
            new Chart(document.getElementById('cpuChart'), {
                type: 'line',
                data: {
                    labels: cpuLabels,
                    datasets: [{
                        label: 'CPU %',
                        data: cpuValues,
                        borderColor: '#4e73df',
                        tension: 0.4
                    }]
                }
            });

            const memLabels = Object.keys(data.memory_trend);
            const memValues = Object.values(data.memory_trend);
            
            new Chart(document.getElementById('memoryChart'), {
                type: 'line',
                data: {
                    labels: memLabels,
                    datasets: [{
                        label: 'Memory %',
                        data: memValues,
                        borderColor: '#1cc88a',
                        tension: 0.4
                    }]
                }
            });
        }
    }

    // Refresh single device
    function refreshDeviceSNMP(deviceId) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        $.post(`/api/snmp/monitoring/run`, {
            device_id: deviceId,
            _token: '{{ csrf_token() }}'
        }, function(response) {
            loadDeviceSNMPData(deviceId);
            showToast('SNMP data refreshed', 'success');
        }).always(function() {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        });
    }

    // Run SNMP monitoring
    function runSNMPMonitoring() {
        Swal.fire({
            title: 'Running SNMP Monitoring...',
            html: 'Please wait while we collect SNMP data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('/api/snmp/monitoring/run', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire('Complete!', 'SNMP monitoring cycle completed', 'success')
                .then(() => location.reload());
        }).fail(function() {
            Swal.fire('Error', 'SNMP monitoring failed', 'error');
        });
    }

    // Discover devices
    function discoverDevices() {
        const modal = new bootstrap.Modal(document.getElementById('discoveryModal'));
        modal.show();
    }

    function startDiscovery() {
        const network = $('#networkRange').val();
        const community = $('#communityString').val();

        if (!network) {
            Swal.fire('Error', 'Please enter a network range', 'error');
            return;
        }

        $('#discoveryResults').html(`
            <div class="text-center py-3">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Scanning network...</p>
            </div>
        `);

        $.post('/api/snmp/discover', {
            network: network,
            community: community,
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                let html = `<h6>Found ${response.data.length} device(s):</h6>`;
                response.data.forEach((device, index) => {
                    html += `
                        <div class="border rounded p-2 mb-2">
                            <strong>${device.name || 'Unknown'}</strong><br>
                            <small>IP: ${device.ip}</small><br>
                            <small>${device.description || ''}</small>
                        </div>
                    `;
                });
                $('#discoveryResults').html(html);
            }
        });
    }

    // Clean up interval on page leave
    $(window).on('beforeunload', function() {
        clearInterval(refreshInterval);
    });
</script>
@endpush
@endsection