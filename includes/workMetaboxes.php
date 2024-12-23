<?php

namespace Domain;

//use Domain\Models\MetaBox;
//use Domain\Models\MetaBoxField;

class WorkMetaboxes {

  private $metaboxes = [];
  private $metabox_task = 'work_tasks';
  private $metabox_name = 'work_tasks';
  private $plugin_url = '';

  public function __construct($plugin_url) {

    $this->plugin_url  = $plugin_url;

    add_action('add_meta_boxes', [$this, 'registerMetaBoxes']);

    // save meta boxes
    add_action('save_post', [$this, 'saveProjectMetaBoxesData']);

    //$this->generateMetaboxesData();
    add_action('admin_enqueue_scripts', [$this, 'enqueueTaskMetaboxScripts']);

    add_filter('the_content', [$this, 'renderWorkTasks']);

    add_action('wp_ajax_add_task_comment', [$this, 'handle_add_task_comment']);

    add_action('wp_ajax_add_task', [$this, 'handleTaskAjaxSubmission']);

    //add_action('wp_ajax_submit_task', 'handleTaskSubmission');
  }

  function enqueueTaskMetaboxScripts($hook) {
    if ('post.php' === $hook || 'post-new.php' === $hook) {
        wp_enqueue_script('task-meta-box', $this->plugin_url . '/public/js/tasks-meta-box.js', [], false, true);
        wp_localize_script('task-meta-box', 'taskMetaBox', [
            'ajaxurl' => admin_url('admin-ajax.php'), // Pass AJAX URL
            'nonce' => wp_create_nonce('form_nonce'), // Generate a nonce for security
        ]);
    }
  }
  
  // Retrieve all Tasked related to this particular Work
  public function renderWorkTasks($content) {
    if (is_singular('work')) {
        // get_post_meta - can retrieve multiple post_meta with the same name (refer to milestone from projectMetaBoxes)
        $tasks = get_post_meta(get_the_ID(), $this->metabox_task, true);        
        if ($tasks) {
            $content .= '<h3>Tasks</h3><ul>';
            foreach ($tasks as $task) {
                $content .= '<li><strong>' . esc_html($task['name']) . ':</strong> ' . esc_html($task['description']) . '</li>';
            }
            $content .= '</ul>';
        }
    }
    return $content;
  }

  // Function to save the meta box data 
  public function saveProjectMetaBoxesData($post_id) {
    if (!isset($_POST[$this->metabox_name .'_nonce']) 
      || !wp_verify_nonce($_POST[$this->metabox_name .'_nonce'], 'save_'.$this->metabox_name)) {
      return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $tasks = $_POST[$this->metabox_name] ?? [];
    $sanitized_tasks = array_map(function ($task) {
      return [
          'name' => sanitize_text_field($task['name']),
          'description' => sanitize_textarea_field($task['description']),
          'comments' => sanitize_textarea_field($task['comments'] ?? ''), // Save comments
      ];
    }, $tasks);

    update_post_meta($post_id, $this->metabox_name, $sanitized_tasks);
  }

  public function renderWorkTaskMetaBox($post) {
    $tasks = get_post_meta($post->ID, $this->metabox_name, false); // Retrieve tasks array
    krsort($tasks);    
    $active_task = 'active-task ';
    ?>
    <div id="task-meta-box">
        <div id="task-list">            
            <?php if (!empty($tasks) && is_array($tasks)) : ?>
                <strong class="inline-alert">Total Task Found: <?php echo count($tasks) ?></strong>
                <ul>
                    <?php foreach ($tasks as $index => $task) : ?>
                        <li>
                            <div class="[<?php echo $index ?>] task-item <?php if($index === 1): echo 'active-task'; else: echo 'inactive-task'; endif; ?>">
                                <div class="task-header">                                    
                                    <h3><?php echo esc_html($task['title']); ?></h3> 
                                    <div>
                                        <span class="task-date"><?php echo esc_html($task['date_added']); ?></span>
                                        <span class="task-status"><strong>Status:</strong> <?php echo esc_html($task['status'] ?? 'Pending'); ?></span>
                                    </div>
                                </div>
                                <div class="task-content">  
                                    <p><?php echo esc_html($task['description']); ?></p>                                    
                                </div>
                            </div>

                            <div id="comments-<?php echo $index; ?>">
                                <h4>Comments:</h4>
                                <?php if (!empty($task['comments']) && is_array($task['comments'])) : ?>
                                    <ul>
                                        <?php foreach ($task['comments'] as $comment) : ?>
                                            <li style="border-bottom: 1px solid #ddd; margin-bottom: 5px;">
                                                <p><strong><?php echo esc_html($comment['name']); ?></strong> (<?php echo esc_html($comment['date']); ?>):</p>
                                                <p><?php echo esc_html($comment['comment']); ?></p>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else : ?>
                                    <p>No comments yet.</p>
                                <?php endif; ?>
                            </div>

                            <div class="add-comment-box">
                                <textarea class="comment-input" data-task-index="<?php echo $index; ?>" placeholder="Add a comment"></textarea>
                                <button type="button" class="submit-comment" data-task-index="<?php echo $index; ?>">Submit</button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No tasks added yet.</p>
            <?php endif; ?>
        </div>

        <div id="add-task-container">
            <?php /*
            <button type="button" id="add-task-button">Add Task</button>
            <div id="task-form" style="display: none;">
                <h4>Add New Task:</h4>
                <textarea id="new-task-description" placeholder="Task description"></textarea>
                <button type="button" id="save-task-button">Save Task</button>
            </div>
            */
            ?>
            <div id="taskPopup" class="task-popup">
                <div class="task-popup-content">
                    <div class="modal-head">
                        <span class="close-button">&times;</span>
                        <h2>Add Task</h2>
                    </div>
                    <div id="action-feedback" class="feedback"></div>
                    <div id="taskForm" class="modal-content">
                        <div class="field-group">
                            <label for="task-title">Title</label>
                            <input type="text" id="task-title" name="task-title" required>
                        </div>
                        <div class="field-group">
                            <label for="task-description">Description</label>
                            <textarea id="task-description" name="task-description" required></textarea>
                        </div>
                        <div class="field-group align-right">
                            <button id="save-task" type="button">Save Task</button>
                        </div>
                    </div>
                </div>
            </div>
            <button id="openPopup">Add New Task</button>

        </div>
    </div>
    <?php
  }


  public function renderWorkTaskMetaBox2($post) {
      $tasks = get_post_meta($post->ID, $this->metabox_name, true) ?: [];
      wp_nonce_field('save_'. $this->metabox_name, $this->metabox_name. '_nonce');
  
      echo '<div id="tasks-container">';
      foreach ($tasks as $index => $task) {
        echo '<div class="task-item">';
        echo '<div class="task-name">'. esc_attr($task['name'] ?? '') .'</div>';
        echo '<div class="task-description">'. esc_attr($task['description'] ?? '') .'</div>';
        // comments
        /*
        echo '<textarea placeholder="Write a comment..." class="comment-input" data-task-index="' . $index . '" style="width: 100%;"></textarea>
            <!--<textarea class="task-description comment-input" data-task-index="' . $index . '" name="'. $this->metabox_name .'[' . $index . '][comments]" placeholder="Task Comments (optional)" style="width: 100%;">' . esc_textarea($task['comments'] ?? '') . '</textarea>-->
            <!--<div class="justify-right"><button type="button" class="remove-task">Remove</button></div>-->
            <button type="button" class="submit-comment" data-task-index="' . $index . '" style="margin-top: 5px; background: blue; color: white; padding: 5px;">Add Comment</button>
        </div>';
        */
      }
      echo '</div>';
      echo '<button type="button" id="add-task">Add Task</button>';
  }

  // attached and called by 'Project' - custom post type
  public function registerMetaBoxes() {
    add_meta_box(
      $this->metabox_name,        // Meta box ID
      'Work Tasks',        // Meta box title
      [$this, 'renderWorkTaskMetaBox'], // Callback function to render the meta box
      'work',              // Post type
      'normal',               // Context
      'high'                  // Priority
    );    
  }

  function handleTaskAjaxSubmission() {
    // Check nonce for security
    check_ajax_referer('form_nonce', 'nonce');
    // Get POST data
    // Save task (this example assumes tasks are saved as custom post type)
    /*$task_id = wp_insert_post(array(
        'post_title' => $task_title,
        'post_content' => $task_description,
        'post_status' => 'publish',
        'post_type' => 'task',
    ));*/    

    $new_task_id = wp_insert_post(array(
        'post_title' => sanitize_text_field($_POST['task_title']),
        'post_content' => sanitize_textarea_field($_POST['task_description']),
        'post_status' => 'publish',
        'post_type' => 'work_task',
    ));

    /*
    $date = current_time('Y-m-d H:i:s');
    $task_data = array(
        'title' => sanitize_text_field($_POST['task_title']),
        'description' => sanitize_textarea_field($_POST['task_description']), 
        'date_added' => $date,
        'status' => 'publish',
    );
    $new_task_id = add_post_meta(intval($_POST['post_id']), $this->metabox_task, $task_data); 
    */

    if ($new_task_id) {
        wp_send_json_success(array('message' => 'Task added successfully!'));
    } else {
        wp_send_json_error(array('message' => 'Failed to add task.'));
    }
  }

  public function handle_add_task_comment() {    
    // Check user permissions and nonce
    check_ajax_referer('form_nonce', 'nonce');

    $post_id = intval($_POST['post_id']);
    $task_index = intval($_POST['task_index']);
    $comment_text = sanitize_textarea_field($_POST['comment_text']);

    $current_user = wp_get_current_user();
    $user_name = $current_user->exists() ? $current_user->display_name : 'Guest';

    $tasks = get_post_meta($post_id, $this->metabox_name, true) ?: [];
    $date = current_time('Y-m-d H:i:s');

    $new_comment = [
        'text' => $comment_text,
        'date' => $date,
        'name' => $user_name,
    ];
    
    $tasks[$task_index]['comments'][] = $new_comment;
    update_post_meta($post_id, $this->metabox_name, $tasks);
    
    wp_send_json_success(['comment' => $new_comment]);
  }
  
}