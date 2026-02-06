<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BillController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\NoteController;
use App\Http\Controllers\Api\V1\TeamController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

// Public authentication routes
Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
});

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
        Route::get('/user', [AuthController::class, 'user'])->name('api.v1.auth.user');
        Route::put('/user', [AuthController::class, 'updateProfile'])->name('api.v1.auth.update');
    });

    // User routes
    Route::apiResource('users', UserController::class)->only(['index', 'show']);

    // Team routes - require active team
    Route::middleware('team')->group(function () {
        // Teams
        Route::apiResource('teams', TeamController::class);
        Route::post('teams/{team}/members', [TeamController::class, 'addMember'])->name('api.v1.teams.members.add');
        Route::delete('teams/{team}/members/{user}', [TeamController::class, 'removeMember'])->name('api.v1.teams.members.remove');
        Route::post('teams/{team}/switch', [TeamController::class, 'switch'])->name('api.v1.teams.switch');

        // Bills
        Route::apiResource('bills', BillController::class);
        Route::patch('bills/{bill}/pay', [BillController::class, 'markAsPaid'])->name('api.v1.bills.pay');
        Route::get('bills/{bill}/upcoming', [BillController::class, 'upcoming'])->name('api.v1.bills.upcoming');

        // Categories
        Route::apiResource('categories', CategoryController::class);

        // Transactions
        Route::apiResource('transactions', TransactionController::class);
        Route::get('transactions/{transaction}/receipt', [TransactionController::class, 'receipt'])->name('api.v1.transactions.receipt');

        // Notes
        Route::apiResource('notes', NoteController::class);
    });
});
