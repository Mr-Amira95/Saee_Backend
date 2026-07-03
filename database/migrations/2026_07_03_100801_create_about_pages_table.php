<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_pages', function (Blueprint $table) {
            $table->id();
            $table->json('page_badge')->nullable();
            $table->json('page_title')->nullable();
            $table->json('page_subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->json('mission')->nullable();
            $table->json('vision')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_pages');
    }
};
