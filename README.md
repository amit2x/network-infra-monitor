<<<<<<< HEAD
I'll write the complete README.md file in proper markdown format from start to finish.

**README.md**
```markdown
# 🖥️ Network Infrastructure Monitoring & Asset Management System

A comprehensive web-based system for monitoring, managing, and tracking network infrastructure assets such as switches, routers, and associated components. Built with Laravel 12, Bootstrap 5, and MySQL.

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php)](https://php.net)
[![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?style=flat-square&logo=bootstrap)](https://getbootstrap.com)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql)](https://mysql.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📋 Table of Contents

- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [System Requirements](#-system-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Database Setup](#-database-setup)
- [User Roles & Permissions](#-user-roles--permissions)
- [Scheduler Setup](#-scheduler-setup)
- [Usage Guide](#-usage-guide)
- [API Documentation](#-api-documentation)
- [Project Structure](#-project-structure)
- [Commands Reference](#-commands-reference)
- [Troubleshooting](#-troubleshooting)
- [Contributing](#-contributing)
- [Roadmap](#-roadmap)
- [License](#-license)
- [Support](#-support)

---

## 🚀 Features

### Core Modules

#### 📡 Device Management
- Complete CRUD operations for network devices
- Support for switches, routers, firewalls, access points, servers
- Device lifecycle tracking (procurement, installation, warranty, AMC)
- Real-time device status monitoring (Online/Offline/Maintenance)
- Critical device flagging for priority monitoring
- Custom device code auto-generation

#### 📍 Location Management
- Hierarchical location structure (Airport → Terminal → IT Room → Rack)
- Physical deployment mapping
- GPS coordinate support for location visualization
- Parent-child location relationships
- Device count per location tracking

#### 🔌 Port Management
- Visual port grid and list views
- Port type support (Copper, SFP, SFP+, QSFP)
- Service mapping to ports (CCTV, WiFi, VoIP, etc.)
- Connected device tracking
- VLAN configuration
- Bulk port update functionality
- Port utilization statistics

#### 📊 Monitoring & Alerts
- Automated ICMP ping monitoring
- Real-time device availability tracking
- Response time measurement
- Critical device priority monitoring
- Warranty and AMC expiry alerts
- Email notifications for critical events
- Monitoring logs with detailed history

#### 📈 Reporting
- Device inventory reports with filtering
- Contract expiry reports (Warranty & AMC)
- Port utilization reports with charts
- Device availability reports with trends
- CSV export for all reports
- Printable report formats

#### 🔐 User Management
- Role-based access control (RBAC)
- Three default roles: Admin, Network Engineer, Viewer
- Customizable permissions per role
- User activity logs
- Profile management
- Secure authentication system

---

## 🛠 Technology Stack

| Category | Technology | Version |
|----------|------------|---------|
| **Backend Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.2+ |
| **Database** | MySQL | 8.0+ |
| **Frontend Framework** | Bootstrap | 5.3.2 |
| **JavaScript** | jQuery | 3.7.1 |
| **Charts** | Chart.js | 4.4.1 |
| **Data Tables** | DataTables | 1.13.8 |
| **Icons** | Font Awesome | 6.5.1 |
| **Notifications** | SweetAlert2 | 11.x |
| **RBAC** | Spatie Laravel Permission | 6.x |

---

## 💻 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.3+)
- **Web Server**: Apache 2.4+ with mod_rewrite or Nginx 1.18+
- **PHP Extensions**: 
  - BCMath
  - Ctype
  - Fileinfo
  - JSON
  - Mbstring
  - OpenSSL
  - PDO
  - Tokenizer
  - XML
  - GD (for image processing)

### Recommended
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 500MB+ (depends on data volume)
- **Composer**: 2.x
- **Node.js**: 18.x+ (for Vite asset compilation)

---

## 📦 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/network-infra-monitor.git
cd network-infra-monitor
```

### Step 2: Install PHP Dependencies

```bash
composer install
```

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

### Step 4: Environment Configuration

```bash
# Copy the example environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### Step 5: Configure Environment Variables

Edit the `.env` file with your database and mail settings:

```env
APP_NAME="Network Infrastructure Monitor"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=network_infra
DB_USERNAME=root
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=noreply@networkmonitor.com
MAIL_FROM_NAME="Network Monitor"
```

### Step 6: Generate Storage Link

```bash
php artisan storage:link
```

### Step 7: Compile Assets

```bash
# For development
npm run dev

# For production
npm run build
```

### Step 8: Create Database

Create a MySQL database named `network_infra`:

```bash
mysql -u root -p
CREATE DATABASE network_infra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 9: Database Setup

```bash
# Run migrations
php artisan migrate

# Run seeders (creates default users, roles, and sample data)
php artisan db:seed

# Or run specific seeders
php artisan db:seed --class=LocationSeeder
php artisan db:seed --class=DeviceSeeder
```

### Step 10: Set Permissions

```bash
# For Linux/macOS
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app

# For Linux with Apache
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Step 11: Start Development Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## ⚙️ Configuration

### Default Users After Seeding

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@networkmonitor.com | Admin@123456 |
| **Network Engineer** | engineer@networkmonitor.com | Engineer@123456 |
| **Viewer** | viewer@networkmonitor.com | Viewer@123456 |

> ⚠️ **Security Note**: Change these passwords immediately after first login in production!

### Permissions & Roles

The system uses Spatie's Laravel Permission package. Permissions are automatically created when running seeder:

```bash
php artisan db:seed
```

Key permissions:
- `view dashboard` - Access dashboard
- `create devices` - Add new devices
- `edit devices` - Modify device details
- `delete devices` - Remove devices
- `view devices` - View device list
- `manage ports` - Configure ports
- `view alerts` - See alerts
- `resolve alerts` - Resolve alerts
- `run monitoring` - Execute monitoring
- `view reports` - Access reports
- `manage users` - User administration
- `manage settings` - System settings

### Monitoring Configuration

Configure monitoring settings in the admin panel or directly in `.env`:

```env
MONITORING_INTERVAL=5          # Minutes between monitoring cycles
PING_TIMEOUT=2                 # Ping timeout in seconds
LOG_RETENTION_DAYS=30          # Days to keep monitoring logs
DEFAULT_PORT_COUNT=24          # Default ports for new switches
```

---

## 🗄 Database Setup

### Fresh Installation with Sample Data

```bash
# Run all migrations and seeders
php artisan migrate:fresh --seed

# This will create:
# - All database tables
# - Default roles and permissions
# - 3 default users
# - Sample locations (airports, terminals, IT rooms, racks)
# - Sample devices with ports
# - Sample monitoring data
```

### Database Structure

The system creates the following tables:

| Table | Description |
|-------|-------------|
| `locations` | Hierarchical location data |
| `devices` | Network device inventory |
| `ports` | Device port configuration |
| `monitoring_logs` | Ping check history |
| `alerts` | System alerts and notifications |
| `users` | User accounts |
| `roles` | Spatie roles |
| `permissions` | Spatie permissions |
| `model_has_roles` | User role assignments |
| `model_has_permissions` | User permission assignments |

---

## 🔐 User Roles & Permissions

### Admin Role
- Full system access
- User management
- System settings configuration
- All device operations
- Report generation

### Network Engineer Role
- Device management
- Port configuration
- Run monitoring
- View and resolve alerts
- Access reports
- View locations

### Viewer Role
- View dashboard
- View devices and locations
- View alerts (read-only)
- View reports

### Managing Roles and Permissions

```bash
# List all roles
php artisan permission:show

# Create new role
php artisan tinker
>>> $role = Spatie\Permission\Models\Role::create(['name' => 'custom-role']);

# Assign permissions to role
>>> $role->givePermissionTo('view devices');
>>> $role->givePermissionTo(['create devices', 'edit devices']);

# Assign role to user
>>> $user = User::find(1);
>>> $user->assignRole('custom-role');
```

---

## 🔄 Scheduler Setup

### Linux/macOS (Cron)

Add this to your crontab:

```bash
crontab -e
```

Add the following line:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Windows (Task Scheduler)

Create a batch file `scheduler.bat`:

```batch
@echo off
cd C:\path-to-your-project
php artisan schedule:run
```

Add to Windows Task Scheduler to run every minute.

### Verify Scheduler

```bash
# List all scheduled tasks
php artisan schedule:list

# Test run scheduler
php artisan schedule:run

# Test specific command
php artisan monitoring:run

# Test with verbose output
php artisan schedule:run -v
```

### Scheduled Tasks Overview

| Task | Frequency | Description |
|------|-----------|-------------|
| Critical device monitoring | Every 5 minutes | Ping critical devices only |
| Full monitoring cycle | Every 15 minutes | Ping all enabled devices |
| Expiry date check | Daily at 9:00 AM | Check warranty and AMC expiry |
| Log cleanup | Daily at 1:00 AM | Remove logs older than 30 days |
| Daily report | Daily at 8:00 AM | Generate and email daily report |
| Database backup | Daily at 2:00 AM | Backup database with compression |

---

## 📖 Usage Guide

### Quick Start

1. Login with admin credentials
2. Add Locations (Airports → Terminals → IT Rooms → Racks)
3. Add Devices with location mapping
4. Configure Ports for switches
5. Enable Monitoring for devices
6. View Dashboard for real-time status

### Device Management Workflow
graph LR
    A[Add Location] --> B[Add Device]
    B --> C[Configure Ports]
    C --> D[Enable Monitoring]
    D --> E[View Reports]

1. **Add Location** - Navigate to Locations → Add Location
2. **Create Device** - Navigate to Devices → Add Device
3. **Configure Ports** - Click on device → Ports tab → Configure ports
4. **Enable Monitoring** - Set monitoring flag in device settings
5. **View Status** - Dashboard shows real-time device status

### Adding a New Device

1. Go to **Devices** → **Add Device**
2. Fill in device information:
   - Name, Type, Vendor, Model
   - Serial Number (unique)
   - IP Address (must be reachable)
   - MAC Address (optional)
3. Select location from hierarchy
4. Set lifecycle dates (procurement, warranty, AMC)
5. Enable monitoring
6. Click **Create Device**

### Configuring Ports

1. Go to device detail page
2. Click **Ports** tab
3. View visual port grid
4. Click on any port to configure:
   - Set status (Active/Free/Down/Disabled)
   - Assign service name (CCTV, WiFi, etc.)
   - Enter connected device
   - Configure VLAN ID
   - Set speed
5. Use **Bulk Edit** for multiple ports

### Running Monitoring

**Manual:**
1. Go to **Monitoring** → **Run Monitoring Now**
2. Wait for cycle to complete
3. View results in logs

**Automatic:**
- System automatically pings devices based on schedule
- Critical devices monitored more frequently
- Alerts generated for status changes

### Generating Reports

1. Navigate to **Reports** section
2. Select report type:
   - **Inventory** - Complete device list
   - **Expiry** - Warranty/AMC status
   - **Port Usage** - Port utilization
   - **Availability** - Device uptime
3. Apply filters as needed
4. Export to CSV or Print

---

## 🔌 API Documentation

### Authentication

The API uses Laravel Sanctum for authentication.

**Login and get token:**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@networkmonitor.com", "password": "Admin@123456"}'
```

**Response:**
```json
{
    "token": "1|abcdef123456789",
    "user": {
        "id": 1,
        "name": "System Admin",
        "email": "admin@networkmonitor.com"
    }
}
```

**Use token in requests:**
```bash
curl http://localhost:8000/api/devices \
  -H "Authorization: Bearer 1|abcdef123456789"
```

### Available Endpoints

#### Dashboard
```bash
GET /api/dashboard/stats
```
Response:
```json
{
    "success": true,
    "data": {
        "devices": {
            "total": 6,
            "online": 5,
            "offline": 1,
            "maintenance": 0
        },
        "ports": {
            "total": 120,
            "active": 85,
            "free": 30,
            "down": 5
        },
        "alerts": {
            "total": 12,
            "critical": 2,
            "unresolved": 5
        }
    }
}
```

#### Devices
```bash
# List all devices
GET /api/devices

# Get single device
GET /api/devices/{id}

# Create device
POST /api/devices

# Update device
PUT /api/devices/{id}

# Delete device
DELETE /api/devices/{id}

# Ping device
POST /api/devices/{id}/ping

# Get device ports
GET /api/devices/{id}/ports
```

#### Locations
```bash
# List all locations
GET /api/locations

# Get single location
GET /api/locations/{id}

# Get devices in location
GET /api/locations/{id}/devices
```

#### Alerts
```bash
# List all alerts
GET /api/alerts

# Get single alert
GET /api/alerts/{id}

# Resolve alert
POST /api/alerts/{id}/resolve

# Get unread count
GET /api/alerts/count/unread
```

#### Monitoring
```bash
# Get monitoring logs
GET /api/monitoring/logs

# Run monitoring
POST /api/monitoring/run

# Get monitoring stats
GET /api/monitoring/stats
```

### API Error Responses

```json
{
    "success": false,
    "message": "Device not found",
    "errors": {
        "id": ["The selected id is invalid."]
    }
}
```

---

## 📁 Project Structure

```
network-infra-monitor/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       ├── BackupDatabaseCommand.php
│   │       ├── CheckExpiryDatesCommand.php
│   │       ├── CleanOldLogsCommand.php
│   │       ├── GenerateDailyReportCommand.php
│   │       └── RunMonitoringCommand.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AlertController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── DeviceController.php
│   │   │   │   └── LocationController.php
│   │   │   ├── Admin/
│   │   │   │   ├── SettingsController.php
│   │   │   │   └── UserController.php
│   │   │   ├── AlertController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── DeviceController.php
│   │   │   ├── LocationController.php
│   │   │   ├── MonitoringController.php
│   │   │   ├── PortController.php
│   │   │   ├── ProfileController.php
│   │   │   └── ReportController.php
│   │   ├── Requests/
│   │   │   ├── Device/
│   │   │   │   ├── StoreDeviceRequest.php
│   │   │   │   └── UpdateDeviceRequest.php
│   │   │   ├── Location/
│   │   │   │   └── StoreLocationRequest.php
│   │   │   ├── Port/
│   │   │   │   └── UpdatePortRequest.php
│   │   │   ├── Profile/
│   │   │   │   ├── UpdatePasswordRequest.php
│   │   │   │   └── UpdateProfileRequest.php
│   │   │   └── User/
│   │   │       ├── StoreUserRequest.php
│   │   │       └── UpdateUserRequest.php
│   │   └── Middleware/
│   ├── Mail/
│   │   ├── DailyReport.php
│   │   └── DeviceDownAlert.php
│   ├── Models/
│   │   ├── Alert.php
│   │   ├── Device.php
│   │   ├── Location.php
│   │   ├── MonitoringLog.php
│   │   ├── Port.php
│   │   └── User.php
│   └── Services/
│       ├── DeviceService.php
│       ├── LocationService.php
│       └── MonitoringService.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── permission.php
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── 2024_01_01_000001_create_locations_table.php
│   │   ├── 2024_01_01_000002_create_devices_table.php
│   │   ├── 2024_01_01_000003_create_ports_table.php
│   │   ├── 2024_01_01_000004_create_monitoring_logs_table.php
│   │   └── 2024_01_01_000005_create_alerts_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── DeviceSeeder.php
│       └── LocationSeeder.php
├── public/
│   ├── css/
│   ├── js/
│   └── index.php
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   └── app.js
│   └── views/
│       ├── admin/
│       │   ├── settings.blade.php
│       │   └── users/
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       ├── alerts/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── devices/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── emails/
│       │   └── device-down-alert.blade.php
│       ├── layouts/
│       │   └── app.blade.php
│       ├── locations/
│       │   ├── create.blade.php
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── monitoring/
│       │   ├── logs.blade.php
│       │   └── stats.blade.php
│       ├── ports/
│       │   ├── edit.blade.php
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── profile/
│       │   ├── activity.blade.php
│       │   └── edit.blade.php
│       └── reports/
│           ├── availability.blade.php
│           ├── expiry.blade.php
│           ├── inventory.blade.php
│           └── port-usage.blade.php
├── routes/
│   ├── api.php
│   ├── console.php
│   └── web.php
├── storage/
│   ├── app/
│   ├── backups/
│   ├── framework/
│   └── logs/
├── .env.example
├── .gitignore
├── composer.json
├── LICENSE
├── package.json
├── README.md
└── vite.config.js
```

---

## 📟 Commands Reference

### Monitoring Commands

```bash
# Run full monitoring cycle
php artisan monitoring:run

# Monitor only critical devices
php artisan monitoring:run --critical-only

# Monitor specific device
php artisan monitoring:run --device=1

# Monitor with custom timeout
php artisan monitoring:run --timeout=3

# Check expiry dates for next 30 days (default)
php artisan monitoring:check-expiry

# Check expiry for next 60 days
php artisan monitoring:check-expiry --days=60

# Check only warranty expiry
php artisan monitoring:check-expiry --type=warranty

# Check only AMC expiry
php artisan monitoring:check-expiry --type=amc

# Check expiry and send notifications
php artisan monitoring:check-expiry --notify

# Clean logs older than 30 days (default)
php artisan monitoring:clean-logs

# Clean logs older than 90 days
php artisan monitoring:clean-logs --days=90

# Clean only monitoring logs
php artisan monitoring:clean-logs --type=logs

# Clean only resolved alerts
php artisan monitoring:clean-logs --type=alerts

# Force clean without confirmation
php artisan monitoring:clean-logs --force
```

### Report Commands

```bash
# Generate today's report
php artisan report:daily

# Generate report for specific date
php artisan report:daily --date=2024-01-15

# Generate report in JSON format
php artisan report:daily --format=json

# Generate report in CSV format
php artisan report:daily --format=csv

# Send report via email
php artisan report:daily --email=admin@example.com
```

### Maintenance Commands

```bash
# Backup database
php artisan db:backup

# Backup with compression
php artisan db:backup --compress

# Backup to custom path
php artisan db:backup --path=/custom/backup/path
```

### Artisan Commands

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Rebuild cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# List all registered routes
php artisan route:list

# Show scheduled tasks
php artisan schedule:list

# Create new user
php artisan tinker
>>> User::create(['name' => 'User', 'email' => 'user@test.com', 'password' => bcrypt('password')]);
```

---

## 🔧 Troubleshooting

### Common Issues and Solutions

#### 1. Database Connection Error

**Error:** `SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost'`

**Solution:**
```bash
# Check MySQL is running
sudo service mysql status
# or
sudo systemctl status mysql

# Start MySQL if not running
sudo service mysql start

# Verify credentials in .env file
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=network_infra
DB_USERNAME=root
DB_PASSWORD=your_password

# Test connection
php artisan db:show
```

#### 2. Permission Denied Errors

**Error:** `The stream or file could not be opened in append mode`

**Solution:**
```bash
# Set proper permissions for storage
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app

# For Linux with Apache
sudo chown -R www-data:www-data storage bootstrap/cache
```

#### 3. 500 Internal Server Error

**Error:** `500 Server Error` with blank page

**Solution:**
```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Reset application cache
php artisan optimize:clear

# Check .env file exists
ls -la .env

# Verify APP_KEY is set
php artisan key:generate
```

#### 4. Assets Not Loading (CSS/JS)

**Error:** Vite manifest not found or assets 404

**Solution:**
```bash
# Install dependencies
npm install

# Build assets for development
npm run dev

# Build assets for production
npm run build

# Clear cache
php artisan optimize:clear
```

#### 5. Routes Not Working

**Error:** `404 Not Found` for application routes

**Solution:**
```bash
# Clear route cache
php artisan route:clear

# Check registered routes
php artisan route:list

# For Apache, ensure .htaccess exists
# For Nginx, check configuration
```

#### 6. Scheduler Not Running

**Error:** Scheduled tasks not executing

**Solution:**
```bash
# Check crontab is set
crontab -l

# Should show:
# * * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1

# Test scheduler manually
php artisan schedule:run

# Check scheduler logs
tail -f storage/logs/monitoring.log

# Enable detailed logging
php artisan schedule:run -v
```

#### 7. Spatie Permission Errors

**Error:** `Target class [permission] does not exist`

**Solution:**
```bash
# Check Spatie is installed
composer show spatie/laravel-permission

# If not installed
composer require spatie/laravel-permission

# Publish config and migrations
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"

# Run migrations
php artisan migrate

# Clear cache
php artisan optimize:clear
```

#### 8. Login Issues

**Error:** Unable to login with default credentials

**Solution:**
```bash
# Re-run seeder to create default users
php artisan db:seed --class=DatabaseSeeder

# Or create user via tinker
php artisan tinker
>>> User::create([
    'name' => 'Admin',
    'email' => 'admin@test.com',
    'password' => bcrypt('password123'),
    'employee_id' => 'EMP001',
    'department' => 'IT',
    'is_active' => true,
    'email_verified_at' => now()
]);
>>> $user = User::find(1);
>>> $user->assignRole('admin');
```

### Debug Mode

Enable debug mode temporarily for troubleshooting (never in production):

```env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

Check logs:
```bash
# Laravel application logs
tail -f storage/logs/laravel.log

# Monitoring logs
tail -f storage/logs/monitoring.log

# Scheduler logs
tail -f storage/logs/schedule-health.json
```

---

## 🤝 Contributing

### Development Setup

1. Fork the repository
2. Clone your fork:
   ```bash
   git clone https://github.com/your-username/network-infra-monitor.git
   ```
3. Create a feature branch:
   ```bash
   git checkout -b feature/amazing-feature
   ```
4. Install dependencies:
   ```bash
   composer install
   npm install
   ```
5. Copy `.env.example` to `.env` and configure database
6. Run migrations:
   ```bash
   php artisan migrate:fresh --seed
   ```
7. Make your changes
8. Test your changes:
   ```bash
   php artisan test
   ```
9. Commit with descriptive messages:
   ```bash
   git commit -m 'feat: Add amazing feature'
   ```
10. Push to your branch:
    ```bash
    git push origin feature/amazing-feature
    ```
11. Create a Pull Request

### Coding Standards

Follow Laravel coding standards:

```bash
# Run Laravel Pint for code formatting
./vendor/bin/pint

# Run PHPStan for static analysis
./vendor/bin/phpstan analyse

# Run tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit
```

### Commit Message Convention

Use conventional commits:
- `feat:` New feature
- `fix:` Bug fix
- `docs:` Documentation changes
- `style:` Code style changes
- `refactor:` Code refactoring
- `test:` Adding tests
- `chore:` Maintenance tasks

### Pull Request Process

1. Update the README.md with details of changes if needed
2. Update the documentation if needed
3. The PR should pass all tests
4. The PR should follow coding standards
5. Get review from at least one maintainer

---

## 🎯 Roadmap

### Phase 4 (Upcoming)
- [ ] SNMP monitoring (CPU, bandwidth, memory)
- [ ] Network topology visualization
- [ ] Auto device discovery via SNMP
- [ ] Rack visualization with front/rear view
- [ ] Mobile app (Flutter integration)
- [ ] Integration with Zabbix
- [ ] Integration with Wazuh (SIEM)
- [ ] Advanced notification channels (Slack, Teams, SMS)
- [ ] Multi-tenancy support
- [ ] Audit trail and activity logging
- [ ] Custom dashboard widgets

### Phase 5 (Long-term)
- [ ] AI-powered anomaly detection
- [ ] Predictive maintenance alerts
- [ ] Network traffic analysis
- [ ] Configuration backup management
- [ ] IP address management (IPAM)
- [ ] Change management workflow
- [ ] SLA management and reporting
- [ ] Real-time WebSocket monitoring
- [ ] Docker containerization
- [ ] Kubernetes deployment support

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

```
MIT License

Copyright (c) 2024 Network Infrastructure Monitor

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

---

## 📞 Support

### Documentation
- [Laravel Documentation](https://laravel.com/docs)
- [Bootstrap Documentation](https://getbootstrap.com/docs/5.3)
- [Chart.js Documentation](https://www.chartjs.org/docs)
- [Spatie Permission Docs](https://spatie.be/docs/laravel-permission)

### Getting Help
- **GitHub Issues**: [Create Issue](https://github.com/your-username/network-infra-monitor/issues)
- **Email Support**: support@yourcompany.com
- **Documentation**: This README file

### Bug Reports
When reporting a bug, please include:
1. Description of the issue
2. Steps to reproduce
3. Expected behavior
4. Actual behavior
5. Screenshots if applicable
6. Environment details:
   - PHP version
   - Laravel version
   - Database version
   - Browser and version

### Feature Requests
For feature requests, please include:
1. Clear description of the feature
2. Use case and benefits
3. Any implementation ideas
4. Mockups or examples if available

---

## 🙏 Acknowledgments

Built with the following open-source technologies:

- **[Laravel](https://laravel.com)** - The PHP framework for web artisans
- **[Spatie Laravel Permission](https://spatie.be)** - Role-based access control
- **[Bootstrap](https://getbootstrap.com)** - Frontend component library
- **[Chart.js](https://www.chartjs.org)** - JavaScript charting library
- **[DataTables](https://datatables.net)** - Advanced table plugin
- **[Font Awesome](https://fontawesome.com)** - Icon library
- **[SweetAlert2](https://sweetalert2.github.io)** - Beautiful alert dialogs
- **[jQuery](https://jquery.com)** - JavaScript library

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Controllers** | 12+ |
| **Models** | 6 |
| **Views** | 30+ |
| **Commands** | 5 |
| **Services** | 3 |
| **Database Tables** | 8+ |
| **API Endpoints** | 15+ |
| **Scheduled Tasks** | 8+ |
| **Form Requests** | 8+ |
| **Middleware** | 5+ |

---

<div align="center">
    <br>
    <strong>Built with ❤️ using Laravel 12</strong>
    <br><br>
    <sub>© 2024 Network Infrastructure Monitor. All rights reserved.</sub>
    <br><br>
    <a href="#-network-infrastructure-monitoring--asset-management-system">
        ⬆️ Back to Top
    </a>
</div>
```
=======
# network-infra-monitor
*Network Infra Monitor is a Laravel-based web app for centralized tracking and management of network devices like switches and routers. It supports asset lifecycle, port mapping, warranty/AMC tracking, and deployment details with role-based access, helping IT teams monitor utilization and maintain infrastructure efficiently.
>>>>>>> c799fcfbfd9b7ec6f2abb787c448c7813e2b6b7a
