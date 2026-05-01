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

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #4e73df;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --dark: #5a5c69;
        }

        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        .sidebar {
            min-width: 250px;
            max-width: 250px;
            min-height: 100vh;
            background: var(--dark);
            color: #fff;
            transition: all 0.3s;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
        }

        .sidebar.active {
            margin-left: -250px;
        }

        .sidebar-header {
            background: rgba(0,0,0,0.2);
        }

        .sidebar ul li a {
            color: rgba(255,255,255,0.8);
            transition: all 0.3s;
        }

        .sidebar ul li a:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }

        .sidebar ul li.active > a {
            background: var(--primary);
            color: #fff;
        }

        #content {
            width: calc(100% - 250px);
            margin-left: 250px;
            min-height: 100vh;
            transition: all 0.3s;
            background: #f8f9fc;
        }

        #content.active {
            width: 100%;
            margin-left: 0;
        }

        .card {
            border: none;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .card-header {
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
        }

        .border-left-primary { border-left: 4px solid var(--primary) !important; }
        .border-left-success { border-left: 4px solid var(--success) !important; }
        .border-left-info { border-left: 4px solid var(--info) !important; }
        .border-left-warning { border-left: 4px solid var(--warning) !important; }
        .border-left-danger { border-left: 4px solid var(--danger) !important; }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Notification styles */
        .notification-item {
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fc;
        }
        
        .notification-item.unread {
            background-color: #e8f4fd;
            border-left: 3px solid var(--primary);
        }
        
        .notification-item.unread:hover {
            background-color: #dceefb;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }
            .sidebar.active {
                margin-left: 0;
            }
            #content {
                width: 100%;
                margin-left: 0;
            }
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
    <div class="wrapper">
        <!-- Sidebar -->
        <nav id="sidebar" class="sidebar bg-dark">
            <!--<div class="sidebar-header p-3">-->
            <!--    <h3 class="text-white mb-0">-->
            <!--        <i class="fas fa-network-wired me-2"></i>-->
            <!--        NetMonitor-->
            <!--    </h3>-->
            <!--</div>-->
            
            <div class="sidebar-header border-bottom">
    @php
        // Check if the file exists in your public folder
        $logoPath = 'logo_dashboard.png';
        $fullPath = public_path($logoPath);
    @endphp

    @if(file_exists($fullPath))
        {{-- Full width image, no padding --}}
        <img src="{{ asset($logoPath) }}" class="img-fluid d-block w-100" style="max-height: 100px;" alt="NetMonitor">
    @else
        {{-- Fallback with padding for text alignment --}}
        <div class="p-3">
            <h3 class="text-white mb-0">
                <i class="fas fa-network-wired me-2"></i>
                NetMonitor
            </h3>
        </div>
    @endif
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
                <li class="{{ request()->routeIs('snmp.*') ? 'active' : '' }}">
                    <a href="{{ route('snmp.dashboard') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-project-diagram me-2"></i> SNMP Monitor
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
                        <span class="badge bg-danger float-end" id="alert-count-badge" style="display: none;">0</span>
                    </a>
                </li>

                <li class="{{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                    <a href="{{ route('monitoring.logs') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-chart-line me-2"></i> Monitoring
                    </a>
                </li>
                
                <li class="{{ request()->routeIs('topology.*') ? 'active' : '' }}">
                    <a href="{{ route('topology.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-project-diagram me-2"></i> Topology
                    </a>
                </li>
                <li class="{{ request()->routeIs('racks.*') ? 'active' : '' }}">
                    <a href="{{ route('racks.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-server me-2"></i> Rack View
                    </a>
                </li>
                <li class="{{ request()->routeIs('bandwidth.*') ? 'active' : '' }}">
                    <a href="{{ route('bandwidth.dashboard') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-chart-area me-2"></i> Bandwidth
                    </a>
                </li>
                <li class="{{ request()->routeIs('mib-browser.*') ? 'active' : '' }}">
                    <a href="{{ route('mib-browser.index') }}" class="text-white text-decoration-none px-3 py-2 d-block">
                        <i class="fas fa-search me-2"></i> MIB Browser
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
                    <a href="#adminSubmenu" data-bs-toggle="collapse" class="text-white text-decoration-none px-3 py-2 d-block dropdown-toggle">
                        <i class="fas fa-users-cog me-2"></i> Administration
                    </a>
                    <ul class="collapse list-unstyled {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">
                        <li>
                            <a href="{{ route('admin.users.index') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">
                                <i class="fas fa-users me-2"></i> Users
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.audit.index') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">
                                <i class="fas fa-history me-2"></i> Audit Trail
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.settings') }}" class="text-white-50 text-decoration-none px-5 py-2 d-block">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                        </li>
                    </ul>
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
                            <button class="btn btn-light position-relative dropdown-toggle" type="button" 
                                    id="notificationDropdown" data-bs-toggle="dropdown" 
                                    aria-expanded="false" onclick="loadNotifications()">
                                <i class="fas fa-bell"></i>
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
                                      id="unread-alerts" style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow" 
                                 style="width: 350px;" aria-labelledby="notificationDropdown">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Notifications</h6>
                                    <a href="{{ route('alerts.index') }}" class="small text-decoration-none">View All</a>
                                </div>
                                <div id="notification-list" style="max-height: 350px; overflow-y: auto;">
                                    <div class="text-center p-4" id="notification-loading">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <p class="text-muted small mt-2 mb-0">Loading notifications...</p>
                                    </div>
                                    <div id="notification-items"></div>
                                    <div class="text-center p-3" id="notification-empty" style="display: none;">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small mb-0">No new notifications</p>
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
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Please fix the following errors:</h6>
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
    <!-- Guest View for Login/Register -->
    <div class="min-vh-100">
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
        window.alertsCountUrl = '{{ route("alerts.unread-count") }}';
        window.alertsListUrl = '{{ route("alerts.index") }}';
        window.alertsResolveUrl = '{{ route("alerts.resolve", ["alert" => "__ID__"]) }}';

        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
            });

            // Load initial data
            loadAlertCount();
            
            // Refresh alert count every 30 seconds
            setInterval(loadAlertCount, 30000);

            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert-dismissible').fadeOut('slow');
            }, 5000);
        });

        // Load alert count for badge
        function loadAlertCount() {
            $.get(window.alertsCountUrl, function(data) {
                const badge = $('#unread-alerts');
                const sidebarBadge = $('#alert-count-badge');
                
                if (data.count > 0) {
                    badge.text(data.count).show();
                    sidebarBadge.text(data.count).show();
                } else {
                    badge.hide();
                    sidebarBadge.hide();
                }
            }).fail(function() {
                console.error('Failed to load alerts count');
            });
        }

        // Load notifications into dropdown
        function loadNotifications() {
            const loadingEl = $('#notification-loading');
            const itemsEl = $('#notification-items');
            const emptyEl = $('#notification-empty');
            
            // Show loading, hide items
            loadingEl.show();
            itemsEl.html('');
            emptyEl.hide();
            
            $.ajax({
                url: window.alertsListUrl,
                data: {
                    per_page: 10,
                    resolved: 0 // Only show unresolved
                },
                success: function(response) {
                    loadingEl.hide();
                    
                    // Check if we have alerts from HTML response
                    // Since we're using blade views, we need to parse or use API
                    // Let's use a different approach - fetch recent unresolved alerts
                    fetchRecentAlerts();
                },
                error: function() {
                    loadingEl.hide();
                    itemsEl.html(`
                        <div class="text-center p-3 text-danger">
                            <i class="fas fa-exclamation-circle mb-2"></i>
                            <p class="small mb-0">Failed to load notifications</p>
                        </div>
                    `);
                }
            });
        }

        // Fetch recent unresolved alerts
        function fetchRecentAlerts() {
            const itemsEl = $('#notification-items');
            const emptyEl = $('#notification-empty');
            
            // Get alerts from the alerts page data
            $.ajax({
                url: window.alertsListUrl + '?resolved=0&per_page=10',
                method: 'GET',
                success: function(html) {
                    // Parse the HTML to extract alert rows
                    const tempDiv = $('<div>').html(html);
                    const alertRows = tempDiv.find('tbody tr');
                    
                    if (alertRows.length === 0) {
                        emptyEl.show();
                        return;
                    }
                    
                    let notificationHtml = '';
                    let count = 0;
                    
                    alertRows.each(function() {
                        if (count >= 5) return false; // Show max 5
                        
                        const row = $(this);
                        const severity = row.find('.badge:first').text().trim();
                        const title = row.find('a').text().trim();
                        const time = row.find('td:eq(3)').text().trim();
                        const isResolved = row.find('.badge.bg-success').length > 0;
                        const alertId = row.find('.alert-checkbox').val();
                        
                        const severityClass = severity.toLowerCase().includes('critical') ? 'danger' :
                                            severity.toLowerCase().includes('high') ? 'warning' : 'info';
                        
                        notificationHtml += `
                            <div class="notification-item p-3 border-bottom ${isResolved ? '' : 'unread'}" 
                                 onclick="window.location.href='/alerts/${alertId}'">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 small fw-bold text-dark">${title}</p>
                                        <small class="text-muted">
                                            <span class="badge bg-${severityClass} me-1" style="font-size: 0.65rem;">${severity}</span>
                                            ${time}
                                        </small>
                                    </div>
                                    <div>
                                        <span class="badge bg-${isResolved ? 'success' : 'warning'} rounded-pill" 
                                              style="font-size: 0.6rem;">
                                            ${isResolved ? 'Resolved' : 'Pending'}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        `;
                        count++;
                    });
                    
                    if (notificationHtml) {
                        itemsEl.html(notificationHtml);
                    } else {
                        emptyEl.show();
                    }
                },
                error: function() {
                    // Fallback: create notification items from global state
                    itemsEl.html(`
                        <a href="${window.alertsListUrl}" class="dropdown-item text-center text-primary">
                            <i class="fas fa-external-link-alt me-1"></i> View All Alerts
                        </a>
                    `);
                }
            });
        }

        // Resolve alert from notification
        function resolveAlertFromNotification(alertId) {
            const url = window.alertsResolveUrl.replace('__ID__', alertId);
            $.post(url, {
                _token: '{{ csrf_token() }}'
            }, function(response) {
                Swal.fire({
                    title: 'Resolved!',
                    text: 'Alert has been resolved.',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
                loadNotifications();
                loadAlertCount();
            });
        }
    </script>

    @stack('scripts')
</body>
</html>