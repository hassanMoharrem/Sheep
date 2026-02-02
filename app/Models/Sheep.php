<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sheep extends Model
{
    protected $table = 'sheep';
    protected $fillable = ['code', 'breed_id', 'gender','health_status_id','current_status_id','next_status_id', 'birth_date', 'weight', 'mother_id' ,'is_active', 'visible'];

    public function breed()
    {
        return $this->belongsTo(Breed::class);
    }
    public function currentStatus()
    {
        return $this->belongsTo(Status::class, 'current_status_id');
    }
    public function nextStatus()
    {
        return $this->belongsTo(Status::class, 'next_status_id');
    }
    public function mother()
    {
        return $this->belongsTo(Sheep::class, 'mother_id');
    }
    public function offspring()
    {
        return $this->hasMany(Sheep::class, 'mother_id');
    }
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}
