<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'point_cost',
        'stock',
        'image',
    ];

    public function productExchanges()
    {
        return $this->belongsTo(ProductExchange::class);
    }
}
