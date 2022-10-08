<?php

/**
 * List table for cron schedules.
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * Cron schedule list table class.
 */
class Log_Table extends \WP_List_Table
{

    /**
     * Array of cron event schedules that are added by WordPress core.
     *
     * @var array<int,string> Array of schedule names.
     */
    protected static $core_schedules;

    /**
     * Array of cron event schedule names that are in use by events.
     *
     * @var array<int,string> Array of schedule names.
     */
    protected static $used_schedules;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(array(
            'singular' => 'wp-douban',
            'plural'   => 'wp-doubans',
            'ajax'     => false,
            'screen'   => 'wp-douban',
        ));
    }

    /**
     * Gets the name of the primary column.
     *
     * @return string The name of the primary column.
     */
    protected function get_primary_column_name()
    {
        return 'type';
    }

    /**
     * Prepares the list table items and arguments.
     *
     * @return void
     */
    public function prepare_items()
    {
        global $wpdb;

        $currentPage = $this->get_pagenum();

        $offset = ($currentPage - 1) * 40;

        $subjects = $wpdb->get_results("SELECT * FROM $wpdb->douban_log ORDER BY create_time DESC LIMIT 40 OFFSET {$offset}");

        $this->items = $subjects;

        $this->set_pagination_args(array(
            'total_items' => $this->get_subject_count(),
            'per_page'    => 40,
        ));
    }


    protected function get_subject_count()
    {
        global $wpdb;
        $subjects = $wpdb->get_results("SELECT id FROM $wpdb->douban_log");
        return count($subjects);
    }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'type':
            case 'action':
            case 'status':
            case 'message':
            case 'create_time':
            case 'account_id':
                return $item->$column_name;

            default:
                return print_r($item, true);
        }
    }

    protected function extra_tablenav($which)
    {
        $link = array(
            'page'                  => 'log',
            'wpd_action'       => 'empty_log',
        );
        $link = add_query_arg($link, admin_url('admin.php'));
        $link = wp_nonce_url($link, "wpd_empty_log");


        printf(
            '<a href="%s" class="button">清空日志</a>',
            esc_url($link)
        );
    }

    protected function column_cb($event)
    {
        return '<input type="checkbox" name="crontrol_delete[%3$s][%4$s]" value="%5$s" id="%1$s">';
    }

    protected function handle_row_actions($event, $column_name, $primary)
    {
        if ($primary !== $column_name) {
            return '';
        }

        $links = array();


        return $this->row_actions($links);
    }

    /**
     * Returns an array of column names for the table.
     *
     * @return array<string,string> Array of column names keyed by their ID.
     */
    public function get_columns()
    {
        return array(
            'type'     => '类型',
            'action' => '操作',
            'status' => '状态',
            'message' => '备注',
            'create_time' => '时间',
            'account_id' => 'ID',
        );
    }
}
