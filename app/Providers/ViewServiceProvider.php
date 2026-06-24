<?php

namespace App\Providers;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use App\Models\Notification;
use App\Models\Chat;
use Illuminate\Support\Facades\Cache;


use App\Models\Category;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
               // Bind sidebar data for all views
        /**For sidebar menu refer sidebar blade file */
        
        View::composer('*', function ($view) {
    if(Auth::check()){
        $permissionId = config('global_permissions.View');
        $roleId = session('role_id');
        $supportAccess = session('support_access');
        $userId = session('user_id');

        $cacheKey = "menus_{$roleId}_{$permissionId}_{$supportAccess}";

        $categories = Cache::remember($cacheKey, 600, function () use ($permissionId, $roleId) {
            return Category::with(['children' => function ($query) use($permissionId, $roleId) {
                $query->join('role_category_permissions', 'role_category_permissions.category_id', '=', 'category.id')
                      ->where('category.is_active_cat', 1)
                      ->where('role_category_permissions.roles_id', $roleId)
                      ->where('role_category_permissions.permission_id', $permissionId)
                      ->orderBy('order_by', 'ASC');
            }])
            ->join('role_category_permissions', 'role_category_permissions.category_id', '=', 'category.id')
            ->where('role_category_permissions.roles_id', $roleId)
            ->where('role_category_permissions.permission_id', $permissionId)
            ->where('category.parent_id', 1)
            ->where('category.is_active_cat', 1)
            ->select('category.*')
            ->orderBy('order_by', 'ASC')
            ->get();
        });

        $chat_drp = Chat::with('sender')
            ->where('receiver_id', $userId)
            ->where('is_read', 1)
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('sender_id');
        $chat_cnt=Chat::where('receiver_id',$userId)
              ->where('is_read', 1)
               ->distinct('sender_id')
              ->count('sender_id');
        $topbar_drp_cnt=Notification::where('receiver_id', session('user_id'))
              ->where('is_read', 1)
               ->distinct('sender_id')
              ->count('sender_id');
        $topbar_drp = Notification::where('receiver_id',$userId)
            ->select('subject', 'message','id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $view->with([
            'sidebarCategory'=> $categories,
            'topbar_drp'=>$topbar_drp,
            'topbar_drp_cnt' => $topbar_drp_cnt,
            'chat_msg' =>$chat_drp,
            'chat_cnt'=>$chat_cnt
        ]);
    }
});

       
        // View::composer('*', function ($view) {
        //     /**Check whether User logged in */
        //     if(Auth::check()){
        //         $permissionId = config('global_permissions.View');
        //         $chat_drp = Chat::with('sender')
        //                                         ->where('receiver_id', session('user_id'))
        //                                         ->where('is_read',0)
        //                                         ->orderBy('created_at', 'desc')
        //                                         ->get()
        //                                         ->unique('sender_id');   // only keep one per sender
        //        // dd($chat_drp);
        //          $topbar_drp_cnt = Notification::where('receiver_id', session('user_id'))
        //            ->where('is_read', config('global.notify_unread'))
        //            ->selectRaw('count(*) as count')
        //            ->value('count');
        //         $topbar_drp=Notification::where('receiver_id',session('user_id'))
        //                                      ->select('subject', 'message','id')
        //                                     ->orderBy('created_at', 'desc') // Assuming 'created_at' is the timestamp column
        //                                     ->limit(5)
        //                                     ->get(); 
        //         $categories = Category::with(['children' => function ($query) use($permissionId) {
        //             $query->join('role_category_permissions', 'role_category_permissions.category_id', '=', 'category.id')
        //                   ->where('category.is_active_cat', 1) 
        //                   ->where('role_category_permissions.roles_id', session('role_id')) // Filter by role ID
        //                   ->where('role_category_permissions.permission_id',  $permissionId)
        //                    ->orderBy('order_by', 'ASC'); // Filter by permission ID
        //         }])
        //         ->join('role_category_permissions', 'role_category_permissions.category_id', '=', 'category.id')
        //         ->where('role_category_permissions.roles_id', session('role_id')) // Filter by role ID
        //         ->where('role_category_permissions.permission_id',  $permissionId)  // Filter by permission ID
        //         ->where('category.parent_id', 1) // Fetch only parent categories
        //         ->where('category.is_active_cat', 1) 
        //         ->select('category.*') // Select necessary fields
        //         ->orderBy('order_by', 'ASC')
        //         ->get();
        //         $view->with(['sidebarCategory'=> $categories,'topbar_drp'=>$topbar_drp,'topbar_drp_cnt' => $topbar_drp_cnt,'chat_msg' =>$chat_drp]);
        //     }
        // });
    }
}
