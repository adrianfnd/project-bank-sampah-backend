<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteCollection extends Model
{
    use HasFactory;

    protected $table = 'waste_collections';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name', 
        'user_id',
        'weight_total',
        'point_total',
        'address',
        'collection_date',
        'confirmation_status',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function waste()
    {
        return $this->belongsTo(Waste::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
