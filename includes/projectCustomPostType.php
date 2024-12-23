<?php

namespace Domain;

use Domain\ProjectMetaboxes;
use Domain\ProjectCategoryTaxonomy;
use Domain\ProjectStatusTaxonomy;

use Domain\TaskCustomPostType;

class ProjectCustomPostType {
  private $post_type = 'atom_project';
  private $project_metabox;
  private $custom_post_type_project;

  public function __construct(private $plugin_root_url) {
    $category_taxonomy = new ProjectCategoryTaxonomy($this->post_type);
    $status_taxonomy = new ProjectStatusTaxonomy($this->post_type);
    
    // child of Project
    $this->custom_post_type_task = new TaskCustomPostType($this->plugin_root_url);
    // Hook into the 'init' action to register the custom post type
    add_action('init', [$this, 'registerProjectCustomPostType']);
    
    $this->project_metabox = new ProjectMetaboxes($this->custom_post_type_task);  
  }  

  // Function to register the custom post type
  public function registerProjectCustomPostType() {
      $labels = array(
          'name'                  => _g('Projects'),
          'singular_name'         => _g('Project'),
          'add_new'               => _g('Add New'),
          'add_new_item'          => _g('Add New Project'),
          'edit_item'             => _g('Edit Project'),
          'new_item'              => _g('New Project'),
          'all_items'             => _g('All Projects'),
          'view_item'             => _g('View Project'),
          'search_items'          => _g('Search Projects'),
          'not_found'             => _g('No projects found'),
          'not_found_in_trash'    => _g('No projects found in Trash'),
          'menu_name'             => _g('Projects')
      );

      $args = array(
          'labels'        => $labels,
          'public'        => true,
          'has_archive'   => true,
          'supports'      => array('title', 'editor', 'custom-fields'), //
          'register_meta_box_cb' => array($this->project_metabox, 'registerMetaBoxes')
      );
      register_post_type($this->post_type, $args);      
  }
}