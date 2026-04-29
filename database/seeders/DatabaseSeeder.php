<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use LocationSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::create(['name' => 'admin']);
        $engineerRole = Role::create(['name' => 'network_engineer']);
        $viewerRole = Role::create(['name' => 'viewer']);

        // Create permissions
        $permissions = [
            'view dashboard',
            'create devices',
            'edit devices',
            'delete devices',
            'view devices',
            'create locations',
            'edit locations',
            'delete locations',
            'view locations',
            'manage ports',
            'view alerts',
            'resolve alerts',
            'run monitoring',
            'view reports',
            'manage users',
            'manage settings'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign permissions to roles
        $adminRole->givePermissionTo(Permission::all());

        $engineerRole->givePermissionTo([
            'view dashboard',
            'view devices',
            'create devices',
            'edit devices',
            'view locations',
            'manage ports',
            'view alerts',
            'resolve alerts',
            'run monitoring',
            'view reports'
        ]);

        $viewerRole->givePermissionTo([
            'view dashboard',
            'view devices',
            'view locations',
            'view alerts',
            'view reports'
        ]);

        // Create admin user
        $admin = User::create([
            'name' => 'System Admin',
            'email' => 'admin@networkmonitor.com',
            'password' => bcrypt('Admin@123456'),
            'employee_id' => 'EMP001',
            'department' => 'IT',
            'phone' => '1234567890',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $admin->assignRole('admin');

        // Create network engineer
        $engineer = User::create([
            'name' => 'Network Engineer',
            'email' => 'engineer@networkmonitor.com',
            'password' => bcrypt('Engineer@123456'),
            'employee_id' => 'EMP002',
            'department' => 'Networks',
            'phone' => '1234567891',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $engineer->assignRole('network_engineer');

        // Create viewer
        $viewer = User::create([
            'name' => 'Report Viewer',
            'email' => 'viewer@networkmonitor.com',
            'password' => bcrypt('Viewer@123456'),
            'employee_id' => 'EMP003',
            'department' => 'Management',
            'phone' => '1234567892',
            'is_active' => true,
            'email_verified_at' => now()
        ]);
        $viewer->assignRole('viewer');

        // Seed sample locations and devices
        $this->call([
            LocationSeeder::class,
            DeviceSeeder::class,
        ]);
    }
}
