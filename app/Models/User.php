<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'name',
        'address',
        'email',
        'phone_number',
        'image',
        'otp',
        'email_verified_at',
        'password',
        'current_point',
        'role_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function wasteBanks()
    {
        return $this->hasMany(WasteBank::class);
    }

    public function wasteCollections()
    {
        return $this->hasMany(WasteCollection::class, 'created_by');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    public function xenditLogs()
    {
        return $this->hasMany(XenditLog::class);
    }

    public function productExchanges()
    {
        return $this->hasMany(ProductExchange::class);
    }

    public function ppobPayments()
    {
        return $this->hasMany(PPOBPayment::class);
    }
}
