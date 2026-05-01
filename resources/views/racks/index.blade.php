@extends('layouts.app')

@section('title', 'Rack Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-server me-2"></i>Rack Management
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Racks</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('racks.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Rack
        </a>
    </div>

    <!-- Filters -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Search rack name or code..."
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="location_id" class="form-select">
                        <option value="">All Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->full_path ?? $location->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Racks Grid -->
    <div class="row">
        @forelse($racks as $rack)
        <div class="col-xl-4 col-lg-6 mb-4">
            <div class="card shadow h-100">
                <div class="card-header py-3 d-flex justify-content-between align-items-center bg-white">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-server me-2"></i>{{ $rack->name }}
                    </h6>
                    <span class="badge bg-secondary">{{ $rack->rack_code }}</span>
                </div>
                <div class="card-body">
                    <!-- Mini Rack Visualization -->
                    <div class="rack-preview mb-3" style="height: 200px; overflow: hidden; border: 1px solid #ddd; background: #f8f9fc;">
                        <div class="rack-mini-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1px;">
                            @for($i = 1; $i <= min($rack->total_units, 20); $i++)
                                @php
                                    $item = $rack->rackItems->firstWhere('unit_start', $i);
                                @endphp
                                <div class="rack-unit {{ $item ? '' : 'empty' }}" 
                                     style="height: 10px; background: {{ $item ? ($item->device->status === 'online' ? '#1cc88a' : '#e74a3b') : '#ddd' }};"
                                     title="{{ $item ? $item->device->name : 'Empty U' . $i }}">
                                </div>
                            @endfor
                        </div>
                    </div>
                    
                    <div class="row small text-muted mb-3">
                        <div class="col-6">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $rack->location->name ?? 'N/A' }}
                        </div>
                        <div class="col-6 text-end">
                            <i class="fas fa-layer-group me-1"></i>
                            {{ $rack->total_units }}U
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-info">{{ $rack->rackItems->count() }} device(s)</span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('racks.show', $rack->id) }}" class="btn btn-info">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ route('racks.edit', $rack->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger" onclick="deleteRack({{ $rack->id }}, '{{ $rack->name }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-server fa-4x text-muted mb-3"></i>
                <h4>No Racks Found</h4>
                <p class="text-muted">Create your first rack to start managing device placement.</p>
                <a href="{{ route('racks.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create Rack
                </a>
            </div>
        </div>
        @endforelse
    </div>

    <div class="d-flex justify-content-center">
        {{ $racks->links() }}
    </div>
</div>

@push('scripts')
<script>
    function deleteRack(id, name) {
        Swal.fire({
            title: 'Delete Rack?',
            text: `Are you sure you want to delete "${name}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/racks/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush
@endsection