<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('download_app_pages', function (Blueprint $table) {
            $table->id();
            $table->json('badge')->nullable();
            $table->json('title')->nullable();
            $table->json('subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->string('android_url')->nullable();
            $table->string('ios_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('download_app_pages');
    }
};
