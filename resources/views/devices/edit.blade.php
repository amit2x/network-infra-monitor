@extends('layouts.app')

@section('title', 'Edit Device - ' . $device->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>Edit Device
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.show', $device->id) }}">{{ $device->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('devices.show', $device->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Device
        </a>
    </div>

    <form action="{{ route('devices.update', $device->id) }}" method="POST" class="needs-validation" novalidate>
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Basic Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Device Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $device->name) }}" required maxlength="255">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Device Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="switch" {{ old('type', $device->type) == 'switch' ? 'selected' : '' }}>Switch</option>
                                    <option value="router" {{ old('type', $device->type) == 'router' ? 'selected' : '' }}>Router</option>
                                    <option value="firewall" {{ old('type', $device->type) == 'firewall' ? 'selected' : '' }}>Firewall</option>
                                    <option value="access_point" {{ old('type', $device->type) == 'access_point' ? 'selected' : '' }}>Access Point</option>
                                    <option value="server" {{ old('type', $device->type) == 'server' ? 'selected' : '' }}>Server</option>
                                    <option value="other" {{ old('type', $device->type) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="vendor" class="form-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor" id="vendor" class="form-select @error('vendor') is-invalid @enderror" required>
                                    <option value="">Select Vendor</option>
                                    <option value="Cisco" {{ old('vendor', $device->vendor) == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                    <option value="Juniper" {{ old('vendor', $device->vendor) == 'Juniper' ? 'selected' : '' }}>Juniper</option>
                                    <option value="HP" {{ old('vendor', $device->vendor) == 'HP' ? 'selected' : '' }}>HP</option>
                                    <option value="Dell" {{ old('vendor', $device->vendor) == 'Dell' ? 'selected' : '' }}>Dell</option>
                                    <option value="Arista" {{ old('vendor', $device->vendor) == 'Arista' ? 'selected' : '' }}>Arista</option>
                                    <option value="Fortinet" {{ old('vendor', $device->vendor) == 'Fortinet' ? 'selected' : '' }}>Fortinet</option>
                                    <option value="Other" {{ old('vendor', $device->vendor) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" name="model" id="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model', $device->model) }}" required>
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="serial_number" id="serial_number"
                                       class="form-control @error('serial_number') is-invalid @enderror"
                                       value="{{ old('serial_number', $device->serial_number) }}" required>
                                @error('serial_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="online" {{ old('status', $device->status) == 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ old('status', $device->status) == 'offline' ? 'selected' : '' }}>Offline</option>
                                    <option value="maintenance" {{ old('status', $device->status) == 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                                    <option value="decommissioned" {{ old('status', $device->status) == 'decommissioned' ? 'selected' : '' }}>Decommissioned</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Network Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Network Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ip_address" class="form-label">IP Address <span class="text-danger">*</span></label>
                                <input type="text" name="ip_address" id="ip_address"
                                       class="form-control @error('ip_address') is-invalid @enderror"
                                       value="{{ old('ip_address', $device->ip_address) }}" required>
                                @error('ip_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="mac_address" class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" id="mac_address"
                                       class="form-control @error('mac_address') is-invalid @enderror"
                                       value="{{ old('mac_address', $device->mac_address) }}">
                                @error('mac_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="firmware_version" class="form-label">Firmware Version</label>
                                <input type="text" name="firmware_version" id="firmware_version"
                                       class="form-control @error('firmware_version') is-invalid @enderror"
                                       value="{{ old('firmware_version', $device->firmware_version) }}">
                                @error('firmware_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Location & Lifecycle -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Location & Lifecycle</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                                <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location['id'] }}" {{ old('location_id', $device->location_id) == $location['id'] ? 'selected' : '' }}>
                                            {{ $location['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry</label>
                                <input type="date" name="warranty_expiry" id="warranty_expiry"
                                       class="form-control @error('warranty_expiry') is-invalid @enderror"
                                       value="{{ old('warranty_expiry', optional($device->warranty_expiry)->format('Y-m-d')) }}">
                                @error('warranty_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="amc_expiry" class="form-label">AMC Expiry</label>
                                <input type="date" name="amc_expiry" id="amc_expiry"
                                       class="form-control @error('amc_expiry') is-invalid @enderror"
                                       value="{{ old('amc_expiry', optional($device->amc_expiry)->format('Y-m-d')) }}">
                                @error('amc_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                
                
                <!-- SNMP Configuration Card (Add after Lifecycle card) -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-project-diagram me-2"></i>SNMP Configuration
                        </h6>
                        <div>
                            <span class="badge bg-{{ $device->snmp_enabled ? 'success' : 'secondary' }} me-2">
                                {{ $device->snmp_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                            @if($device->snmp_enabled)
                            <button type="button" class="btn btn-sm btn-info" onclick="testSNMP()">
                                <i class="fas fa-vial me-1"></i> Test Connection
                            </button>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="snmp_enabled" 
                                           name="snmp_enabled" value="1" 
                                           {{ old('snmp_enabled', $device->snmp_enabled) ? 'checked' : '' }}
                                           onchange="toggleSNMPSettings()">
                                    <label class="form-check-label fw-bold" for="snmp_enabled">
                                        Enable SNMP Monitoring
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="snmp_polling_enabled" 
                                           name="snmp_polling_enabled" value="1"
                                           {{ old('snmp_polling_enabled', $device->snmp_polling_enabled) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="snmp_polling_enabled">
                                        Enable Auto Polling
                                    </label>
                                </div>
                            </div>
                        </div>
                
                        <div id="snmpSettings" style="{{ old('snmp_enabled', $device->snmp_enabled) ? '' : 'display:none;' }}">
                            <hr>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_community" class="form-label">SNMP Community String</label>
                                    <input type="text" name="snmp_community" id="snmp_community" 
                                           class="form-control" placeholder="public"
                                           value="{{ old('snmp_community', $device->snmp_community) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_version" class="form-label">SNMP Version</label>
                                    <select name="snmp_version" id="snmp_version" class="form-select">
                                        <option value="1" {{ old('snmp_version', $device->snmp_version) == '1' ? 'selected' : '' }}>v1</option>
                                        <option value="2c" {{ old('snmp_version', $device->snmp_version) == '2c' ? 'selected' : '' }}>v2c</option>
                                        <option value="3" {{ old('snmp_version', $device->snmp_version) == '3' ? 'selected' : '' }}>v3</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_port" class="form-label">SNMP Port</label>
                                    <input type="number" name="snmp_port" id="snmp_port" 
                                           class="form-control" min="1" max="65535"
                                           value="{{ old('snmp_port', $device->snmp_port) }}">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_timeout" class="form-label">Timeout (seconds)</label>
                                    <input type="number" name="snmp_timeout" id="snmp_timeout" 
                                           class="form-control" min="1" max="10"
                                           value="{{ old('snmp_timeout', $device->snmp_timeout) }}">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_polling_interval" class="form-label">Polling Interval</label>
                                    <select name="snmp_polling_interval" id="snmp_polling_interval" class="form-select">
                                        <option value="60" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 60 ? 'selected' : '' }}>Every 1 min (Critical)</option>
                                        <option value="300" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 300 ? 'selected' : '' }}>Every 5 min (Normal)</option>
                                        <option value="900" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 900 ? 'selected' : '' }}>Every 15 min (Low)</option>
                                        <option value="1800" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 1800 ? 'selected' : '' }}>Every 30 min</option>
                                        <option value="3600" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 3600 ? 'selected' : '' }}>Every 1 hour</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div id="snmpStatus" class="w-100">
                                        @if($device->snmp_enabled)
                                            <span class="badge bg-warning text-dark">Not Tested Recently</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Remarks -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_critical" name="is_critical" value="1" {{ old('is_critical', $device->is_critical) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_critical">Critical Device</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="monitoring_enabled" name="monitoring_enabled" value="1" {{ old('monitoring_enabled', $device->monitoring_enabled) ? 'checked' : '' }}>
                                <label class="form-check-label" for="monitoring_enabled">Enable Monitoring</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3"
                                      class="form-control @error('remarks') is-invalid @enderror">{{ old('remarks', $device->remarks) }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-warning text-white">
                        <h6 class="m-0 font-weight-bold">Device Code</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="h3 text-warning mb-0">{{ $device->device_code }}</div>
                        <small class="text-muted">Cannot be modified</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('devices.show', $device->id) }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-save me-1"></i> Update Device
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>
<!-- SNMP Configuration Card (Add after Lifecycle card) -->
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-project-diagram me-2"></i>SNMP Configuration
        </h6>
        <div>
            <span class="badge bg-{{ $device->snmp_enabled ? 'success' : 'secondary' }} me-2">
                {{ $device->snmp_enabled ? 'Enabled' : 'Disabled' }}
            </span>
            @if($device->snmp_enabled)
            <button type="button" class="btn btn-sm btn-info" onclick="testSNMP()">
                <i class="fas fa-vial me-1"></i> Test Connection
            </button>
            @endif
        </div>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="snmp_enabled" 
                           name="snmp_enabled" value="1" 
                           {{ old('snmp_enabled', $device->snmp_enabled) ? 'checked' : '' }}
                           onchange="toggleSNMPSettings()">
                    <label class="form-check-label fw-bold" for="snmp_enabled">
                        Enable SNMP Monitoring
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="snmp_polling_enabled" 
                           name="snmp_polling_enabled" value="1"
                           {{ old('snmp_polling_enabled', $device->snmp_polling_enabled) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="snmp_polling_enabled">
                        Enable Auto Polling
                    </label>
                </div>
            </div>
        </div>

        <div id="snmpSettings" style="{{ old('snmp_enabled', $device->snmp_enabled) ? '' : 'display:none;' }}">
            <hr>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="snmp_community" class="form-label">SNMP Community String</label>
                    <input type="text" name="snmp_community" id="snmp_community" 
                           class="form-control" placeholder="public"
                           value="{{ old('snmp_community', $device->snmp_community) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="snmp_version" class="form-label">SNMP Version</label>
                    <select name="snmp_version" id="snmp_version" class="form-select">
                        <option value="1" {{ old('snmp_version', $device->snmp_version) == '1' ? 'selected' : '' }}>v1</option>
                        <option value="2c" {{ old('snmp_version', $device->snmp_version) == '2c' ? 'selected' : '' }}>v2c</option>
                        <option value="3" {{ old('snmp_version', $device->snmp_version) == '3' ? 'selected' : '' }}>v3</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="snmp_port" class="form-label">SNMP Port</label>
                    <input type="number" name="snmp_port" id="snmp_port" 
                           class="form-control" min="1" max="65535"
                           value="{{ old('snmp_port', $device->snmp_port) }}">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="snmp_timeout" class="form-label">Timeout (seconds)</label>
                    <input type="number" name="snmp_timeout" id="snmp_timeout" 
                           class="form-control" min="1" max="10"
                           value="{{ old('snmp_timeout', $device->snmp_timeout) }}">
                </div>
                <div class="col-md-4 mb-3">
                    <label for="snmp_polling_interval" class="form-label">Polling Interval</label>
                    <select name="snmp_polling_interval" id="snmp_polling_interval" class="form-select">
                        <option value="60" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 60 ? 'selected' : '' }}>Every 1 min (Critical)</option>
                        <option value="300" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 300 ? 'selected' : '' }}>Every 5 min (Normal)</option>
                        <option value="900" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 900 ? 'selected' : '' }}>Every 15 min (Low)</option>
                        <option value="1800" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 1800 ? 'selected' : '' }}>Every 30 min</option>
                        <option value="3600" {{ old('snmp_polling_interval', $device->snmp_polling_interval) == 3600 ? 'selected' : '' }}>Every 1 hour</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div id="snmpStatus" class="w-100">
                        @if($device->snmp_enabled)
                            <span class="badge bg-warning text-dark">Not Tested Recently</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSNMPSettings() {
        if ($('#snmp_enabled').is(':checked')) {
            $('#snmpSettings').slideDown();
        } else {
            $('#snmpSettings').slideUp();
        }
    }

    function testSNMP() {
        const deviceId = {{ $device->id }};
        const statusEl = $('#snmpStatus');
        
        statusEl.html('<span class="badge bg-info"><i class="fas fa-spinner fa-spin me-1"></i> Testing...</span>');

        $.ajax({
            url: `/snmp/devices/${deviceId}/test`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    statusEl.html('<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Connected</span>');
                    Swal.fire({
                        title: 'SNMP Connection Successful!',
                        html: `
                            <div class="text-start">
                                <p><strong>Device:</strong> ${response.data.name || 'N/A'}</p>
                                <p><strong>Description:</strong> ${response.data.description || 'N/A'}</p>
                                <p><strong>Uptime:</strong> ${response.data.uptime || 'N/A'}</p>
                            </div>
                        `,
                        icon: 'success'
                    });
                } else {
                    statusEl.html('<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Failed</span>');
                    Swal.fire('Connection Failed', response.message || 'Check SNMP settings', 'error');
                }
            },
            error: function(xhr) {
                statusEl.html('<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Error</span>');
                Swal.fire('Error', xhr.responseJSON?.message || 'Connection test failed', 'error');
            }
        });
    }
</script>
@endpush
@endsection


