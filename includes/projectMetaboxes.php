<?php

namespace Domain;

use Domain\Models\MetaBox;
use Domain\Models\MetaBoxField;

class ProjectMetaboxes {

  private $metaboxes = [];
  private $screen_name = 'atom_project';

  public function __construct(private $custom_post_type_task) {
    // save meta boxes
    add_action('save_post', array($this, 'saveProjectMetaBoxesData'));

    $this->generateMetaboxesData();
  }

  // Function to save the meta box data 
  public function saveProjectMetaBoxesData($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;
    
    foreach($this->metaboxes as $metabox) { 
      foreach ($metabox->fields as $field) {
        $field->saveValue($post_id);
      }       
    }    
  }

  private function getStatuses($taxonomy = 'project_status') {
    // Get the taxonomy terms 
    $terms = get_terms(array( 'taxonomy' => $taxonomy, 'hide_empty' => false, )); 
    // Get the current post terms 
    $post_terms = wp_get_post_terms($post->ID, $taxonomy, array('fields' => 'ids'));

    return ['list' => $terms, 'selected' => $post_terms];
  }

  // Don't do any query here, by the time this function is executed, the custom post types and taxonomies hasn't even been created yet. 
  // Any query to those will fail
  private function generateMetaboxesData() {
    $index = 0;
    $contact_info_meta = new MetaBox(
        'project_contacts', 
        _g('Contacts'), 
        $this->$screen_name, 
        'side', 
    );
    $contact_info_meta->addField(
      new MetaBoxField(
          id: 'contact_person',
          label: _g('Person'),
          type: 'text',
          name: 'contact_person',
          value: '',
      )
    );
    $contact_info_meta->addField(
        new MetaBoxField(
            id: 'contact_phone',
            label: _g('Phone'),
            type: 'text',
            name: 'contact_phone',
            value: '',
        )
    );
    $contact_info_meta->addField(
        new MetaBoxField(
            id: 'contact_email',
            label: _g('Email'),
            type: 'email',
            name: 'contact_email',
            value: '',
        )
    );
    
    array_push($this->metaboxes, $contact_info_meta);   

    // TAXONOMY status
    $status_meta = new MetaBox(
        'project_status', 
        _g('Project Status'), 
        $this->$screen_name, 
        'side', 
    );    
    $status_meta->addField(
      new MetaBoxField(
          id: 'project_status',
          label: _g('Current Status'),
          type: 'select',
          name: 'project_status',
          value: '',
          options: '',
          callable: function($field, $post_id) { 
            $terms = get_terms(array('taxonomy' => 'project_status', 'hide_empty' => false));
            $field->setOptions(select_field_data_format($terms, ['id' => 'term_id', 'name' => 'name'])); 
            // Update options with term names 
            $post_terms = wp_get_post_terms($post_id, 'project_status', array('fields' => 'ids')); 
            return !empty($post_terms) ? $post_terms[0] : ''; 
          },
          is_taxonomy: true
      )
    );
    array_push($this->metaboxes, $status_meta);

    $milestone_meta = new MetaBox(
      'project_milestone',
      _g('Mile Stones'),
      $this->$screen_name, 
      'side'
    );
    $milestone_meta->addField(
      new MetaBoxField(
          id: 'project_milestone',
          label: _g(''),
          type: 'div',
          name: 'project_milestone',
          value: '',
          options: '',
          callable: function($field, $post_id) {
            $milestones = get_post_meta($post_id, '_milestone', false); // Retrieve all milestone entries
            $milestone_as_list = '';
            arsort($milestones); // reverse sort
            if (!empty($milestones)) {
                foreach ($milestones as $milestone) {
                  $milestone_as_list .= '<li><strong>Status:</strong> <em>' . esc_html($milestone['status']) . '</em> <br />Date: ' . esc_html($milestone['date']) . '</li>';
                }
                $milestone_as_list = '<ul class="status-list">'. $milestone_as_list . '</ul>';
            } else {
              $milestone_as_list = 'No milestones recorded.';
            }

            return $milestone_as_list; 
          },
      )
    );

    array_push($this->metaboxes, $milestone_meta);
    
  }

  // attached and called by 'Project' - custom post type
  public function registerMetaBoxes() {
    foreach($this->metaboxes as $metabox) { 
          add_meta_box( 
              $metabox->id, 
              $metabox->title, 
              array($metabox, 'renderMetabox'), 
              $metabox->screen,
              $metabox->context,
              $metabox->priority
          );
      }

      // meta box for Tasks
      add_meta_box( 
          'project_tasks', 
          _g('Task'), 
          array($this->custom_post_type_task, 'renderTasksAsMetabox'), 
          $this->$screen_name, 
          'normal'
      );      
  } 
  
}