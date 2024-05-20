<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function rolePermissions()
    {
        return $this->belongsTo(RoleHasPermission::class);
    }
}
