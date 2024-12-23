<?php

namespace Domain;

use Domain\WorkMetaboxes;
//use Domain\ProjectMetaboxes;
//use Domain\ProjectCategoryTaxonomy;
//use Domain\ProjectStatusTaxonomy;

class WorkCustomPostType {
  private $post_type = 'work';
  private $work_metabox;
  private $plugin_url;

  public function __construct($plugin_url) {
    $this->plugin_url = $plugin_url;

    //$category_taxonomy = new ProjectCategoryTaxonomy($this->post_type);
    //$status_taxonomy = new ProjectStatusTaxonomy($this->post_type);
    
    // Hook into the 'init' action to register the custom post type
    add_action('init', [$this, 'registerCustomPostType']);
    
    $this->work_metabox = new WorkMetaboxes($plugin_url);    
  }  

  // Function to register the custom post type
  public function registerCustomPostType() {
      $labels = array(
          'name'                  => _g('Work'),
          'singular_name'         => _g('Work'),
          'add_new'               => _g('Add New'),
          'add_new_item'          => _g('Add New Work'),
          'edit_item'             => _g('Edit Work'),
          'new_item'              => _g('New Work'),
          'all_items'             => _g('All Works'),
          'view_item'             => _g('View Work'),
          'search_items'          => _g('Search Works'),
          'not_found'             => _g('No works found'),
          'not_found_in_trash'    => _g('No works found in Trash'),
          'menu_name'             => _g('Works')
      );

      $args = array(
          'labels'        => $labels,
          'public'        => true,
          'has_archive'   => true,
          'rewrite'            => ['slug' => 'works'],
          'supports'      => array('title', 'editor', 'thumbnail'), //, 'custom-fields'
         // 'show_in_rest'       => true, // Enables Gutenberg editor
          'menu_icon'          => 'dashicons-portfolio',
          //'register_meta_box_cb' => array($this->work_metabox, 'registerMetaBoxes')
      );
      register_post_type($this->post_type, $args);
  }
}