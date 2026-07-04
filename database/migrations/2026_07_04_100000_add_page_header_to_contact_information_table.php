<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contact_information', function (Blueprint $table) {
            $table->json('page_badge')->nullable()->after('id');
            $table->json('page_title')->nullable()->after('page_badge');
            $table->json('page_subtitle')->nullable()->after('page_title');
        });
    }

    public function down(): void
    {
        Schema::table('contact_information', function (Blueprint $table) {
            $table->dropColumn(['page_badge', 'page_title', 'page_subtitle']);
        });
    }
};
