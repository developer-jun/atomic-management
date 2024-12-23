jQuery(document).ready(function($) {
    $('#addTaskButton').on('click', function(event) {
        event.preventDefault();

        var data = {
            action: 'submit_task',
            nonce: atomic.nonce,
            taskTitle: $('#taskTitle').val(),
            taskDescription: $('#taskDescription').val(),
            projectId: $('#projectId').val() // Ensure you have a hidden input with the project ID
        };

        $.post(atomic.ajaxurl, data, function(response) {
            if (response.success) {
                alert(response.data.message);
                // Optionally append the new task to the task list
                $('#taskList').append('<li>' + data.taskTitle + '</li>');
                // Reset form fields if necessary
                $('#taskTitle').val('');
                $('#taskDescription').val('');
            } else {
                alert(response.data.message);
            }
        });
    });
});

