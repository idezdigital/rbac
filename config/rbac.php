<?php

use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use App\Providers\NovaServiceProvider;


return [
    /*
    |--------------------------------------------------------------------------
    | Application Permissions
    |--------------------------------------------------------------------------
    */

    'permissions' => [
        'Roles' => [
            'view-roles' => 'View Roles',
            'create-roles' => 'Create Roles'
        ]
    ],
];
