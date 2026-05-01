@extends('layouts.app')

@section('title', 'Network Topology')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-project-diagram me-2"></i>Network Topology
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Topology</li>
                </ol>
            </nav>
        </div>
        <div>
            <button class="btn btn-success" onclick="discoverAllTopology()">
                <i class="fas fa-search-plus me-1"></i> Discover All
            </button>
            <button class="btn btn-info" onclick="refreshTopology()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Topology Map</h6>
                </div>
                <div class="card-body p-0">
                    <div id="topologyMap" style="height: 600px; background: #f8f9fc;"></div>
                </div>
            </div>
        </div>
        <div class="col-lg-3">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Devices</h6>
                </div>
                <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                    <div class="list-group list-group-flush">
                        @foreach($devices as $device)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-{{ $device->type === 'router' ? 'network-wired' : 'server' }} me-2"></i>
                                    {{ $device->name }}
                                </div>
                                <div>
                                    <span class="badge bg-{{ $device->status === 'online' ? 'success' : 'danger' }}">
                                        {{ ucfirst($device->status) }}
                                    </span>
                                    <button class="btn btn-sm btn-outline-info ms-1" 
                                            onclick="discoverDeviceTopology({{ $device->id }})"
                                            title="Discover neighbors">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted">{{ $device->ip_address }}</small>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/vis-network@9.1.6/dist/vis-network.min.js"></script>
<script>
    let network;

    $(document).ready(function() {
        loadTopology();
    });

    function loadTopology() {
        $.get('{{ route("topology.data") }}', function(data) {
            renderTopology(data);
        });
    }

    function renderTopology(data) {
        const container = document.getElementById('topologyMap');
        
        const nodes = new vis.DataSet(data.nodes.map(node => ({
            id: node.id,
            label: `${node.label}\n${node.ip}`,
            shape: node.shape || 'box',
            color: {
                background: node.color,
                border: '#333',
                highlight: { background: '#4e73df', border: '#333' }
            },
            font: { size: 12, face: 'Arial' },
            borderWidth: 2,
        })));

        const edges = new vis.DataSet(data.edges.map(edge => ({
            from: edge.from,
            to: edge.to,
            label: edge.label,
            arrows: 'to',
            color: { color: edge.color, highlight: '#4e73df' },
            smooth: { type: 'curvedCW', roundness: 0.2 },
        })));

        const options = {
            physics: {
                solver: 'forceAtlas2Based',
                forceAtlas2Based: {
                    gravitationalConstant: -50,
                    centralGravity: 0.01,
                    springLength: 200,
                    springConstant: 0.08,
                },
                stabilization: { iterations: 150 },
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                zoomView: true,
                dragView: true,
            },
            layout: {
                improvedLayout: true,
            },
        };

        if (network) {
            network.destroy();
        }

        network = new vis.Network(container, { nodes, edges }, options);
        
        // Click event
        network.on('click', function(params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                window.location.href = `/devices/${nodeId}`;
            }
        });
    }

    function discoverDeviceTopology(deviceId) {
        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        $.post(`/topology/devices/${deviceId}/discover`, {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            if (response.success) {
                showToast(response.message, 'success');
                loadTopology();
            }
        }).always(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-search"></i>';
        });
    }

    function discoverAllTopology() {
        Swal.fire({
            title: 'Discovering topology...',
            text: 'This may take a few minutes for large networks.',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('/topology/discover-all', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire('Complete!', response.message, 'success')
                .then(() => loadTopology());
        });
    }

    function refreshTopology() {
        loadTopology();
    }
</script>
@endpush
@endsection