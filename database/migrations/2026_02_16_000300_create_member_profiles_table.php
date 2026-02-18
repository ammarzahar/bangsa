<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('member_profiles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('group_id');
            $table->uuid('user_id');
            $table->string('username');
            $table->string('photo_url')->nullable();
            $table->string('full_name');
            $table->string('short_bio', 500)->nullable();
            $table->string('current_role')->nullable();
            $table->string('business')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('website_url')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['group_id', 'user_id']);
            $table->unique(['group_id', 'username']);
            $table->index(['group_id', 'is_featured']);
            $table->index(['group_id', 'city']);
            $table->index(['group_id', 'country']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_profiles');
    }
};