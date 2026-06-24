<div class="form-group">
    <label for="inventory_id">Inventory Item</label>
    <select name="inventory_id" id="inventory_id" class="form-control" required>
        @foreach($items as $id => $name)
            <option value="{{ $id }}" 
                {{ isset($inventory_assignment) && $inventory_assignment->inventory_id == $id ? 'selected' : '' }}>
                {{ $name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label for="assigned_to">Assign To (User ID)</label>
    <input type="text" name="assigned_to" id="assigned_to" class="form-control"
           value="{{ $inventory_assignment->assigned_to ?? '' }}" required>
</div>

<div class="form-group">
    <label for="status">Status</label>
    <select name="status" id="status" class="form-control" required>
        <option value="active" {{ (isset($inventory_assignment) && $inventory_assignment->status == 'active') ? 'selected' : '' }}>Active</option>
        <option value="inactive" {{ (isset($inventory_assignment) && $inventory_assignment->status == 'inactive') ? 'selected' : '' }}>Inactive</option>
    </select>
</div>