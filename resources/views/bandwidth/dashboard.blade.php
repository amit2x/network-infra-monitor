@extends('layouts.app')

@section('title', 'Bandwidth Monitoring')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-chart-area me-2"></i>Bandwidth Monitoring
            </h1>
        </div>
        <button class="btn btn-success" onclick="collectAllBandwidth()">
            <i class="fas fa-download me-1"></i> Collect Now
        </button>
    </div>

    <!-- Top Talkers -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Talkers (Last Hour)</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Device</th>
                                    <th>Port</th>
                                    <th>Inbound</th>
                                    <th>Outbound</th>
                                    <th>In %</th>
                                    <th>Out %</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topTalkers as $talker)
                                <tr>
                                    <td>{{ $talker['device_name'] }}</td>
                                    <td>Port {{ $talker['port_number'] }}</td>
                                    <td><span class="text-primary">{{ $talker['in_bandwidth'] }}</span></td>
                                    <td><span class="text-success">{{ $talker['out_bandwidth'] }}</span></td>
                                    <td>
                                        <div class="progress" style="height: 8px; width: 100px;">
                                            <div class="progress-bar bg-{{ $talker['in_utilization'] > 80 ? 'danger' : ($talker['in_utilization'] > 50 ? 'warning' : 'success') }}"
                                                 style="width: {{ min($talker['in_utilization'], 100) }}%"></div>
                                        </div>
                                        <small>{{ $talker['in_utilization'] }}%</small>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 8px; width: 100px;">
                                            <div class="progress-bar bg-{{ $talker['out_utilization'] > 80 ? 'danger' : ($talker['out_utilization'] > 50 ? 'warning' : 'success') }}"
                                                 style="width: {{ min($talker['out_utilization'], 100) }}%"></div>
                                        </div>
                                        <small>{{ $talker['out_utilization'] }}%</small>
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

    <!-- Device Bandwidth Graphs -->
    <div class="row" id="bandwidthGraphs">
        @foreach($devices->take(4) as $device)
        <div class="col-lg-6 mb-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ $device->name }} - Bandwidth</h6>
                </div>
                <div class="card-body">
                    <canvas id="chart-{{ $device->id }}" height="250"></canvas>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    function collectAllBandwidth() {
        Swal.fire({
            title: 'Collecting bandwidth data...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        $.post('/bandwidth/collect-all', {
            _token: '{{ csrf_token() }}'
        }, function(response) {
            Swal.fire('Complete!', response.message, 'success');
        });
    }
</script>
@endpush
@endsection