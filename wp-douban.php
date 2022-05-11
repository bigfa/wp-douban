<?php
/*
Plugin Name: WP-Douban
Plugin URI: https://fatesinger.com/101005
Description: 豆瓣内容列表展示
Version: 4.0.0
Author: Bigfa
Author URI: https://fatesinger.com
*/

define('WPD_VERSION', '4.0.0');
define('WPD_URL', plugins_url('', __FILE__));
define('WPD_PATH', dirname(__FILE__));
define('WPD_ADMIN_URL', admin_url());
define('WPD_CACHE_TIME', 60 * 60 * 24 * 30);
define('WPD_CACHE_KEY', 'WPD');
define('WPD_CACHE_IMAGE', true);

/**
 * 加载函数
 */
require WPD_PATH . '/functions.php';

/**
 * 插件激活,新建数据库
 */
register_activation_hook(__FILE__, 'wpd_install');
function wpd_install()
{
    $thumb_path = ABSPATH . "douban_cache/";
    if (file_exists($thumb_path)) {
        if (!is_writeable($thumb_path)) {
            @chmod($thumb_path, '511');
        }
    } else {
        @mkdir($thumb_path, '511', true);
    }
}
new WPD_Douban();
