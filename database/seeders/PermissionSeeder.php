<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $permissions = [
            // Admin permissions
            ['name' => 'view_dashboard',           'display_name' => 'View Dashboard',              'scope' => 'admin', 'group' => 'dashboard'],
            ['name' => 'view_all_orders',           'display_name' => 'View All Orders',             'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'manage_orders',             'display_name' => 'Manage Orders',               'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'assign_drivers',            'display_name' => 'Assign Drivers',              'scope' => 'admin', 'group' => 'orders'],
            ['name' => 'view_clients',              'display_name' => 'View Clients',                'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'manage_clients',            'display_name' => 'Manage Clients',              'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'verify_client_documents',   'display_name' => 'Verify Client Documents',    'scope' => 'admin', 'group' => 'clients'],
            ['name' => 'view_drivers',              'display_name' => 'View Drivers',                'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'manage_drivers',            'display_name' => 'Manage Drivers',              'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'verify_driver_documents',   'display_name' => 'Verify Driver Documents',    'scope' => 'admin', 'group' => 'drivers'],
            ['name' => 'view_invoices',             'display_name' => 'View Invoices',               'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'manage_invoices',           'display_name' => 'Manage Invoices',             'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'manage_wallet',             'display_name' => 'Manage Client Wallets',       'scope' => 'admin', 'group' => 'billing'],
            ['name' => 'view_reports',              'display_name' => 'View Reports',                'scope' => 'admin', 'group' => 'reports'],
            ['name' => 'manage_admins',             'display_name' => 'Manage Admins',               'scope' => 'admin', 'group' => 'admin_mgmt'],
            ['name' => 'grant_admin_permissions',   'display_name' => 'Grant Admin Permissions',    'scope' => 'admin', 'group' => 'admin_mgmt'],
            ['name' => 'view_system_logs',          'display_name' => 'View System Logs',            'scope' => 'admin', 'group' => 'system'],
            ['name' => 'manage_system_settings',    'display_name' => 'Manage System Settings',     'scope' => 'admin', 'group' => 'system'],

            // Client permissions
            ['name' => 'place_orders',              'display_name' => 'Place Orders',                'scope' => 'client', 'group' => 'orders'],
            ['name' => 'view_own_orders',           'display_name' => 'View Own Orders',             'scope' => 'client', 'group' => 'orders'],
            ['name' => 'view_all_company_orders',   'display_name' => 'View All Company Orders',    'scope' => 'client', 'group' => 'orders'],
            ['name' => 'cancel_orders',             'display_name' => 'Cancel Orders',               'scope' => 'client', 'group' => 'orders'],
            ['name' => 'track_orders',              'display_name' => 'Track Orders',                'scope' => 'client', 'group' => 'orders'],
            ['name' => 'view_invoices',             'display_name' => 'View Invoices',               'scope' => 'client', 'group' => 'billing'],
            ['name' => 'download_invoices',         'display_name' => 'Download Invoices',           'scope' => 'client', 'group' => 'billing'],
            ['name' => 'view_wallet',               'display_name' => 'View Wallet',                 'scope' => 'client', 'group' => 'billing'],
            ['name' => 'top_up_wallet',             'display_name' => 'Top Up Wallet',               'scope' => 'client', 'group' => 'billing'],
            ['name' => 'view_reports',              'display_name' => 'View Reports',                'scope' => 'client', 'group' => 'reports'],
            ['name' => 'export_reports',            'display_name' => 'Export Reports',              'scope' => 'client', 'group' => 'reports'],
            ['name' => 'manage_addresses',          'display_name' => 'Manage Saved Addresses',     'scope' => 'client', 'group' => 'addresses'],
            ['name' => 'invite_employees',          'display_name' => 'Invite Employees',            'scope' => 'client', 'group' => 'team'],
            ['name' => 'manage_team',               'display_name' => 'Manage Team Permissions',    'scope' => 'client', 'group' => 'team'],
            ['name' => 'view_team',                 'display_name' => 'View Team Members',           'scope' => 'client', 'group' => 'team'],
        ];

        $rows = array_map(fn($p) => array_merge($p, [
            'description' => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]), $permissions);

        DB::table('permissions')->upsert(
            $rows,
            ['name', 'scope'],
            ['display_name', 'group', 'updated_at']
        );
    }
}
