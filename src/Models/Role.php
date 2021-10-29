<?php

namespace iDez\Rbac\Models;

use App\Models\User;
use iDez\Rbac\Exceptions\UnknownPermissionException;
use App\Traits\HasUUID;
use Illuminate\Support\Facades\Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
class Role extends Model
{
    use HasUUID;

    protected $fillable = [
        'key',
        'name',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updating(function ($role) {
            $columns = collect($role)
                ->filter(fn($value, $key) => Str::startsWith($key, 'permissions_'));

            $columns->keys()->each(function($column) use ($role) {
                unset($role->{$column});
            });

            $role->permissions = $columns->flatten();
            return $role;
        });

        parent::booted();
    }

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
     * @param  array  $permissions
     */
    public function setPermissionsAttribute(Collection $permissions)
    {
        if (!$this->id) {
            $this->save();
        }

        $this->revokeAll();

        $permissions->each(fn ($permission) => $this->grant($permission));

        $this->setRelations([]);
    }

    /**
     * Check if a user has a given permission.
     *
     * @param  string  $permission
     *
     * @return bool
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->listPermissions());
    }

    public function listPermissions()
    {
        return $this->permissions->pluck('key')->toArray();
    }

    /**
     * Give Permission to a Role.
     *
     * @param  string  $permission
     *
     * @return bool
     */
    public function grant($permission)
    {
        if ($this->hasPermission($permission)) {
            return true;
        }

        if (!array_key_exists($permission, Gate::abilities())) {
            throw new UnknownPermissionException("Unknown permission {$permission}");
        }

        return $this->permissions()->create([
            'role_id' => $this->id,
            'key' => $permission,
        ]);
    }

    /**
     * Revokes a Permission from a Role.
     *
     * @param  string  $permission
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

    /** Acessors & Mutators mostly for Nova */
    public function getPermissionsAttribute()
    {
        return $this->permissions()->pluck('key');
    }

    /** Acessors & Mutators mostly for Nova */
    public function getAttribute($key)
    {
        if (strpos($key, 'permissions_') !== false) {
            $slug = Str::after($key, 'permissions_');

            $permissions = collect(config('rbac.permissions'))
                ->mapWithKeys(fn($item, $key) => Str::slug($key) === $slug ? [Str::slug($key) => $item] : []);

            return $this->getPermissionsAttribute()->intersect(array_keys($permissions[$slug]))->values();
        }

        return parent::getAttribute($key);
    }
}
