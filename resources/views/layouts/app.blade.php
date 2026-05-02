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
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --primary: #4e73df;
            --primary-light: #6e8ef7;
            --primary-dark: #3a54b4;
            --success: #1cc88a;
            --info: #36b9cc;
            --warning: #f6c23e;
            --danger: #e74a3b;
            --secondary: #858796;
            --dark: #1e1e2d;
            --darker: #1a1a27;
            --sidebar-width: 250px;
            --topbar-height: 55px;
            --transition-speed: 0.25s;
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f5f6fa;
            overflow-x: hidden;
        }

        .wrapper {
            display: flex;
            width: 100%;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            min-width: var(--sidebar-width);
            max-width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #1e1e2d 0%, #1a1a27 100%);
            color: #fff;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
        }

        .sidebar.active {
            margin-left: calc(var(--sidebar-width) * -1);
        }

        /* Sidebar Header */
        .sidebar-header {
            /*padding: 15px;*/
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            text-align: center;
            flex-shrink: 0;
        }

        .sidebar-logo {
            max-height: 100px;
            width: 100%;
            margin: 0 auto;
            display: block;
        }

        .sidebar-header h3 {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #fff, #a8b8d8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin: 0;
        }

        /* Sidebar Navigation - Scrollable */
        .sidebar-nav {
            padding: 8px 0;
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 3px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }

        /* Section Headings */
        .sidebar-heading {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255, 255, 255, 0.3);
            padding: 6px 18px;
            margin-top: 5px;
            font-weight: 600;
        }

        /* Sidebar Items */
        .sidebar-item {
            margin: 1px 8px;
            border-radius: 8px;
            transition: all var(--transition-speed);
        }

        .sidebar-item a {
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            padding: 8px 14px;
            display: flex;
            align-items: center;
            border-radius: 8px;
            transition: all var(--transition-speed);
            font-size: 0.8rem;
            font-weight: 500;
            position: relative;
            white-space: nowrap;
        }

        .sidebar-item a:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #fff;
            transform: translateX(2px);
        }

        .sidebar-item.active a {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #fff;
            box-shadow: 0 3px 12px rgba(78, 115, 223, 0.35);
            font-weight: 600;
        }

        .sidebar-item.active a::before {
            content: '';
            position: absolute;
            left: -10px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 16px;
            background: var(--primary-light);
            border-radius: 0 3px 3px 0;
        }

        /* Sidebar Icons */
        .sidebar-icon {
            width: 18px;
            text-align: center;
            margin-right: 10px;
            font-size: 0.85rem;
            opacity: 0.75;
            transition: all var(--transition-speed);
            flex-shrink: 0;
        }

        .sidebar-item.active .sidebar-icon {
            opacity: 1;
        }

        .sidebar-item:hover .sidebar-icon {
            opacity: 1;
            transform: scale(1.05);
        }

        /* Sidebar Badge */
        .sidebar-badge {
            margin-left: auto;
            font-size: 0.65rem;
            padding: 2px 7px;
            border-radius: 12px;
            font-weight: 600;
            transition: all var(--transition-speed);
            flex-shrink: 0;
        }

        .sidebar-badge-danger {
            background: var(--danger);
            color: #fff;
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        /* Submenu */
        .sidebar-dropdown > a::after {
            content: '\f107';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            margin-left: auto;
            font-size: 0.7rem;
            transition: transform var(--transition-speed);
            opacity: 0.4;
        }

        .sidebar-dropdown > a[aria-expanded="true"]::after {
            transform: rotate(180deg);
        }

        .sidebar-submenu {
            padding: 2px 0;
            margin: 0 8px;
        }

        .sidebar-submenu a {
            padding: 7px 14px 7px 42px;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.5);
            text-decoration: none;
            display: block;
            border-radius: 6px;
            transition: all var(--transition-speed);
            font-weight: 400;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-submenu a:hover {
            background: rgba(255, 255, 255, 0.04);
            color: #fff;
        }

        .sidebar-submenu a.active {
            color: var(--primary-light);
            font-weight: 500;
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 10px 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            flex-shrink: 0;
            background: var(--darker);
        }

        .sidebar-footer .user-info {
            display: flex;
            align-items: center;
        }

        .sidebar-footer .user-avatar {
            width: 32px;
            height: 32px;
            min-width: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.75rem;
            margin-right: 8px;
        }

        .sidebar-footer .user-name {
            font-size: 0.75rem;
            font-weight: 600;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-footer .user-role {
            font-size: 0.65rem;
            color: rgba(255, 255, 255, 0.5);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        /* Main Content */
        #content {
            width: calc(100% - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
            background: #f5f6fa;
        }

        #content.active {
            width: 100%;
            margin-left: 0;
        }

        /* Top Navbar */
        .navbar {
            height: var(--topbar-height);
            background: #fff !important;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            z-index: 1030;
            padding: 0 20px;
        }

        .navbar .btn-outline-secondary {
            border-color: #e8e8e8;
            color: #666;
            border-radius: 8px;
            width: 36px;
            height: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-speed);
            font-size: 0.9rem;
            flex-shrink: 0;
        }

        .navbar .btn-outline-secondary:hover {
            background: #f5f5f5;
            border-color: #ddd;
        }

        /* Notification Button */
        .btn-notification {
            position: relative;
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: 1px solid #e8e8e8;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-speed);
            font-size: 0.9rem;
            color: #666;
            flex-shrink: 0;
        }

        .btn-notification:hover {
            background: #f5f5f5;
        }

        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            font-size: 0.55rem;
            padding: 2px 5px;
            border-radius: 10px;
        }

        /* User Button */
        .btn-user {
            border-radius: 8px;
            padding: 6px 12px;
            border: 1px solid #e8e8e8;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition-speed);
            font-weight: 500;
            font-size: 0.85rem;
            color: #444;
            flex-shrink: 0;
        }

        .btn-user:hover {
            background: #f5f5f5;
        }

        .btn-user .user-avatar-sm {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.65rem;
            font-weight: 600;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
            transition: all var(--transition-speed);
        }

        .card:hover {
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            border-radius: 12px 12px 0 0 !important;
            padding: 12px 18px;
            font-size: 0.9rem;
        }

        /* Border Left Cards */
        .border-left-primary { border-left: 3px solid var(--primary) !important; }
        .border-left-success { border-left: 3px solid var(--success) !important; }
        .border-left-info { border-left: 3px solid var(--info) !important; }
        .border-left-warning { border-left: 3px solid var(--warning) !important; }
        .border-left-danger { border-left: 3px solid var(--danger) !important; }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            font-size: 0.85rem;
        }

        /* Breadcrumb */
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin: 3px 0 0 0;
            font-size: 0.78rem;
        }

        .breadcrumb-item a {
            color: var(--primary);
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #999;
        }

        /* Animations */
        .fade-in {
            animation: fadeIn 0.35s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                margin-left: calc(var(--sidebar-width) * -1);
            }
            .sidebar.active {
                margin-left: 0;
            }
            #content {
                width: 100%;
                margin-left: 0;
            }
        }

        @media (max-width: 576px) {
            .btn-user span {
                display: none;
            }
            .navbar {
                padding: 0 10px;
            }
        }

        /* Sidebar overlay for mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1035;
        }

        @media (max-width: 992px) {
            .sidebar-overlay.active {
                display: block;
            }
        }

        /* Dropdown menus */
        .dropdown-menu {
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            font-size: 0.85rem;
        }

        .dropdown-item {
            font-size: 0.83rem;
            padding: 8px 16px;
        }

        /* Notification items */
        .notification-item {
            cursor: pointer;
            transition: all 0.2s;
            border-left: 3px solid transparent;
            font-size: 0.8rem;
        }

        .notification-item:hover {
            background-color: #f8f9fc;
        }

        .notification-item.unread {
            background-color: #e8f4fd;
            border-left-color: var(--primary);
        }
    </style>

    @stack('styles')
</head>
<body>
    @auth
    <div class="wrapper">
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <!-- Premium Compact Sidebar -->
        <nav id="sidebar" class="sidebar">
            <!-- Logo -->
            <div class="sidebar-header">
                @php
                    $logoPath = 'logo_dashboard.png';
                    $fullPath = public_path($logoPath);
                @endphp
                @if(file_exists($fullPath))
                    <img src="{{ asset($logoPath) }}" class="sidebar-logo" alt="NetMonitor">
                @else
                    <h3 class="mb-0">
                        <i class="fas fa-network-wired me-2"></i>NetMonitor
                    </h3>
                @endif
            </div>

            <!-- Scrollable Navigation -->
            <div class="sidebar-nav">
                <!-- MAIN -->
                <div class="sidebar-heading">Main</div>
                
                <div class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <a href="{{ route('dashboard') }}">
                        <span class="sidebar-icon"><i class="fas fa-th-large"></i></span>
                        <span>Dashboard</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('devices.*') ? 'active' : '' }}">
                    <a href="{{ route('devices.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-server"></i></span>
                        <span>Devices</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                    <a href="{{ route('locations.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-map-marker-alt"></i></span>
                        <span>Locations</span>
                    </a>
                </div>

                <!-- MONITORING -->
                <div class="sidebar-heading">Monitoring</div>

                <div class="sidebar-item {{ request()->routeIs('snmp.*') ? 'active' : '' }}">
                    <a href="{{ route('snmp.dashboard') }}">
                        <span class="sidebar-icon"><i class="fas fa-broadcast-tower"></i></span>
                        <span>SNMP Monitor</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('monitoring.*') ? 'active' : '' }}">
                    <a href="{{ route('monitoring.logs') }}">
                        <span class="sidebar-icon"><i class="fas fa-chart-line"></i></span>
                        <span>Monitoring</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
                    <a href="{{ route('alerts.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-bell"></i></span>
                        <span>Alerts</span>
                        <span class="sidebar-badge sidebar-badge-danger" id="alert-count-badge" style="display: none;">0</span>
                    </a>
                </div>

                <!-- VISUALIZATION -->
                <div class="sidebar-heading">Visualization</div>

                <div class="sidebar-item {{ request()->routeIs('topology.*') ? 'active' : '' }}">
                    <a href="{{ route('topology.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-project-diagram"></i></span>
                        <span>Topology</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('racks.*') ? 'active' : '' }}">
                    <a href="{{ route('racks.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-grip-vertical"></i></span>
                        <span>Rack View</span>
                    </a>
                </div>

                <div class="sidebar-item {{ request()->routeIs('bandwidth.*') ? 'active' : '' }}">
                    <a href="{{ route('bandwidth.dashboard') }}">
                        <span class="sidebar-icon"><i class="fas fa-chart-area"></i></span>
                        <span>Bandwidth</span>
                    </a>
                </div>

                <!-- TOOLS -->
                <div class="sidebar-heading">Tools</div>

                <div class="sidebar-item {{ request()->routeIs('mib-browser.*') ? 'active' : '' }}">
                    <a href="{{ route('mib-browser.index') }}">
                        <span class="sidebar-icon"><i class="fas fa-search"></i></span>
                        <span>MIB Browser</span>
                    </a>
                </div>

                <!-- Reports Dropdown -->
                <div class="sidebar-item sidebar-dropdown {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <a href="#reportsSubmenu" data-bs-toggle="collapse" 
                       aria-expanded="{{ request()->routeIs('reports.*') ? 'true' : 'false' }}">
                        <span class="sidebar-icon"><i class="fas fa-file-alt"></i></span>
                        <span>Reports</span>
                    </a>
                    <div class="collapse sidebar-submenu {{ request()->routeIs('reports.*') ? 'show' : '' }}" id="reportsSubmenu">
                        <a href="{{ route('reports.inventory') }}" class="{{ request()->routeIs('reports.inventory') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list me-2"></i>Inventory
                        </a>
                        <a href="{{ route('reports.expiry') }}" class="{{ request()->routeIs('reports.expiry') ? 'active' : '' }}">
                            <i class="fas fa-calendar-alt me-2"></i>Expiry
                        </a>
                        <a href="{{ route('reports.port-usage') }}" class="{{ request()->routeIs('reports.port-usage') ? 'active' : '' }}">
                            <i class="fas fa-plug me-2"></i>Port Usage
                        </a>
                        <a href="{{ route('reports.availability') }}" class="{{ request()->routeIs('reports.availability') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar me-2"></i>Availability
                        </a>
                    </div>
                </div>

                <!-- ADMIN -->
                @can('manage users')
                <div class="sidebar-heading">Administration</div>
                
                <div class="sidebar-item sidebar-dropdown {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                    <a href="#adminSubmenu" data-bs-toggle="collapse" 
                       aria-expanded="{{ request()->routeIs('admin.*') ? 'true' : 'false' }}">
                        <span class="sidebar-icon"><i class="fas fa-shield-alt"></i></span>
                        <span>Admin</span>
                    </a>
                    <div class="collapse sidebar-submenu {{ request()->routeIs('admin.*') ? 'show' : '' }}" id="adminSubmenu">
                        <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a href="{{ route('admin.audit.index') }}" class="{{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                            <i class="fas fa-history me-2"></i>Audit Trail
                        </a>
                        <a href="{{ route('admin.settings') }}" class="{{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                    </div>
                </div>
                @endcan
            </div>

            <!-- Footer -->
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                    </div>
                    <div style="overflow: hidden;">
                        <div class="user-name">{{ Auth::user()->name ?? 'User' }}</div>
                        <div class="user-role">{{ ucfirst(Auth::user()->roles->first()->name ?? 'User') }}</div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div id="content">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg">
                <div class="container-fluid">
                    <button type="button" id="sidebarCollapse" class="btn btn-outline-secondary">
                        <i class="fas fa-bars"></i>
                    </button>

                    <div class="ms-auto d-flex align-items-center gap-2">
                        <!-- Notification -->
                        <div class="dropdown">
                            <button class="btn-notification" type="button" id="notificationDropdown" 
                                    data-bs-toggle="dropdown" aria-expanded="false" onclick="loadNotifications()">
                                <i class="fas fa-bell"></i>
                                <span class="notification-badge badge bg-danger" id="unread-alerts" style="display: none;">0</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3" 
                                 style="width: 360px;" aria-labelledby="notificationDropdown">
                                <div class="p-3 border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fw-bold small">Notifications</h6>
                                    <a href="{{ route('alerts.index') }}" class="small text-decoration-none">View All</a>
                                </div>
                                <div id="notification-list" style="max-height: 380px; overflow-y: auto;">
                                    <div class="text-center p-4" id="notification-loading">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                                        <p class="text-muted small mt-2 mb-0">Loading...</p>
                                    </div>
                                    <div id="notification-items"></div>
                                    <div class="text-center p-4" id="notification-empty" style="display: none;">
                                        <i class="fas fa-bell-slash fa-2x text-muted mb-2"></i>
                                        <p class="text-muted small mb-0">No new notifications</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- User -->
                        <div class="dropdown">
                            <button class="btn-user dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <div class="user-avatar-sm">
                                    {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 2)) }}
                                </div>
                                <span class="d-none d-md-inline">{{ Auth::user()->name ?? 'User' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3">
                                <li><a class="dropdown-item py-2" href="{{ route('profile.edit') }}">
                                    <i class="fas fa-user-edit me-2 text-primary"></i> Profile
                                </a></li>
                                <li><a class="dropdown-item py-2" href="{{ route('profile.activity') }}">
                                    <i class="fas fa-history me-2 text-info"></i> My Activity
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">
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
            <main class="py-3 px-3 px-md-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show fade-in" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show fade-in" role="alert">
                        <h6 class="alert-heading small"><i class="fas fa-exclamation-triangle me-2"></i>Please fix:</h6>
                        <ul class="mb-0 small">
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
    <div class="min-vh-100 bg-light">
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

        $(document).ready(function() {
            // Sidebar toggle
            $('#sidebarCollapse').on('click', function() {
                $('#sidebar').toggleClass('active');
                $('#content').toggleClass('active');
                $('#sidebarOverlay').toggleClass('active');
            });

            // Close sidebar on overlay click
            $('#sidebarOverlay').on('click', function() {
                $('#sidebar').removeClass('active');
                $('#sidebarOverlay').removeClass('active');
            });

            // Close sidebar on mobile when clicking content
            $(document).on('click', function(e) {
                if ($(window).width() <= 992) {
                    if (!$(e.target).closest('#sidebar').length && 
                        !$(e.target).closest('#sidebarCollapse').length &&
                        !$(e.target).closest('#sidebarOverlay').length) {
                        $('#sidebar').removeClass('active');
                        $('#sidebarOverlay').removeClass('active');
                    }
                }
            });

            loadAlertCount();
            setInterval(loadAlertCount, 30000);

            setTimeout(function() {
                $('.alert-dismissible').fadeOut('slow');
            }, 5000);
        });

        function loadAlertCount() {
            $.get(window.alertsCountUrl, function(data) {
                const badge = $('#unread-alerts');
                const sidebarBadge = $('#alert-count-badge');
                
                if (data.count > 0) {
                    badge.text(data.count > 99 ? '99+' : data.count).show();
                    sidebarBadge.text(data.count).show();
                } else {
                    badge.hide();
                    sidebarBadge.hide();
                }
            });
        }

        function loadNotifications() {
            $.get(window.alertsCountUrl, function(data) {
                $('#notification-loading').hide();
                if (data.count === 0) {
                    $('#notification-empty').show();
                } else {
                    $('#notification-items').html(`
                        <a href="{{ route("alerts.index") }}" class="dropdown-item text-center py-3">
                            <i class="fas fa-bell me-2"></i> 
                            You have <strong>${data.count}</strong> unread alert(s)
                        </a>
                    `);
                }
            });
        }
    </script>

    @stack('scripts')
</body>
</html>