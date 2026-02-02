<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'total_price',
        'real_total_price',
        'sold_at',
    ];

    public function sheep()
    {
        return $this->belongsToMany(Sheep::class, 'sale_sheep')
            ->withPivot('price', 'real_price')
            ->withTimestamps();
    }
}
