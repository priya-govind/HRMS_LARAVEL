<table>
    <thead>
        <tr>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">S.no</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Project Name</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Module Name</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Task Name</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Employee Name</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Deadline Date</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Employee Task Status</th>
            <th style="font-weight:bold;background-color:#215dab;color: white;vertical-align: middle;">Overall Task Satus</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tasks as $task)
        <tr>
            
           <td style="text-align: center; vertical-align: middle; width:50px">{{ $loop->iteration }}</td>
           <td style="text-align: center; vertical-align: middle; width:200px;">{{ $task->task->project->proj_name ?? 'N/A' }}</td>
           <td style="text-align: center; vertical-align: middle; width:200px;">{{ $task->task->modules->module_name ?? 'N/A' }}</td>
            <td style="text-align: center; vertical-align: middle; width:200px;">{{ $task->task->task_name }}</td>
            
            <td style="text-align: center; vertical-align: middle; width:200px;">{{ $task->employee->name ?? 'N/A' }}</td>
            <td style="text-align: center; vertical-align: middle;  width:150px;">{{  \Carbon\Carbon::parse($task->task->endDate)->format('d/m/Y h:i A') }}</td>
            <?php
            $status_lookup = array_flip(config('global_task_status'));
            $emp_status_name = $status_lookup[$task->emp_task_status] ?? 'Unknown';
            $overall= $status_lookup[$task->task?->task_status] ?? 'Unknown';
            ?>
            <td style="text-align: center; vertical-align: middle;width:100px;">{{ $emp_status_name }}</td>
            <td style="text-align: center; vertical-align: middle;width:100px;">{{ $overall }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
