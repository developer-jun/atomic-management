<?php

namespace Domain;

class ProjectStatusTaxonomy {
  private $taxonomy_name = 'project_status';

  public function __construct(private $post_type) {
    add_action('init', [$this, 'registerCustomStatusTaxonomy']);    
    add_action('admin_menu', [$this, 'remove_default_taxonomy_metabox']);  
    add_action('admin_footer', [$this, 'single_selection_taxonomy']);
  }

  function registerCustomStatusTaxonomy() {
    $labels = array(
        'name'              => 'Project Statuses',
        'singular_name'     => 'Project Status',
        'search_items'      => 'Search Project Statuses',
        'all_items'         => 'All Project Statuses',
        'edit_item'         => 'Edit Project Status',
        'update_item'       => 'Update Project Status',
        'add_new_item'      => 'Add New Project Status',
        'new_item_name'     => 'New Project Status Name',
        'menu_name'         => 'Project Statuses',
    );

    $args = array(
        'hierarchical'      => true, // This makes it behave more like tags
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'project-status'),
    );

    register_taxonomy($this->taxonomy_name, array($this->post_type), $args);
  }

  

  function single_selection_taxonomy() {
    global $typenow;
    if ($typenow == $this->post_type) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('#taxonomy-<?= $this->taxonomy_name ?> input[type=checkbox]').click(function() {
                    if ($(this).is(':checked')) {
                        $('#taxonomy-<?= $this->taxonomy_name ?> input[type=checkbox]').not(this).prop('checked', false);
                    }
                });
            });
        </script>
        <?php
    }
  }
  
  function remove_default_taxonomy_metabox() {
    remove_meta_box($this->taxonomy_name.'div', $this->post_type, 'side'); // Change 'project_statusdiv' if necessary
  }

}