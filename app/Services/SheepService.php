<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Sheep;
use App\Models\Task;
use Illuminate\Support\Facades\Log;

class SheepService
{
    /**
     * Create a new class instance.
     */
    public function __construct(public Notification $notification, public Task $task)
    {
        //
    }


    public function listenTask()
    {
        $data = $this->task->whereDate('scheduled_date', now()->toDateString())->get();

        
        $this->notification->create([
            'title' => 'new tasks for today',
            'body' => '' . $data,   
        ]);

    }
}
