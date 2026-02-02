<?php

use App\Http\Controllers\Admin\SheepController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\NoteController;

// Login route for Sanctum
Route::post('login', [LoginController::class, 'login']);


Route::middleware(['auth:sanctum'])->prefix('admin')->group(function () {
    Route::prefix('sheep')->group(function() {
        Route::get('/', [SheepController::class, 'index']);
        Route::get('/fast', [SheepController::class, 'getDataFast']);
        Route::post('/', [SheepController::class, 'store']);
        Route::get('/{id}', [SheepController::class, 'show']);
        Route::get('/popular/mothers', [SheepController::class, 'popularMothers']);
        Route::put('/{id}', [SheepController::class, 'update']);
        Route::put('/toggle-visibility/{id}', [SheepController::class, 'toggleVisibility']);
        Route::delete('/{id}', [SheepController::class, 'destroy']);
    });
    Route::prefix('notes')->group(function() {
        Route::get('/{id}', [NoteController::class, 'index']);
        Route::post('/', [NoteController::class, 'store']);
        Route::get('/note/{id}', [NoteController::class, 'show']);
        Route::put('/{id}', [NoteController::class, 'update']);
        Route::delete('/{id}', [NoteController::class, 'destroy']);
    });
    Route::prefix('tasks')->group(function() {
        Route::get('/{id}', [\App\Http\Controllers\Admin\TaskController::class, 'index']);
        // Route::post('/', [\App\Http\Controllers\Admin\TaskController::class, 'store']);
        // Route::get('/task/{id}', [\App\Http\Controllers\Admin\TaskController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\TaskController::class, 'update']);
        Route::put('/result/{id}', [\App\Http\Controllers\Admin\TaskController::class, 'updateActionType']);
        // Route::delete('/{id}', [\App\Http\Controllers\Admin\TaskController::class, 'destroy']);
    });
    Route::prefix('statuses')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\StatusController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\StatusController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\StatusController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\StatusController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\StatusController::class, 'destroy']);
    });
    Route::prefix('breeds')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\BreedController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\BreedController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\BreedController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\BreedController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\BreedController::class, 'destroy']);
    });
    Route::prefix('expenses')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ExpenseController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\ExpenseController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\ExpenseController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\ExpenseController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ExpenseController::class, 'destroy']);
    });
    Route::prefix('expense-frequencies')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ExpenseFrequencyController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\ExpenseFrequencyController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\ExpenseFrequencyController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\ExpenseFrequencyController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ExpenseFrequencyController::class, 'destroy']);
    });
    Route::prefix('expense-types')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\ExpenseTypeController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\ExpenseTypeController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\ExpenseTypeController::class, 'show']);
        Route::put('/{id}', [\App\Http\Controllers\Admin\ExpenseTypeController::class, 'update']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\ExpenseTypeController::class, 'destroy']);
    });
    Route::prefix('sales')->group(function() {
        Route::get('/', [\App\Http\Controllers\Admin\SaleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\SaleController::class, 'store']);
        Route::get('/{id}', [\App\Http\Controllers\Admin\SaleController::class, 'show']);
        Route::delete('/{id}', [\App\Http\Controllers\Admin\SaleController::class, 'destroy']);
    });


});


