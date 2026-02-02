<?php

use App\Http\Controllers\BillController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');
Route::get('/contact', [LegalController::class, 'contact'])->name('legal.contact');

Route::get('join/team/{team}', [TeamController::class, 'join'])->name('team.join')->middleware(['guest', 'signed']);

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/team/create', [TeamController::class, 'create'])->name('team.create');
    Route::post('/team/store', [TeamController::class, 'store'])->name('team.store');
    Route::get('/team/switch/{team}', [TeamController::class, 'switch'])->name('team.switch');
});

Route::middleware(['auth', 'verified', 'team'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/calendar', [DashboardController::class, 'calendar'])->name('calendar');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index')->middleware('feature:reports');

    // Team routes
    Route::controller(TeamController::class)->name('team.')->prefix('team')->group(function () {
        Route::get('settings', 'index')->name('settings');
        Route::put('settings', 'update');
        Route::delete('delete', 'destroy')->name('delete');

        Route::post('member', 'addMember')->name('member.add');
        Route::delete('member/{user}', 'removeMember')->name('member.remove');
    });

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::delete('/notifications/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
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
        Route::get('/{bill}/invoice', 'showInvoiceForm')->name('bills.invoice');
    });

    // Category routes
    Route::resource('categories', CategoryController::class)->except('create', 'show', 'edit');

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::get('/transactions/{transaction}/receipt', [TransactionController::class, 'showReceipt'])->name('transactions.receipt');

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

Route::get('/visit/bill/{bill}', [BillController::class, 'visit'])
    ->middleware(['auth', 'verified', 'signed'])
    ->name('visit.bill');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/debug.php';

Route::fallback(function () {
    return Inertia::render('errors/404', [
        'status' => 404,
        'message' => 'The requested page could not be found.',
    ])
        ->toResponse(request())
        ->setStatusCode(404);
});
