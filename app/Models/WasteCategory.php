<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'price_per_unit', 
        'unit',
        'type',
    ];

    public function wastes()
    {
        return $this->hasMany(Waste::class, 'category_id');
    }
}