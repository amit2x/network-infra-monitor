<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Network Monitor') }} - @yield('title', 'Dashboard')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body>
    @auth
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark">
            <div class="sidebar-header p-3">
                <h3 class="text-white mb-0">
                    <i class="fas fa-network-wired me-2"></i>
                    NetMonitor
                </h3>
            </div>

            <ul class="list-unstyled components">
                <li class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                    </a>
                </li>

                <li class="{{ request()->routeIs('devices.*') ? 'active' : '' }}">
                    <a href="{{ route('devices.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-server me-2"></i> Devices
                    </a>
                </li>

                <li class="{{ request()->routeIs('locations.*') ? 'active' : '' }}">
                    <a href="{{ route('locations.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-map-marker-alt me-2"></i> Locations
                    </a>
                </li>

                <li class="{{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                    <a href="{{ route('alerts.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-bell me-2"></i> Alerts
                        <span class="badge bg-danger float-end" id="alert-count">0</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                    <a href="{{ route('monitoring.logs') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-chart-line me-2"></i> Monitoring
                    </a>
                </li>

                <li>
                    <a href="#reportsSubmenu" data-bs-toggle="collapse" class="text-white text-decoration-none px-3 py-2 d-block dropdown-toggle">
                        <i class="fas fa-file-alt me-2"></i> Reports
                    </a>
                    <ul class="collapse list-unstyled" id="reportsSubmenu">
                        <li>
                            <a href="{{ route('reports.inventory') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">Inventory Report</a>
                        </li>
                        <li>
                            <a href="{{ route('reports.expiry') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">Expiry Report</a>
                        </li>
                        <li>
                            <a href="{{ route('reports.port-usage') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">Port Usage Report</a>
                        </li>
                    </ul>
                </li>

                @can('manage users')
                <li class="{{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-users-cog me-2"></i> Administration
                    </a>
                </li>
                @endcan
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="content" class="ms-auto">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center">
                        <!-- Notification Dropdown -->
                        <div class="dropdown me-3">
                            <button class="btn btn-light position-relative dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="unread-alerts">
                                    0
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow" style="width: 350px;">
                                <div class="p-3 border-bottom">
                                    <h6 class="mb-0">Notifications</h6>
                                </div>
                                <div id="notification-list" style="max-height: 300px; overflow-y: auto;">
                                    <div class="text-center p-3">
                                        <div class="spinner-border spinner-border-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        Loading notifications...
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown">
                            <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle me-1"></i>
                                {{ Auth::user()->name ?? 'User' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-2"></i> Profile
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i> Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="py-4 px-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
    @else
    <!-- Guest View (Login/Register pages) -->
    <div class="min-vh-100 d-flex align-items-center justify-content-center bg-light">
        @yield('content')
    </div>
    @endauth

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Set the alerts count URL for the external JS file
        window.alertsCountUrl = '{{ route("alerts.unread-count") }}';

        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // Load unread alerts count
            loadAlertCount();
            setInterval(loadAlertCount, 30000); // Refresh every 30 seconds

            function loadAlertCount() {
                const alertsRoute = window.alertsCountUrl || '/alerts/count/unread';

                $.get(alertsRoute, function(data) {
                    $('#unread-alerts').text(data.count);
                    if (data.count > 0) {
                        $('#unread-alerts').show();
                    } else {
                        $('#unread-alerts').hide();
                    }
                }).fail(function() {
                    console.log('Failed to load alerts count');
                });
            }

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').fadeOut('slow');
            }, 5000);
        });
    </script>

    @stack('scripts')
</body>
</html>
