<?php

use App\Http\Controllers\CommunityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\MemberProfileController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\PlatformController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TautWebhookController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');
Route::post('/integrations/taut/webhook', TautWebhookController::class)
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->middleware('throttle:60,1')
    ->name('integrations.taut.webhook');
require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'home'])->name('dashboard.home');
    Route::get('/communities', [CommunityController::class, 'index'])->name('communities.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');

    Route::get('/billing/plans', [SubscriptionController::class, 'plans'])->name('billing.plans');
    Route::get('/billing/subscriptions', [SubscriptionController::class, 'index'])->name('billing.subscriptions');
    Route::post('/billing/subscriptions', [SubscriptionController::class, 'store'])->name('billing.subscriptions.store');

    Route::get('/dashboard/platform', [DashboardController::class, 'platform'])->name('dashboard.platform');
    Route::get('/platform/users', [PlatformController::class, 'users'])->name('platform.users');
    Route::patch('/platform/users/{userId}', [PlatformController::class, 'updateUser'])->name('platform.users.update');
    Route::delete('/platform/users/{userId}', [PlatformController::class, 'deleteUser'])->name('platform.users.delete');
    Route::delete('/platform/users', [PlatformController::class, 'bulkDeleteUsers'])->name('platform.users.bulk-delete');
    Route::get('/platform/groups', [PlatformController::class, 'groups'])->name('platform.groups');
    Route::post('/platform/groups/{groupId}/suspend', [PlatformController::class, 'suspend'])->name('platform.groups.suspend');
    Route::post('/platform/groups/{groupId}/activate', [PlatformController::class, 'activate'])->name('platform.groups.activate');
});

Route::prefix('/{group_slug}')
    ->where(['group_slug' => '[a-z0-9-]{3,50}'])
    ->middleware('resolve.group')
    ->group(function () {
        Route::get('/', [GroupController::class, 'show'])->name('groups.show');
        Route::get('/invite/{invite_token}', [GroupController::class, 'invite'])
            ->where(['invite_token' => '[A-Za-z0-9]{20,80}'])
            ->name('groups.invite');
        Route::get('/members', [MemberProfileController::class, 'directory'])->name('groups.members.directory');
        Route::get('/member/{username}', [MemberProfileController::class, 'show'])
            ->where(['username' => '[a-z0-9-]{3,40}'])
            ->name('groups.member.show');

        Route::middleware(['auth'])->group(function () {
            Route::get('/settings', [GroupController::class, 'settings'])->name('groups.settings');
            Route::patch('/settings', [GroupController::class, 'updateSettings'])->name('groups.settings.update');

            Route::post('/join', [MembershipController::class, 'join'])->name('groups.join');
            Route::get('/membership-requests', [MembershipController::class, 'requests'])->name('groups.membership.requests');
            Route::post('/membership-requests/{requestId}/approve', [MembershipController::class, 'approve'])->name('groups.membership.approve');
            Route::post('/membership-requests/{requestId}/reject', [MembershipController::class, 'reject'])->name('groups.membership.reject');

            Route::get('/member/{username}/edit', [MemberProfileController::class, 'edit'])
                ->where(['username' => '[a-z0-9-]{3,40}'])
                ->name('groups.member.edit');
            Route::patch('/member/{username}', [MemberProfileController::class, 'update'])
                ->where(['username' => '[a-z0-9-]{3,40}'])
                ->name('groups.member.update');

            Route::get('/dashboard', [DashboardController::class, 'group'])->name('dashboard.group');
        });
    });
