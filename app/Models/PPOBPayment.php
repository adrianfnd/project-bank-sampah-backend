<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PPOBPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'transaction_id',
        'biller_name',
        'biller_account',
        'amount',
        'payment_date',
        'created_by',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function createdByUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
