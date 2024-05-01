<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCollection extends Model
{
    use HasFactory;

    protected $table = 'waste_collections';

    protected $fillable = [
        'waste_weight',
        'collection_date',
        'description',
        'created_by',
    ];

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
