<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'account_type')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->string('account_type')->default(User::TYPE_USER)->after('full_name')->index();
            });
        }

        DB::table('users')
            ->where('is_platform_owner', true)
            ->update(['account_type' => User::TYPE_ADMIN]);

        DB::table('users')
            ->where('email', 'ammarzahar@gmail.com')
            ->update([
                'account_type' => User::TYPE_ADMIN,
                'is_platform_owner' => true,
            ]);

        if (!Schema::hasColumn('groups', 'invite_token')) {
            Schema::table('groups', function (Blueprint $table): void {
                $table->string('invite_token', 64)->nullable()->after('owner_id')->unique();
            });
        }

        foreach (DB::table('groups')
            ->whereNull('invite_token')
            ->orderBy('id')
            ->get() as $group) {
            DB::table('groups')
                ->where('id', $group->id)
                ->update(['invite_token' => Str::random(40)]);
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('groups', 'invite_token')) {
            Schema::table('groups', function (Blueprint $table): void {
                $table->dropUnique(['invite_token']);
                $table->dropColumn('invite_token');
            });
        }

        if (Schema::hasColumn('users', 'account_type')) {
            Schema::table('users', function (Blueprint $table): void {
                $table->dropIndex(['account_type']);
                $table->dropColumn('account_type');
            });
        }
    }
};
