<?php

add_action('admin_menu', 'db_menu');
function db_menu()
{
    add_menu_page(
        __('WP DOUBAN Settings', 'wp-douban'),
        __('WP_DOUBAN', 'wp-douban'),
        'manage_options',
        'wpdouban',
        'db_setting_page',
        'dashicons-chart-pie'
    );
    add_submenu_page('wpdouban', __('WP DOUBAN Settings', 'wp-douban'), __('Settings', 'wp-douban'), 'manage_options', 'wpdouban', 'db_setting_page');
    add_submenu_page('wpdouban', __('My Items', 'wp-douban'), __('My Items', 'wp-douban'), 'manage_options', 'subject', 'db_subject_page');
    add_submenu_page('wpdouban', __('All Items', 'wp-douban'), __('All Items', 'wp-douban'), 'manage_options', 'subject_all', 'db_all_subject_page');
    add_submenu_page('wpdouban', __('Plugin Logs', 'wp-douban'), __('Plugin Logs', 'wp-douban'), 'manage_options', 'log', 'db_log_page');
    add_submenu_page(null, __('Edit Item', 'wp-douban'), __('Edit Item', 'wp-douban'), 'manage_options', 'subject_edit', 'db_edit_subject_page');

    add_action('admin_init', 'db_setting_group');
}

function db_setting_group()
{
    register_setting('db_setting_group', 'db_setting');
}

function db_edit_subject_page()
{
    @include WPD_PATH . '/tpl/tpl-subject-edit.php';
}

function db_log_page()
{
    @include WPD_PATH . '/tpl/tpl-log.php';
}

function db_all_subject_page()
{
    @include WPD_PATH . '/tpl/tpl-subject-all.php';
}

function db_subject_page()
{
    @include WPD_PATH . '/tpl/tpl-subject.php';
}


function db_setting_page()
{
    @include WPD_PATH . '/tpl/tpl-setting.php';
}

function db_get_setting($key = NULL)
{
    $setting = get_option('db_setting');
    if (isset($setting[$key])) {
        return $setting[$key];
    } else {
        return false;
    }
}

function db_delete_setting()
{
    delete_option('db_setting');
}

function db_setting_key($key)
{
    if ($key) {
        return "db_setting[$key]";
    }

    return false;
}
function db_update_setting($setting)
{
    update_option('db_setting', $setting);
}
