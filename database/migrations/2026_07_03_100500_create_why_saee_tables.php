<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('why_saee_sections', function (Blueprint $table) {
            $table->id();
            $table->json('badge')->nullable();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->timestamps();
        });

        Schema::create('why_saee_reasons', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 100)->nullable();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('why_saee_reasons');
        Schema::dropIfExists('why_saee_sections');
    }
};
