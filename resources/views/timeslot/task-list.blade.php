<table class="list-group">
  <th>
    <td> Duration:</td>
    <td>Module </td>
    <td>Function</td>
  </th>
  @forelse ($tasks as $task)
    <tr class="list-group-item">
        <td>{{ \Carbon\Carbon::parse($task->from_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($task->from_time)->format('g:i A') }}</td>
      <td><strong>{{ $task->module }}</strong></td>
      <td>{{ $task->description }}</td>
</tr>
  @empty
    <tr class="list-group-item text-muted">No tasks found for this date.</tr>
  @endforelse
</table>
