<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('/team/store', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/switch/{team}', [TeamController::class, 'switch'])->name('team.switch');
});

Route::middleware(['auth', 'verified', 'team'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');

    Route::get('team/settings', [TeamController::class, 'index'])->name('team.settings');
    Route::put('team/settings', [TeamController::class, 'update']);
    Route::delete('team/delete', [TeamController::class, 'destroy'])->name('team.delete');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');

    // Bill routes
    Route::controller(BillController::class)->prefix('bills')->group(function () {
        Route::get('/', 'index')->name('bills.index');
        Route::get('/create', 'create')->name('bills.create');
        Route::post('/', 'store')->name('bills.store');
        Route::get('/{bill}', 'show')->name('bills.show');
        Route::get('/{bill}/edit', 'edit')->name('bills.edit');
        Route::put('/{bill}', 'update')->name('bills.update');
        Route::delete('/{bill}', 'destroy')->name('bills.destroy');
        Route::patch('/{bill}/pay', 'markAsPaid')->name('bills.pay');
    });

    // Category routes
    Route::resource('categories', CategoryController::class)->except('create', 'show', 'edit');

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Bill payment routes
    Route::get('/bills/{bill}/payment', [BillController::class, 'showPaymentForm'])->name('bills.payment');
    Route::get('/bills/{bill}/payment-details', [BillController::class, 'getPaymentDetails'])->name('bills.payment-details');

    // Notes routes
    Route::controller(NoteController::class)
        ->prefix('notes')
        ->name('notes.')
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/', 'store')->name('store');
            Route::match(['put', 'patch'], '/{note}', 'update')->name('update');
            Route::delete('/{note}', 'destroy')->name('destroy');
        });
});

Route::any('/test', function () {
    $item = \App\Models\Note::with('notable')->first();
    $bill = \App\Models\Bill::with('notes')->first();

    // $bill->notes()->sync($item->id);

    dd($bill->toArray(), $item->toArray(), $item->related->first()->notable, $item->bills);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
