@extends('layouts.app')

@section('title', 'Create Rack')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-plus-circle me-2"></i>Create New Rack
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('racks.index') }}">Racks</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rack Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('racks.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Rack Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}" required
                                       placeholder="e.g., Rack-A-01">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
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
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="total_units" class="form-label">Total Units (U) <span class="text-danger">*</span></label>
                                <select name="total_units" id="total_units" class="form-select @error('total_units') is-invalid @enderror" required>
                                    <option value="42" {{ old('total_units', 42) == 42 ? 'selected' : '' }}>42U (Standard)</option>
                                    <option value="24" {{ old('total_units') == 24 ? 'selected' : '' }}>24U (Half)</option>
                                    <option value="48" {{ old('total_units') == 48 ? 'selected' : '' }}>48U (Large)</option>
                                    <option value="52" {{ old('total_units') == 52 ? 'selected' : '' }}>52U (Extra Large)</option>
                                </select>
                                @error('total_units')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="position_x" class="form-label">Position X</label>
                                <input type="number" name="position_x" id="position_x" 
                                       class="form-control" value="{{ old('position_x') }}"
                                       placeholder="Row position">
                            </div>
                            <div class="col-md-4">
                                <label for="position_y" class="form-label">Position Y</label>
                                <input type="number" name="position_y" id="position_y" 
                                       class="form-control" value="{{ old('position_y') }}"
                                       placeholder="Column position">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control" placeholder="Optional description">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('racks.index') }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Create Rack
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Rack Preview</h6>
                </div>
                <div class="card-body text-center">
                    <div style="border: 2px solid #4e73df; width: 120px; margin: 0 auto; padding: 10px; background: #2c3e50;">
                        <div class="text-white small mb-2">42U Rack</div>
                        @for($i = 0; $i < 21; $i++)
                            <div style="height: 6px; background: #34495e; margin: 2px 0;"></div>
                        @endfor
                    </div>
                    <small class="text-muted d-block mt-2">Standard 42U rack visualization</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection