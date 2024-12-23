<?php

namespace Domain\Models;

class MetaBoxField {

  public function __construct(
    private $id,
    private $label,
    private $type,
    private $name,
    private $value = '',
    private $options = [],
    private $callable = null,
    private $is_taxonomy = false
  ) {}

  public function getId() {
      return $this->id;
  }

  public function getLabel() {
      return $this->label;
  }

  public function getType() {
      return $this->type;
  }

  public function getName() {
      return $this->name;
  }

  public function getValue() {
      return $this->value;
  }

  public function setValue($value) {
      $this->value = $value;
  }

  public function setOptions($options) {
    $this->options = $options;
}

  public function getOptions() {
      return $this->options;
  }

  public function renderField() {    
      echo '<label class="form-field" for="' . esc_attr($this->id) . '"><em>' . esc_html($this->label) . ':</em>';
      switch ($this->type) {
          case 'text':
              echo '<input type="text" id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '" value="' . esc_attr($this->value) . '"/>';
              break;
          case 'textarea':
              echo '<textarea id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '">' . esc_textarea($this->value) . '</textarea>';
              break;
          case 'select':                
              echo '<select id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '">';
              
              foreach ($this->options as $option) {
                if(is_array($option)) {
                    echo '<option value="' . esc_attr($option['id']) . '"' . selected($this->value, $option['id'], false) . '>' . esc_html($option['name']) . '</option>';
                    continue;
                }
                echo '<option value="' . esc_attr($option) . '"' . selected($this->value, $option, false) . '>' . esc_html($option) . '</option>';
              }
              echo '</select>';
              break;
          case 'checkbox':
              echo '<input type="checkbox" id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '" value="1" ' . checked($this->value, '1', false) . '/>';
              break;
          case 'radio':
              echo '<div class="block">'; 
              foreach ($this->options as $option) {
                  echo '<div class="field-group">';
                  echo '<input type="radio" id="' . esc_attr($this->id) . '_' . esc_attr($option) . '" name="' . esc_attr($this->name) . '" value="' . esc_attr($option) . '" ' . checked($this->value, $option, false) . '/>';
                  echo '<label for="' . esc_attr($this->id) . '_' . esc_attr($option) . '">' . esc_html($option) . '</label>';
                  echo '</div>';
              }
              echo '</div>';
              break;
           case 'div':
                echo $this->value;     
            break;
          default:
              echo '<input type="text" id="' . esc_attr($this->id) . '" name="' . esc_attr($this->name) . '" value="' . esc_attr($this->value) . '"/>';
              break;
      }
      echo '</label>';
  } 

  public function loadValue($post_id) {
    if (is_callable($this->callable)) { 
        $this->value = call_user_func($this->callable, $this, $post_id); 
        return;
    }
    
    $this->value = get_post_meta($post_id, $this->name, true);
  }

    public function saveValue($post_id) { 
        if (!isset($_POST[$this->name])) { 
            return;
        }
        if ($this->is_taxonomy) {
            $new_status = intval($_POST[$this->name]); 
            // this will be for the milestone, 
            $selected_status = wp_get_post_terms($post_id, 'project_status', ['fields' => 'all']);
            //print_r($status);
            //die();
            //$old_status = wp_get_post_terms($post_id, 'project_status', ['fields' => 'ids']);
            if (empty($old_status) || $old_status[0] !== $new_status) { 
                // Set the new status term 
                wp_set_post_terms($post_id, $new_status, 'project_status'); 

                $term_info = get_term($new_status, 'project_status');
                // Save the milestone in post meta 
                $milestone_data = [ 'id' => $new_status, 'status' => $term_info->name ?? '', 'date' => current_time('mysql') ]; 
                add_post_meta($post_id, '_milestone', $milestone_data); 
            }
            return;
        } 
        
        $sanitized_value = sanitize_text_field($_POST[$this->name]); 
        update_post_meta($post_id, $this->name, $sanitized_value); 
    }

}
