<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('groups', function (Blueprint $table) {
            $table->string('taut_checkout_url', 2048)->nullable()->after('visibility');
        });

        Schema::table('membership_requests', function (Blueprint $table) {
            $table->string('payment_status')->nullable()->after('status');
            $table->string('external_order_id')->nullable()->unique()->after('payment_status');
            $table->timestamp('paid_at')->nullable()->after('external_order_id');
            $table->index(['group_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('membership_requests', function (Blueprint $table) {
            $table->dropIndex(['group_id', 'payment_status']);
            $table->dropUnique(['external_order_id']);
            $table->dropColumn(['payment_status', 'external_order_id', 'paid_at']);
        });

        Schema::table('groups', function (Blueprint $table) {
            $table->dropColumn('taut_checkout_url');
        });
    }
};
