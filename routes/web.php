<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
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
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
