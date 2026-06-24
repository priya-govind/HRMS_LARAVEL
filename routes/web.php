<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectTypeController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TeamTypeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TimesheetController;
use App\Http\Controllers\ChatsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\MonthlyExpenseController;
use App\Http\Controllers\TicketTypeController;
use App\Http\Controllers\ProblemTypeController;
use App\Http\Controllers\AssetTypesController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ItemTypeController;
use App\Http\Controllers\AccessoryTypesController;
use App\Http\Controllers\ComponentTypesController;
use App\Http\Controllers\SoftwareLicensesController;
use App\Http\Controllers\AssetItemsController;
use App\Http\Controllers\AssetHRMSController;
use App\Http\Controllers\ChatBotController;
use App\Http\Controllers\BotMenuController;
use App\Http\Controllers\ProjectModuleController;
//use App\Http\Controllers\PMTimesheetController;
use App\Http\Controllers\HolidaysController;
use App\Exports\AttendanceExport;
use Maatwebsite\Excel\Facades\Excel; 
use App\Exports\TimesheetExport;
use App\Jobs\WeeklyTimesheetJob;
use Illuminate\Support\Facades\Storage;
use App\Models\TeamType;
use Illuminate\Support\Carbon;

use Illuminate\Http\Request;
/**General** */
Route::get('/login', [AuthController::class, 'index'])->middleware('guest');
Route::post('login', [AuthController::class, 'login'])->name('login');
Route::post('/check-roles', [AuthController::class, 'checkRoles'])->name('check.roles');
Route::get('/', [AuthController::class, 'index'])->middleware('guest');
Route::get('forgot-password', function () {
    return view('forgot_password');
})->middleware('guest')->name('password.request');

Route::post('forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

Route::get('reset-password/{token}', function ($token) {
    return view('reset_password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');

Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/team-type/{id}', [DashboardController::class, 'getTaskStatusData']);
        Route::get('/dashboard/overall-status', [DashboardController::class, 'getOverallStatusComparison']);
        //Route::get('/chkdata_timesheet',[AttendanceController::class,'chkdata_timesheet']);
        Route::get('my_profile', [CommonController::class, 'show_profile'])->name('my_profile');
        Route::put('myprofile_update', [CommonController::class, 'myprofile_update'])->name('myprofile_update');
        Route::match(['get', 'post'], 'birthday_remainder', [CommonController::class, 'birthday_remainder'])->name('birthday_remainder');
        Route::get('birthday_remainder_list',[CommonController::class,'birthday_remainder_list'])->name('birthday_remainder_list');
        Route::get('check_birthday_alert',[CommonController::class, 'check_birthday_alert'])->name('check_birthday_alert');

        Route::get('/checkout', [AuthController::class, 'checkout'])->name('checkout');
        Route::post('/update_checkout_status', [AuthController::class, 'update_checkout_status'])->name('update_checkout_status');

        Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('generate_worklog_report', [CommonController::class, 'generate_worklog_report'])->name('generate_worklog_report');
        /**General** */

        /***Resource Management***/
        /***Category***/

        Route::get('categories', [CategoryController::class, 'category'])->name('categories');
        Route::get('category/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('category/store', [CategoryController::class, 'store'])->name('store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::put('categories/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/categories/{id}/destroy', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::post('/categories/{id}/status_change', [CategoryController::class, 'status_change'])->name('category.status_change');
        Route::get('deleted_category', [CategoryController::class, 'deleted_category'])->name('deleted_category');
        Route::post('/category/{id}/restore', [CategoryController::class, 'restore_deleted'])->name('category.restore');
        /***Category***/

        /**Roles */
        Route::get('roles', [RoleController::class, 'roles'])->name('roles');
        Route::get('roles/create', [RoleController::class, 'create_role'])->name('roles.create');
        Route::post('store_role', [RoleController::class, 'store_role'])->name('store_role');
        Route::get('roles/edit_role/{role}', [RoleController::class, 'edit_role'])->name('roles.edit');
        Route::get('roles/edit_bot_permission/{role}', [RoleController::class, 'edit_bot'])->name('roles.edit_bot_permission');
        Route::post('update_role/{role}', [RoleController::class, 'update_role'])->name('update_role');
        Route::post('update_bot_permission/{role}', [RoleController::class, 'update_bot_permission'])->name('update_bot_permission');
        /**Roles */

        /**Permissions */
        Route::get('permissions', [PermissionController::class, 'permissions'])->name('permissions');
        Route::get('permissions/create', [PermissionController::class, 'create_permission'])->name('permissions.create');
        Route::post('store_permission', [PermissionController::class, 'store_permission'])->name('store_permission');
        Route::get('permissions/edit/{permission}', [PermissionController::class, 'edit_permission'])->name('permissions.edit');
        Route::put('update_permission/{permission}', [PermissionController::class, 'update_permission'])->name('update_permission');
        Route::delete('/permissions/{id}/destroy', [PermissionController::class, 'destroy'])->name('permissions.destroy');
        Route::get('/deleted_permission', [PermissionController::class, 'deleted_permissions'])->name('deleted_permission');
        Route::post('/permissions/{id}/restore', [PermissionController::class, 'restore_deleted'])->name('permissions.restore');
        /**Permissions */

        /***Employees***/
        // Route::resource('employees', UserController::class);
        // Route::get('/employees', [UserController::class, 'index'])->name('employees');
        Route::resource('employees', UserController::class)->names([
            'index' => 'employees',
        ]);

        Route::get('/employees/filter/{emp_state?}', [UserController::class, 'index'])->name('employees.filter');

        Route::get('deleted_employees', [UserController::class, 'deleted_employees'])->name('deleted_employees');
        Route::post('/employees/{id}/restore', [UserController::class, 'restore_deleted'])->name('employees.restore');
        Route::delete('/delete_document/{id}', [UserController::class, 'destroy_docs']);
        Route::delete('/delete_experience/{id}', [UserController::class, 'destroy_experience']);
        Route::delete('/delete_certification/{id}', [UserController::class, 'destroy_certification']);
        Route::delete('/delete_doc_img/{id}', [UserController::class, 'delete_doc_img']);
        Route::delete('/delete_cert_img/{id}', [UserController::class, 'delete_cert_img']);
        Route::post('/employees/{id}/status_change', [UserController::class, 'status_change'])->name('category.status_change');
        Route::get('/employees/{id}/profile/preview', [UserController::class, 'previewProfile']);
        Route::get('/employees/{id}/profile/download', [UserController::class, 'downloadProfile']);
        Route::get('/employees/projects/{id}', [UserController::class, 'AssignedProjects']);
        Route::get('/employees/tasks/{id}', [UserController::class, 'AssignedTasks']);
        Route::get('/employees/modules/{id}', [ProjectModuleController::class, 'ModuleAssignEmployees']);
        /***Employees***/

        /**Expense Management */
        Route::resource('expenses', ExpenseController::class)->except(['show']);
        Route::resource('monthly_expense', MonthlyExpenseController::class);
        Route::get('check-amount-available',[MonthlyExpenseController::class, 'check_amount_availability']);
        Route::get('get_details',[MonthlyExpenseController::class, 'get_details']);
        Route::resource('transactions', TransactionController::class);
        Route::get('check-amount',[TransactionController::class, 'check_amount']);
        Route::post('/send-image-mail', [TransactionController::class, 'sendImageMail']);
        /**Expense Management */
        /**Ticket Management***/
        Route::get('raise_ticket', [TicketController::class, 'index'])->name('tickets.raise_ticket');
        // Route::get('ticket_types',[TicketTypeController::class, 'index'])->name('tickets.ticket_types');
        Route::resource('tickets', TicketController::class);
        Route::get('/tickets/{id}/update_status',[TicketController::class, 'update_status']);
        Route::post('/tickets/{id}/assign', [TicketController::class, 'assign_ticket']);
        Route::get('/tickets/{id}/assigned_members',[TicketController::class, 'assigned_members']);
        Route::put('ticket_ind_update',[TicketController::class,'ticket_ind_update'])->name('ticket_ind_update');
        Route::resource('ticket_types', TicketTypeController::class);
        Route::resource('problem_types', ProblemTypeController::class);
        Route::get('/ticket_types/{id}/status_change', [TicketTypeController::class, 'status_change']);
        Route::get('/problem_types/{id}/status_change', [ProblemTypeController::class, 'status_change']);
        Route::get('/get_problem_type/{id}', [ProblemTypeController::class, 'get_problem_type']);
        /**Ticket Management***/

        /***Attendance Management */
        //Route::get('applied_leaves', [AttendanceController::class, 'applied_leaves'])->name('attendance.applied_leaves');
        Route::match(['get', 'post'], 'applied_leaves', [AttendanceController::class, 'applied_leaves'])->name('attendance.applied_leaves');
        Route::match(['get', 'post'], 'leave_approval_requests', [AttendanceController::class, 'leave_approval_requests'])->name('attendance.leave_approval_requests');
        Route::post('change_leave_state', [AttendanceController::class, 'change_leave_state'])->name('change_leave_state');
        Route::get('mark_attendance', [AttendanceController::class, 'mark_attendance'])->name('attendance.mark_attendance');
        Route::get('attendance_info', [AttendanceController::class, 'attendance_info'])->name('attendance.attendance_info');
        Route::get('/calendar-events', [AttendanceController::class, 'getCalendarEvents']);
        Route::post('attendance_update_mode', [AttendanceController::class, 'attendance_update_mode'])->name('attendance_update_mode');
        Route::post('attendance_update_irregular_chkout', [AttendanceController::class, 'attendance_update_irregular_chkout'])->name('attendance_update_irregular_chkout');
        Route::post('/check-permission-slot', [TimesheetController::class, 'checkPermissionSlot']);
        Route::post('request_off_day', [AttendanceController::class, 'request_off_day'])->name('request_off_day');
        Route::get('retrive_permission/{emp_id}/{module}',[AttendanceController::class, 'retrive_permission']);
        Route::get('view_leave_info/{id}',[AttendanceController::class, 'view_leave_info']);
        Route::resource('working_mode', AttendanceController::class);
        Route::post('assign_module',[AttendanceController::class, 'assign_module'])->name('assign_module');

            // Route::get('/export-attendance', function () {
            //     return Excel::download(new AttendanceExport, 'attendance_week78.xlsx');
            // });
            Route::get('/timesheet_export', function (Illuminate\Http\Request $request) {
                try {
                    $from = $request->input('FromDate') 
                        ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('FromDate'))->format('Y-m-d') 
                        : null;
                    $to = $request->input('ToDate') 
                        ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('ToDate'))->format('Y-m-d') 
                        : null;
                    $given_dt= $request->input('GivenDate') 
                        ? \Carbon\Carbon::createFromFormat('d-m-Y', $request->input('GivenDate'))->format('Y-m-d') 
                        : null;
                    $emp_id = $request->input('emp_id') ?? session('user_id');
                    $emp_name = $request->input('emp_name') ?? session('user_name');
                    $proj_id = $request->input('proj_id') ?? $request->input('proj_id');
                    $module_id = $request->input('module_id') ?? $request->input('module_id');
                    $status_type=$request->input('status_type') ?? $request->input('status_type');
                } catch (\Exception $e) {
                    return back()->withErrors(['date' => 'Invalid date format.']);
                }
                if( $given_dt){
                 $timestamp = str_replace(' ','_',$emp_name ).'_' . Carbon::parse($given_dt)->format('d-M-Y');
                } else if($from && $to){
                     $timestamp = str_replace(' ','_',$emp_name ).'_' . Carbon::parse($from)->format('d M-Y').'to'. Carbon::parse($to)->format('d,M-Y');
                } else {
                 $timestamp = str_replace(' ','_',$emp_name ).'_' . now()->format('d-M-Y');
                }
           
            $filename = "timesheet_{$timestamp}.xlsx";
            
            //return Excel::download(new TimesheetExport($given_dt,$from, $to, $emp_id, $emp_name), $filename);
            return Excel::download(new TimesheetExport($request), $filename);
            })->name('timesheet_export');
            

        /**Attendance Management */


        /***Task Management */
        Route::get('manage_tasks', [TaskController::class, 'manage_tasks'])->name('tasks.manage_tasks');
        Route::get('manage_tasks/filter/active_projects/{proj_id?}', [TaskController::class, 'active_proj_tasks'])->name('tasks.active_projects_tasks');
        Route::get('/manage_tasks/filter/{current_status?}', [TaskController::class, 'manage_tasks'])->name('tasks.manage_tasks.filter');
        Route::get('assign_tasks', [TaskController::class, 'assign_tasks'])->name('tasks.assign_tasks');
        Route::post('create_task', [TaskController::class, 'create_task'])->name('create_task');
        Route::get('tasks/edit_assign_task/{task_id}', [TaskController::class, 'edit_assign_task'])->name('tasks.edit_assign_task');
        Route::put('update_task/{id}', [TaskController::class, 'update_task'])->name('update_task');
        Route::delete('/tasks/{id}/destroy_task', [TaskController::class, 'destroy_task'])->name('tasks.destroy_task');
        Route::get('deleted_tasks', [TaskController::class, 'deleted_tasks'])->name('deleted_tasks');
        Route::post('/tasks/{id}/restore', [TaskController::class, 'restore_deleted'])->name('tasks.restore');
        Route::get('/tasks/get-assigned-members', [TaskController::class, 'getAssignedMembers'])->name('get-assigned-members');
        Route::get('/tasks/{id}/get-assigned-members-info', [TaskController::class, 'getAssignedMembersInfo'])->name('get-assigned-members-info');
        Route::get('/tasks/LoadUnassignedEmployees/{taskid}',[TaskController::class, 'LoadUnassignedEmployees']);
        Route::post('update_task_info', [TaskController::class, 'update_task_info'])->name('update_task_info');
        Route::get('tasks/{id}/user_status', [TaskController::class, 'user_status'])->name('tasks.user_status');
        Route::get('tasks/{id}/pl_status', [TaskController::class, 'pl_status'])->name('tasks.pl_status');
        Route::get('tasks/{id}/get_task_info', [TaskController::class, 'get_task_info'])->name('tasks.get_task_info');
        Route::put('task_ind_update', [TaskController::class, 'task_ind_update'])->name('task_ind_update');
        Route::get('/get_assigned_teams_members/{task_id}',[TaskController::class,'get_assigned_teams_members'])->name('get_assigned_teams_members');
        Route::put('task_update_main', [TaskController::class, 'task_update_main'])->name('task_update_main');
        Route::put('task_update_main_status', [TaskController::class, 'task_update_main_status'])->name('task_update_main_status');
        Route::delete('/tasks/remove_task_employee',[TaskController::class,'remove_task_employee'])->name('remove_task_employee');
        Route::put('/tasks/task_employee_update', [TaskController::class, 'task_employee_update'])->name('task_employee_update');
        Route::get('tasks/{id}/view_user_status', [TaskController::class, 'view_user_status'])->name('tasks.view_user_status');
        Route::get('tasks/load_members_assigned/{task_id}', [TaskController::class, 'load_members_assigned'])->name('load_members_assigned');

        /**Project Type */
        Route::get('proj_types', [ProjectTypeController::class, 'proj_types'])->name('tasks.proj_types');
        Route::post('store_proj_type', [ProjectTypeController::class, 'store_proj_type'])->name('store_proj_type');
        Route::get('tasks/edit_proj_type/{proj_type}', [ProjectTypeController::class, 'edit_proj_type'])->name('tasks.edit_proj_type');
        Route::put('update_proj_type/{id}', [ProjectTypeController::class, 'update_proj_type'])->name('update_proj_type');
        Route::delete('tasks/{id}/destroy_project_type', [ProjectTypeController::class, 'destroy_project_type'])->name('tasks.destroy_project_type');
        Route::get('deleted_projects', [ProjectController::class, 'deleted_projects'])->name('deleted_projects');
        Route::post('/tasks/{id}/restore_project', [ProjectController::class, 'restore_deleted'])->name('tasks.restore_project');

        /**Project Status */
        Route::get('project_status', [ProjectController::class, 'project_status'])->name('tasks.project_status');
        Route::post('store_proj_status', [ProjectController::class, 'store_proj_status'])->name('store_proj_status');
        Route::get('tasks/edit_proj_status/{proj_type}', [ProjectController::class, 'edit_proj_status'])->name('tasks.edit_proj_status');
        Route::put('update_proj_status/{id}', [ProjectController::class, 'update_proj_status'])->name('update_proj_status');
        Route::delete('tasks/{id}/destroy_project_status', [ProjectController::class, 'destroy_project_status'])->name('tasks.destroy_project_status');

        /***Manage Projects */
        Route::get('manage_projects', [ProjectController::class, 'manage_projects'])->name('tasks.manage_projects');
        Route::get('/manage_projects/filter/{active?}', [ProjectController::class, 'manage_projects'])->name('tasks.manage_projects.filter');
        Route::post('tasks/store_projects', [ProjectController::class, 'store_projects'])->name('store_projects');
        Route::get('tasks/{project}/edit_project', [ProjectController::class, 'edit_project'])->name('tasks.edit_project');
        Route::put('update_project/{id}', [ProjectController::class, 'update_project'])->name('update_project');
        Route::delete('/tasks/{id}/destroy_project', [ProjectController::class, 'destroy_project'])->name('tasks.destroy_project');

        /**Project Modules */
        Route::resource('modules', ProjectModuleController::class);

        /**Assign Task */
        Route::get('/get_projects/{type}', [ProjectController::class, 'getProjects']);
        Route::get('/get_project_modules/{proj_id}', [ProjectController::class, 'getProjectModules']);
        Route::get('/get_assign_proj_members/{type}', [ProjectController::class, 'getAssignProjMembers']);
         Route::get('/get_mapped_tasks/{proj_id}/{module_id}', [TaskController::class, 'getMappedTasks']);
        Route::get('/get_projects_all', [ProjectController::class, 'getProjectsAll']);
        Route::get('/get_projects_id/{id}', [ProjectController::class, 'getProjectsById']);
        Route::get('/get_teams/{type}', [TeamController::class, 'getTeams']);
        Route::get('/get_teams_members/{type}', [TeamController::class, 'getTeamMembers']);
        Route::get('/get_teams_members_assign', [TeamController::class, 'GetTeamMemebersAssign']);
        Route::get('/get_team_ctrl_members', [TeamController::class, 'GetCtrlMembersAssign']);
        /***Task Management */

        /**Team Management */
        /**Team Types */
        Route::get('team_types', [TeamTypeController::class, 'team_types'])->name('teams.team_types');
        Route::post('store_team_type', [TeamTypeController::class, 'store_team_type'])->name('store_team_type');
        Route::get('teams/edit_team_type/{id}', [TeamTypeController::class, 'edit_team_type'])->name('teams.edit_team_type');
        Route::get('teams/members/{id}', [TeamController::class, 'list_team_members'])->name('teams.edit_team_type');
        Route::put('update_team_type/{id}', [TeamTypeController::class, 'update_team_type'])->name('update_team_type');
        Route::delete('teams/{id}/destroy_team_type', [TeamTypeController::class, 'destroy_team_type'])->name('teams.destroy_team_type');
        /**Team Types */

        /**Teams */
        Route::get('create_team', [TeamController::class, 'create_team'])->name('teams.create_team');
        Route::get('list_teams', [TeamController::class, 'list_teams'])->name('teams.list_teams');
        Route::post('store_team', [TeamController::class, 'store_team'])->name('store_team');
        Route::get('teams/edit_team/{id}', [TeamController::class, 'edit_team'])->name('teams.edit_team');
        Route::put('update_team/{id}', [TeamController::class, 'update_team'])->name('update_team');
        Route::delete('teams/{id}/destroy_team', [TeamController::class, 'destroy_team'])->name('teams.destroy_team');
        /**Teams */
        /**Team Management */
        Route::match(['get', 'post'], 'tasks_report', [ReportController::class, 'tasks_report'])->name('reports.tasks_report');
        Route::get('emp_report', [ReportController::class, 'employees_report'])->name('reports.emp_report');

        /**Notifications */
        Route::get('notify_type', [NotificationController::class, 'notify_type'])->name('notify_type');
        Route::post('notify_type/store', [NotificationController::class, 'store'])->name('store');
        Route::get('notify_type/{id}/edit', [NotificationController::class, 'edit'])->name('notify_type.edit');
        Route::put('notify_type/{id}', [NotificationController::class, 'update'])->name('update');
        Route::delete('/notify_type/{id}/destroy', [NotificationController::class, 'destroy'])->name('notify_type.destroy');
        Route::get('alert_notifications', [NotificationController::class, 'alert_notifications'])->name('notifications.alert_notifications');
        Route::get('notifications/view_notifications/{id}', [NotificationController::class, 'view_notifications'])->name('notifications.view_notifications');
        Route::get('notifications/make_read/{notify_id?}', [NotificationController::class, 'make_read']);

        Route::get('fill_timesheet',[TimesheetController::class,'fillup_sheet'])->name('fill_timesheet');
        Route::get('view_timesheet/{prev_date}/{user_id?}',[TimesheetController::class,'view_timesheet'])->name('view_timesheet');
        Route::get('edit_timesheet/{id}', [TimesheetController::class, 'edit_timesheet'])->name('edit_timesheet');
        Route::post('/save-timeslot', [TimesheetController::class, 'store'])->name('timeslot.store');
        Route::get('/update_slotcnt/{sltcnt}', [TimesheetController::class, 'update_slotcnt'])->name('update_slotcnt');
        Route::get('/timesheet_fetch', [TimesheetController::class, 'timesheet_fetch'])->name('timesheet_fetch');
        Route::get('/timesheet_log', [TimesheetController::class, 'timesheet_log'])->name('timesheet_log');
        Route::post('/timesheet_log_action', [TimesheetController::class, 'timesheet_log_action'])->name('timesheet_log_action');
        Route::get('generate_report',[ReportController::class,'generate_report'])->name('generate_report');

        
        Route::post('timesheets', [TimesheetController::class, 'store'])->name('timesheets.store');


        Route::get('/chats', [ChatsController::class, 'index'])->name('chats.index');
        Route::get('/search-users', [ChatsController::class, 'search']);
        Route::get('/chat-messages/{userId}', [ChatsController::class, 'getMessages']);
        Route::post('/send-message', [ChatsController::class, 'sendMessage']);
        Route::post('/chat/mute/{userId}', [ChatsController::class, 'muteUser']);
        Route::post('/chat/unmute/{userId}', [ChatsController::class, 'unmuteUser']);
        Route::delete('/chat/message/delete/{id}', [ChatsController::class, 'deleteMessage']);
        Route::post('/chat/block/{userId}', [ChatsController::class, 'blockUser']);
        Route::post('/chat/unblock/{userId}', [ChatsController::class, 'unblockUser']);
        Route::delete('/chat/messages/clear/{userId}', [ChatsController::class, 'clearMessages'])->name('chat.clearMessages');
        Route::post('/chat/mark-chat-read/{senderId}', [ChatsController::class, 'markChatNotificationsAsRead']);
        Route::post('/chat/message/reply', [ChatsController::class, 'replyToMessage'])->name('chat.reply');
        Route::post('/chat/message/forward', [ChatsController::class, 'forwardMessage'])->name('chat.forward');
        Route::post('/chat/upload', [ChatsController::class, 'upload'])->name('chat.upload');
    
        /**Report Management */
        Route::get('attendance_report', [ReportController::class, 'attendance_report'])->name('reports.attendance_report');
        Route::post('attendance_export', [ReportController::class, 'export'])->name('attendance_export');
        Route::get('/attendance_export_excel', function (Request $request) {
            return Excel::download(
                new AttendanceExport($request->start_date, $request->end_date),
                'attendance_report.xlsx'
            );
        })->name('attendance_export_excel');
        Route::get('ticket_reports',[ReportController::class, 'ticket_reports'])->name('reports.ticket_reports');
        Route::post('ticket_report_action',[ReportController::class, 'ticket_report_action'])->name('ticket_report_action');
        Route::get('ticket_report_export', [ReportController::class, 'exportTicketReport'])->name('ticket_report_export');
        Route::post('export-weekly-attendance', [AttendanceController::class, 'exportWeeklyAttendance'])->name('attendance.export');


        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('/attendance/report', [AttendanceController::class, 'report'])->name('attendance.report');
        Route::get('punch_card', [AttendanceController::class, 'punch_card'])->name('attendance.punch_card');


        Route::post('/tasks-report-action', [ReportController::class, 'tasks_report_action'])->name('tasks_report_action');
        Route::get('/export-tasks', [ReportController::class, 'exportTasks'])->name('export-tasks');
        Route::get('/inventory_report',[ReportController::class, 'inventory_report'])->name('reports.inventory_report');

        Route::post('inventory_report_action',[ReportController::class, 'inventory_report_action'])->name('inventory_report_action');
        Route::get('inventory_report_export', [ReportController::class, 'exportInventoryReport'])->name('inventory_report_export');
        });
        Route::post('import_punch_card', [AttendanceController::class, 'import_punch_card'])->name('import_punch_card');

        Route::resource('asset_types', AssetTypesController::class);
        Route::resource('accessory_types', AccessoryTypesController::class);
        Route::resource('component_types', ComponentTypesController::class);
        Route::resource('software_licenses', SoftwareLicensesController::class);
        Route::resource('assets_manage', AssetItemsController::class);
        Route::resource('holidays', HolidaysController::class);
        Route::get('assets_manage/{assset_type}/loadcatdata',[AssetItemsController::class,'LoadAssetCategory']);
        Route::get('assets_manage/{assset_type}/loadbranddata',[AssetItemsController::class,'LoadAssetBrands']);
        Route::post('assets_manage/addCategory',[AssetItemsController::class,'AddCategory']);
        Route::post('assets_manage/addBrand',[AssetItemsController::class,'addBrand']);
        Route::post('assets_manage/store_assigned',[AssetItemsController::class, 'store_assigned']);
        Route::get('assets_manage/{id}/replace_inventory',[AssetItemsController::class, 'replace_inventory']);
        Route::get('assets_manage/{id}/check_assigned',[AssetItemsController::class, 'check_assigned']);

        /***Assset Based on HRMS */

        Route::get('asset_attribute',[AssetHRMSController::class, 'asset_attribute'])->name('asset_attribute.asset_attribute');
        Route::post('add_attribute',[AssetHRMSController::class,'add_attribute'])->name('asset_attribute.add_attribute');
        Route::get('edit_attribute/{id}',[AssetHRMSController::class,'edit_attribute'])->name('asset_attribute.edit_attribute');
        Route::put('update_attribute',[AssetHRMSController::class,'update_attribute'])->name('asset_attribute.update_attribute');
        Route::delete('attribute_destroy/{id}',[AssetHRMSController::class,'attribute_destroy'])->name('asset_attribute.attribute_destroy');
        Route::delete('delete_attr_option/{opt_id}', [AssetHRMSController::class, 'delete_attr_option']);

        Route::get('manage_assets_types',[AssetHRMSController::class, 'manage_assets_types'])->name('manage_assets_types.manage_assets_types');
        Route::post('store_assets_types',[AssetHRMSController::class,'store_assets_types']);
        Route::get('edit_assets_types/{id}',[AssetHRMSController::class,'edit_assets_types'])->name('manage_assets_types.edit_assets_types');
        Route::put('update_assets_types',[AssetHRMSController::class,'update_assets_types'])->name('manage_assets_types.update_assets_types');
        Route::delete('destroy_assets_types/{id}',[AssetHRMSController::class,'destroy_assets_types'])->name('manage_assets_types.destroy_assets_types');

        Route::resource('manage_items_configure', AssetHRMSController::class);
        Route::get('manage_items_configure/{assetId}/{Itemid?}',[AssetHRMSController::class, 'show']);/** Generate configuration and options to gether */
        Route::post('manage_items_configure_action',[AssetHRMSController::class, 'manage_items_configure_action'])->name('manage_items_configure_action');
        Route::get('manage_configuration/{assetId}',[AssetHRMSController::class, 'manage_configuration']);
        Route::post('manage_config_feature', [AssetHRMSController::class, 'manage_config_feature']);
        Route::get('damaged_items',[AssetHRMSController::class,'damaged_items'])->name('damaged_items');
        Route::post('damaged_items_action',[AssetHRMSController::class, 'damaged_items_action'])->name('damaged_items_action');
        /***Assset Based on HRMS */



        Route::get('/asset_report',[ReportController::class, 'asset_report'])->name('reports.asset_report');
        Route::post('asset_report_action',[ReportController::class, 'asset_report_action'])->name('asset_report_action');

        Route::get('/asset_report_hrms',[ReportController::class,'asset_report_hrms'])->name('reports.asset_report_hrms');
        Route::post('asset_report_action_hrms',[ReportController::class, 'asset_report_action_hrms'])->name('asset_report_action_hrms');
        Route::get('asset_report_export_hrms', [ReportController::class, 'exportAssetReportHRMS'])->name('asset_report_export_hrms');

        Route::get('asset_report_export', [ReportController::class, 'exportAssetReport'])->name('asset_report_export');

        Route::resource('manage_inventory', InventoryController::class);
        Route::resource('item_types', ItemTypeController::class);
        Route::get('brands',[ItemTypeController::class, 'brands_list'])->name('inventory.brands');
        Route::post('store_brand',[ItemTypeController::class, 'store_brand']);
        Route::get('brands/{brands}/edit', [ItemTypeController::class, 'edit_brand'])->name('brands.edit');
        Route::put('brands/{brands}', [ItemTypeController::class, 'update_brand'])->name('brands.update');
        Route::delete('/brands/{id}/destroy', [ItemTypeController::class, 'destroy_brand'])->name('brands.destroy');
        Route::get('manage_inventory/{id}/show',[InventoryController::class, 'show']);
        Route::post('manage_inventory/store_assigned',[InventoryController::class, 'store_assigned']);
        Route::get('manage_inventory/{id}/show_user_assets',[InventoryController::class, 'show_user_assets']);
        Route::get('manage_inventory/{id}/replace_inventory',[InventoryController::class, 'replace_inventory']);

        Route::resource('bot_menus', BotMenuController::class);
        Route::get('bot_menus/{id}/toggle', [BotMenuController::class, 'toggleStatus']);
        Route::get('/chatbot/history', [ChatBotController::class, 'history']);
        /**CHAT BOT */
        Route::prefix(config('chatbot.routing.prefix'))
            ->group(function () {
                Route::get('/session', [ChatBotController::class, 'currentSession']);
                Route::post('/message', [ChatBotController::class, 'sendMessage']);
                Route::post('/close', [ChatBotController::class, 'closeSession']);
            });

        // /**Send Timesheet Report to Reporting person */
        // Route::get('/timesheet-send', function () {
        //     // Dispatch the job immediately
        //     WeeklyTimesheetJob::dispatch();

        //     return response()->json([
        //         'status' => 'success',
        //         'message' => 'Timesheet generation and mailing started.'
        //     ]);
        // });
        
        Route::get('/timesheet-send', function () {
            $job = new WeeklyTimesheetJob();
            $job->handle(); // run synchronously

            $teamTypes = TeamType::all();
            $urls = [];

            foreach ($teamTypes as $teamType) {
                $zipFilename = "timesheet_teamtype_{$teamType->id}_" . now()->format('Y-m-d') . ".zip";
                $urls[$teamType->id] = Storage::url("timesheet_history/{$zipFilename}");
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Timesheets generated and mailed.',
                'download_urls' => $urls
            ]);
        });
        /**New Requirement */
        //Route::get('proj_tasks',[TaskController::class,'proj_tasks'])->name('tasks.proj_tasks');
        Route::match(['get', 'post'], 'proj_tasks', [TaskController::class, 'proj_tasks'])->name('tasks.proj_tasks');
        Route::get('add_proj_tasks',[TaskController::class,'add_proj_tasks'])->name('tasks.add_proj_tasks');
        Route::post('store', [TaskController::class, 'store'])->name('tasks.store');
        Route::get('tasks/{id}/edit', [TaskController::class, 'edit'])->name('tasks.edit');
        Route::post('tasks/{id}/update', [TaskController::class, 'update'])->name('tasks.update');
        Route::delete('tasks/{id}/destroy',[TaskController::class, 'destroy'])->name('tasks.destroy');
        Route::get('remove_attachment/{itemId}',[TaskController::class, 'remove_attachment']);
        Route::get('timesheets/check-entry', [TimesheetController::class, 'checkEntry']);
        Route::get('timesheet/edit_dtls/{id}', [TimesheetController::class, 'edit_dtls'])->name('edit_dtls');
        Route::get('tasks/{id}/user_proj_status', [TaskController::class, 'user_proj_status'])->name('tasks.user_proj_status');
        Route::get('/tasks/{id}/get-assigned-members-info-tasks', [TaskController::class, 'getAssignedMembersInfoPmProjects'])->name('get-assigned-members-info-tasks');
        Route::put('task_ind_update_pm_tasks', [TaskController::class, 'task_ind_update_pm_tasks'])->name('task_ind_update_pm_tasks');
        Route::get('tasks/{id}/get_pm_tasks_info', [TaskController::class, 'get_pm_tasks_info'])->name('tasks.get_pm_tasks_info');
        Route::put('task_update_pm_status', [TaskController::class, 'task_update_pm_status'])->name('task_update_pm_status');
        Route::get('timesheets/dates', [TimesheetController::class, 'getTimesheetDates']);
        Route::get('/timesheets/last-entry', [TimesheetController::class, 'lastEntry']);
        Route::post('/timesheet/send-report', [TimesheetController::class, 'sendTimesheetReport'])->name('timesheet.sendReport');
        Route::post('/timesheet_search', [TimesheetController::class, 'timesheet_search'])->name('timesheet_search');
        Route::get('/get_mapped_employees/{proj_id}', [ProjectController::class, 'getMappedEmployees']);
        Route::get('/check_notifications', [NotificationController::class, 'CheckNotifications']);
        Route::get('/quick_search-users', [ChatsController::class, 'quick_search']);
        Route::get('/user/{id}/checkout-status', [AttendanceController::class, 'checkOutStatus']);
        

        Route::post('/attendance/generate-report', [AttendanceController::class, 'generateReport'])->name('attendance.generateReport');
        Route::post('/attendance/update-status', [AttendanceController::class, 'updateStatus'])->name('attendance.updateStatus');



            /**New Requirement */

        /**404 page redirection */
        Route::fallback(function () {
            return response()->view('errors.404',[], 404);
        });