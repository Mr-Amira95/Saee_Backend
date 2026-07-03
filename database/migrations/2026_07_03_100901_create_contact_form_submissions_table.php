<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_form_submissions', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['business', 'contact']);
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('monthly_volume')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message');
            $table->enum('status', ['new', 'contacted', 'closed'])->default('new');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_form_submissions');
    }
};
