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

            // Client permissions — one page-level permission per client-portal page.
            // Having the permission grants full access to everything inside that page.
            ['name' => 'orders',           'display_name' => 'Orders',           'scope' => 'client', 'group' => 'orders'],
            ['name' => 'support',          'display_name' => 'Support',          'scope' => 'client', 'group' => 'support'],
            ['name' => 'payout_invoices',  'display_name' => 'Payout Invoices',  'scope' => 'client', 'group' => 'payout_invoices'],
            ['name' => 'billing',          'display_name' => 'Billing',          'scope' => 'client', 'group' => 'billing'],
            ['name' => 'reports',          'display_name' => 'Reports',          'scope' => 'client', 'group' => 'reports'],
            ['name' => 'team',             'display_name' => 'Team',             'scope' => 'client', 'group' => 'team'],
            ['name' => 'account',          'display_name' => 'Account',          'scope' => 'client', 'group' => 'account'],
            ['name' => 'ai_assistant',     'display_name' => 'AI Assistant',     'scope' => 'client', 'group' => 'ai_assistant'],
        ];

        $rows = array_map(fn($p) => array_merge($p, [
            'description' => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]), $permissions);

        // Drop old granular client permissions that no longer exist in the simplified,
        // page-level model above (cascades to client_employee_permission_user grants).
        $clientPageNames = array_column(array_filter($permissions, fn($p) => $p['scope'] === 'client'), 'name');
        DB::table('permissions')->where('scope', 'client')->whereNotIn('name', $clientPageNames)->delete();

        DB::table('permissions')->upsert(
            $rows,
            ['name', 'scope'],
            ['display_name', 'group', 'updated_at']
        );
    }
}
