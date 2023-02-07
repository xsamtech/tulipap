<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Authorization for "Super administrateur" role
        Gate::define('auth-superadmin', function (User $user) {
            foreach ($user->role_users as $role_user) :
                return preg_match('#^super admin#i', $role_user->role->role_name);
            endforeach;
        });
        // Authorization for "Administrateur" role
        Gate::define('auth-admin', function (User $user) {
            foreach ($user->role_users as $role_user) :
                return preg_match('#^admin#i', $role_user->role->role_name);
            endforeach;
        });
    }
}
