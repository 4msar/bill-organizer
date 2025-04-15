<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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
    Route::resource('categories', CategoryController::class);

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Bill payment routes
    Route::get('/bills/{bill}/payment', [BillController::class, 'showPaymentForm'])->name('bills.payment');
    Route::get('/bills/{bill}/payment-details', [BillController::class, 'getPaymentDetails'])->name('bills.payment-details');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
