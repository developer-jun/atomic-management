<?php

namespace Domain;

class ProjectCategoryTaxonomy {
  public function __construct(private $post_type) {
    add_action('init', [$this, 'registerCategoryTaxonomy']);
  }
  
  // Function to register the custom taxonomy 
  public function registerCategoryTaxonomy() { 
    $labels = array( 
        'name'              => _g('Project Categories'), 
        'singular_name'     => _g('Project Category'), 
        'search_items'      => _g('Search Project Categories'), 
        'all_items'         => _g('All Project Categories'), 
        'parent_item'       => _g('Parent Project Category'), 
        'parent_item_colon' => _g('Parent Project Category:'), 
        'edit_item'         => _g('Edit Project Category'), 
        'update_item'       => _g('Update Project Category'), 
        'add_new_item'      => _g('Add New Project Category'), 
        'new_item_name'     => _g('New Project Category Name'), 
        'menu_name'         => _g('Project Categories'), 
    ); 
    $args = array( 
        'labels'            => $labels, 
        'hierarchical'      => true, // True for hierarchical like categories 
        'show_in_menu'      => true, 
        'show_admin_column' => true, 
        'query_var'         => true, 
        'rewrite'           => array('slug' => 'project-category'), 
    ); 
    register_taxonomy('project_category', array($this->post_type), $args); 
  }
}