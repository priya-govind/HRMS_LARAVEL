<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Projects;

class PMTimesheetController extends Controller
{
    
   public function store(Request $request)
{
    $validated = $request->validate([
        'date'       => 'required|date|after_or_equal:today|before_or_equal:today',
        'from_time'  => 'required',
        'to_time'    => 'required',
        'project_id' => 'required',
        'module_id'  => 'nullable',
        'task_id'    => 'nullable',
        'custom_task'=> 'nullable|string|max:255',
        'comments'   => 'nullable|string',
    ]);

    $validated['employee_id'] = auth()->id();

    Timesheet::create($validated);

    return redirect()->route('timesheets.index')->withSuccess('Timesheet saved successfully!');
}

}
