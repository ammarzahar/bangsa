<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_user_id');
            $table->uuid('group_id')->nullable()->unique();
            $table->uuid('plan_id');
            $table->string('provider');
            $table->string('status')->default('INCOMPLETE');
            $table->string('provider_customer_id')->nullable();
            $table->string('provider_subscription_id')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('grace_period_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->foreign('owner_user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans')->restrictOnDelete();

            $table->index(['owner_user_id', 'status']);
            $table->index(['group_id', 'status']);
            $table->index('current_period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};