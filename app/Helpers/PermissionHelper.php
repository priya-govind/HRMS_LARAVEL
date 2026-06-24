<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Route;
use App\Models\Category;


class PermissionHelper
{
    public static function checkPermission($configKey, $viewPerm)
    {
        // Get route name dynamically
        $full_routeName = Route::currentRouteName();
        $halfName=explode('.', $full_routeName);
     
        if(!empty($halfName[1]) && (config()->has($configKey . '.' . $halfName[1]))){
            $routeName=$halfName[1];
        } else {
            $routeName=$halfName[0];
        }
    
        // Append route name to the config key
        $config_nm = $configKey . '.' . $routeName;
// echo $config_nm;
//  die;

        // Get permission value from config
        $rolePermit = config($config_nm);
// echo  $rolePermit .'=>'.$viewPerm;
// die;
        // Perform permission check
        return Category::SecureUser($rolePermit, $viewPerm);
    }
}
?>