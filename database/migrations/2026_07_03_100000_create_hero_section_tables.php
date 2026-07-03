<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_sections', function (Blueprint $table) {
            $table->id();
            $table->json('badge')->nullable();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->timestamps();
        });

        Schema::create('hero_stats', function (Blueprint $table) {
            $table->id();
            $table->json('key');
            $table->json('value');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_stats');
        Schema::dropIfExists('hero_sections');
    }
};
