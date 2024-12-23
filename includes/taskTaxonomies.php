<?php 
namespace Domain;

class TaskTaxonomies {
  public function __construct(private $post_type) {
    add_Action('init', [$this, 'registerTaskStatusTaxonomy']);
  }

  // Function to register the custom taxonomy 
  public function registerTaskStatusTaxonomy() { 
    $labels = array( 
        'name'              => _g('Task Statuses'), 
        'singular_name'     => _g('Task Status'), 
        'search_items'      => _g('Search Task Statuses'), 
        'all_items'         => _g('All Task Statuses'), 
        'parent_item'       => _g('Parent Task Status'), 
        'edit_item'         => _g('Edit Task Status'), 
        'update_item'       => _g('Update Task Status'), 
        'add_new_item'      => _g('Add New Task Status'), 
        'new_item_name'     => _g('New Task Status Name'), 
        'menu_name'         => _g('Task Statuses'), 
    ); 
    $args = array( 
        'labels'            => $labels, 
        'hierarchical'      => true, // True for hierarchical like categories 
        'show_in_menu'      => true, 
        'show_admin_column' => true, 
        'query_var'         => true, 
        'rewrite'           => array('slug' => 'task-status'), 
    ); 
    register_taxonomy('task_status', array($this->post_type), $args); 
  }
  
}