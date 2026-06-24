// Ensure jQuery is ready

 function deleteItem(id,url) {
    $.ajax({
                url: url,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
        const messageContainer = $('#success-message'); // Select the container for the message
            messageContainer.text(response.message).show(); // Update and display the message

            // Hide the message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
                 var table = $('#tasksTable').DataTable();
                   table.ajax.reload(null, false);
            }, 2000);


         // // Refresh page
      },
      error: function (xhr) {
        const messageContainer = $('#error-message'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message

            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 2000);

      }
            });
}

function callajax(url,method,data) {
     
    $.ajax({
                url: url,
                type: method,
                data: data,
                success: function (response) {
                    const messageContainer = $('#success-message1'); // Select the container for the message
                    messageContainer.text(response.message).show(); // Update and display the message
                  
            // Hide the message after 5 seconds
                setTimeout(function () {
                    messageContainer.hide();
                    //location.reload();
                }, 2000);
      },
      error: function (xhr) {
        const messageContainer = $('#error-message1'); // Select the container for the error message
            const errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'An error occurred';
            messageContainer.text('Error: ' + errorMessage).show(); // Update and display the error message
            // Hide the error message after 5 seconds
            setTimeout(function () {
                messageContainer.hide();
            }, 2000);

      }
            });
}
function updateIds(id) {
    var assignedField = $('#hiddenAssignedIds');
    var newAssignedField = $('#hiddennewAssignedIds');

    // Convert input values to arrays
    var assignedValues = assignedField.val().split(',').filter(Boolean);
    var newAssignedValues = newAssignedField.val().split(',').filter(Boolean);

    // Append to hiddenAssignedIds if not already present
    if (!assignedValues.includes(id.toString())) {
        assignedValues.push(id);
    }

    // Remove from hiddennewAssignedIds
    newAssignedValues = newAssignedValues.filter(value => value !== id.toString());

    // Update fields
    assignedField.val(assignedValues.join(','));
    newAssignedField.val(newAssignedValues.join(','));
}

function RollbackIds(id) {
    var assignedField = $('#hiddenAssignedIds');
    var newAssignedField = $('#hiddennewAssignedIds');

    // Convert input values to arrays safely
    var assignedValues = assignedField.val() ? assignedField.val().split(',').filter(Boolean) : [];
    var newAssignedValues = newAssignedField.val() ? newAssignedField.val().split(',').filter(Boolean) : [];

    id = id.toString(); // Ensure ID is always a string

    // Add to newAssignedValues if not already present
    if (!newAssignedValues.includes(id)) {
        newAssignedValues.push(id);
    }

    // Remove from assignedValues
    assignedValues = assignedValues.filter(value => value !== id);

    // Update input fields
    assignedField.val(assignedValues.join(','));
    newAssignedField.val(newAssignedValues.join(','));
}
