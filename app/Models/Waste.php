<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Waste extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'weight',
        'point',
        'waste_collection_id',
    ];

    public function wasteCollections()
    {
        return $this->belongsTo(WasteCollection::class, 'waste_collection_id');
    }
}
