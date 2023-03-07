<?php

namespace iDez\Rbac\Nova\Resources;

use App\Nova\Resource;
use App\Nova\User;
use iDez\Rbac\Models\Role as RoleModel;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Panel;
use Silvanite\NovaFieldCheckboxes\Checkboxes;

class Role extends Resource
{
    public static $model = RoleModel::class;

    public static function order()
    {
        return 1000;
    }

    public static $title = 'name';

    public static $search = [
        'id',
        'key',
        'name',
    ];

    public static $with = [
        'users',
    ];

    public function fields(Request $request)
    {
        return [
            ID::make()->sortable(),

            Text::make(__('Name'), 'name')
                ->rules('required')
                ->sortable(),

            Slug::make('Slug', 'key')
                ->from('name')
                ->creationRules('unique:roles')
                ->updateRules('unique:roles,key,{{resourceId}}')
                ->sortable()
                ->hideFromIndex(),

            Panel::make('Permissões', $this->permissionFields()),

            Text::make("Usuários", fn() => count($this->users))
                ->onlyOnIndex(),

            BelongsToMany::make('Usuários', 'users', User::class),
        ];
    }

    private function permissionFields()
    {
        $rbacPermissions = config('rbac.permissions');
        foreach($rbacPermissions as $group => $permissions){
            $slug = Str::slug($group);

            $fields[] = Checkboxes::make($group, "permissions_{$slug}")
                ->options($permissions)
                ->columns(3)
                ->hideFromIndex();
        }

//        $fields[] = Checkboxes::make('Permissões', 'permissions')
//            ->options(collect($rbacPermissions)->mapWithKeys(fn($a) => $a)->toArray())
//            ->onlyOnIndex();

        return $fields;
    }

    public function filters(Request $request)
    {
        return [];
    }

    public static function label()
    {
        return "Permissões";
    }

    public static function singularLabel()
    {
        return "Grupo de Permissões";
    }
}
