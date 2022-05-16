<?php

/**
 * List table for cron schedules.
 */

require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

/**
 * Cron schedule list table class.
 */
class Subject_List_Table extends \WP_List_Table
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
    // protected function get_primary_column_name()
    // {
    //     return 'name';
    // }

    /**
     * Prepares the list table items and arguments.
     *
     * @return void
     */
    public function prepare_items()
    {
        global $wpdb;

        $currentPage = $this->get_pagenum();

        $offset = ($currentPage - 1) * 50;

        $filter = !empty($_GET['subject_type']) && $_GET['subject_type'] != 'all' ? " AND f.type = '{$_GET['subject_type']}'" : '';

        $subjects = $wpdb->get_results("SELECT m.*, f.create_time, f.remark, f.score FROM $wpdb->douban_movies m LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id WHERE f.status = 'done'{$filter} ORDER BY f.create_time DESC LIMIT 40 OFFSET {$offset}");

        $this->items = $subjects;

        $this->set_pagination_args(array(
            'total_items' => $this->get_subject_count($_GET['subject_type']),
            'per_page'    => 40,
        ));
    }

    public function get_views()
    {

        $views = array();
        $hooks_type = (!empty($_GET['subject_type']) ? $_GET['subject_type'] : 'all');

        $types = array(
            'all'      => '所有条目',
            'movie' => '电影',
            'book'     => '图书',
            'music'   => '音乐',
            'game'   => '游戏',
            'drama'   => '舞台剧',
        );

        /**
         * Filters the filter types on the cron event listing screen.
         *
         * See the corresponding `crontrol/filtered-events` filter to adjust the filtered events.
         *
         * @since 1.11.0
         *
         * @param string[] $types      Array of filter names keyed by filter name.
         * @param string   $hooks_type The current filter name.
         */
        $types = apply_filters('crontrol/filter-types', $types, $hooks_type);

        $url = admin_url('admin.php?page=subject');

        /**
         * @var array<string,string> $types
         */
        foreach ($types as $key => $type) {


            $link = ('all' === $key) ? $url : add_query_arg('subject_type', $key, $url);

            $views[$key] = sprintf(
                '<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
                esc_url($link),
                $hooks_type === $key ? ' class="current"' : '',
                esc_html($type),
                $this->get_subject_count($key)
            );
        }

        return $views;
    }

    protected function get_subject_count($type)
    {
        global $wpdb;
        $filter = $type && $type != 'all' ? " AND f.type = '{$type}'" : '';
        $subjects = $wpdb->get_results("SELECT m.id FROM $wpdb->douban_movies m LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id WHERE f.status = 'done'{$filter}");
        return count($subjects);
    }

    // protected function extra_tablenav($which)
    // {
    //     wp_nonce_field('crontrol-export-event-csv', 'crontrol_nonce');
    //     printf(
    //         '<input type="hidden" name="crontrol_hooks_type" value="%s"/>',
    //         esc_attr(isset($_GET['crontrol_hooks_type']) ? sanitize_text_field(wp_unslash($_GET['crontrol_hooks_type'])) : 'all')
    //     );
    //     printf(
    //         '<input type="hidden" name="s" value="%s"/>',
    //         esc_attr(isset($_GET['s']) ? sanitize_text_field(wp_unslash($_GET['s'])) : '')
    //     );
    //     printf(
    //         '<button class="button" type="submit" name="crontrol_action" value="export-event-csv">%s</button>',
    //         esc_html__('Export', 'wp-crontrol')
    //     );
    // }

    // private function table_data()
    // {
    //     return [];
    // }

    public function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'name':
            case 'douban_score':
            case 'card_subtitle':
            case 'remark':
            case 'create_time':
            case 'score':
                return $item->$column_name;

            case 'poster':
                return '<img src="' . $item->poster . '" width="100" referrerpolicy="no-referrer">';

            default:
                return print_r($item, true);
        }
    }

    protected function column_cb($event)
    {
        return '<input type="checkbox" name="crontrol_delete[%3$s][%4$s]" value="%5$s" id="%1$s">';
    }

    // protected function handle_row_actions($event, $column_name, $primary)
    // {
    //     if ($primary !== $column_name) {
    //         return '';
    //     }

    //     $links = array();
    //     $link = array(
    //         'page'                  => 'crontrol_admin_manage_page',
    //         'crontrol_action'       => 'run-cron',
    //         'crontrol_id'           => rawurlencode($event->hook),
    //         'crontrol_sig'          => rawurlencode($event->sig),
    //         'crontrol_next_run_utc' => rawurlencode($event->time),
    //     );
    //     $link = add_query_arg($link, admin_url('tools.php'));

    //     $links[] = "<a href='" . esc_url($link) . "'>" . esc_html__('Edit', 'wp-crontrol') . '</a>';

    //     $link = array(
    //         'page'                  => 'crontrol_admin_manage_page',
    //         'crontrol_action'       => 'run-cron',
    //         'crontrol_id'           => rawurlencode($event->hook),
    //         'crontrol_sig'          => rawurlencode($event->sig),
    //         'crontrol_next_run_utc' => rawurlencode($event->time),
    //     );
    //     $link = add_query_arg($link, admin_url('tools.php'));
    //     $link = wp_nonce_url($link, "crontrol-run-cron_{$event->hook}_{$event->sig}");

    //     $links[] = "<a href='" . esc_url($link) . "'>" . esc_html__('Run Now', 'wp-crontrol') . '</a>';

    //     return $this->row_actions($links);
    // }

    /**
     * Returns an array of column names for the table.
     *
     * @return array<string,string> Array of column names keyed by their ID.
     */
    public function get_columns()
    {
        return array(
            'name'     => '标题',
            'poster' => '封面',
            'douban_score' => '豆瓣评分',
            'card_subtitle' => '描述',
            'create_time' => '时间',
            'remark' => '我的短评',
            'score' => '我的评分'
        );
    }
}
