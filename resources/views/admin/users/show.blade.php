@extends('layouts.app')

@section('title', 'User Details - ' . $user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fas fa-user me-2"></i>{{ $user->name }}
            </h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.users.index') }}">Users</a></li>
                    <li class="breadcrumb-item active">{{ $user->name }}</li>
                </ol>
            </nav>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
                <i class="fas fa-edit me-1"></i> Edit
            </a>
            @if($user->id !== auth()->id())
            <button class="btn btn-danger" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="avatar-circle mx-auto mb-3" style="width: 100px; height: 100px; background-color: #4e73df;">
                        <span style="font-size: 2.5rem; color: white; line-height: 100px;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </span>
                    </div>
                    <h4>{{ $user->name }}</h4>
                    <p class="text-muted">{{ $user->email }}</p>

                    <div class="mb-3">
                        @forelse($user->roles as $role)
                            <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'network_engineer' ? 'primary' : 'secondary') }} px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </span>
                        @empty
                            <span class="badge bg-dark px-3 py-2">No Role</span>
                        @endforelse
                    </div>

                    <div class="mb-2">
                        @if($user->is_active)
                            <span class="badge bg-success px-3 py-2">Active</span>
                        @else
                            <span class="badge bg-danger px-3 py-2">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Employee ID</small>
                        <span class="fw-bold">{{ $user->employee_id }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Department</small>
                        <span>{{ $user->department ?? 'Not assigned' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Phone</small>
                        <span>{{ $user->phone ?? 'Not provided' }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Email Verified</small>
                        <div>
                            @if($user->email_verified_at)
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i> 
                                    {{ $user->email_verified_at->format('d-M-Y H:i') }}
                                </span>
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-times-circle me-1"></i> Not verified
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Member Since</small>
                        <span>{{ $user->created_at->format('d-M-Y') }}</span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <span>{{ $user->updated_at->format('d-M-Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <!-- Permissions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Direct Permissions</h6>
                </div>
                <div class="card-body">
                    @if($user->permissions && $user->permissions->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($user->permissions as $permission)
                                <li class="list-group-item d-flex align-items-center px-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    <span>{{ ucfirst(str_replace('_', ' ', $permission->name)) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-shield-alt fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No direct permissions</p>
                            <small class="text-muted">Permissions are inherited from assigned role(s)</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Recent Activity -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                    @if($activities && $activities->count() > 0)
                        <span class="badge bg-primary">{{ $activities->count() }} activities</span>
                    @endif
                </div>
                <div class="card-body">
                    @if($activities && $activities->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Action</th>
                                        <th>Module</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity->performed_at->format('d-M-Y H:i') }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $activity->action_color }}">
                                                {{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                            </span>
                                        </td>
                                        <td>{{ $activity->module }}</td>
                                        <td>
                                            @if($activity->module_name)
                                                <a href="#" class="text-decoration-none">{{ $activity->module_name }}</a>
                                            @else
                                                {{ $activity->description }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $activity->status === 'success' ? 'success' : 'danger' }}">
                                                {{ ucfirst($activity->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5>No Activity Yet</h5>
                            <p class="text-muted">This user hasn't performed any actions yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Account Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Account Actions</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning w-100">
                                <i class="fas fa-edit me-1"></i> Edit User
                            </a>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-info w-100" onclick="toggleUserStatus({{ $user->id }})">
                                <i class="fas fa-toggle-on me-1"></i>
                                {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                            </button>
                        </div>
                        @if($user->id !== auth()->id())
                        <div class="col-md-4">
                            <button class="btn btn-danger w-100" onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')">
                                <i class="fas fa-trash me-1"></i> Delete User
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .avatar-circle {
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }
</style>
@endpush

@push('scripts')
<script>
    function deleteUser(id, name) {
        Swal.fire({
            title: 'Delete User?',
            html: `Are you sure you want to delete <strong>${name}</strong>?<br>This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/admin/users/${id}`,
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Deleted!',
                            text: response.message || 'User has been deleted.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => window.location.href = '{{ route("admin.users.index") }}');
                    },
                    error: function(xhr) {
                        Swal.fire({
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to delete user.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }

    function toggleUserStatus(id) {
        $.ajax({
            url: `/admin/users/${id}/toggle-status`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => location.reload());
            },
            error: function(xhr) {
                Swal.fire({
                    title: 'Error!',
                    text: xhr.responseJSON?.message || 'Failed to update status.',
                    icon: 'error'
                });
            }
        });
    }
</script>
@endpush
@endsection