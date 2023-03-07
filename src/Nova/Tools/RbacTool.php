<?php

namespace iDez\Rbac\Nova\Tools;

use iDez\Rbac\Nova\Resources\Role;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool;
use Laravel\Nova\Menu\MenuSection;


class RbacTool extends Tool
{
    protected $roleResource = Role::class;
    private $customRole = false;

    public function menu(Request $request)
    {
        return MenuSection::make('Grupos & Permissões')
            ->resource(Role::class)
            ->icon('shield-check');
    }

    /**
     * Perform any tasks that need to happen when the tool is booted.
     */
    public function boot()
    {
        Nova::script('Rbac', __DIR__.'/../../../dist/js/tool.js');
        Nova::style('Rbac', __DIR__.'/../../../dist/css/tool.css');

        if (! $this->customRole) {
            Nova::resources([$this->roleResource]);
        }
    }

    /**
     * @param string $roleResource
     *
     * @return mixed
     */
    public function resource(string $roleResource)
    {
        $this->customRole = true;
        $this->roleResource = $roleResource;

        return $this;
    }
}
