<?php
/*
 Plugin Name: Atomic Management
 Description: Project Management system
 Version: 1.0
 Author: Jun
*/

// ------------------------------------------------
// Composer AUTOLOAD
if ( file_exists( dirname( __FILE__ ) . '/vendor/autoload.php' ) ) {
  require_once dirname( __FILE__ ) . '/vendor/autoload.php';
}

// ------------------------------------------------
// SETUP AND ACTIVATION
require_once plugin_dir_path(__FILE__) . '/includes/helpers.php';
// require_once plugin_dir_path(__FILE__) . 'db-table-setup.php';

register_activation_hook(__FILE__, array('Domain\Setup', 'activate'));


// Instantiate the main class 
function run_atomic_plugin() { 
  $plugin = new Domain\Main(
    plugin_root_dir: plugin_dir_path(__FILE__), 
    plugin_root_url: plugin_dir_url(__FILE__)
  ); 
} 
add_action('plugins_loaded', 'run_atomic_plugin');


// ------------------------------------------------
// 
add_action('init', function() {
  // This is where you'll set up any initialization code.
});