<?php

namespace iDez\Rbac\Nova\Resources;

use App\Nova\Resource;
use App\Nova\User;
use iDez\Rbac\Models\Role as RoleModel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Panel;

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
        'slug',
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

            Slug::make('Slug', 'slug')
                ->from('name')
                ->creationRules('unique:roles')
                ->updateRules('unique:roles,slug,{{resourceId}}')
                ->sortable()
                ->hideFromIndex(),

            Panel::make('Permissões', [
                \Idez\NovaCheckboxesField\Checkboxes::make('', 'permissions')
                    ->options(config('rbac.permissions'))
                    ->withGroups()
                    ->stacked(),
            ]),

            Text::make("Usuários", fn() => count($this->users))
                ->onlyOnIndex(),

            BelongsToMany::make('Usuários', 'users', User::class),
        ];
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
