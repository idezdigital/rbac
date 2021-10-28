<?php

namespace iDezDigital\Rbac\Models;

use App\Models\User;
use iDezDigital\Rbac\Exceptions\UnknownPermissionException;
use App\Traits\HasUUID;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Pktharindu\NovaPermissions\Permission;
use Pktharindu\NovaPermissions\Policies\Policy;

class Role extends Model
{
    use HasUUID;

    protected $fillable = [
        'slug',
        'name',
        'permissions',
    ];

    protected $appends = [
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'roles_users', 'role_id', 'user_id');
    }

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * Replace all existing permissions with a new set of permissions.
     *
     * @param array $permissions
     */
    public function setPermissions(array $permissions)
    {
        if (! $this->id) {
            $this->save();
        }

        $this->revokeAll();

        collect($permissions)->each(function ($permission) {
            $this->grant($permission);
        });

        $this->setRelations([]);
    }

    /**
     * Check if a user has a given permission.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return $this->getPermissions->contains('permission_slug', $permission);
    }

    public function listPermissions(){
        return $this->permissions->pluck('key');
    }

    /**
     * Give Permission to a Role.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function grant($permission)
    {
        if ($this->hasPermission($permission)) {
            return true;
        }

        if (!array_key_exists($permission, Gate::abilities())) {
            throw new UnknownPermissionException( "Unknown permission {$permission}");
        }

        return Permission::create([
            'role_id'         => $this->id,
            'permission_slug' => $permission,
        ]);
    }

    /**
     * Revokes a Permission from a Role.
     *
     * @param string $permission
     *
     * @return bool
     */
    public function revoke($permission)
    {
        if (\is_string($permission)) {
            Permission::findOrFail($permission)->delete();

            $this->setRelations([]);

            return true;
        }

        return false;
    }

    /**
     * Remove all permissions from this Role.
     */
    public function revokeAll()
    {
        $this->permissions()->delete();

        $this->setRelations([]);

        return true;
    }

    /**
     * Get a list of permissions.
     *
     * @return array
     */
    public function getPermissionsAttribute()
    {
        return Permission::where('role_id', $this->id)->get('key')->pluck('key')->toArray();
    }

    /**
     * Replace all existing permissions with a new set of permissions.
     *
     * @param array $permissions
     */
    public function setPermissionsAttribute(array $permissions)
    {
        if (! $this->id) {
            $this->save();
        }

        $this->revokeAll();

        collect($permissions)->map(function ($permission) {
            if (! \in_array($permission, Policy::all(), true)) {
                return;
            }

            $this->grant($permission);
        });
    }
}
