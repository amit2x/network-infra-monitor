@extends('layouts.app')

@section('title', 'Edit Location')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>Edit Location: {{ $location->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('locations.index') }}">Locations</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('locations.show', $location->id) }}">{{ $location->name }}</a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('locations.show', $location->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Location
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('locations.update', $location->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Location Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $location->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="type" class="form-label">Location Type <span class="text-danger">*</span></label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">Select Type</option>
                                    <option value="airport" {{ old('type', $location->type) == 'airport' ? 'selected' : '' }}>Airport</option>
                                    <option value="terminal" {{ old('type', $location->type) == 'terminal' ? 'selected' : '' }}>Terminal</option>
                                    <option value="it_room" {{ old('type', $location->type) == 'it_room' ? 'selected' : '' }}>IT Room</option>
                                    <option value="rack" {{ old('type', $location->type) == 'rack' ? 'selected' : '' }}>Rack</option>
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="parent_id" class="form-label">Parent Location</label>
                            <select name="parent_id" id="parent_id" class="form-select">
                                <option value="">None (Top Level)</option>
                                @foreach($parentLocations as $parent)
                                    <option value="{{ $parent['id'] }}" {{ old('parent_id', $location->parent_id) == $parent['id'] ? 'selected' : '' }}>
                                        {{ $parent['name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <hr>
                        <h6 class="font-weight-bold mb-3">Address Details</h6>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea name="address" id="address" rows="2"
                                      class="form-control">{{ old('address', $location->address) }}</textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="city" class="form-label">City</label>
                                <input type="text" name="city" id="city"
                                       class="form-control" value="{{ old('city', $location->city) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="state" class="form-label">State</label>
                                <input type="text" name="state" id="state"
                                       class="form-control" value="{{ old('state', $location->state) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" name="pincode" id="pincode"
                                       class="form-control" value="{{ old('pincode', $location->pincode) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="latitude" class="form-label">Latitude</label>
                                <input type="number" step="any" name="latitude" id="latitude"
                                       class="form-control" value="{{ old('latitude', $location->latitude) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="longitude" class="form-label">Longitude</label>
                                <input type="number" step="any" name="longitude" id="longitude"
                                       class="form-control" value="{{ old('longitude', $location->longitude) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3"
                                      class="form-control">{{ old('description', $location->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1"
                                       {{ old('is_active', $location->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Active Location</label>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('locations.show', $location->id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i> Update Location
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Location Info</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Code</small>
                        <div><span class="badge bg-secondary">{{ $location->code }}</span></div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Full Path</small>
                        <div>{{ $location->full_path }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Created</small>
                        <div>{{ $location->created_at->format('d-M-Y') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
