<?php

add_action('admin_menu', 'db_menu');
function db_menu()
{
    add_menu_page('WP DOUBAN 设置', 'WP_DOUBAN', 'manage_options', 'wpdouban', 'db_setting_page', 'dashicons-chart-pie');
    add_submenu_page('wpdouban', 'WP DOUBAN 设置', '参数设置', 'manage_options', 'wpdouban', 'db_setting_page');
    add_submenu_page('wpdouban', '我的条目', '我的条目', 'manage_options', 'subject', 'db_subject_page');
    add_submenu_page('wpdouban', '所有条目', '所有条目', 'manage_options', 'subject_all', 'db_all_subject_page');
    add_submenu_page(null, '编辑条目', '编辑条目', 'manage_options', 'subject_edit', 'db_edit_subject_page');

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
