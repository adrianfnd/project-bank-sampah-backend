<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WasteBank extends Model
{
    use HasFactory;

    protected $table = 'waste_banks';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'name',
        'address',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
