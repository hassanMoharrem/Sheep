<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = ['sheep_id', 'body'];

    public function sheep()
    {
        return $this->belongsTo(Sheep::class);
    }
}
