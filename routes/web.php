<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DebtorController;
use App\Http\Controllers\DebtController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CollectionActionController;
use App\Http\Controllers\CollectionAssignmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ReportController;

Auth::routes(['register' => false]);

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'company'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Deudores
    Route::resource('debtors', DebtorController::class);
    Route::get('debtors/{debtor}/debts', [DebtorController::class, 'debts'])->name('debtors.debts');

    // Deudas
    Route::resource('debts', DebtController::class);
    Route::post('debts/{debt}/recalculate', [DebtController::class, 'recalculate'])->name('debts.recalculate');
    Route::post('debts/{debt}/cancel', [DebtController::class, 'cancel'])->name('debts.cancel');

    // Cuotas (nested bajo deudas)
    Route::get('debts/{debt}/installments', [InstallmentController::class, 'index'])->name('installments.index');
    Route::put('installments/{installment}', [InstallmentController::class, 'update'])->name('installments.update');

    // Pagos
    Route::resource('payments', PaymentController::class);
    Route::post('payments/{payment}/confirm', [PaymentController::class, 'confirm'])->name('payments.confirm');
    Route::post('payments/{payment}/reject', [PaymentController::class, 'reject'])->name('payments.reject');
    Route::post('payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');

    // Gestiones de cobranza
    Route::resource('collection-actions', CollectionActionController::class)->except(['show']);
    Route::get('collection-actions/debt/{debt}', [CollectionActionController::class, 'byDebt'])->name('collection-actions.by-debt');

    // Asignaciones
    Route::resource('assignments', CollectionAssignmentController::class)->except(['show']);
    Route::post('assignments/{assignment}/deactivate', [CollectionAssignmentController::class, 'deactivate'])->name('assignments.deactivate');

    // Notificaciones
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/send/{debtor}', [NotificationController::class, 'showSendForm'])->name('send-form');
        Route::post('/send', [NotificationController::class, 'send'])->name('send');
        Route::post('/send-bulk', [NotificationController::class, 'sendBulk'])->name('send-bulk');
    });

    // Plantillas de mensajes
    Route::resource('message-templates', MessageTemplateController::class)->except(['show']);

    // Reportes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/debts', [ReportController::class, 'debts'])->name('debts');
        Route::get('/payments', [ReportController::class, 'payments'])->name('payments');
        Route::get('/collections', [ReportController::class, 'collections'])->name('collections');
        Route::get('/overdue', [ReportController::class, 'overdue'])->name('overdue');
        Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
    });

    // Configuración
    Route::prefix('settings')->name('settings.')->middleware('permission:settings.company')->group(function () {
        Route::get('/company', [CompanyController::class, 'edit'])->name('company');
        Route::put('/company', [CompanyController::class, 'update'])->name('company.update');
        Route::resource('/payment-methods', PaymentMethodController::class)->except(['show']);
    });

    // Usuarios
    Route::resource('users', UserController::class)->middleware('permission:settings.users');
});

require __DIR__ . '/auth.php';
