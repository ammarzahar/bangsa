<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('target_group_id')->nullable();
            $table->uuid('actor_user_id')->nullable();
            $table->string('event_type');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('target_group_id')->references('id')->on('groups')->nullOnDelete();
            $table->foreign('actor_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['target_group_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};