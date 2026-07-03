<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showcase_pages', function (Blueprint $table) {
            $table->id();
            $table->json('page_badge')->nullable();
            $table->json('page_title')->nullable();
            $table->json('page_subtitle')->nullable();
            $table->json('section_badge')->nullable();
            $table->json('section_title')->nullable();
            $table->json('section_subtitle')->nullable();
            $table->timestamps();
        });

        Schema::create('showcase_capabilities', function (Blueprint $table) {
            $table->id();
            $table->string('icon', 100)->nullable();
            $table->json('title');
            $table->json('subtitle')->nullable();
            $table->integer('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::create('showcase_how_it_works', function (Blueprint $table) {
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
        Schema::dropIfExists('showcase_how_it_works');
        Schema::dropIfExists('showcase_capabilities');
        Schema::dropIfExists('showcase_pages');
    }
};
