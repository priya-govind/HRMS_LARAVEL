<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Roles;
/**Policies are registered and defined in AuthServiceProvider */
class CategoryPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    /**Refer Page model => SecureUser ()*/
    public function CategoryPermit($categoryId, $permissionId)
    {
        
        /**  Check if the user's role has permission to view the page based on role_id
         * refer role_page_permissions table
         */
        $role = Roles::find(session('role_id')); // Fetch the role using the role_id from session

        if ($role) {
// $res=$role->roleCategoryPermissions()
//                 ->where('category_id', $categoryId) // Check for the specific page
//                 ->where('permission_id', $permissionId) // Check for the specific permission
//                 ->exists();
//                 dd($res);


            return $role->roleCategoryPermissions()
                ->where('category_id', $categoryId) // Check for the specific page
                ->where('permission_id', $permissionId) // Check for the specific permission
                ->exists(); // Return true if the permission exists
        } else {
            return false; // Return false if the role is not found
        }
        
    }
    
}
