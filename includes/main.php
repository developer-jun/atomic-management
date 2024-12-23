<?php
namespace Domain;

use Domain\Constants;
use Domain\ProjectCustomPostType;
use Domain\TaskCustomPostType;

class Main {     
    private $custom_post_type_project;
    private $prefix = '';
    // Constructor to initialize the plugin
    public function __construct(
            private $plugin_root_dir, 
            private $plugin_root_url
        ) {
        $prefix = Constants::PLUGIN_PREFIX ?? 'atomic_';

        // Enqueue custom admin styles 
        add_action('admin_enqueue_scripts', array($this, 'enqueueAdminStylesAndScripts'));

        // Hook into the 'admin_menu' action to add the admin menu 
        add_action('admin_menu', array($this, 'addAdminMenu'));

        $this->custom_post_type_project = new ProjectCustomPostType($this->plugin_root_url);
        //$this->custom_post_type_task = new TaskCustomPostType($this->plugin_root_url);
    }

    public function addAdminMenu() { 
        add_menu_page( 
            _g('Atomic Management Dashboard'),              // Page title 
            _g('Atomic Management'),              // Menu title 
            'manage_options',                       // Capability 
            $this->prefix .'dashboard',                 // Menu slug 
            function() { 
                include $this->plugin_root_dir .'/public/partials/admin-dashboard.php';
            },
            'dashicons-admin-site',                 // Icon 
            6                                       // Position 
        );

        add_submenu_page( 
            $this->prefix. 'dashboard', 
            'Dashboard', // Page title 
            'Dashboard', // Menu title 
            'manage_options', // Capability 
            $this->prefix. 'dashboard', // Menu slug 
            function() { 
                echo 'DASH';
            }
        );

        add_submenu_page( 
            $this->prefix. 'dashboard', 
            'Governing Sub Menu', // Page title 
            'Governing Sub Menu', // Menu title 
            'manage_options', // Capability 
            $this->prefix. 'submenu', // Menu slug 
            function() { 
                echo 'Sub Menu';
            }
        );        
    }

    // Function to enqueue admin styles 
    function enqueueAdminStylesAndScripts($hook) {
        wp_enqueue_style(
            Constants::TEXT_DOMAIN.'-admin', 
            $this->plugin_root_url . '/public/css/govern-admin.css'
        );        
    }  

}
