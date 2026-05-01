<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-network-wired me-2"></i>SNMP Configuration
        </h6>
        <button type="button" class="btn btn-sm btn-info" onclick="testSNMP()">
            <i class="fas fa-vial me-1"></i> Test SNMP Connection
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info small">
            <i class="fas fa-info-circle me-2"></i>
            SNMP allows advanced monitoring of CPU, memory, interfaces, and bandwidth utilization.
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="snmp_enabled" 
                           name="snmp_enabled" value="1" 
                           {{ old('snmp_enabled', $device->snmp_enabled ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="snmp_enabled">Enable SNMP Monitoring</label>
                </div>
                <div class="form-text">Enable to collect performance metrics via SNMP</div>
            </div>

            <div class="col-md-6 mb-3">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="snmp_polling_enabled" 
                           name="snmp_polling_enabled" value="1"
                           {{ old('snmp_polling_enabled', $device->snmp_polling_enabled ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="snmp_polling_enabled">Enable Auto Polling</label>
                </div>
                <div class="form-text">Automatically collect SNMP data at intervals</div>
            </div>
        </div>

        <div id="snmpSettings" style="{{ old('snmp_enabled', $device->snmp_enabled ?? false) ? '' : 'display:none;' }}">
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="snmp_community" class="form-label">SNMP Community String</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="text" name="snmp_community" id="snmp_community" 
                               class="form-control" placeholder="public"
                               value="{{ old('snmp_community', $device->snmp_community ?? '') }}">
                    </div>
                    <div class="form-text">Community string for SNMP v1/v2c</div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="snmp_version" class="form-label">SNMP Version</label>
                    <select name="snmp_version" id="snmp_version" class="form-select">
                        <option value="1" {{ old('snmp_version', $device->snmp_version ?? '2c') == '1' ? 'selected' : '' }}>v1</option>
                        <option value="2c" {{ old('snmp_version', $device->snmp_version ?? '2c') == '2c' ? 'selected' : '' }}>v2c</option>
                        <option value="3" {{ old('snmp_version', $device->snmp_version ?? '2c') == '3' ? 'selected' : '' }}>v3</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="snmp_port" class="form-label">SNMP Port</label>
                    <input type="number" name="snmp_port" id="snmp_port" 
                           class="form-control" placeholder="161" min="1" max="65535"
                           value="{{ old('snmp_port', $device->snmp_port ?? 161) }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="snmp_timeout" class="form-label">Timeout (seconds)</label>
                    <input type="number" name="snmp_timeout" id="snmp_timeout" 
                           class="form-control" placeholder="1" min="1" max="10" step="0.5"
                           value="{{ old('snmp_timeout', $device->snmp_timeout ?? 1) }}">
                </div>

                <div class="col-md-4 mb-3">
                    <label for="snmp_polling_interval" class="form-label">Polling Interval (seconds)</label>
                    <select name="snmp_polling_interval" id="snmp_polling_interval" class="form-select">
                        <option value="60" {{ old('snmp_polling_interval', $device->snmp_polling_interval ?? 300) == 60 ? 'selected' : '' }}>Every 1 min (Critical)</option>
                        <option value="300" {{ old('snmp_polling_interval', $device->snmp_polling_interval ?? 300) == 300 ? 'selected' : '' }}>Every 5 min (Normal)</option>
                        <option value="900" {{ old('snmp_polling_interval', $device->snmp_polling_interval ?? 300) == 900 ? 'selected' : '' }}>Every 15 min (Low)</option>
                        <option value="1800" {{ old('snmp_polling_interval', $device->snmp_polling_interval ?? 300) == 1800 ? 'selected' : '' }}>Every 30 min</option>
                        <option value="3600" {{ old('snmp_polling_interval', $device->snmp_polling_interval ?? 300) == 3600 ? 'selected' : '' }}>Every 1 hour</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">SNMP Status</label>
                    <div id="snmpStatus" class="mt-2">
                        <span class="badge bg-secondary">Not Tested</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle SNMP settings visibility
    $('#snmp_enabled').on('change', function() {
        if ($(this).is(':checked')) {
            $('#snmpSettings').slideDown();
        } else {
            $('#snmpSettings').slideUp();
        }
    });

    // Test SNMP connection
    function testSNMP() {
        const community = $('#snmp_community').val() || 'public';
        const port = $('#snmp_port').val() || 161;
        const timeout = $('#snmp_timeout').val() || 1;
        const deviceId = '{{ $device->id ?? 0 }}';

        if (!deviceId) {
            showToast('Device must be saved first', 'warning');
            return;
        }

        const statusEl = $('#snmpStatus');
        statusEl.html('<span class="badge bg-info"><i class="fas fa-spinner fa-spin me-1"></i> Testing...</span>');

        $.ajax({
            url: `/api/snmp/devices/${deviceId}/test`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            data: {
                community: community,
                port: port,
                timeout: timeout
            },
            success: function(response) {
                if (response.success) {
                    statusEl.html('<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Connected</span>');
                    Swal.fire({
                        title: 'Success!',
                        html: `
                            <div class="text-start">
                                <p><strong>Device Name:</strong> ${response.data.name || 'N/A'}</p>
                                <p><strong>Description:</strong> ${response.data.description || 'N/A'}</p>
                                <p><strong>Uptime:</strong> ${response.data.uptime || 'N/A'}</p>
                                <p><strong>Location:</strong> ${response.data.location || 'N/A'}</p>
                            </div>
                        `,
                        icon: 'success'
                    });
                } else {
                    statusEl.html('<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>');
                    Swal.fire('Connection Failed', response.message, 'error');
                }
            },
            error: function(xhr) {
                statusEl.html('<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>');
                Swal.fire('Error', xhr.responseJSON?.message || 'Connection test failed', 'error');
            }
        });
    }
</script>
@endpush