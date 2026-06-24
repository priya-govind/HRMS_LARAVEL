<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\RoleCategoryPermission; 
use App\Policies\CategoryPolicy;
use App\Policies\PagePolicy;
use App\Models\Category;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    protected $policies = [
        RoleCategoryPermission::class => CategoryPolicy::class,
        Category::class => PagePolicy::class,

        // Model mapped to Policy
    ];

    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {

        /**Mainly used in blade files for checking Permissions */

        Gate::define('CategoryPermit', function ($user, $pageId, $permissionId) {
            // Logic to check if the user has the required permission

                /**Check Permission based on user role */
                $chk_agn=$user->role->roleCategoryPermissions()
                ->where('page_id', $pageId)
                ->where('permission_id', $permissionId)
                ->exists();
                if($chk_agn){
                    return true;
                } else{
                    return false;
                }
        });
        Gate::define('BotmenuPermit', function ($user, $menuId, $permissionId) {
            // Logic to check if the user has the required permission

                /**Check Permission based on user role */
                $chk_agn=$user->role->roleBotmenuPermissions()
                ->where('bot_id', $menuId)
                ->where('permission_id', $permissionId)
                ->exists();
                if($chk_agn){
                    return true;
                } else{
                    return false;
                }
        });
        
         Gate::define('PagePermit', [PagePolicy::class, 'PagePermit']);
        
        // Gate::define('PagePermit', function ($user, $category_id, $permission_id) {
        //     return app(PagePolicy::class)->PagePermit($user, $category_id, $permission_id);
        // });

    }
}
