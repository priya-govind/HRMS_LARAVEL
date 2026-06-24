<!-- Task dropdown -->
<div class="form-group">
    <label>Task</label>
    <select name="task_id" id="task_id" class="form-control">
        <option value="">Select Task</option>
        <!-- tasks loaded via AJAX -->
        <option value="other">Other (Create new task)</option>
    </select>
</div>

<!-- Modal for ad-hoc task -->
<div class="modal fade" id="otherTaskModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <form id="otherTaskForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add Custom Task</h5>
          <span class="close">&times;</span>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label>Task Name</label>
            <input type="text" name="custom_task" id="custom_task" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Description / Comments</label>
            <textarea name="comments" id="custom_comments" class="form-control"></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" id="saveCustomTask" class="btn btn-primary">Use Task</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
        $('#project_id').change(function() {
        let proj_id = $(this).val();
        $.get('/get_project_modules/' + proj_id, function(data) {
            $('#module_id').empty().append('<option value="">Select Module</option>');
            $.each(data, function(id, name) {
                $('#module_id').append('<option value="'+id+'">'+name+'</option>');
            });
        });
    });

    $('#module_id').change(function() {
        let module_id = $(this).val();
        $.get('/get_module_tasks/' + module_id, function(data) {
            $('#task_id').empty().append('<option value="">Select Task</option><option value="other">Other</option>');
            $.each(data, function(id, name) {
                $('#task_id').append('<option value="'+id+'">'+name+'</option>');
            });
        });
    });
    $('#task_id').change(function() {
    if ($(this).val() === 'other') {
        $('#otherTaskModal').modal('show');
    }
});

$('#saveCustomTask').click(function() {
    let customTask = $('#custom_task').val();
    if(customTask.trim() === '') {
        alert('Please enter a task name');
        return;
    }

    // Add hidden input to main form
    if($('#custom_task_hidden').length === 0) {
        $('#dataForm').append('<input type="hidden" name="custom_task" id="custom_task_hidden">');
    }
    $('#custom_task_hidden').val(customTask);

    // Also copy comments if needed
    if($('#custom_comments').val().trim() !== '') {
        if($('#custom_comments_hidden').length === 0) {
            $('#dataForm').append('<input type="hidden" name="comments" id="custom_comments_hidden">');
        }
        $('#custom_comments_hidden').val($('#custom_comments').val());
    }

    // Close modal and reset dropdown
    $('#otherTaskModal').modal('hide');
    $('#task_id').val('');
});

});

</script>