<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class XenditLog extends Model
{
    use HasFactory;

    protected $table = 'xendit_logs';
    
    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'external_id',
        'user_id',
        'is_high',
        'payment_method',
        'status',
        'merchant_name',
        'amount',
        'paid_amount',
        'bank_code',
        'paid_at',
        'payer_email',
        'description',
        'adjusted_received_amount',
        'fees_paid_amount',
        'updated',
        'created',
        'currency',
        'payment_channel',
        'payment_destination',
    ];
}
