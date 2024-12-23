<?php
namespace Domain\Models;

use Domain\Models\MetaBoxField;

class MetaBox {    
    public $fields = [];

    public function __construct(
        public $id,
        public $title,
        // public $callback,
        public $screen,
        public $context = 'advanced', 
        public $priority = 'default') {
    }

    public function addField(MetaBoxField $field) {
        $this->fields[] = $field;
    }

    public function getFields() {
        return $this->fields;
    }    

    public function renderMetabox($post) {
        if(!empty($this->fields)) {
            
            echo '<div class="meta-block">';
            foreach ($this->fields as $field) {                
                $field->loadValue($post->ID);
                
                $field->renderField();
            }
            echo '</div>';
        }       
    }  
}

