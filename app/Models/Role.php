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
        return $this->hasMany(User::class);
    }

    public function rolePermissions()
    {
        return $this->belongsToMany(RolePermission::class, 'role_has_permission', 'role_id', 'permission_id');
    }
}
