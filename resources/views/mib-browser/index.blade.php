@extends('layouts.app')

@section('title', 'SNMP MIB Browser')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-search me-2"></i>SNMP MIB Browser
            </h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-3">
            <!-- Device Selection -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Select Device</h6>
                </div>
                <div class="card-body">
                    <select id="deviceSelect" class="form-select mb-3" onchange="selectDevice()">
                        <option value="">Choose a device...</option>
                        @foreach($devices as $device)
                            <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->ip_address }})</option>
                        @endforeach
                    </select>
                    <div id="deviceInfo" style="display:none;">
                        <small class="text-muted">Community: <span id="deviceCommunity"></span></small>
                    </div>
                </div>
            </div>

            <!-- Common OIDs -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Common OIDs</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @foreach($commonOIDs as $oid)
                        <a href="#" class="list-group-item list-group-item-action" 
                           onclick="walkOID('{{ $oid['oid'] }}')">
                            <small class="d-block text-muted">{{ $oid['oid'] }}</small>
                            <span>{{ $oid['name'] }}</span>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-9">
            <!-- OID Input -->
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-sitemap"></i></span>
                                <input type="text" id="oidInput" class="form-control" 
                                       placeholder="Enter OID (e.g., 1.3.6.1.2.1.1.1.0)">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary w-100" onclick="getOID()">
                                <i class="fas fa-search me-1"></i> GET
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-info w-100" onclick="walkOID()">
                                <i class="fas fa-list me-1"></i> WALK
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Results -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary" id="resultTitle">Results</h6>
                    <span class="badge bg-secondary" id="resultCount" style="display:none;"></span>
                </div>
                <div class="card-body">
                    <div id="resultsContainer">
                        <div class="text-center text-muted py-5">
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <h5>SNMP MIB Browser</h5>
                            <p>Select a device and enter an OID to query.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let selectedDeviceId = null;

    function selectDevice() {
        selectedDeviceId = $('#deviceSelect').val();
        if (selectedDeviceId) {
            $('#deviceInfo').show();
            const deviceName = $('#deviceSelect option:selected').text();
            $('#deviceCommunity').text('******');
        }
    }

    function getOID() {
        const oid = $('#oidInput').val().trim();
        if (!oid) {
            Swal.fire('Error', 'Please enter an OID', 'error');
            return;
        }
        if (!selectedDeviceId) {
            Swal.fire('Error', 'Please select a device first', 'error');
            return;
        }

        $('#resultsContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Querying OID...</p>
            </div>
        `);

        $.get(`/mib-browser/devices/${selectedDeviceId}/get`, { oid: oid }, function(response) {
            if (response.success) {
                $('#resultTitle').text('GET Result');
                $('#resultCount').hide();
                
                let html = `
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>OID</th>
                                    <th>Value</th>
                                    <th>Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>${oid}</code></td>
                                    <td><strong>${response.data || 'No value'}</strong></td>
                                    <td><span class="badge bg-info">SNMP GET</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `;
                $('#resultsContainer').html(html);
            } else {
                $('#resultsContainer').html(`
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-1"></i>
                        No response for OID: ${oid}
                    </div>
                `);
            }
        });
    }

    function walkOID(oid = null) {
        const queryOid = oid || $('#oidInput').val().trim();
        if (!queryOid) {
            Swal.fire('Error', 'Please enter an OID', 'error');
            return;
        }
        if (!selectedDeviceId) {
            Swal.fire('Error', 'Please select a device first', 'error');
            return;
        }

        $('#resultsContainer').html(`
            <div class="text-center py-4">
                <div class="spinner-border text-primary"></div>
                <p class="mt-2">Walking OID tree...</p>
            </div>
        `);

        $.get(`/mib-browser/devices/${selectedDeviceId}/walk`, { oid: queryOid }, function(response) {
            if (response.success) {
                $('#resultTitle').text('WALK Results');
                $('#resultCount').text(response.count + ' results').show();
                
                let html = `
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>OID</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                Object.entries(response.data).forEach(([oid, value], index) => {
                    html += `
                        <tr>
                            <td>${index + 1}</td>
                            <td><code>${oid}</code></td>
                            <td>${value || 'N/A'}</td>
                        </tr>
                    `;
                });

                html += '</tbody></table></div>';
                $('#resultsContainer').html(html);
            }
        });
    }
</script>
@endpush
@endsection