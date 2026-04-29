@extends('layouts.app')

@section('title', 'User Details')

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
            <button class="btn btn-danger" onclick="deleteUser({{ $user->id }})">
                <i class="fas fa-trash me-1"></i> Delete
            </button>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4">
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
                        @foreach($user->roles as $role)
                            <span class="badge bg-{{ $role->name === 'admin' ? 'danger' : ($role->name === 'network_engineer' ? 'primary' : 'secondary') }} px-3 py-2">
                                {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                            </span>
                        @endforeach
                    </div>

                    <div class="mb-2">
                        @if($user->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Contact Information</h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <small class="text-muted">Employee ID</small>
                        <div class="fw-bold">{{ $user->employee_id }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Department</small>
                        <div>{{ $user->department }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Phone</small>
                        <div>{{ $user->phone ?? 'Not provided' }}</div>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted">Email Verified</small>
                        <div>
                            @if($user->email_verified_at)
                                <span class="text-success">
                                    <i class="fas fa-check-circle me-1"></i> {{ $user->email_verified_at->format('d-M-Y H:i') }}
                                </span>
                            @else
                                <span class="text-danger">
                                    <i class="fas fa-times-circle me-1"></i> Not verified
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Permissions</h6>
                </div>
                <div class="card-body">
                    @if($user->permissions->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach($user->permissions as $permission)
                                <li class="list-group-item px-0">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    {{ ucfirst(str_replace('_', ' ', $permission->name)) }}
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Permissions inherited from role(s).</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Activity</h6>
                </div>
                <div class="card-body">
                    @if($activities->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Date/Time</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($activities as $activity)
                                    <tr>
                                        <td>{{ $activity->created_at->format('d-M-Y H:i') }}</td>
                                        <td>{{ ucfirst($activity->event_type) }}</td>
                                        <td>{{ $activity->message }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center text-muted py-3">No recent activity</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function deleteUser(id) {
        Swal.fire({
            title: 'Delete User?',
            text: "This action cannot be undone!",
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
                        Swal.fire('Deleted!', 'User has been deleted.', 'success')
                            .then(() => window.location.href = '{{ route("admin.users.index") }}');
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to delete user.', 'error');
                    }
                });
            }
        });
    }
</script>
@endpush
@endsection
