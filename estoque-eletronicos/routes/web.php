<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::middleware('can:admin')->group(function () {
        Route::resource('produtos', ProductController::class)
            ->except(['index', 'show'])->parameters(['produtos' => 'product'])->names('products');
        Route::resource('categorias', CategoryController::class)
            ->except('show')->parameters(['categorias' => 'category'])->names('categories');
        Route::resource('fornecedores', SupplierController::class)
            ->except('show')->parameters(['fornecedores' => 'supplier'])->names('suppliers');
        Route::resource('usuarios', UserController::class)
            ->except('show')->parameters(['usuarios' => 'user'])->names('users');
    });

    Route::get('/produtos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/produtos/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/movimentacoes', [StockMovementController::class, 'index'])->name('movements.index');
    Route::get('/movimentacoes/nova', [StockMovementController::class, 'create'])->name('movements.create');
    Route::post('/movimentacoes', [StockMovementController::class, 'store'])->name('movements.store');

    Route::get('/relatorios/produtos', [ReportController::class, 'products'])->name('reports.products');
    Route::get('/relatorios/movimentacoes', [ReportController::class, 'movements'])->name('reports.movements');
});
