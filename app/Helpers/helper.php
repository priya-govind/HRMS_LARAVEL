<?php 


use App\Helpers\PermissionHelper;

if (!function_exists('checkPermission')) {
    function checkPermission($configKey, $viewPerm)
    {
        return PermissionHelper::checkPermission($configKey, $viewPerm);
    }
}
