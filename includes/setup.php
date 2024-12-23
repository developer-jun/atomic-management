<?php
namespace Domain;

use Domain\Constants;

class Setup {
  
  public static function activate() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Define table names
    $table_categories = $wpdb->prefix . Constants::TABLE_CATEGORIES; 
    $table_projects = $wpdb->prefix . Constants::TABLE_PROJECTS;

    // SQL for creating Categories Table
    /*$sql_categories = "CREATE TABLE $table_categories (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      name tinytext NOT NULL,
      description text,
      PRIMARY KEY  (id)
    ) $charset_collate;";

    // SQL for creating Projects Table
    $sql_projects = "CREATE TABLE $table_projects (
      id mediumint(9) NOT NULL AUTO_INCREMENT,
      category_id mediumint(9) NOT NULL,
      name tinytext NOT NULL,
      description text,
      created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      updated_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
      PRIMARY KEY  (id),
      FOREIGN KEY (category_id) REFERENCES $table_categories(id)
    ) $charset_collate;";
    */
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    //dbDelta($sql_categories);
    //dbDelta($sql_projects);
  }
}
