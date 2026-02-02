<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseMonitor extends Model
{
    protected $fillable = [
        'sheep_id',
        'current_status_id',
        'next_status_id',
        'date_monitored',
    ];



    public function sheep()
    {
        return $this->belongsTo(Sheep::class);
    }
    public function currentStatus()
    {
        return $this->belongsTo(Status::class, 'current_status_id');
    }
    public function nextStatus()
    {
        return $this->belongsTo(Status::class, 'next_status_id');
    }
}
