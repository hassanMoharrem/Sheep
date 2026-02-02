<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleSheep extends Model
{
    use HasFactory;

    protected $table = 'sale_sheep';

    protected $fillable = [
        'sale_id',
        'sheep_id',
        'price',
        'real_price',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function sheep()
    {
        return $this->belongsTo(Sheep::class);
    }
}
