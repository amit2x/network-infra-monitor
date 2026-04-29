@extends('layouts.app')

@section('title', 'Locations')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-map-marker-alt me-2"></i>Locations
        </h1>
        <a href="{{ route('locations.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Location
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('locations.index') }}" class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by name or code..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="airport" {{ request('type') == 'airport' ? 'selected' : '' }}>Airport</option>
                        <option value="terminal" {{ request('type') == 'terminal' ? 'selected' : '' }}>Terminal</option>
                        <option value="it_room" {{ request('type') == 'it_room' ? 'selected' : '' }}>IT Room</option>
                        <option value="rack" {{ request('type') == 'rack' ? 'selected' : '' }}>Rack</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Apply Filters
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('locations.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Locations Table -->
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover" id="locationsTable">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Parent Location</th>
                            <th>Devices</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($locations as $location)
                        <tr>
                            <td>
                                <span class="badge bg-secondary">{{ $location->code }}</span>
                            </td>
                            <td>
                                <a href="{{ route('locations.show', $location->id) }}" class="text-decoration-none">
                                    {{ $location->name }}
                                </a>
                            </td>
                            <td>
                                <span class="badge bg-{{
                                    $location->type === 'airport' ? 'primary' :
                                    ($location->type === 'terminal' ? 'info' :
                                    ($location->type === 'it_room' ? 'success' : 'warning'))
                                }}">
                                    {{ ucfirst(str_replace('_', ' ', $location->type)) }}
                                </span>
                            </td>
                            <td>{{ $location->parent ? $location->parent->full_path : '-' }}</td>
                            <td>
                                <span class="badge bg-info">{{ $location->devices_count }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $location->is_active ? 'success' : 'danger' }}">
                                    {{ $location->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('locations.show', $location->id) }}"
                                       class="btn btn-sm btn-info" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('locations.edit', $location->id) }}"
                                       class="btn btn-sm btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-danger"
                                            onclick="deleteLocation({{ $location->id }})" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-map-marker-alt fa-3x text-muted mb-3"></i>
                                <h5>No Locations Found</h5>
                                <p class="text-muted">Start by adding your first location.</p>
                                <a href="{{ route('locations.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-1"></i> Add Location
                                </a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $locations->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this location? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>

    function deleteLocation(id) {
        const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
        document.getElementById('deleteForm').action = `/locations/${id}`;
        modal.show();
    }
</script>
@endpush
@endsection
