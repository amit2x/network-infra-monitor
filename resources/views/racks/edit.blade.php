@extends('layouts.app')

@section('title', 'Edit Rack - ' . $rack->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-edit me-2"></i>Edit Rack: {{ $rack->name }}
            </h1>
        </div>
        <a href="{{ route('racks.show', $rack->id) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Edit Rack Details</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('racks.update', $rack->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Rack Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $rack->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="location_id" class="form-label">Location <span class="text-danger">*</span></label>
                                <select name="location_id" id="location_id" class="form-select @error('location_id') is-invalid @enderror" required>
                                    @foreach($locations as $location)
                                        <option value="{{ $location['id'] }}" {{ old('location_id', $rack->location_id) == $location['id'] ? 'selected' : '' }}>
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
                                <select name="total_units" id="total_units" class="form-select" required>
                                    <option value="42" {{ old('total_units', $rack->total_units) == 42 ? 'selected' : '' }}>42U (Standard)</option>
                                    <option value="24" {{ old('total_units', $rack->total_units) == 24 ? 'selected' : '' }}>24U (Half)</option>
                                    <option value="48" {{ old('total_units', $rack->total_units) == 48 ? 'selected' : '' }}>48U (Large)</option>
                                    <option value="52" {{ old('total_units', $rack->total_units) == 52 ? 'selected' : '' }}>52U (Extra Large)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="position_x" class="form-label">Position X</label>
                                <input type="number" name="position_x" id="position_x" 
                                       class="form-control" value="{{ old('position_x', $rack->position_x) }}">
                            </div>
                            <div class="col-md-4">
                                <label for="position_y" class="form-label">Position Y</label>
                                <input type="number" name="position_y" id="position_y" 
                                       class="form-control" value="{{ old('position_y', $rack->position_y) }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" rows="3" 
                                      class="form-control">{{ old('description', $rack->description) }}</textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <a href="{{ route('racks.show', $rack->id) }}" class="btn btn-secondary me-2">Cancel</a>
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-save me-1"></i> Update Rack
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection