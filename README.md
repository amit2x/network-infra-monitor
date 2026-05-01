```markdown
# 🖥️ Network Infrastructure Monitoring & Asset Management System

A comprehensive web-based system for monitoring, managing, and tracking network infrastructure assets such as switches, routers, and associated components. Built with Laravel 12, Bootstrap 5, and MySQL.

*Network Infra Monitor is a Laravel-based web app for centralized tracking and management of network devices like switches and routers. It supports asset lifecycle, port mapping, warranty/AMC tracking, SNMP monitoring, network topology visualization, rack management, and deployment details with role-based access, helping IT teams monitor utilization and maintain infrastructure efficiently.*

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

#### 🔌 SNMP Monitoring (Phase 4)
- SNMP v1, v2c, and v3 support with authentication
- CPU and memory utilization monitoring
- Interface statistics and bandwidth tracking
- Real-time SNMP trap receiver
- SNMP MIB browser for OID exploration
- Device discovery via SNMP
- Configurable polling intervals

#### 📍 Location Management
- Hierarchical location structure (Airport → Terminal → IT Room → Rack)
- Physical deployment mapping with GPS coordinates
- Parent-child location relationships
- Device count per location tracking

#### 🖥️ Rack Visualization (Phase 4)
- Visual rack front/rear view
- Device mounting and unmounting
- Rack unit (U) tracking
- Color-coded device status
- Rack utilization statistics
- Multiple rack management per location

#### 🌐 Network Topology (Phase 4)
- CDP/LLDP neighbor discovery
- Interactive topology map with Vis.js
- Device relationship visualization
- Auto-discovery of network connections
- Real-time topology updates

#### 📊 Bandwidth Monitoring (Phase 4)
- Per-port bandwidth collection
- Inbound/outbound traffic tracking
- Bandwidth utilization graphs
- Top talkers identification
- Historical bandwidth trends

#### 🔌 Port Management
- Visual port grid and list views
- Port type support (Copper, SFP, SFP+, QSFP)
- Service mapping to ports (CCTV, WiFi, VoIP, etc.)
- Connected device tracking
- VLAN configuration
- Bulk port update functionality

#### 📊 Monitoring & Alerts
- Automated ICMP ping monitoring
- SNMP threshold alerts (CPU, Memory)
- Warranty and AMC expiry alerts
- Email notifications for critical events
- Monitoring logs with detailed history
- Audit trail for all CRUD operations

#### 📈 Reporting
- Device inventory reports with filtering
- Contract expiry reports (Warranty & AMC)
- Port utilization reports with charts
- Device availability reports with trends
- Bandwidth usage reports
- CSV export for all reports

#### 🔐 User Management
- Role-based access control (RBAC)
- Three default roles: Admin, Network Engineer, Viewer
- Customizable permissions per role
- User activity logs with audit trail
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
| **Topology** | Vis.js | 9.1.6 |
| **Maps** | Leaflet | 1.9.4 |
| **Data Tables** | DataTables | 1.13.8 |
| **Icons** | Font Awesome | 6.5.1 |
| **Notifications** | SweetAlert2 | 11.x |
| **RBAC** | Spatie Laravel Permission | 6.x |
| **SNMP** | PHP SNMP Extension | Native |

---

## 💻 System Requirements

### Minimum Requirements
- **PHP**: 8.2 or higher
- **MySQL**: 8.0 or higher (or MariaDB 10.3+)
- **Web Server**: Apache 2.4+ with mod_rewrite or Nginx 1.18+
- **PHP Extensions**: 
  - BCMath, Ctype, Fileinfo, JSON, Mbstring
  - OpenSSL, PDO, Tokenizer, XML, GD
  - **SNMP** (for SNMP monitoring features)

### Recommended
- **RAM**: 2GB minimum, 4GB recommended
- **Storage**: 500MB+ (depends on data volume)
- **Composer**: 2.x
- **Node.js**: 18.x+ (for Vite asset compilation)

---

## 📦 Installation

### Step 1: Clone the Repository

```bash
git clone https://github.com/amit2x/network-infra-monitor.git
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

### Step 4: Enable SNMP Extension (Optional)

**Windows (XAMPP):**
Open `php.ini` and uncomment: `extension=snmp`

**Linux:**
```bash
sudo apt-get install php-snmp snmp snmp-mibs-downloader
sudo systemctl restart apache2
```

### Step 5: Environment Configuration

```bash
cp .env.example .env
php artisan key:generate
```

### Step 6: Configure Environment Variables

Edit the `.env` file:

```env
APP_NAME="Network Infrastructure Monitor"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=network_infra
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app-password
MAIL_FROM_ADDRESS=noreply@networkmonitor.com

# SNMP Configuration
SNMP_VERSION=2c
SNMP_COMMUNITY=public
SNMP_TIMEOUT=1
SNMP_RETRIES=2
SNMP_PORT=161

# Google Maps 
GOOGLE_MAPS_API_KEY=####
```

### Step 7: Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE network_infra CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations and seeders
php artisan migrate
php artisan db:seed

# Or fresh install with sample data
php artisan migrate:fresh --seed
```

### Step 8: Compile Assets & Link Storage

```bash
php artisan storage:link
npm run dev    # Development
npm run build  # Production
```

### Step 9: Set Permissions

```bash
chmod -R 755 storage bootstrap/cache
chmod -R 777 storage/logs storage/framework storage/app
sudo chown -R www-data:www-data storage bootstrap/cache  # Linux
```

### Step 10: Start Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

---

## ⚙️ Configuration

### Default Users

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@networkmonitor.com | Admin@123456 |
| **Network Engineer** | engineer@networkmonitor.com | Engineer@123456 |
| **Viewer** | viewer@networkmonitor.com | Viewer@123456 |

> ⚠️ Change these passwords immediately after first login!

### Permissions

Key permissions managed by Spatie:
- `view dashboard`, `create devices`, `edit devices`, `delete devices`
- `manage ports`, `view alerts`, `resolve alerts`
- `run monitoring`, `view reports`, `manage users`, `manage settings`

### SNMP Configuration

```env
SNMP_VERSION=2c          # 1, 2c, or 3
SNMP_COMMUNITY=public    # Community string
SNMP_TIMEOUT=1           # Seconds
SNMP_RETRIES=2           # Retry attempts
SNMP_PORT=161            # Default SNMP port
```

---

## 🔄 Scheduler Setup

Add to crontab:

```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### Scheduled Tasks

| Task | Frequency | Command |
|------|-----------|---------|
| Critical SNMP monitoring | Every 5 min | `snmp:monitor --critical-only` |
| Full SNMP monitoring | Every 15 min | `snmp:monitor` |
| Expiry check | Daily 9 AM | `monitoring:check-expiry --notify` |
| Log cleanup | Daily 1 AM | `monitoring:clean-logs` |
| SNMP data cleanup | Daily 2 AM | `snmp:clean-data` |
| Database backup | Daily 2 AM | `db:backup --compress` |

---

## 📖 Usage Guide

### Quick Start Workflow

1. **Login** with admin credentials
2. **Add Locations**: Airports → Terminals → IT Rooms → Racks
3. **Create Racks**: Define rack sizes (42U, 24U, etc.)
4. **Add Devices**: Register devices with IP, SNMP config
5. **Mount Devices**: Place devices in racks
6. **Configure Ports**: Map services to ports
7. **Enable SNMP**: Configure community strings
8. **Discover Topology**: Find network neighbors
9. **Monitor**: View real-time dashboards

### SNMP Monitoring Setup

1. Enable SNMP on device: Edit Device → SNMP Configuration
2. Configure community string (default: public)
3. Test connection using "Test SNMP" button
4. Enable auto-polling for continuous monitoring
5. View data on SNMP Dashboard

### Rack Management

1. Create rack at location: Racks → Create Rack
2. Click on rack to view visualization
3. Mount devices using "Mount Device" button
4. Select U position and side (front/rear)
5. View front/rear rack views

### Topology Discovery

1. Navigate to Topology page
2. Click "Discover All" to scan network
3. View interactive topology map
4. Click on nodes for device details

---

## 📟 Commands Reference

### SNMP Commands
```bash
php artisan snmp:monitor                        # Full SNMP cycle
php artisan snmp:monitor --critical-only        # Critical devices only
php artisan snmp:discover 192.168.1.0/24       # Discover devices
php artisan snmp:discover 192.168.1.0/24 --add-devices  # Auto-add
php artisan snmp:clean-data --days=30           # Clean old data
php artisan snmp:trap-listen                    # Listen for SNMP traps
```

### Monitoring Commands
```bash
php artisan monitoring:run                      # Full ping monitoring
php artisan monitoring:run --critical-only      # Critical only
php artisan monitoring:check-expiry             # Check contracts
php artisan monitoring:clean-logs --days=90     # Clean old logs
```

### Maintenance Commands
```bash
php artisan db:backup                           # Backup database
php artisan db:backup --compress                # Compressed backup
php artisan report:daily                        # Generate daily report
php artisan report:daily --email=admin@test.com # Email report
```

---

## 🔧 Troubleshooting

### SNMP Issues
```bash
# Check SNMP extension
php -m | grep snmp

# Test SNMP connectivity
php artisan snmp:monitor --device=1

# Check SNMP logs
tail -f storage/logs/snmp-full.log
```

### Common Issues
```bash
# Clear all caches
php artisan optimize:clear

# Check application logs
tail -f storage/logs/laravel.log

# Verify routes
php artisan route:list
```

---

## 📁 Project Structure

```
network-infra-monitor/
├── app/
│   ├── Console/Commands/          # Artisan commands
│   ├── Http/Controllers/          # All controllers
│   │   ├── Api/                   # API controllers
│   │   └── Admin/                 # Admin controllers
│   ├── Models/                    # Eloquent models
│   ├── Services/                  # Business logic
│   │   ├── SNMPService.php        # SNMP operations
│   │   ├── SNMPMonitoringService.php
│   │   ├── TopologyService.php    # Network topology
│   │   ├── RackService.php        # Rack management
│   │   ├── BandwidthService.php   # Bandwidth monitoring
│   │   └── AuditService.php       # Audit trail
│   └── Traits/                    # Reusable traits
├── database/migrations/           # Database schema
├── resources/views/               # Blade templates
│   ├── snmp/                      # SNMP dashboard
│   ├── topology/                  # Topology map
│   ├── racks/                     # Rack visualization
│   ├── bandwidth/                 # Bandwidth charts
│   └── mib-browser/               # MIB browser
├── routes/
│   ├── web.php                    # Web routes
│   ├── api.php                    # API routes
│   └── console.php                # Scheduler
└── config/
    └── snmp.php                   # SNMP configuration
```

---

## 📊 Project Statistics

| Metric | Value |
|--------|-------|
| **Controllers** | 20+ |
| **Models** | 12+ |
| **Views** | 50+ |
| **Commands** | 10+ |
| **Services** | 8+ |
| **Database Tables** | 15+ |
| **API Endpoints** | 30+ |
| **Scheduled Tasks** | 8+ |

---

## 📄 License

MIT License - See [LICENSE](LICENSE) file.

---

<div align="center">
    <strong>Built with ❤️ using Laravel 12</strong>
    <br>
    <sub>© 2024 Network Infrastructure Monitor. All rights reserved.</sub>
</div>
```

This is the final, complete README.md file with all Phase 4 features included. The merge conflict is resolved by combining both versions into one comprehensive document.