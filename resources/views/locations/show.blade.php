@extends('layouts.app')

@section('title', 'Location Details - ' . $location->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-map-marker-alt me-2"></i>{{ $location->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('locations.index') }}">Locations</a></li>
                    <li class="breadcrumb-item active">{{ $location->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            <button class="btn btn-danger" onclick="deleteLocation({{ $location->id }})">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Location Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location Information</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Location Code</label>
                            <div><span class="badge bg-secondary">{{ $location->code }}</span></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted small">Type</label>
                            <div>
                                <span class="badge bg-{{
                                    $location->type === 'airport' ? 'primary' :
                                    ($location->type === 'terminal' ? 'info' :
                                    ($location->type === 'it_room' ? 'success' : 'warning'))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $location->type)) }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <!-- ... rest of location details ... -->
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Map -->
            @if($location->latitude && $location->longitude)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-map me-2"></i>Location Map
                    </h6>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 300px; width: 100%;">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center text-muted">
                                <div class="spinner-border spinner-border-sm mb-2" role="status">
                                    <span class="visually-hidden">Loading map...</span>
                                </div>
                                <p class="small">Loading map...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @if($location->latitude && $location->longitude)
                <div class="card-footer bg-light small text-muted">
                    <i class="fas fa-map-pin me-1"></i>
                    {{ number_format($location->latitude, 6) }}, {{ number_format($location->longitude, 6) }}
                </div>
                @endif
            </div>
            @else
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location Map</h6>
                </div>
                <div class="card-body text-center py-4">
                    <i class="fas fa-map-marked-alt fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No coordinates configured</p>
                    <a href="{{ route('locations.edit', $location->id) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-plus me-1"></i> Add Coordinates
                    </a>
                </div>
            </div>
            @endif

            <!-- Quick Stats -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Stats</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Devices</div>
                                <div class="h4 mb-0 text-primary">{{ $location->devices->count() }}</div>
                            </div>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="border rounded p-3 text-center">
                                <div class="text-muted small">Created</div>
                                <div class="h6 mb-0">{{ $location->created_at->format('d-M-Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($location->latitude && $location->longitude)
@push('scripts')
<script>
    // Fixed Google Maps implementation
    (function() {
        // Function to initialize the map
        function initMap() {
            try {
                const lat = {{ $location->latitude }};
                const lng = {{ $location->longitude }};
                const locationName = @json($location->name);
                const locationType = @json($location->type);
                const locationCode = @json($location->code);
                const locationAddress = @json($location->address);

                const position = { lat: lat, lng: lng };
                const mapElement = document.getElementById('map');

                // Create map
                const map = new google.maps.Map(mapElement, {
                    zoom: 15,
                    center: position,
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.TOP_RIGHT
                    },
                    streetViewControl: false,
                    fullscreenControl: true,
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    }
                });

                // Create marker
                const marker = new google.maps.Marker({
                    position: position,
                    map: map,
                    title: locationName,
                    animation: google.maps.Animation.DROP
                });

                // Create info window
                const infoWindowContent = `
                    <div style="padding: 10px; min-width: 200px;">
                        <h6 style="margin: 0 0 8px 0; color: #4e73df;">
                            <i class="fas fa-map-marker-alt"></i> ${locationName}
                        </h6>
                        <table style="font-size: 12px; width: 100%;">
                            <tr>
                                <td style="padding: 2px 5px;"><strong>Type:</strong></td>
                                <td style="padding: 2px 5px;">${locationType.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())}</td>
                            </tr>
                            <tr>
                                <td style="padding: 2px 5px;"><strong>Code:</strong></td>
                                <td style="padding: 2px 5px;">${locationCode}</td>
                            </tr>
                            ${locationAddress ? `
                            <tr>
                                <td style="padding: 2px 5px;"><strong>Address:</strong></td>
                                <td style="padding: 2px 5px;">${locationAddress}</td>
                            </tr>
                            ` : ''}
                            <tr>
                                <td style="padding: 2px 5px;"><strong>Coordinates:</strong></td>
                                <td style="padding: 2px 5px;">${lat.toFixed(6)}, ${lng.toFixed(6)}</td>
                            </tr>
                        </table>
                    </div>
                `;

                const infoWindow = new google.maps.InfoWindow({
                    content: infoWindowContent,
                    maxWidth: 300
                });

                // Open info window on marker click
                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });

                // Open info window by default
                infoWindow.open(map, marker);

                // Add circle for airports
                @if($location->type === 'airport')
                const circle = new google.maps.Circle({
                    strokeColor: '#4e73df',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#4e73df',
                    fillOpacity: 0.1,
                    map: map,
                    center: position,
                    radius: 2000, // 2km radius
                    clickable: false
                });
                @endif

                // Add a custom control for coordinates
                const coordDiv = document.createElement('div');
                coordDiv.style.backgroundColor = '#fff';
                coordDiv.style.padding = '5px 10px';
                coordDiv.style.fontSize = '12px';
                coordDiv.style.border = '1px solid #ccc';
                coordDiv.style.borderRadius = '4px';
                coordDiv.style.marginTop = '5px';
                coordDiv.innerHTML = `<i class="fas fa-crosshairs"></i> ${lat.toFixed(6)}, ${lng.toFixed(6)}`;

                map.controls[google.maps.ControlPosition.BOTTOM_LEFT].push(coordDiv);

                console.log('Google Maps loaded successfully');

            } catch (error) {
                console.error('Error initializing map:', error);
                showMapError(error.message);
            }
        }

        // Function to show map error
        function showMapError(message) {
            const mapElement = document.getElementById('map');
            mapElement.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100">
                    <div class="text-center text-muted p-3">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3 text-warning"></i>
                        <p class="mb-1"><strong>Map Error</strong></p>
                        <p class="small mb-2">${message || 'Failed to load Google Maps'}</p>
                        <button class="btn btn-sm btn-outline-primary" onclick="location.reload()">
                            <i class="fas fa-sync-alt me-1"></i> Reload
                        </button>
                    </div>
                </div>
            `;
        }

        // Function to load Google Maps script
        function loadGoogleMapsScript() {
            const apiKey = '{{ config("services.google_maps.api_key", "AIzaSyCFF7jVShzevQigbcn0r4PlVO4is419SB8") }}';

            if (!apiKey || apiKey === 'YOUR_API_KEY') {
                showMapError('Google Maps API key not configured');
                return;
            }

            // Remove any existing Google Maps scripts
            const existingScripts = document.querySelectorAll('script[src*="maps.googleapis.com"]');
            existingScripts.forEach(script => script.remove());

            // Create and load the script
            const script = document.createElement('script');
            script.src = `https://maps.googleapis.com/maps/api/js?key=${apiKey}&callback=initMap&loading=async&v=weekly`;
            script.async = true;
            script.defer = true;

            script.onerror = function() {
                showMapError('Failed to load Google Maps. Check your API key and internet connection.');
            };

            // Make initMap globally accessible
            window.initMap = initMap;

            document.head.appendChild(script);

            // Set a timeout for map loading
            setTimeout(function() {
                if (!window.google || !window.google.maps) {
                    showMapError('Map loading timed out. Please check your connection.');
                }
            }, 10000); // 10 seconds timeout
        }

        // Load map when page is ready
        if (document.readyState === 'complete' || document.readyState === 'interactive') {
            loadGoogleMapsScript();
        } else {
            document.addEventListener('DOMContentLoaded', loadGoogleMapsScript);
        }
    })();
</script>
@endpush
@endif

@push('scripts')
<script>
    function deleteLocation(id) {
        Swal.fire({
            title: 'Delete Location?',
            text: "This action cannot be undone! All associated data will be deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/locations/${id}`;
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
