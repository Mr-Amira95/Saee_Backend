<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Finance pages were reduced to Handover, Client Statements, Driver Payroll and
 * Expenses (admin) and a single Statements page (client portal). Rename the
 * surviving permissions in place so existing grants are kept, carry grants of
 * removed pages over to the page that replaces them, then drop the old rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        // ── Admin ────────────────────────────────────────────────────────────
        $this->rename('admin', 'finances.checkout_approvals', 'finances.handover', 'Handover');

        $handoverId   = $this->ensure('admin', 'finances.handover', 'Handover', 'finances', $now);
        $statementsId = $this->ensure('admin', 'finances.client_statements', 'Client Statements', 'finances', $now);

        // Settlements' driver cash settling now lives in Handover
        $this->copyAdminGrants(['finances.settlements'], $handoverId, $now);
        // Client payouts / invoices / billing now live in Client Statements
        $this->copyAdminGrants(['finances.settlements', 'finances.cod_invoices', 'finances.client_billing'], $statementsId, $now);

        DB::table('permissions')->where('scope', 'admin')
            ->whereIn('name', ['finances.settlements', 'finances.cod_invoices', 'finances.reconciliation', 'finances.client_billing'])
            ->delete();

        // ── Client portal ────────────────────────────────────────────────────
        $this->rename('client', 'payout_invoices', 'statements', 'Statements');
        $clientStatementsId = $this->ensure('client', 'statements', 'Statements', 'statements', $now);

        $billingId = DB::table('permissions')->where('scope', 'client')->where('name', 'billing')->value('id');
        if ($billingId) {
            DB::table('client_employee_permission_user')->where('permission_id', $billingId)->get()
                ->each(fn ($grant) => DB::table('client_employee_permission_user')->insertOrIgnore([
                    'employee_user_id'  => $grant->employee_user_id,
                    'permission_id'     => $clientStatementsId,
                    'client_profile_id' => $grant->client_profile_id,
                    'granted_by'        => $grant->granted_by,
                    'expires_at'        => $grant->expires_at,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]));

            DB::table('permissions')->where('id', $billingId)->delete();
        }
    }

    public function down(): void
    {
        $this->rename('admin', 'finances.handover', 'finances.checkout_approvals', 'Checkout Approvals');
        $this->rename('client', 'statements', 'payout_invoices', 'Payout Invoices');
        DB::table('permissions')->where('scope', 'admin')->where('name', 'finances.client_statements')->delete();
        // Removed permissions are recreated by the DatabaseSeeder of the previous release.
    }

    private function rename(string $scope, string $from, string $to, string $displayName): void
    {
        $exists = DB::table('permissions')->where('scope', $scope)->where('name', $to)->exists();
        if (! $exists) {
            DB::table('permissions')->where('scope', $scope)->where('name', $from)
                ->update(['name' => $to, 'display_name' => $displayName, 'group' => $scope === 'client' ? $to : 'finances']);
        }
    }

    private function ensure(string $scope, string $name, string $displayName, string $group, $now): int
    {
        $id = DB::table('permissions')->where('scope', $scope)->where('name', $name)->value('id');

        return $id ?: DB::table('permissions')->insertGetId([
            'name'         => $name,
            'display_name' => $displayName,
            'description'  => null,
            'scope'        => $scope,
            'group'        => $group,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    private function copyAdminGrants(array $fromNames, int $toPermissionId, $now): void
    {
        $fromIds = DB::table('permissions')->where('scope', 'admin')->whereIn('name', $fromNames)->pluck('id');

        DB::table('admin_permission_user')->whereIn('permission_id', $fromIds)->get()
            ->each(fn ($grant) => DB::table('admin_permission_user')->insertOrIgnore([
                'admin_user_id' => $grant->admin_user_id,
                'permission_id' => $toPermissionId,
                'granted_by'    => $grant->granted_by,
                'expires_at'    => $grant->expires_at,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]));
    }
};
