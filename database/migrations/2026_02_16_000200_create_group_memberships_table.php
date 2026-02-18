<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('group_memberships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('group_id');
            $table->uuid('user_id');
            $table->string('role')->default('MEMBER');
            $table->string('status')->default('PENDING');
            $table->uuid('approved_by_user_id')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('groups')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('approved_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['group_id', 'user_id']);
            $table->index(['group_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_memberships');
    }
};