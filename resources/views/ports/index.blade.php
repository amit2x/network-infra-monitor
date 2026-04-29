@extends('layouts.app')

@section('title', 'Port Management - ' . $device->name)

@section('content')
<div class="container-fluid">
    <!-- Device Info Header -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-1">
                        <a href="{{ route('devices.show', $device->id) }}" class="text-decoration-none">
                            {{ $device->name }}
                        </a>
                    </h4>
                    <div class="text-muted">
                        <span class="me-3">
                            <i class="fas fa-network-wired me-1"></i> {{ $device->ip_address }}
                        </span>
                        <span class="me-3">
                            <i class="fas fa-map-marker-alt me-1"></i> {{ $device->location->full_path }}
                        </span>
                        <span class="badge bg-{{ $device->status === 'online' ? 'success' : 'danger' }}">
                            {{ ucfirst($device->status) }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-sm btn-success" onclick="exportPorts()">
                            <i class="fas fa-download me-1"></i> Export
                        </button>
                        <button class="btn btn-sm btn-primary" onclick="bulkEditMode()">
                            <i class="fas fa-edit me-1"></i> Bulk Edit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Active Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $ports->where('status', 'active')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-secondary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">Free Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $ports->where('status', 'free')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Down Ports</div>
                    <div class="h5 mb-0 font-weight-bold">{{ $ports->where('status', 'down')->count() }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Utilization</div>
                    <div class="h5 mb-0 font-weight-bold">
                        {{ $ports->count() > 0 ? round(($ports->where('status', 'active')->count() / $ports->count()) * 100, 1) : 0 }}%
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Port Grid View -->
    <div class="card shadow">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Port Grid View</h6>
            <div>
                <button class="btn btn-sm btn-light" onclick="switchView('grid')" id="gridViewBtn">
                    <i class="fas fa-th"></i> Grid
                </button>
                <button class="btn btn-sm btn-light" onclick="switchView('list')" id="listViewBtn">
                    <i class="fas fa-list"></i> List
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Legend -->
            <div class="d-flex flex-wrap mb-4 gap-3">
                <span class="badge bg-success p-2">
                    <i class="fas fa-plug me-1"></i> Active
                </span>
                <span class="badge bg-light text-dark p-2">
                    <i class="fas fa-circle me-1"></i> Free
                </span>
                <span class="badge bg-danger p-2">
                    <i class="fas fa-exclamation-triangle me-1"></i> Down
                </span>
                <span class="badge bg-secondary p-2">
                    <i class="fas fa-ban me-1"></i> Disabled
                </span>
            </div>

            <!-- Port Grid -->
            <div id="portGridView">
                <div class="row g-2">
                    @foreach($ports as $port)
                    <div class="col-6 col-md-3 col-lg-2">
                        <div class="card port-card border-{{
                            $port->status === 'active' ? 'success' :
                            ($port->status === 'down' ? 'danger' :
                            ($port->status === 'disabled' ? 'secondary' : 'light'))
                        }} cursor-pointer"
                             onclick="openPortModal({{ $device->id }}, {{ $port->id }})"
                             data-port-id="{{ $port->id }}">
                            <div class="card-body text-center p-2">
                                <div class="port-number font-weight-bold mb-1">
                                    {{ $port->port_number }}
                                </div>
                                <div class="port-type small text-muted mb-1">
                                    @if($port->type === 'sfp' || $port->type === 'sfp_plus')
                                        <i class="fas fa-wave-square text-info"></i> SFP
                                    @elseif($port->type === 'copper')
                                        <i class="fas fa-network-wired text-warning"></i> Copper
                                    @else
                                        <i class="fas fa-plug"></i> {{ ucfirst($port->type) }}
                                    @endif
                                </div>
                                <div class="port-status">
                                    <span class="badge bg-{{
                                        $port->status === 'active' ? 'success' :
                                        ($port->status === 'down' ? 'danger' :
                                        ($port->status === 'disabled' ? 'secondary' : 'light'))
                                    }}">
                                        {{ ucfirst($port->status) }}
                                    </span>
                                </div>
                                @if($port->service_name)
                                <div class="small mt-1 text-truncate" title="{{ $port->service_name }}">
                                    {{ $port->service_name }}
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Port List View (Hidden by default) -->
            <div id="portListView" style="display: none;">
                <div class="table-responsive">
                    <table class="table table-hover" id="portsTable">
                        <thead>
                            <tr>
                                <th>Port #</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Service</th>
                                <th>Connected Device</th>
                                <th>VLAN</th>
                                <th>Speed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($ports as $port)
                            <tr>
                                <td>{{ $port->port_number }}</td>
                                <td>
                                    <span class="badge bg-{{ $port->type === 'sfp' ? 'info' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $port->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{
                                        $port->status === 'active' ? 'success' :
                                        ($port->status === 'down' ? 'danger' :
                                        ($port->status === 'disabled' ? 'secondary' : 'light'))
                                    }}">
                                        {{ ucfirst($port->status) }}
                                    </span>
                                </td>
                                <td>{{ $port->service_name ?? '-' }}</td>
                                <td>{{ $port->connected_device ?? '-' }}</td>
                                <td>{{ $port->vlan_id ?? '-' }}</td>
                                <td>{{ $port->speed_mbps ? $port->speed_mbps . ' Mbps' : '-' }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                            onclick="openPortModal({{ $device->id }}, {{ $port->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Port Edit Modal -->
<div class="modal fade" id="portModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Port Configuration</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="portModalContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .port-card {
        transition: all 0.3s;
        cursor: pointer;
    }
    .port-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .port-number {
        font-size: 1.2rem;
    }
    .cursor-pointer {
        cursor: pointer;
    }
</style>
@endpush

@push('scripts')
<script>

    function switchView(view) {
        if (view === 'grid') {
            $('#portGridView').show();
            $('#portListView').hide();
            $('#gridViewBtn').addClass('active');
            $('#listViewBtn').removeClass('active');
        } else {
            $('#portGridView').hide();
            $('#portListView').show();
            $('#gridViewBtn').removeClass('active');
            $('#listViewBtn').addClass('active');
        }
    }

    function openPortModal(deviceId, portId) {
        const modal = new bootstrap.Modal(document.getElementById('portModal'));

        $.get(`/devices/${deviceId}/ports/${portId}/edit`, function(response) {
            $('#portModalContent').html(response);
            modal.show();
        });
    }

    function updatePort(event, deviceId, portId) {
        event.preventDefault();
        const form = $('#portForm');
        const formData = form.serialize();

        $.ajax({
            url: `/devices/${deviceId}/ports/${portId}`,
            method: 'PUT',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                bootstrap.Modal.getInstance(document.getElementById('portModal')).hide();
                Swal.fire({
                    title: 'Success',
                    text: 'Port updated successfully',
                    icon: 'success',
                    timer: 1500
                }).then(() => location.reload());
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMessage = '';
                for (const key in errors) {
                    errorMessage += errors[key] + '\n';
                }
                Swal.fire({
                    title: 'Error',
                    text: errorMessage,
                    icon: 'error'
                });
            }
        });
    }

    function bulkEditMode() {
        // Toggle checkboxes for bulk editing
        $('.port-card').each(function() {
            const checkbox = $(this).find('input[type="checkbox"]');
            if (checkbox.length === 0) {
                $(this).prepend('<input type="checkbox" class="port-checkbox position-absolute" style="top: 5px; left: 5px;">');
            }
        });

        // Show bulk action buttons
        if (!$('#bulkActions').length) {
            const bulkHtml = `
                <div id="bulkActions" class="my-3 p-3 bg-light rounded">
                    <h6>Bulk Actions</h6>
                    <div class="row g-2">
                        <div class="col-md-3">
                            <select class="form-select form-select-sm" id="bulkStatus">
                                <option value="">Set Status</option>
                                <option value="active">Active</option>
                                <option value="free">Free</option>
                                <option value="down">Down</option>
                                <option value="disabled">Disabled</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="text" class="form-control form-control-sm" id="bulkService" placeholder="Service Name">
                        </div>
                        <div class="col-md-2">
                            <input type="number" class="form-control form-control-sm" id="bulkVlan" placeholder="VLAN ID" min="1" max="4096">
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-primary btn-sm w-100" onclick="applyBulkUpdate()">
                                Apply
                            </button>
                        </div>
                        <div class="col-md-2">
                            <button class="btn btn-secondary btn-sm w-100" onclick="cancelBulkEdit()">
                                Cancel
                            </button>
                        </div>
                    </div>
                </div>
            `;
            $('.card-body').first().prepend(bulkHtml);
        }
    }

    function applyBulkUpdate() {
        const selectedPorts = [];
        $('.port-checkbox:checked').each(function() {
            selectedPorts.push($(this).closest('.port-card').data('port-id'));
        });

        if (selectedPorts.length === 0) {
            Swal.fire('Warning', 'Please select at least one port', 'warning');
            return;
        }

        const data = {
            ports: selectedPorts.map(id => ({
                id: id,
                status: $('#bulkStatus').val(),
                service_name: $('#bulkService').val(),
                vlan_id: $('#bulkVlan').val()
            }))
        };

        $.ajax({
            url: `/devices/{{ $device->id }}/ports/bulk-update`,
            method: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success',
                    text: response.message,
                    icon: 'success'
                }).then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error',
                    text: xhr.responseJSON.message || 'Failed to update ports',
                    icon: 'error'
                });
            }
        });
    }

    function cancelBulkEdit() {
        $('.port-checkbox').remove();
        $('#bulkActions').remove();
    }

    function exportPorts() {
        // Export logic here
        Swal.fire({
            title: 'Export',
            text: 'Port export functionality coming soon',
            icon: 'info'
        });
    }
</script>
@endpush
@endsection
