<?php
/*
Plugin Name: WP-Douban
Plugin URI: https://fatesinger.com/101005
Description: 豆瓣内容列表展示
Version: 4.0.1
Author: Bigfa
Author URI: https://fatesinger.com
*/

define('WPD_VERSION', '4.0.1');
define('WPD_URL', plugins_url('', __FILE__));
define('WPD_PATH', dirname(__FILE__));
define('WPD_ADMIN_URL', admin_url());
define('WPD_CACHE_IMAGE', false);
define('WPD_LOAD_SCRIPTS', true);
define('WPD_UID', 54529369);

### DB Table Name
global $wpdb;
$wpdb->douban_collection   = $wpdb->prefix . 'douban_collection';
$wpdb->douban_faves   = $wpdb->prefix . 'douban_faves';
$wpdb->douban_genres  = $wpdb->prefix . 'douban_genres';
$wpdb->douban_movies  = $wpdb->prefix . 'douban_movies';
$wpdb->douban_relation  = $wpdb->prefix . 'douban_relation';


/**
 * Create Database on install
 * Add schedule job
 * Creat cache folder
 */

register_activation_hook(__FILE__, 'wpd_install');
function wpd_install()
{
    //wp_schedule_event(time(), 'hourly', 'db_sync');
    $thumb_path = ABSPATH . "douban_cache/";
    if (file_exists($thumb_path)) {
        if (!is_writeable($thumb_path)) {
            @chmod($thumb_path, '511');
        }
    } else {
        @mkdir($thumb_path, '511', true);
    }


    // Create DB Tables (5 Tables)
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $create_table = [];
    $create_table['douban_collection'] = "CREATE TABLE $wpdb->douban_collection (" .
        "id int(10) NOT NULL auto_increment," .
        "name varchar(256) default ''," .
        "poster varchar(256) default ''," .
        "douban_id varchar(16) default ''," .
        "PRIMARY KEY  (id)" .
        ") $charset_collate;";

    $create_table['douban_faves'] = "CREATE TABLE $wpdb->douban_faves (" .
        "id int(10) NOT NULL auto_increment," .
        "subject_id int(10) default 0," .
        "remark varchar(512) default ''," .
        "create_time datetime," .
        "type varchar(16) default ''," .
        "score varchar(16) default ''," .
        "status varchar(16) default ''," .
        "PRIMARY KEY  (id)" .
        ") $charset_collate;";

    $create_table['douban_genres'] = "CREATE TABLE $wpdb->douban_genres (" .
        "id int(10) NOT NULL auto_increment," .
        "movie_id int(10) default 0," .
        "name varchar(16) default ''," .
        "type varchar(16) default 'movie'," .
        "PRIMARY KEY  (id)" .
        ") $charset_collate;";

    $create_table['douban_movies'] = "CREATE TABLE $wpdb->douban_movies (" .
        "id int(10) NOT NULL auto_increment," .
        "name varchar(256)," .
        "poster varchar(512)," .
        "link varchar(256)," .
        "`delete` int," .
        "`douban_id` int," .
        "douban_score varchar(16)," .
        "`year` varchar(16)," .
        "`type` varchar(16) default 'movie'," .
        "pubdate varchar(32)," .
        "faves int," .
        "card_subtitle varchar(256)," .
        "PRIMARY KEY (id)" .
        ") $charset_collate;";

    $create_table['douban_relation'] = "CREATE TABLE $wpdb->douban_relation (" .
        "id int(10) NOT NULL auto_increment," .
        "movie_id int default 0," .
        "collection_id int default 0," .
        "PRIMARY KEY  (id)" .
        ") $charset_collate;";

    dbDelta($create_table['douban_collection']);
    dbDelta($create_table['douban_faves']);
    dbDelta($create_table['douban_genres']);
    dbDelta($create_table['douban_movies']);
    dbDelta($create_table['douban_relation']);
}

/**
 * Load classes
 */
require WPD_PATH . '/functions.php';
//require WPD_PATH . '/db.php';
new WPD_Douban();
