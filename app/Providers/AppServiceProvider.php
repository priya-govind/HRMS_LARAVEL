<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
//use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        //$this->app['router']->aliasMiddleware('role', RoleMiddleware::class);

        /**Assign Records in Permission table Globally and stores them in cache.
         * Cache::remember => checks whether global value exist in cache
         * if not it loads the query and stores them in cache memory.
         */
    if (env('APP_ENV') === 'production') {
        URL::forceScheme('https');
    }


         $categories = DB::table('category')->pluck('id', 'url_link')->toArray();
         // Set the configuration values
        config(['global.categories' => $categories]);

        $permissions = Cache::remember('permissions', 3600, function () {
            return DB::table('permissions')->pluck('id', 'permission_name')->toArray();
        });
        config(['global_permissions' => $permissions]);

        $roles = Cache::remember('roles', 3600, function () {
            return DB::table('roles')->pluck('id', 'role_name')->toArray();
        });
        config(['global_roles' => $roles]);

        $task_status = Cache::remember('project_status', 3600, function () {
            return DB::table('project_status')->pluck('id', 'proj_status_name')->toArray();
        });
        config(['global_task_status' => $task_status]);

        $work_mode = Cache::remember('working_mode', 3600, function () {
            return DB::table('working_mode')->pluck('color','id')->toArray();
        });
        config(['global_working_mode' => $work_mode]);

        $adminUser = Cache::remember('admin_user', 3600, function () {
            return DB::table('users')->where('id', 1)->first(['id', 'name', 'email']);
        });

        config(['global.admin' => [
            'id' => $adminUser->id,
            'name' => $adminUser->name,
            'email' => $adminUser->email,
        ]]);
        
        $adminUserId = config('app.admin_user_id'); // Fetch from config

        $adminUser = Cache::remember('admin_user', 3600, function () use ($adminUserId) {
            return DB::table('users')->where('id', $adminUserId)->first(['id', 'name', 'email']);
        });

        if ($adminUser) {
            Config::set('global.admin', [
                'id' => $adminUser->id,
                'name' => $adminUser->name,
                'email' => $adminUser->email,
                'role' => 'Admin', 
            ]);
        } else {
            Log::warning("Admin user with ID {$adminUserId} not found.");
        }
         
    }
    
}
