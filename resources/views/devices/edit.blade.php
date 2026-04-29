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
@endsection
