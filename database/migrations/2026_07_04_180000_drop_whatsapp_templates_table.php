<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('whatsapp_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('event')->unique();
            $table->text('template_body');
            $table->timestamps();
        });
    }
};
