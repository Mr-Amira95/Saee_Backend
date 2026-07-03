<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question', 'answer', 'category']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->json('question')->after('id');
            $table->json('answer')->after('question');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn(['question', 'answer']);
        });

        Schema::table('faqs', function (Blueprint $table) {
            $table->string('question')->after('id');
            $table->text('answer')->after('question');
            $table->string('category')->default('general')->after('answer');
        });
    }
};
