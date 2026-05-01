@extends('layouts.app')

@section('title', 'Add New Device')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus-circle me-2"></i>Add New Device
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('devices.index') }}">Devices</a></li>
                    <li class="breadcrumb-item active">Add New</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('devices.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <form action="{{ route('devices.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf

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
                                       value="{{ old('name') }}" required maxlength="255"
                                       placeholder="e.g., Core-Switch-01">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="type" class="form-label">Device Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="switch" {{ old('type') == 'switch' ? 'selected' : '' }}>Switch</option>
                                    <option value="router" {{ old('type') == 'router' ? 'selected' : '' }}>Router</option>
                                    <option value="firewall" {{ old('type') == 'firewall' ? 'selected' : '' }}>Firewall</option>
                                    <option value="access_point" {{ old('type') == 'access_point' ? 'selected' : '' }}>Access Point</option>
                                    <option value="server" {{ old('type') == 'server' ? 'selected' : '' }}>Server</option>
                                    <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="vendor" class="form-label">Vendor <span class="text-danger">*</span></label>
                                <select name="vendor" id="vendor" class="form-select @error('vendor') is-invalid @enderror" required>
                                    <option value="">Select Vendor</option>
                                    <option value="Cisco" {{ old('vendor') == 'Cisco' ? 'selected' : '' }}>Cisco</option>
                                    <option value="Juniper" {{ old('vendor') == 'Juniper' ? 'selected' : '' }}>Juniper</option>
                                    <option value="HP" {{ old('vendor') == 'HP' ? 'selected' : '' }}>HP</option>
                                    <option value="Dell" {{ old('vendor') == 'Dell' ? 'selected' : '' }}>Dell</option>
                                    <option value="Arista" {{ old('vendor') == 'Arista' ? 'selected' : '' }}>Arista</option>
                                    <option value="Fortinet" {{ old('vendor') == 'Fortinet' ? 'selected' : '' }}>Fortinet</option>
                                    <option value="Other" {{ old('vendor') == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="model" class="form-label">Model <span class="text-danger">*</span></label>
                                <input type="text" name="model" id="model"
                                       class="form-control @error('model') is-invalid @enderror"
                                       value="{{ old('model') }}" required
                                       placeholder="e.g., WS-C2960X-48FPD-L">
                                @error('model')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="serial_number" class="form-label">Serial Number <span class="text-danger">*</span></label>
                                <input type="text" name="serial_number" id="serial_number"
                                       class="form-control @error('serial_number') is-invalid @enderror"
                                       value="{{ old('serial_number') }}" required
                                       placeholder="Unique serial number">
                                @error('serial_number')
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
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-network-wired"></i></span>
                                    <input type="text" name="ip_address" id="ip_address"
                                           class="form-control @error('ip_address') is-invalid @enderror"
                                           value="{{ old('ip_address') }}" required
                                           placeholder="192.168.1.1" data-inputmask="'alias': 'ip'">
                                </div>
                                @error('ip_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="mac_address" class="form-label">MAC Address</label>
                                <input type="text" name="mac_address" id="mac_address"
                                       class="form-control @error('mac_address') is-invalid @enderror"
                                       value="{{ old('mac_address') }}"
                                       placeholder="00:1B:44:11:3A:B7">
                                @error('mac_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="firmware_version" class="form-label">Firmware Version</label>
                                <input type="text" name="firmware_version" id="firmware_version"
                                       class="form-control @error('firmware_version') is-invalid @enderror"
                                       value="{{ old('firmware_version') }}"
                                       placeholder="e.g., 15.2(2)E7">
                                @error('firmware_version')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
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
                                <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                                <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    <option value="">Select Location</option>
                                    @foreach($locations as $location)
                                        <option value="{{ $location['id'] }}" {{ old('location_id') == $location['id'] ? 'selected' : '' }}>
                                            {{ $location['name'] }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('location_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="procurement_date" class="form-label">Procurement Date</label>
                                <input type="date" name="procurement_date" id="procurement_date"
                                       class="form-control @error('procurement_date') is-invalid @enderror"
                                       value="{{ old('procurement_date') }}">
                                @error('procurement_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="installation_date" class="form-label">Installation Date</label>
                                <input type="date" name="installation_date" id="installation_date"
                                       class="form-control @error('installation_date') is-invalid @enderror"
                                       value="{{ old('installation_date') }}">
                                @error('installation_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lifecycle Management -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lifecycle Management</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="warranty_expiry" class="form-label">Warranty Expiry Date</label>
                                <input type="date" name="warranty_expiry" id="warranty_expiry"
                                       class="form-control @error('warranty_expiry') is-invalid @enderror"
                                       value="{{ old('warranty_expiry') }}">
                                @error('warranty_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="amc_expiry" class="form-label">AMC Expiry Date</label>
                                <input type="date" name="amc_expiry" id="amc_expiry"
                                       class="form-control @error('amc_expiry') is-invalid @enderror"
                                       value="{{ old('amc_expiry') }}">
                                @error('amc_expiry')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="eol_date" class="form-label">End of Life Date</label>
                                <input type="date" name="eol_date" id="eol_date"
                                       class="form-control @error('eol_date') is-invalid @enderror"
                                       value="{{ old('eol_date') }}">
                                @error('eol_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Port Configuration (For Switch) -->
                <div class="card shadow mb-4" id="portConfig" style="display: none;">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Port Configuration</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            For switches, you can specify the number of ports. Default ports will be created automatically.
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="port_count" class="form-label">Number of Ports</label>
                                <select name="port_count" id="port_count" class="form-select">
                                    <option value="8">8 Ports</option>
                                    <option value="16">16 Ports</option>
                                    <option value="24" selected>24 Ports</option>
                                    <option value="48">48 Ports</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                
                 <!-- SNMP Configuration Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-project-diagram me-2"></i>SNMP Configuration
                        </h6>
                        <span class="badge bg-info">Optional</span>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info small">
                            <i class="fas fa-info-circle me-2"></i>
                            SNMP (Simple Network Management Protocol) enables advanced monitoring of CPU, memory, bandwidth, and interface statistics. Configure these settings if you want to use SNMP monitoring.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="snmp_enabled" 
                                           name="snmp_enabled" value="1" 
                                           {{ old('snmp_enabled') ? 'checked' : '' }}
                                           onchange="toggleSNMPSettings()">
                                    <label class="form-check-label fw-bold" for="snmp_enabled">
                                        Enable SNMP Monitoring
                                    </label>
                                </div>
                                <div class="form-text">Enable to collect performance metrics via SNMP</div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="snmp_polling_enabled" 
                                           name="snmp_polling_enabled" value="1"
                                           {{ old('snmp_polling_enabled') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="snmp_polling_enabled">
                                        Enable Auto Polling
                                    </label>
                                </div>
                                <div class="form-text">Automatically collect SNMP data at intervals</div>
                            </div>
                        </div>

                        <div id="snmpSettings" style="{{ old('snmp_enabled') ? '' : 'display:none;' }}">
                            <hr>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_community" class="form-label">SNMP Community String</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                                        <input type="text" name="snmp_community" id="snmp_community" 
                                               class="form-control @error('snmp_community') is-invalid @enderror"
                                               placeholder="public"
                                               value="{{ old('snmp_community') }}">
                                    </div>
                                    <div class="form-text">Community string for SNMP v1/v2c (default: public)</div>
                                    @error('snmp_community')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="snmp_version" class="form-label">SNMP Version</label>
                                    <select name="snmp_version" id="snmp_version" class="form-select">
                                        <option value="1" {{ old('snmp_version') == '1' ? 'selected' : '' }}>v1</option>
                                        <option value="2c" {{ old('snmp_version', '2c') == '2c' ? 'selected' : '' }}>v2c</option>
                                        <option value="3" {{ old('snmp_version') == '3' ? 'selected' : '' }}>v3</option>
                                    </select>
                                    <div class="form-text">SNMP protocol version</div>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="snmp_port" class="form-label">SNMP Port</label>
                                    <input type="number" name="snmp_port" id="snmp_port" 
                                           class="form-control @error('snmp_port') is-invalid @enderror"
                                           placeholder="161" min="1" max="65535"
                                           value="{{ old('snmp_port', 161) }}">
                                    <div class="form-text">Default SNMP port is 161</div>
                                    @error('snmp_port')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="snmp_timeout" class="form-label">Timeout (seconds)</label>
                                    <input type="number" name="snmp_timeout" id="snmp_timeout" 
                                           class="form-control @error('snmp_timeout') is-invalid @enderror"
                                           placeholder="1" min="1" max="10" step="0.5"
                                           value="{{ old('snmp_timeout', 1) }}">
                                    <div class="form-text">Response timeout in seconds</div>
                                    @error('snmp_timeout')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label for="snmp_polling_interval" class="form-label">Polling Interval</label>
                                    <select name="snmp_polling_interval" id="snmp_polling_interval" class="form-select">
                                        <option value="60" {{ old('snmp_polling_interval') == 60 ? 'selected' : '' }}>Every 1 min (Critical)</option>
                                        <option value="300" {{ old('snmp_polling_interval', 300) == 300 ? 'selected' : '' }}>Every 5 min (Normal)</option>
                                        <option value="900" {{ old('snmp_polling_interval') == 900 ? 'selected' : '' }}>Every 15 min (Low)</option>
                                        <option value="1800" {{ old('snmp_polling_interval') == 1800 ? 'selected' : '' }}>Every 30 min</option>
                                        <option value="3600" {{ old('snmp_polling_interval') == 3600 ? 'selected' : '' }}>Every 1 hour</option>
                                    </select>
                                    <div class="form-text">How often to poll this device</div>
                                </div>

                                <div class="col-md-4 mb-3 d-flex align-items-end">
                                    <div class="alert alert-warning small mb-0 w-100">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        <strong>Tip:</strong> Use shorter intervals for critical devices only.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Additional Settings -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Additional Settings</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_critical" name="is_critical" value="1" {{ old('is_critical') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_critical">Critical Device</label>
                                    <div class="form-text">Critical devices generate high-priority alerts</div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="monitoring_enabled" name="monitoring_enabled" value="1" {{ old('monitoring_enabled', true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="monitoring_enabled">Enable Monitoring</label>
                                    <div class="form-text">Device will be included in automated ping checks</div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <textarea name="remarks" id="remarks" rows="3"
                                      class="form-control @error('remarks') is-invalid @enderror"
                                      placeholder="Any additional notes or comments">{{ old('remarks') }}</textarea>
                            @error('remarks')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                <!-- Quick Info Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Device Code Preview</h6>
                    </div>
                    <div class="card-body text-center">
                        <div id="deviceCodePreview" class="h2 text-primary mb-0">
                            ---
                        </div>
                        <small class="text-muted">Auto-generated upon creation</small>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Help & Tips</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Device name should be descriptive and unique
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                IP address must be reachable for monitoring
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Serial number must be unique in the system
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                Select a rack location for physical mapping
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <a href="{{ route('devices.index') }}" class="btn btn-secondary btn-lg">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <div>
                        <button type="reset" class="btn btn-warning btn-lg me-2">
                            <i class="fas fa-undo me-1"></i> Reset
                        </button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save me-1"></i> Create Device
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Toggle SNMP settings visibility
    function toggleSNMPSettings() {
        if ($('#snmp_enabled').is(':checked')) {
            $('#snmpSettings').slideDown();
        } else {
            $('#snmpSettings').slideUp();
        }
    }
    $(document).ready(function() {
        // Initialize SNMP settings visibility
        toggleSNMPSettings();
        // Show/hide port configuration based on device type
        $('#type').on('change', function() {
            if ($(this).val() === 'switch') {
                $('#portConfig').slideDown();
            } else {
                $('#portConfig').slideUp();
            }

            // Generate device code preview
            const type = $(this).val();
            if (type) {
                const prefix = type.substring(0, 3).toUpperCase();
                $('#deviceCodePreview').text(prefix + '-XXXXXX');
            } else {
                $('#deviceCodePreview').text('---');
            }
        });

        // IP address masking
        $('#ip_address').on('input', function() {
            const value = $(this).val();
            const ipFormat = /^(\d{1,3}\.){3}\d{1,3}$/;

            if (value.length > 0 && !ipFormat.test(value)) {
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Form validation
        (function() {
            'use strict';
            const forms = document.querySelectorAll('.needs-validation');

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }

                    form.classList.add('was-validated');
                }, false);
            });
        })();
    });
</script>
@endpush
@endsection
