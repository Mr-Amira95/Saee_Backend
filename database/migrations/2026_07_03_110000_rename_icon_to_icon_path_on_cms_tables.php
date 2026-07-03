<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected array $tables = [
        'services',
        'industries',
        'showcase_capabilities',
        'showcase_how_it_works',
        'why_saee_reasons',
    ];

    public function up(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('icon_path', 100)->nullable()->after('id');
            });
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('icon');
            });
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $table) {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->string('icon', 100)->nullable()->after('id');
            });
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropColumn('icon_path');
            });
        }
    }
};
