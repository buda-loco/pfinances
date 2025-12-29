<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\IncomeController; // [NEW] Link Controller
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
})->middleware('auth');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/portfolio', [PortfolioController::class, 'index'])->name('portfolio.index');

    // Search routes
    Route::get('/search', [SearchController::class, 'search'])->name('search');
    Route::get('/search/details', [SearchController::class, 'details'])->name('search.details');
    Route::post('/search/update', [SearchController::class, 'update'])->name('search.update');

    // Transaction routes
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/export', [TransactionController::class, 'export'])->name('transactions.export');
    Route::get('/transactions/{transaction}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::patch('/transactions/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');
    Route::post('/transactions/bulk-categorize', [TransactionController::class, 'bulkCategorize'])->name('transactions.bulk-categorize');
    Route::post('/transactions/bulk-update', [TransactionController::class, 'bulkUpdate'])->name('transactions.bulk-update');
    Route::get('/transactions/{transaction}/suggestions', [TransactionController::class, 'suggestions'])->name('transactions.suggestions');

    // Account routes
    Route::resource('accounts', AccountController::class);

    // Category routes
    Route::resource('categories', CategoryController::class);

    // Project routes
    Route::resource('projects', ProjectController::class);

    // Budget routes
    Route::resource('budgets', BudgetController::class);

    // Expense routes
    Route::get('/expenses', [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/expenses/export', [App\Http\Controllers\ExpenseController::class, 'export'])->name('expenses.export');

    // Income routes [NEW]
    Route::get('/income', [IncomeController::class, 'index'])->name('income.index');
    Route::get('/income/export', [IncomeController::class, 'export'])->name('income.export');

    // Import routes
    Route::get('/import', [ImportController::class, 'index'])->name('import.index');
    Route::post('/import/upload', [ImportController::class, 'upload'])->name('import.upload');
});

require __DIR__ . '/auth.php';
