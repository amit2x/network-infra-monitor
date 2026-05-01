@extends('layouts.app')

@section('title', 'Audit Detail')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-search me-2"></i>Audit Detail
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.audit.index') }}">Audit Trail</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Activity Details</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Action</label>
                            <div>
                                <span class="badge bg-{{ $activity->action_color }} px-3 py-2">
                                    <i class="fas {{ $activity->action_icon }} me-1"></i>
                                    {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted small">Module</label>
                            <div>
                                <i class="fas {{ $activity->module_icon }} me-1"></i>
                                {{ $activity->module }}
                                @if($activity->module_id)
                                    (#{{ $activity->module_id }})
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small">Description</label>
                        <div>{{ $activity->description }}</div>
                    </div>

                    @if($activity->old_values)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Old Values</label>
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->old_values, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif

                    @if($activity->new_values)
                    <div class="mb-3">
                        <label class="form-label text-muted small">New Values</label>
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->new_values, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif

                    @if($activity->metadata)
                    <div class="mb-3">
                        <label class="form-label text-muted small">Additional Data</label>
                        <pre class="bg-light p-3 rounded"><code>{{ json_encode($activity->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">User Information</h6>
                </div>
                <div class="card-body">
                    @if($activity->user)
                        <div class="text-center mb-3">
                            <div class="avatar-circle mx-auto mb-2" style="width: 60px; height: 60px; background-color: #4e73df;">
                                <span style="font-size: 1.5rem; color: white;">
                                    {{ strtoupper(substr($activity->user->name, 0, 2)) }}
                                </span>
                            </div>
                            <h6>{{ $activity->user->name }}</h6>
                            <small class="text-muted">{{ $activity->user->email }}</small>
                        </div>
                    @else
                        <p class="text-muted text-center">System Action</p>
                    @endif
                    <hr>
                    <div class="mb-2">
                        <small class="text-muted">Role</small>
                        <div>{{ ucfirst($activity->user_role ?? 'System') }}</div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Technical Details</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Date/Time</small>
                        <div>{{ $activity->performed_at->format('d-M-Y H:i:s') }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">IP Address</small>
                        <div>{{ $activity->ip_address }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">URL</small>
                        <div class="text-break">{{ $activity->url }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Method</small>
                        <div>{{ $activity->method }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Browser</small>
                        <div>{{ $activity->browser }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Platform</small>
                        <div>{{ $activity->platform }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Status</small>
                        <div>
                            <span class="badge bg-{{ $activity->status === 'success' ? 'success' : 'danger' }}">
                                {{ ucfirst($activity->status) }}
                            </span>
                        </div>
                    </div>
                    @if($activity->error_message)
                    <div class="mb-2">
                        <small class="text-muted">Error Message</small>
                        <div class="text-danger">{{ $activity->error_message }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
