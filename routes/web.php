<?php

use App\Models\Task;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {

    return Task::whereDate('scheduled_date', now()->toDateString())->get();
    // return view('welcome');
});


