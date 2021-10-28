<?php

namespace iDezDigital\Rbac\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class RbacServiceProvider extends ServiceProvider
{

    private function setup()
    {
        $this->publishes([__DIR__ . "/../../config/rbac.php" => config_path("rbac.php")]);
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/rbac.php', 'rbac');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setup();

        foreach (config('rbac.permissions') as $key => $permissions) {
            Gate::define($key, fn (User $user) => $user->hasPermission($key));
        }
    }
}
