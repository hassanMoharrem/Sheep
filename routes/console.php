<?php

use App\Models\Sheep;
use App\Services\SheepService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
   $service = app(SheepService::class);
   $data = $service->listenTask();

   Log::debug($data);
})->dailyAt('00:00');
