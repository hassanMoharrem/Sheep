<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Resource routes for tasks and notes (Admin)
Route::resource('tasks', App\Http\Controllers\Admin\TaskController::class);
Route::resource('notes', App\Http\Controllers\Admin\NoteController::class);
