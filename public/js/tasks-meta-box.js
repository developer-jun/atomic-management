/*
jQuery(document).ready(function($) {
    $('#addTaskButton').on('click', function(event) {
        event.preventDefault();

        var data = {
            action: 'submit_task',
            nonce: myAjax.nonce,
            taskTitle: $('#taskTitle').val(),
            taskDescription: $('#taskDescription').val(),
            projectId: $('#projectId').val() // Ensure you have a hidden input with the project ID
        };

        $.post(myAjax.ajaxurl, data, function(response) {
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
*/
jQuery(document).ready(function($) {
    $('#taskAssignee').select2({
        placeholder: 'Select a user',
        allowClear: true
    });
});

document.addEventListener('DOMContentLoaded', function () {
    // Add comment submission logic
    /*
    document.querySelectorAll('.submit-comment').forEach(function (button) {
        button.addEventListener('click', function () {
            console.log('submit button triggered');
            const taskIndex = this.getAttribute('data-task-index');
            const commentInput = document.querySelector(`.comment-input[data-task-index="${taskIndex}"]`);
            const commentText = commentInput.value;

            if (!commentText) {
                alert('Please enter a comment.');
                return;
            }

            // Prepare data
            const formData = new FormData();
            formData.append('nonce', taskMetaBox.nonce);
            formData.append('action', 'add_task_comment');
            formData.append('post_id', document.getElementById('post_ID').value); // WordPress post ID
            formData.append('task_index', taskIndex);
            formData.append('comment_text', commentText);

            fetch(taskMetaBox.ajaxurl, {
                method: 'POST',
                body: formData,
            })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        // Append new comment to the UI
                        const commentsContainer = document.getElementById(`comments-${taskIndex}`);
                        const newComment = document.createElement('div');
                        newComment.className = 'comment-item';
                        newComment.style.cssText = 'margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;';
                        newComment.innerHTML = `<p><strong>${data.comment.name}</strong> (${data.comment.date}):</p>
                                                <p>${data.comment.text}</p>`;
                        commentsContainer.appendChild(newComment);

                        // Clear input
                        commentInput.value = '';
                    } else {
                        alert('Error adding comment: ' + data);
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    //alert('Error adding comment.');
                    return;
                });
            });
        });
    });
    */   
});
document.addEventListener('DOMContentLoaded', function() {
    

    document.querySelectorAll('.submit-comment').forEach(function (button) {        
        button.addEventListener('click', function () {
            button.classList.add('sending');
            setTimeout(function(){
                button.classList.remove('sending');
                }, 4500);
    
            console.log('submit button triggered');
            const taskIndex = this.getAttribute('data-task-index');
            const taskIdInput = document.querySelector(`.taskId-input[data-task-index="${taskIndex}"]`);
            const taskId = taskIdInput.value;
            const commentAuthorInput = document.querySelector(`.commentAuthor-input[data-task-index="${taskIndex}"]`);
            const commentAuthor = commentAuthorInput.value;
            const commentInput = document.querySelector(`.commentText-input[data-task-index="${taskIndex}"]`);
            const commentText = commentInput.value;
            
    
            //alert('taskIndex: [' +taskIndex + '] - taskId: [' + taskId +'] - comment: [' + commentText + ']');
            if (!commentText) {
                alert('Please enter a comment.');
                return;
            }
    
            
    
            // Prepare data
            const formData = new FormData();
            formData.append('nonce', atomic.nonce);
            formData.append('action', 'submit_task_comment');
            formData.append('taskId', taskId);
            formData.append('commentAuthor', commentAuthor);
            formData.append('commentText', commentText);
    
    
    
            //formData.append('post_id', document.getElementById('post_ID').value); // WordPress post ID
            
            //formData.append('comment_text', commentText);
    
            fetch(atomic.ajaxurl, {
                method: 'POST',
                body: formData,
            })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    // Append new comment to the UI
                    const commentsContainer = document.getElementById(`comments-${taskIndex}`);
                    const newComment = document.createElement('div');
                    newComment.className = 'comment-item';
                    newComment.style.cssText = 'margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;';
                    newComment.innerHTML = `<p><strong>${data.comment.name}</strong> (${data.comment.date}):</p>
                                            <p>${data.comment.text}</p>`;
                    commentsContainer.appendChild(newComment);
    
                    // Clear input
                    commentInput.value = '';
                } else {
                    alert('Error adding comment: ' + data);
                }
            })
            .catch((error) => {
                console.error('Error:', error);
                //alert('Error adding comment.');
                return;
            });
        });
    });
});

async function fetchData(url = '', options = {}) {
    try {
        const response = await fetch(url, options); 
        if (!response.ok) { 
            throw new Error('Network response was not ok'); 
        } 
        
        const result = await response.json(); 
        if (result.success) { 
            return result.data; 
        } else { 
            alert(result.message); 
            return null;
        }
        
    } catch(error) {
        console.error('Error:', error);
        return null;
    };
}

async function getTask(task_id = 0) {
    const url = atomic.ajaxurl + '?action=get_task'
        + '&nonce=' + encodeURIComponent(atomic.nonce) 
        + '&taskId=' + encodeURIComponent(task_id); 
    const data = { 
        method: 'GET', 
        headers: { 'Content-Type': 'application/json' } 
    };
    
    const data_result = await fetchData(url, data);
    // console.log('data_result: ', data_result);
    return data_result;
}

function handleTaskFormSubmit() {
    const task_id           = document.getElementById('taskId').value;
    const project_id        = document.getElementById('projectId').value;
    const assigned_user     = document.getElementById('taskAssignee').value;
    const task_title        = document.getElementById('taskTitle').value;
    const task_description  = document.getElementById('taskDescription').value;
    const task_status       = document.getElementById('taskStatus').value;

    if (!task_title || !task_description) {
        alert('Task Title and Description is a must.');
        return;
    } 

    const formData = new FormData();
    formData.append('nonce', atomic.nonce);
    formData.append('action', 'submit_task');
    formData.append('projectId', project_id);
    formData.append('assignedUser', assigned_user);
    formData.append('taskTitle', task_title);
    formData.append('taskDescription', task_description);
    formData.append('taskId', task_id);
    formData.append('taskStatus', task_status);

    fetchData(atomic.ajaxurl, {
        method: 'POST',
        body: formData,
    }).then(result_data => {
        console.log('post result: ', result_data);
    });
}

function toggleModalForm(action = 'open') {
    if(action === 'open') {
        document.querySelector('#taskPopup').style.display = 'block';
        document.querySelector('.modal-content').style.display = 'none';
        document.querySelector('#modalLoader').style.display = 'flex';
    } else if(action === 'hide-loader') {
        document.querySelector('.modal-content').style.display = 'block';
        document.querySelector('#modalLoader').style.display = 'none';
    } else {
        // close
        document.querySelector('#taskPopup').style.display = 'none';
    }
}

function populateForm(form_data) {
    toggleModalForm('hide-loader');

    if(form_data) {        
        const formTitle         = document.getElementById('formTitle');
        const taskId            = document.getElementById('taskId');
        const taskTitle         = document.getElementById('taskTitle');
        const taskDescription   = document.getElementById('taskDescription');
        const addTaskButton     = document.getElementById('addTaskButton');

        addTaskButton.innerText = "Update Task";
        formTitle.innerText     = "EDIT Task";
        taskId.value            = form_data.task_id;
        taskDescription.value   = form_data.description;
        taskTitle.value         = form_data.title;
        jQuery('#taskAssignee').val(form_data.assignee['ID']).trigger('change');
    }
}

function taskModalEventHandler() {
    const formModal = document.getElementById('taskPopup');

    if(formModal) {
        
        const openModalButton = document.querySelector('#openPopup');
        const closeButton = document.querySelector('.close-button');
        const cancelButton = document.querySelector('#cancelTaskButton');

        // NOT WORKING
        // show the modal
        openModalButton.addEventListener('click', function() {
            console.log('OPENING MODAL');
            //formModal.style.display = 'block';
            toggleModalForm('open');
            setTimeout(function(){
                toggleModalForm('hide-loader');
            }, 100);
        });

        // close the modal
        closeButton.addEventListener('click', function() {
            console.log('closeButton closing modal');
            //formModal.style.display = 'none';
            toggleModalForm('close');
        });

        // cancel button
        cancelButton.addEventListener('click', function() {
            console.log('cancelButton closing modal');
            //formModal.style.display = 'none';
            toggleModalForm('close');
        });        

        // close modal when user click outside
        window.addEventListener('click', function(event) {
            if (event.target == formModal) {
                // formModal.style.display = 'none';
                toggleModalForm('close');
            }
        });      
    }    

    const addTaskButton = document.getElementById('addTaskButton');
    if(addTaskButton) {
        addTaskButton.addEventListener('click', handleTaskFormSubmit);
    }
}

function prePopulateForm(defaultData) {
    if(saveTaskButton) {
        // Save a new task
        saveTaskButton.addEventListener('click', function () {
            const actionFeedback = document.getElementById('action-feedback');
            const taskTitle = document.getElementById('task-title').value.trim();        
            const taskDescription = document.getElementById('task-description').value.trim();

            let process = true;
            let titleFeedback;
            let descriptionFeedback;
            console.log(taskTitle);
            if (!taskTitle) {            
                //alert('Please enter a task description.');
                titleFeedback = document.createElement('span');

                //titleFeedback.className = 'comment-item';
                //titleFeedback.style.cssText = 'margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;';
                titleFeedback.innerHTML = "Task Title is Required!";

                process = false;
            } else {
                titleFeedback = null;
            }
            
            if (!taskDescription) {            
                // alert('Please enter a task description.');
                descriptionFeedback = document.createElement('span');

                //titleFeedback.className = 'comment-item';
                //titleFeedback.style.cssText = 'margin-bottom: 10px; border: 1px solid #ddd; padding: 5px;';
                descriptionFeedback.innerHTML = "Task Description is Required!";            
                process = false;
            } else {
                descriptionFeedback = null;
            }

            console.log('clearing reset');
            actionFeedback.innerHTML = '';
            actionFeedback.classList.add('block');
            if(!process) {            
                if(titleFeedback) {
                    console.log('title feedback');
                    actionFeedback.appendChild(titleFeedback);
                }

                if(descriptionFeedback) {
                    console.log('description feedback');
                    actionFeedback.appendChild(descriptionFeedback);
                }
                actionFeedback.classList.add('error');
                return;
            } else {
                actionFeedback.classList.remove('error');
            }

            // 16mby-fvUF6IzAQD2dftVfKaQNq2EQHTm

            let formData = new FormData();
            formData.append('action', 'add_task');                
            formData.append('task_assignee', document.getElementById('taskAssignee').value);
            formData.append('post_id', document.getElementById('post_ID').value); // WordPress post ID
            formData.append('task_title', taskTitle);
            formData.append('task_description', taskDescription);
            formData.append('nonce', taskMetaBox.nonce);

            console.log('posting to: ' + taskMetaBox.ajaxurl);
            
            fetch(taskMetaBox.ajaxurl, {
                method: 'POST',
                body: formData,
            })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        //location.reload(); // Reload the page to show the new task
                        console.log('SUCCESS');
                        const resultFeedback = document.createElement('span');
                        resultFeedback.innerHTML = data.data.message;
                        actionFeedback.append(resultFeedback);                    
                        actionFeedback.classList.add('success');
                        setTimeout(function(){
                            // reset values to defaults
                            //resultFeedback.remove();
                            //actionFeedback.remove();
                            formData = new FormData(); 
                            // location.reload();
                        }, 5000);
                    } else {
                        alert(data.message || 'Error adding task.');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    //alert('Error adding task.');

                    return false;
                });
        });
    }
}


// carefull with adding event listener
// make sure the target exists first, note that this script will be enqueue on all the admin pages not just task custom post related.    
document.addEventListener('DOMContentLoaded', function() {    
    document.querySelectorAll('.edit-task').forEach(function (link) {        
        link.addEventListener('click', function () {

            const taskId = this.getAttribute('data-task-id');            
            toggleModalForm('open');
            getTask(taskId).then(data_result => {                 
                // document.getElementById('modalLoader').style.display = 'none';
                //console.log('data_result: ', data_result); 
                populateForm(data_result);                
            });
            
            return false;
        });
    });


    // ADD TASK MODAL handler for SHOW and HIDE
    taskModalEventHandler();

});


document.querySelectorAll('.trigger').forEach(function (button) {
    console.log('tigger list:');
    console.log(button);
    button.addEventListener('click', function () {
        console.log('Button trigger clicked');
    });
});


   


/*document.addEventListener('DOMContentLoaded', function () {
    hiddenContainer.addEventListener("click", (e) => {
        if (e.target.classList.contains("clickable")) {
            console.log(`${e.target.textContent} clicked!`);
        }
    });
 
  document.addEventListener("click", (e) => {
    const hiddenContainer = document.querySelectorAll("comment-form")[0];
    if(hiddenContainer) {
        if (e.target.id === "clickable" && hiddenContainer.style.display !== "none") {
            console.log("Button clicked!");
        }
    }
    if (e.target.id === "clickable" && hiddenContainer.style.display !== "none") {
      console.log("Button clicked!");
    }
  })
});
*/
/*
const hiddenContainer = document.getElementById("comment-form");

  // Observe changes in the 'style' attribute
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.target.style.display !== "none") {
        console.log("Container became visible!");

        // Select all buttons with the 'clickable' class
        const buttons = document.querySelectorAll(".clickable");
        buttons.forEach((button) => {
          // Attach click event listener to each button
          button.addEventListener("click", () => {
            console.log(`${button.textContent} clicked!`);
          });
        });
      }
    });
  });

  observer.observe(hiddenContainer, { attributes: true, attributeFilter: ["style"] });

  // Toggle visibility of the container
  const toggle = document.getElementById("toggle");
  toggle.addEventListener("change", () => {
    hiddenContainer.style.display = toggle.checked ? "block" : "none";
  });
*/
    /*


    let taskIndex = document.querySelectorAll('#tasks-container .task-item').length;

    // Add Task Button
    document.getElementById('add-task').addEventListener('click', function () {       

        const taskItem = document.createElement('div');
        taskItem.classList.add('task-item');
        taskItem.style.cssText = 'margin-bottom: 15px; border: 1px solid #ccc;';

        // Create Task Name Input
        const taskNameInput = document.createElement('input');
        taskNameInput.type = 'text';
        taskNameInput.name = `work_tasks[${taskIndex}][name]`;
        taskNameInput.placeholder = 'Task Name';
        //taskNameInput.style.cssText = 'width: 100%; margin-bottom: 5px;';
        taskNameInput.classList.add('task-name');

        // Create Task Description Textarea
        const taskDescriptionTextarea = document.createElement('textarea');
        taskDescriptionTextarea.name = `work_tasks[${taskIndex}][description]`;
        taskDescriptionTextarea.placeholder = 'Task Description';
        //taskDescriptionTextarea.style.cssText = 'width: 100%;';
        taskDescriptionTextarea.classList.add('task-description');

        // Create Remove Task Button
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.textContent = 'Remove';
        removeButton.classList.add('remove-task');
        //removeButton.style.cssText = 'background: red; color: white; border: none; padding: 5px;';
        removeButton.addEventListener('click', function () {
            taskItem.remove();
        });

        // Append Elements to Task Item
        taskItem.appendChild(taskNameInput);
        taskItem.appendChild(taskDescriptionTextarea);
        taskItem.appendChild(removeButton);

        document.getElementById('tasks-container').appendChild(taskItem);

        // Increment Task Index
        taskIndex++;
    });

    // Handle Remove Task Button
    document.getElementById('tasks-container').addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-task')) {
            event.target.closest('.task-item').remove();
        }
    });
});*/


/*jQuery(document).ready(function ($) {
  let taskIndex = $('#tasks-container .task-item').length;

  $('#add-task').on('click', function () {
      const taskTemplate = `
          <div class="task-item">
              <input type="text" name="work_tasks[${taskIndex}][name]" placeholder="Task Name" />
              <textarea name="work_tasks[${taskIndex}][description]" placeholder="Task Description"></textarea>
              <button type="button" class="remove-task">Remove</button>
          </div>`;
      $('#tasks-container').append(taskTemplate);
      taskIndex++;
  });

  $('#tasks-container').on('click', '.remove-task', function () {
      $(this).closest('.task-item').remove();
  });
});*/
