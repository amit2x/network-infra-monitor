@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Welcome Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h2 class="mb-2">
                                Welcome back, {{ Auth::user()->name }}! 👋
                            </h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-shield-alt me-2"></i>
                                Role:
                                <span class="badge bg-{{ Auth::user()->hasRole('admin') ? 'danger' : (Auth::user()->hasRole('network_engineer') ? 'primary' : 'secondary') }} px-3 py-2">
                                    {{ ucfirst(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}
                                </span>
                                <span class="mx-2">|</span>
                                <i class="fas fa-building me-2"></i>
                                Department: {{ Auth::user()->department ?? 'N/A' }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-clock me-2"></i>
                                Last Login: {{ Auth::user()->last_login_at ? \Carbon\Carbon::parse(Auth::user()->last_login_at)->diffForHumans() : 'First Login' }}
                            </p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary">
                                <i class="fas fa-user-edit me-1"></i> Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Devices -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['total'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-server fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Online Devices -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Online Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['online'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Offline Devices -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Offline Devices</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $deviceStats['offline'] ?? 0 }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Port Utilization -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Port Utilization</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $portUtilization ?? 0 }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plug fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Role-Based Quick Start Guide -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-book me-2"></i>
                        Quick Start Guide - {{ ucfirst(str_replace('_', ' ', Auth::user()->roles->first()->name ?? 'User')) }}
                    </h6>
                </div>
                <div class="card-body">
                    @role('admin')
                    <!-- Admin Guide -->
                    <div class="row">
                        <div class="col-lg-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-crown me-2"></i>Administrator Guide
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                As an <strong>Administrator</strong>, you have full control over the system. Here's what you can do:
                            </div>

                            <div class="accordion" id="adminGuide">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#admin1">
                                            <i class="fas fa-users-cog me-2"></i> 1. User Management
                                        </button>
                                    </h2>
                                    <div id="admin1" class="accordion-collapse collapse show" data-bs-parent="#adminGuide">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled">
                                                <li>✅ <strong>Create Users:</strong> Go to <a href="{{ route('admin.users.create') }}">Administration → Create User</a></li>
                                                <li>✅ <strong>Assign Roles:</strong> Assign Admin, Network Engineer, or Viewer roles</li>
                                                <li>✅ <strong>Manage Permissions:</strong> Control what each role can access</li>
                                                <li>✅ <strong>Activate/Deactivate:</strong> Toggle user account status</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin2">
                                            <i class="fas fa-cogs me-2"></i> 2. System Settings
                                        </button>
                                    </h2>
                                    <div id="admin2" class="accordion-collapse collapse" data-bs-parent="#adminGuide">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled">
                                                <li>🔧 <strong>Monitoring Interval:</strong> Set how often devices are pinged</li>
                                                <li>🔧 <strong>Email Notifications:</strong> Configure alert email settings</li>
                                                <li>🔧 <strong>Log Retention:</strong> Manage how long logs are kept</li>
                                                <li>🔧 <strong>System Info:</strong> View PHP version, database size, disk space</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin3">
                                            <i class="fas fa-database me-2"></i> 3. Data Management
                                        </button>
                                    </h2>
                                    <div id="admin3" class="accordion-collapse collapse" data-bs-parent="#adminGuide">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled">
                                                <li>📊 <strong>Bulk Operations:</strong> Export all device data to CSV</li>
                                                <li>📊 <strong>Reports:</strong> Generate inventory and expiry reports</li>
                                                <li>📊 <strong>Backup:</strong> Run database backups via <code>php artisan db:backup</code></li>
                                                <li>📊 <strong>Cleanup:</strong> Remove old logs to optimize performance</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#admin4">
                                            <i class="fas fa-shield-alt me-2"></i> 4. Security Best Practices
                                        </button>
                                    </h2>
                                    <div id="admin4" class="accordion-collapse collapse" data-bs-parent="#adminGuide">
                                        <div class="accordion-body">
                                            <ul class="list-unstyled">
                                                <li>🔒 Change default passwords immediately</li>
                                                <li>🔒 Regularly review user access and permissions</li>
                                                <li>🔒 Monitor audit logs for suspicious activity</li>
                                                <li>🔒 Keep system and dependencies updated</li>
                                                <li>🔒 Backup database before major changes</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-tasks me-2"></i>Quick Actions
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="{{ route('admin.users.create') }}" class="text-decoration-none">
                                        <div class="card border-left-primary shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-user-plus fa-2x text-primary mb-3"></i>
                                                <h6>Add New User</h6>
                                                <small class="text-muted">Create user accounts</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('admin.settings') }}" class="text-decoration-none">
                                        <div class="card border-left-warning shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-cog fa-2x text-warning mb-3"></i>
                                                <h6>System Settings</h6>
                                                <small class="text-muted">Configure system</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('devices.create') }}" class="text-decoration-none">
                                        <div class="card border-left-success shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-server fa-2x text-success mb-3"></i>
                                                <h6>Add Device</h6>
                                                <small class="text-muted">Register new device</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
                                        <div class="card border-left-info shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                                                <h6>Generate Report</h6>
                                                <small class="text-muted">View reports</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <h5 class="text-danger mt-4 mb-3">
                                <i class="fas fa-exclamation-triangle me-2"></i>Important Notes
                            </h5>
                            <div class="alert alert-warning">
                                <ul class="mb-0">
                                    <li>Default passwords should be changed immediately</li>
                                    <li>Monitor the <a href="{{ route('alerts.index') }}">Alerts</a> section regularly</li>
                                    <li>Ensure monitoring scheduler is running</li>
                                    <li>Review <a href="{{ route('reports.expiry') }}">Expiry Reports</a> weekly</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endrole

                    @role('network_engineer')
                    <!-- Network Engineer Guide -->
                    <div class="row">
                        <div class="col-lg-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-network-wired me-2"></i>Network Engineer Guide
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                As a <strong>Network Engineer</strong>, you manage network devices and monitor their health.
                            </div>

                            <div class="accordion" id="engineerGuide">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#eng1">
                                            <i class="fas fa-server me-2"></i> 1. Device Management
                                        </button>
                                    </h2>
                                    <div id="eng1" class="accordion-collapse collapse show" data-bs-parent="#engineerGuide">
                                        <div class="accordion-body">
                                            <h6>Adding a New Device:</h6>
                                            <ol>
                                                <li>Go to <a href="{{ route('devices.create') }}">Devices → Add Device</a></li>
                                                <li>Fill in device details:
                                                    <ul>
                                                        <li><strong>Name:</strong> Descriptive name (e.g., Core-Switch-DEL-01)</li>
                                                        <li><strong>Type:</strong> Switch, Router, Firewall, etc.</li>
                                                        <li><strong>IP Address:</strong> Must be reachable for monitoring</li>
                                                        <li><strong>Serial Number:</strong> Unique identifier</li>
                                                    </ul>
                                                </li>
                                                <li>Select the physical <strong>Location</strong> (Rack)</li>
                                                <li>Set lifecycle dates (Warranty, AMC)</li>
                                                <li>Enable <strong>Monitoring</strong></li>
                                            </ol>

                                            <h6>Managing Existing Devices:</h6>
                                            <ul>
                                                <li>🔍 <strong>Search:</strong> Use the search bar to find devices by name, IP, or serial</li>
                                                <li>📊 <strong>Filter:</strong> Filter by type, status, vendor, or location</li>
                                                <li>🔄 <strong>Ping:</strong> Click the Ping button to test connectivity</li>
                                                <li>✏️ <strong>Edit:</strong> Update device information as needed</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eng2">
                                            <i class="fas fa-plug me-2"></i> 2. Port Configuration
                                        </button>
                                    </h2>
                                    <div id="eng2" class="accordion-collapse collapse" data-bs-parent="#engineerGuide">
                                        <div class="accordion-body">
                                            <h6>Port Management:</h6>
                                            <ol>
                                                <li>Navigate to <a href="{{ route('devices.index') }}">Devices</a> → Click on a device</li>
                                                <li>Click the <strong>Ports</strong> tab</li>
                                                <li>Click on any port to configure:
                                                    <ul>
                                                        <li><strong>Status:</strong> Active, Free, Down, Disabled</li>
                                                        <li><strong>Service:</strong> CCTV, WiFi, VoIP, etc.</li>
                                                        <li><strong>Connected Device:</strong> What's connected to this port</li>
                                                        <li><strong>VLAN ID:</strong> 1-4096</li>
                                                        <li><strong>Speed:</strong> In Mbps (e.g., 1000 for 1Gbps)</li>
                                                    </ul>
                                                </li>
                                            </ol>

                                            <h6>Bulk Operations:</h6>
                                            <ul>
                                                <li>📦 <strong>Bulk Edit:</strong> Update multiple ports at once</li>
                                                <li>📊 <strong>Grid View:</strong> Visual representation of all ports</li>
                                                <li>📋 <strong>List View:</strong> Tabular view with full details</li>
                                            </ul>

                                            <div class="alert alert-info small mt-2">
                                                <i class="fas fa-lightbulb me-1"></i>
                                                <strong>Tip:</strong> Keep port documentation updated for easier troubleshooting!
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eng3">
                                            <i class="fas fa-chart-line me-2"></i> 3. Monitoring & Alerts
                                        </button>
                                    </h2>
                                    <div id="eng3" class="accordion-collapse collapse" data-bs-parent="#engineerGuide">
                                        <div class="accordion-body">
                                            <h6>Monitoring:</h6>
                                            <ul>
                                                <li>✅ Devices are pinged automatically based on schedule</li>
                                                <li>✅ Click <strong>Run Monitoring Now</strong> for manual check</li>
                                                <li>✅ View logs at <a href="{{ route('monitoring.logs') }}">Monitoring → Logs</a></li>
                                                <li>✅ Check statistics at <a href="{{ route('monitoring.stats') }}">Monitoring → Stats</a></li>
                                            </ul>

                                            <h6>Alerts:</h6>
                                            <ul>
                                                <li>🔔 <strong>Device Down:</strong> When a device stops responding</li>
                                                <li>🔔 <strong>Warranty Expiry:</strong> 30 days before expiration</li>
                                                <li>🔔 <strong>AMC Expiry:</strong> Contract renewal reminders</li>
                                                <li>✅ <strong>Resolve alerts</strong> after addressing the issue</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eng4">
                                            <i class="fas fa-clipboard-list me-2"></i> 4. Reports
                                        </button>
                                    </h2>
                                    <div id="eng4" class="accordion-collapse collapse" data-bs-parent="#engineerGuide">
                                        <div class="accordion-body">
                                            <h6>Available Reports:</h6>
                                            <ul>
                                                <li>📋 <a href="{{ route('reports.inventory') }}"><strong>Inventory Report:</strong></a> Complete device list with details</li>
                                                <li>📅 <a href="{{ route('reports.expiry') }}"><strong>Expiry Report:</strong></a> Upcoming warranty/AMC expirations</li>
                                                <li>🔌 <a href="{{ route('reports.port-usage') }}"><strong>Port Usage:</strong></a> Port utilization across devices</li>
                                                <li>📈 <a href="{{ route('reports.availability') }}"><strong>Availability:</strong></a> Device uptime and response times</li>
                                            </ul>
                                            <p>All reports can be exported to <strong>CSV</strong> for further analysis.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-tasks me-2"></i>Quick Actions
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="{{ route('devices.create') }}" class="text-decoration-none">
                                        <div class="card border-left-success shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-plus-circle fa-2x text-success mb-3"></i>
                                                <h6>Add Device</h6>
                                                <small class="text-muted">Register new device</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('devices.index') }}" class="text-decoration-none">
                                        <div class="card border-left-primary shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-list fa-2x text-primary mb-3"></i>
                                                <h6>View Devices</h6>
                                                <small class="text-muted">Manage devices</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('monitoring.stats') }}" class="text-decoration-none">
                                        <div class="card border-left-info shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-chart-bar fa-2x text-info mb-3"></i>
                                                <h6>Monitoring</h6>
                                                <small class="text-muted">View statistics</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('alerts.index') }}" class="text-decoration-none">
                                        <div class="card border-left-danger shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-bell fa-2x text-danger mb-3"></i>
                                                <h6>View Alerts</h6>
                                                <small class="text-muted">Check alerts</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <h5 class="text-warning mt-4 mb-3">
                                <i class="fas fa-lightbulb me-2"></i>Best Practices
                            </h5>
                            <div class="alert alert-success">
                                <ul class="mb-0">
                                    <li>Document port assignments thoroughly</li>
                                    <li>Mark critical devices appropriately</li>
                                    <li>Update device status when in maintenance</li>
                                    <li>Regularly review and resolve alerts</li>
                                    <li>Keep warranty and AMC dates updated</li>
                                    <li>Use the search and filter for large inventories</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endrole

                    @role('viewer')
                    <!-- Viewer Guide -->
                    <div class="row">
                        <div class="col-lg-6">
                            <h5 class="text-primary mb-3">
                                <i class="fas fa-eye me-2"></i>Viewer Guide
                            </h5>
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                As a <strong>Viewer</strong>, you have read-only access to monitor network status and generate reports.
                            </div>

                            <div class="accordion" id="viewerGuide">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#view1">
                                            <i class="fas fa-desktop me-2"></i> 1. Dashboard Overview
                                        </button>
                                    </h2>
                                    <div id="view1" class="accordion-collapse collapse show" data-bs-parent="#viewerGuide">
                                        <div class="accordion-body">
                                            <p>The dashboard shows:</p>
                                            <ul>
                                                <li>📊 <strong>Total Devices:</strong> Number of registered devices</li>
                                                <li>✅ <strong>Online Devices:</strong> Currently reachable devices</li>
                                                <li>❌ <strong>Offline Devices:</strong> Devices not responding</li>
                                                <li>📈 <strong>Port Utilization:</strong> Percentage of ports in use</li>
                                            </ul>
                                            <p>Use the <strong>Refresh</strong> button or wait for auto-refresh every 30 seconds.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#view2">
                                            <i class="fas fa-server me-2"></i> 2. Viewing Devices
                                        </button>
                                    </h2>
                                    <div id="view2" class="accordion-collapse collapse" data-bs-parent="#viewerGuide">
                                        <div class="accordion-body">
                                            <h6>Device List:</h6>
                                            <ul>
                                                <li>🔍 <strong>Search:</strong> Find devices by name, IP address</li>
                                                <li>🔍 <strong>Filter:</strong> Filter by type, status, or location</li>
                                                <li>👁️ <strong>View Details:</strong> Click on any device to see full information</li>
                                                <li>📊 <strong>Ports:</strong> View port configuration and utilization</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#view3">
                                            <i class="fas fa-chart-bar me-2"></i> 3. Reports
                                        </button>
                                    </h2>
                                    <div id="view3" class="accordion-collapse collapse" data-bs-parent="#viewerGuide">
                                        <div class="accordion-body">
                                            <h6>Available Reports:</h6>
                                            <ul>
                                                <li>📋 <strong>Inventory:</strong> Complete list of all devices</li>
                                                <li>📅 <strong>Expiry:</strong> Warranty and AMC status</li>
                                                <li>🔌 <strong>Port Usage:</strong> Port utilization details</li>
                                                <li>📈 <strong>Availability:</strong> Device uptime statistics</li>
                                            </ul>
                                            <p>All reports can be <strong>exported to CSV</strong> for Excel analysis.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#view4">
                                            <i class="fas fa-question-circle me-2"></i> 4. Getting Help
                                        </button>
                                    </h2>
                                    <div id="view4" class="accordion-collapse collapse" data-bs-parent="#viewerGuide">
                                        <div class="accordion-body">
                                            <p>If you need to:</p>
                                            <ul>
                                                <li>🔧 <strong>Add/Edit devices:</strong> Contact a Network Engineer</li>
                                                <li>🔧 <strong>Configure ports:</strong> Contact a Network Engineer</li>
                                                <li>🔧 <strong>Manage users:</strong> Contact an Administrator</li>
                                                <li>🔧 <strong>System issues:</strong> Contact IT Support</li>
                                            </ul>
                                            <p>Your role provides <strong>read-only access</strong> for monitoring purposes.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <h5 class="text-success mb-3">
                                <i class="fas fa-tasks me-2"></i>Quick Actions
                            </h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="{{ route('devices.index') }}" class="text-decoration-none">
                                        <div class="card border-left-primary shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-server fa-2x text-primary mb-3"></i>
                                                <h6>View Devices</h6>
                                                <small class="text-muted">Browse devices</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('reports.inventory') }}" class="text-decoration-none">
                                        <div class="card border-left-info shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-file-alt fa-2x text-info mb-3"></i>
                                                <h6>View Reports</h6>
                                                <small class="text-muted">Generate reports</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('locations.index') }}" class="text-decoration-none">
                                        <div class="card border-left-success shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-map-marker-alt fa-2x text-success mb-3"></i>
                                                <h6>View Locations</h6>
                                                <small class="text-muted">Browse locations</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('alerts.index') }}" class="text-decoration-none">
                                        <div class="card border-left-warning shadow-sm h-100 hover-card">
                                            <div class="card-body text-center py-4">
                                                <i class="fas fa-bell fa-2x text-warning mb-3"></i>
                                                <h6>View Alerts</h6>
                                                <small class="text-muted">Check notifications</small>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>

                            <h5 class="text-info mt-4 mb-3">
                                <i class="fas fa-info-circle me-2"></i>Important Notes
                            </h5>
                            <div class="alert alert-info">
                                <ul class="mb-0">
                                    <li>You have <strong>read-only access</strong> to the system</li>
                                    <li>Cannot modify devices, ports, or settings</li>
                                    <li>Can view all dashboards and reports</li>
                                    <li>Can export reports for analysis</li>
                                    <li>Contact admin for any changes needed</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .accordion-button:not(.collapsed) {
        background-color: #f8f9fc;
        color: #4e73df;
    }
    .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0,0,0,.125);
    }
</style>
@endpush

@push('scripts')
<script>
    $(document).ready(function() {
        // Auto-refresh dashboard data every 60 seconds
        setInterval(function() {
            $.get('{{ route("dashboard.data") }}', function(data) {
                updateDashboardStats(data);
            });
        }, 60000);

        function updateDashboardStats(data) {
            // Update device counts
            if (data.deviceStats) {
                // You can update specific elements here
                console.log('Dashboard data refreshed', data);
            }
        }
    });
</script>
@endpush
@endsection
