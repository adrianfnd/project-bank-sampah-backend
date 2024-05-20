<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_type',
        'total_balance_involved',
        'user_id',
        'description',
        'created_by',
        'xendit_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function xenditLog()
    {
        return $this->belongsTo(XenditLog::class, 'xendit_id');
    }

    public function ppobPayment()
    {
        return $this->hasOne(PPOBPayment::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
