<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('display_name', 150);
            $table->string('description', 255)->nullable();
            $table->enum('scope', ['admin', 'client']);
            $table->string('group', 50)->nullable();
            $table->timestamps();

            $table->unique(['name', 'scope']);
            $table->index('scope');
            $table->index('group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
