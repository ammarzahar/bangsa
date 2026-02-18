<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->string('visibility')->default('PUBLIC');
            $table->string('status')->default('INACTIVE');
            $table->uuid('owner_id');
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->restrictOnDelete();
            $table->index('owner_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};