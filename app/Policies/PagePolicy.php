<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use Illuminate\Support\Facades\Log;



class PagePolicy
{
    // public function PagePermit(User $user, $category_id, $permission_id)
    // {
    //     // Your custom logic here
    //     // Return true if user has permission, false otherwise
    //     return $user->hasPermission($category_id, $permission_id); // Example
    // }

public function PagePermit(User $user, $configKey, $PermitType)
{
    $routeName = Route::currentRouteName();  
    
   // Log::info('Route Name: '.$routeName);
    $routeParts = explode('.', $routeName ?? '');
    $routeKey = count($routeParts) === 2 ? $routeParts[1] : ($routeParts[0] ?? 'default');
   // Log::info('Config Key'.$configKey);
   // Log::info('route Key: '.$routeKey);
    $permissionId = config($configKey . '.' . $routeKey);
   // Log::info('permissionId: '.$permissionId);
    if(empty($permissionId)){
        $url_link=$routeParts[0];
       // Log::info('$url_link=>'.$url_link);
        $permissionId=config('global.categories.'.$url_link);
        // Log::info('permissionId: '.$permissionId);
    }
    return Category::SecureUser($permissionId, $PermitType);
}

}