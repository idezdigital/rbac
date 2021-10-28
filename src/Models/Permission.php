<?php

namespace iDezDigital\Rbac\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'key';
    protected $fillable = [
        'role_id',
        'key',
    ];

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'roles_permissions', 'key', 'role_id');
    }

    public function hasUsers()
    {
        return (bool) $this->roles()->has('users')->count();
    }
}
