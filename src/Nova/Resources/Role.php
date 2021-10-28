<?php

namespace iDezDigital\Rbac\Nova\Resources;

use App\Nova\Resource;
use App\Nova\User;
use iDezDigital\Rbac\Models\Role as RoleModel;
use Laravel\Nova\Fields\ID;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\BelongsToMany;
use Benjaminhirsch\NovaSlugField\TextWithSlug;
use Silvanite\NovaFieldCheckboxes\Checkboxes;

class Role extends Resource
{
    public static $model = RoleModel::class;

    public static function order()
    {
        return 1000;
    }

    public static function group()
    {
        return 'Administrativo';
    }

    public static function icon()
    {
        return icon('lock-closed')  ;
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

            Checkboxes::make('Permissões', 'permissions')
                ->options(config('rbac.permissions')),

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
