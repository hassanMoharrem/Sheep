<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = ['sheep_id', 'action_type_id', 'scheduled_date', 'status', 'result', 'completed_at'];

    public function sheep()
    {
        return $this->belongsTo(Sheep::class);
    }
    public function actionType()
    {
        return $this->belongsTo(Status::class, 'action_type_id');
    }
}
