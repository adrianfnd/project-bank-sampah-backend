<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleHasPermission extends Model
{
    use HasFactory;

    protected $table = 'role_has_permissions';

    protected $primaryKey = 'id';

    protected $fillable = [
        'role_id', 
        'permission_id'
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function rolePermission()
    {
        return $this->belongsTo(RolePermission::class, 'permission_id');
    }
}
