<?php
/*
Plugin Name: WP-Douban
Plugin URI: https://fatesinger.com/74915
Description: 豆瓣内容列表展示
Version: 3.0.0
Author: Bigfa
Author URI: https://fatesinger.com
*/

define('WPD_VERSION', '3.0.0');
define('WPD_URL', plugins_url('', __FILE__));
define('WPD_PATH', dirname( __FILE__ ));
define('WPD_ADMIN_URL', admin_url());
define('WPD_CACHE_TIME', 60*60*24*30);
define('WPD_CACHE_KEY', 'WPD');

/**
 * 加载函数
 */
require WPD_PATH . '/functions.php';

/**
 * 插件激活,新建数据库
 */
register_activation_hook(__FILE__, 'wpd_install');

