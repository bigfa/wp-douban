<?php
/*
Plugin Name: WP-Douban
Plugin URI: http://fatesinger.com/74915
Description: 豆瓣内容列表展示
Version: 1.0
Author: Bigfa
Author URI: http://fatesinger.com
*/

define('WPD_VERSION', '1.9');
define('WPD_URL', plugins_url('', __FILE__));
define('WPD_PATH', dirname( __FILE__ ));
define('WPD_ADMIN_URL', admin_url());


/**
 * 加载函数
 */
require WPD_PATH . '/functions.php';

/**
 * 插件激活,新建数据库
 */
register_activation_hook(__FILE__, 'wpd_install');

