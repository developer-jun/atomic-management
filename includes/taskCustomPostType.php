<?php

namespace Domain;

use Domain\Models\Comment;
use Domain\TaskTaxonomies;
//use Domain\ProjectMetaboxes;
//use Domain\ProjectCategoryTaxonomy;
//use Domain\ProjectStatusTaxonomy;

class TaskCustomPostType {
  private $post_type = 'task';
  private $task_taxonomy_name = 'task_status';
  private $comment;
  //private $task_metabox;

  public function __construct(private $plugin_root_url) {
    $this->comment = new Comment();
    // Enqueue custom admin styles 
    add_action('admin_enqueue_scripts', [$this, 'enqueueTaskScripts']);

    //$category_taxonomy = new ProjectCategoryTaxonomy($this->post_type);
    //$status_taxonomy = new ProjectStatusTaxonomy($this->post_type);
    
    // Hook into the 'init' action to register the custom post type
    add_action('init', [$this, 'registerTaskCustomPostType']);

    // Taxonomy related to Task Custom Post
    $task_status = new TaskTaxonomies($this->post_type);
    
    //$this->task_metabox = new WorkMetaboxes($plugin_url);

    add_action('admin_menu', [$this, 'removeTaskFromMenu']);

    add_action('wp_ajax_submit_task', [$this, 'handleAjaxTaskSubmission']);

    add_action('add_meta_boxes', [$this, 'addCommentsMetabox']);

    add_action('wp_ajax_get_task_comments', [$this, 'getTaskComments']);
    add_action('wp_ajax_nopriv_get_task_comments', [$this, 'getTaskComments']);

    add_action('wp_ajax_get_task', [$this, 'getTask']);
    add_action('wp_ajax_nopriv_get_task', [$this, 'getTask']);

    add_action('wp_ajax_submit_task_comment', [$this, 'handleTaskCommentSubmission']); 
    add_action('wp_ajax_nopriv_submit_task_comment', [$this, 'handleTaskCommentSubmission']);
  }

  function addCommentsMetabox() {
    add_meta_box(
        'commentsdiv', // Meta box ID
        __('Comments'), // Meta box title
        'post_comment_meta_box', // Callback function
        'task', // Post type
        'normal', // Context
        'high' // Priority
    );
  }

  function getTaskComments() {
    check_ajax_referer('task_forms_nonce', 'nonce');

    $task_id = intval($_POST['taskId']);

    // Retrieve comments for the specified task
    $comments = $this->comment->getComments($task_id);
    $comments_array = array();

    foreach ($comments as $comment) {
        $comments_array[] = array(
            'author' => $comment->comment_author,
            'content' => $comment->comment_content,
            'date' => $comment->comment_date,
        );
    }

    wp_send_json_success($comments_array);
  }

  function getTask() {
    check_ajax_referer('task_forms_nonce', 'nonce');

    $task_id = intval($_GET['taskId']);

    $task_post = get_post($task_id); 
    if ($task_post) { 
        $assignee_id = get_post_meta($task_id, 'assigned_user', true); 
        $assignee = get_userdata($assignee_id); 
        $task_data = array( 
            'task_id' => $task_id,
            'title' => $task_post->post_title, 
            'description' => $task_post->post_content, 
            'assignee' => $assignee ? [
                'display_name' => $assignee->display_name,
                'ID' => $assignee->ID
            ] : [], 
        ); 
        
        wp_send_json_success($task_data); 
    } else {
        wp_send_json_error(array('message' => 'Task not found.'));
    }
  }


  public function handleTaskCommentSubmission() {
    
    check_ajax_referer('task_forms_nonce', 'nonce'); 
    
    $comment_author = sanitize_text_field($_POST['commentAuthor']); 
    $comment_text = sanitize_textarea_field($_POST['commentText']); 
    $task_id = intval($_POST['taskId']); 

    $error = [];
    // do server validation
    if(!$task_id) {
        $error[] = 'Task not found!';
    }
    if(!$comment_author) {
        $error[] = 'Author is Required!';
    }
    if(!$comment_text) {
        $error[] = 'Comment is Required!';
    }
    // Prepare the comment data 
    $commentdata = array( 
        'comment_post_ID' => $task_id, 
        'comment_author' => $comment_author, 
        'comment_content' => $comment_text, 
        'comment_approved' => 1, 
    ); 
    $comment_id = 0;
    if(empty($error)) {
        // Insert the comment into the database 
        $comment_id = wp_insert_comment($commentdata); 
    }    
    
    if ($comment_id) { 
        wp_send_json_success(array('message' => 'Comment added successfully!', 'data' => $commentdata)); 
    } else { 
        wp_send_json_error(array('message' => $error)); 
    }
  }

  public function handleAjaxTaskSubmission() {
    check_ajax_referer('task_forms_nonce', 'nonce');

    $task_id = intval($_POST['taskId']);
    $task_title = sanitize_text_field($_POST['taskTitle']);
    $task_description = sanitize_textarea_field($_POST['taskDescription']);
    $project_id = intval($_POST['projectId']);
    $task_assignee = intval($_POST['assignedUser']);
    if($task_id) {
        $data = array(
            'ID' => $task_id,
            'post_title' => $task_title,
            'post_content' => $task_description,
            'meta_input' => array(
              'assigned_user' => $task_assignee,
             )
        );
          
        $result = wp_update_post( $data );
        // Set the status term for the task 
        wp_set_post_terms($task_id, array(intval($_POST['taskStatus'])), $this->task_taxonomy_name);
        if($result) {
            wp_send_json_success(array(
                'message' => 'Task updated successfully!', 
                'task_id' => $result
                )
            );
        } else {
            wp_send_json_error(array('message' => 'Failed to add task.'));
        }
    } else {

        $new_task_id = wp_insert_post(array(
            'post_title' => $task_title,
            'post_content' => $task_description,
            'post_status' => 'publish',
            'post_type' => $this->post_type,
            'post_parent' => $project_id,
        ));

        if ($new_task_id) {
            // Assign the task to the selected user 
            update_post_meta($new_task_id, 'assigned_user', $task_assignee);
            // Set the status term for the task 
            wp_set_post_terms($new_task_id, array(intval($_POST['taskStatus'])), $this->task_taxonomy_name);
            


            wp_send_json_success(array(
                'message' => 'Task added successfully!', 
                'task_id' => $new_task_id
                )
            );
        } else {
            wp_send_json_error(array('message' => 'Failed to add task.'));
        }
    }
  }

  // Function to enqueue admin styles 
  public function enqueueTaskScripts($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_script(
            'task-post-type', 
            $this->plugin_root_url . '/public/js/tasks-meta-box.js', 
            [], 
            false, 
            true
        );
        wp_localize_script(
            'task-post-type', 
            'atomic', [
                'ajaxurl' => admin_url('admin-ajax.php'), // Pass AJAX URL
                'nonce' => wp_create_nonce('task_forms_nonce'), // Generate a nonce for security
            ]
        );

        /*wp_enqueue_script('atomic-admin-ajax', 
            $this->plugin_root_url . '/public/js/atomic-ajax.js', 
            array('jquery'), 
            null, 
            true
        );*/         
    }

    // select2 dropdown/filter
    wp_enqueue_style(
        'select2', 
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css'
    );
    wp_enqueue_script(
        'select2', 
        'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', 
        array('jquery'), 
        null, 
        true
    );
    // when using local copy of select2
    /*wp_enqueue_script(
        'my-select2-init', 
        $this->plugin_root_url . '/js/my-select2-init.js', 
        array('select2'), 
        null, 
        true
    );*/  
}

  // Function to register the custom post type
  public function registerTaskCustomPostType() {
    register_post_type(
      'task', 
      array( 
        'labels' => array(
          'name' => 'Tasks', 
          'singular_name' => 'Task'
        ), 
        'public' => true, 
        'supports' => array(
          'title', 
          'editor', 
          'comments', 
          'custom-fields'
        ), 
        'has_archive' => true, 
        'rewrite' => array(
          'slug' => 'tasks'
        ), 
        'hierarchical' => true, 
      )
    );
  }

  // called directly from projectMetaboxes where it is added as a metabox
  public function renderTasksAsMetabox() {
    $author_name = '';
    $user = wp_get_current_user();    
    if($user) {
        $author_name = esc_html( $user->user_login );
        // $user_name = $current_user->display_name;
    }
    // $tasks = get_post_meta($post->ID, $this->metabox_name, false); // Retrieve tasks array
    $tasks = get_children(array('post_parent' => get_the_ID(), 'post_type' => $this->post_type));
    krsort($tasks);    
    $active_task = 'active-task ';

    $task_statuses = get_terms( array(
        'taxonomy'   => $this->task_taxonomy_name,
        'hide_empty' => false,
    ) );
    sort($task_statuses); 
    ?>
    <div id="task-meta-box">
        <div id="task-list">
            <?php if (!empty($tasks) && is_array($tasks)) : ?>
                <div class="content-right">
                    <strong class="inline-alert">Total Task Found: <?php echo count($tasks) ?></strong>
                </div>
                <ul>               
                    <?php 
                    $counter = 0;
                    foreach ($tasks as $task) : 
                        $assigned_user_id = get_post_meta($task->ID, 'assigned_user', true);

                        $statuses = wp_get_post_terms($task->ID, $this->task_taxonomy_name);

                        $user_info = [];
                        if ($assigned_user_id) {
                            $user_info = get_userdata($assigned_user_id);                            
                        }    
                        ?>
                        <li>
                            <div class="task-item <?php if($counter === 0): echo 'active-task'; else: echo 'inactive-task'; endif; ?>">
                                <div class="task-header">
                                    <div>
                                        <span class="task-date"><?php echo esc_html($task->post_date); ?></span>
                                        <span class="task-status">
                                            <strong>Status:</strong>
                                            <em>
                                            <?php 
                                            if(!empty($statuses)) {
                                                echo $statuses[0]->name;
                                            } else {
                                                echo '--';
                                            }
                                            ?>
                                            </em>
                                        </span><br />
                                    </div>
                                    <div class="divider">
                                        <span class="task-assign"><strong>Assigned To:</strong> <em><?php echo esc_html($user_info->display_name); ?></em></span>
                                    </div>
                                    <h3><?php echo esc_html($task->post_title); ?></h3>                                    
                                </div>
                                <div class="task-content">  
                                    <p><?php echo esc_html($task->post_content); ?></p>                                    
                                </div>
                                <a class="edit-task" data-task-id="<?php echo $task->ID; ?>" href="#">Edit</a>
                            </div>
                            <?php  
                                $comments = $this->comment->getComments($task->ID);
                            ?>
                            <div id="comments-<?php echo $index; ?>" class="comment-wrapper">
                                <h4>Comments:</h4>
                                <?php if (!empty($comments) && is_array($comments)) : ?>
                                    <ul>
                                        <?php foreach ($comments as $comment) : ?>
                                            <li style="border-bottom: 1px solid #ddd; margin-bottom: 5px;">
                                                <p><strong><?php echo esc_html($comment['author']); ?></strong> (<?php echo esc_html($comment['date']); ?>):</p>
                                                <p><?php echo esc_html($comment['content']); ?></p>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p>No comments yet.</p>
                                <?php endif; ?>
                            </div>
                            <div class="comment-form">
                                <input class="trigger-checkbox" type="checkbox" id="AddComment<?php echo $task->ID; ?>" name="AddComment<?php echo $index; ?>"/>
                                <label class="trigger" for="AddComment<?php echo $task->ID; ?>" class="side-label">&nbsp;</label>                                
                                <div class="add-comment-box helper-show">
                                    <input type="hidden" class="taskId-input" data-task-index="<?php echo $task->ID; ?>" value="<?php echo $task->ID; ?>">
                                    <input type="text" class="commentAuthor-input" data-task-index="<?php echo $task->ID; ?>" value="<?php echo $author_name ?>" required />
                                    <textarea class="commentText-input" data-task-index="<?php echo $task->ID; ?>" placeholder="Add a comment"></textarea>
                                    <!--<button type="button" class="submit-comment check" data-task-index="<?php echo $index; ?>">Submit <i class="fa-solid fa-check"></i></button>-->
                                    <button type="button"  class="my-btn processing submit-comment" data-task-index="<?php echo $task->ID; ?>">Submit Comment</button>
                                </div>
                                
                            </div>
                        </li>
                    <?php $counter++;
                        endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No tasks added yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <div id="taskPopup" class="task-popup project">
        <div class="task-popup-content">            
            <form id="taskForm">
                <div class="modal-head">
                    <span class="close-button">&times;</span>
                    <h2 class="form-title" id="formTitle">Add Task</h2>
                </div>
                <div id="modalLoader" class="lds-dual-ring"></div>
                <div class="modal-content">                    
                    <input type="hidden" id="projectId" value="<?php echo get_the_ID(); ?>">
                    <input type="hidden" id="taskId" value="0">
                    <label for="taskAssignee">Assign to</label> 
                    <select class="select" id="taskAssignee" name="taskAssignee" style="width: 100%" required> 
                        <option value="">Select User</option> 
                        <?php
                            $users = get_users();
                            foreach ($users as $user) {
                                $option_value = esc_html($user->display_name);
                                if($user->roles) {
                                    $option_value .= ' [' . implode(', ',$user->roles) . ']';
                                }                        
                                echo '<option value="' . esc_attr($user->ID) . '">' . $option_value . '</option>'; 
                            } 
                        ?> 
                    </select>
                    
                    <label for="taskTitle">Title</label>
                    <input type="text" id="taskTitle" name="taskTitle" required>
                    <label for="taskDescription">Description</label>
                    <textarea id="taskDescription" name="taskDescription" required></textarea>
                    <label for="taskStatus">Status</label>
                    <select class="select" id="taskStatus" name="taskStatus" style="width: 100%"> 
                    <?php foreach($task_statuses as $task_status) {
                        echo '<option value="'.$task_status->term_id.'">'.$task_status->name.'</option>';
                        } ?>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="form-btn form-submit" id="addTaskButton">Add Task</button>
                    <button type="button" class="form-btn form-cancel" id="cancelTaskButton">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <button type="button" id="openPopup">Add New Task</button>    
    <?php
  }
  function removeTaskFromMenu() {
    //remove_menu_page('edit.php?post_type='. $this->post_type);
  } 

}