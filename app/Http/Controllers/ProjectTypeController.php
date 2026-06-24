<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\PermissionHelper;
use App\Helpers\ActivityHelper;
use App\Models\ProjectType;
use Illuminate\Validation\Rule;


use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class ProjectTypeController extends Controller
{
    public function proj_types(Request $request){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->view_perm);
        if(!$cat_permission){ 
          return redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');
        }
          
          if ($request->ajax()) {
            $proj_types = ProjectType::orderBy('id', 'desc')->get();
          
          

            return DataTables::of($proj_types)
                ->addIndexColumn()
                   ->addColumn('action', function($row) {
                    return '<button data-id="'.$row->id.'" class="btn btn-primary btn-sm editButton"><i class="fa fa-edit"></i></button>&nbsp;|&nbsp;
                      <button type="button"  class="btn btn-danger btn-sm delete-btn" data-id="'.$row->id.'"  data-type="category"><i class="fa fa-trash-o"></i></button>
                     ';
                })
                ->rawColumns(['action'])
                ->make(true);
          }
  
  
          return view('tasks.project_type',['LoadDatatables' => true]);
      
    }
    public function edit_proj_type($id){
            $proj_types= ProjectType::find($id); 
            return PermissionHelper::checkPermission('global.categories', $this->edit_perm) ? response()->json($proj_types) : redirect()->route('dashboard')->withMessage('Not Authorized to see this page.');  
    }

    public function store_proj_type(Request $request){
          $validated = $request->validate([
              'proj_typ_name' => [
                                  'required',
                                  Rule::unique('project_type')->whereNull('deleted_at')
                              ]
          ]);
          
         $data = $request->except('_token');
          // Mass assigment
         $proj_types= ProjectType::create($data);
          $log_name='projects';
          ActivityHelper::logActivity('Create New Project Type',$log_name, $proj_types, [
            'request' => request()->all()
        ]);
          return  response()->json(['message' => 'Project Type Added successfully!']);
          // return redirect()->route('permissions')
          //         ->withSuccess('New Permission created successfully.');
    }
    public function update_proj_type(Request $request){
        $proj_types= ProjectType::find($request->id);
        $request->validate([
           'proj_typ_name' => [
                                  'required',
                                  Rule::unique('project_type', 'proj_typ_name')
                                      ->ignore($request->id)       // allow current record
                                      ->whereNull('deleted_at')    // exclude soft-deleted rows
                              ],
        ]);

        $log_name='projects';
          ActivityHelper::logActivity('Edit Project Type',$log_name, $proj_types, [
            'request' => request()->all()
        ]);
        $data = $request->all();
        
        $proj_types->update($data);
        return  response()->json(['message' => 'Project Type Updated successfully!'],200);

    }
    public function destroy_project_type($id){
        $cat_permission= PermissionHelper::checkPermission('global.categories',$this->del_perm);
       if(!$cat_permission){ 
        return  response()->json(['message' => 'Not Authorized to see this page.'],200);
       }else{
        $user = Auth::user();
        $proj_types= ProjectType::find($id);
      if ($proj_types) {
      $log_name='projects';
           ActivityHelper::logActivity('Project Type Deleted',$log_name, $proj_types, [
                      'request' => request()->all()
                  ]);
          $proj_types->delete();
      }
      
      
        return response()->json(['message' => 'Record Deleted successfully!'],200);
        }
    }
}
