<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $table = 'role_permissions';

    protected $primaryKey = 'id';

    public $incrementing = true;
    
    protected $fillable = [
        'permission_name',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permission', 'permission_id', 'role_id');
    }
}