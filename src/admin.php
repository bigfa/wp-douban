<?php

class WPD_ADMIN
{
    public function __construct()
    {
        add_action('wp_ajax_wpd_import', [$this, 'import']);
        add_action('init', [$this, 'action_handle_posts']);
    }

    public function action_handle_posts()
    {
        if (isset($_POST['crontrol_action']) && 'export-event-csv' === $_POST['crontrol_action']) {

            $type = isset($_POST['crontrol_hooks_type']) ? $_POST['crontrol_hooks_type'] : 'all';
            $headers = array(
                'hook',
                'arguments',
                'next_run',
                'next_run_gmt',
                'action',
                'recurrence',
                'interval',
            );
            $filename = sprintf(
                'cron-events-%s-%s.csv',
                $type,
                gmdate('Y-m-d-H.i.s')
            );
            $csv = fopen('php://output', 'w');

            if (false === $csv) {
                wp_die(esc_html__('Could not save CSV file.', 'wp-crontrol'));
            }

            header('Content-Type: text/csv; charset=utf-8');
            header(
                sprintf(
                    'Content-Disposition: attachment; filename="%s"',
                    esc_attr($filename)
                )
            );

            fputcsv($csv, $headers);

            if (isset($events[$type])) {
                foreach ($events[$type] as $event) {
                    $row = array();
                    fputcsv($csv, $row);
                }
            }

            fclose($csv);

            exit;
        }
    }

    public function import()
    {
        global $wpdb;
        if (!isset($_FILES['file'])) {
            wp_send_json_error(esc_html__('File missing', 'mmp'));
        }

        $details = array();
        $file = $_FILES['file']['tmp_name'];
        $handle = fopen($file, 'r');
        while (($data = fgetcsv($handle)) !== false) {
            $douban_id = explode('/', $data['6'])[4];
            if ($douban_id) {
                $movie = $wpdb->get_results("SELECT * FROM wp_douban_movies WHERE douban_id = '{$douban_id}'");
                $movie = $movie[0];
                //  echo $item['6'];
                if ($movie->name == '未知电影' || $movie->name == '未知电视剧') {
                    $wpdb->update('wp_douban_movies', ['name' => trim(explode('/',  $data['0'])[0]), 'poster' => str_replace('webp', 'jpg', $data['7'])], ['douban_id' => $douban_id]);
                }
            }
            $details[] = $data;
        }
        fclose($handle);

        wp_send_json_success(array(
            'details' => $details
        ));
    }
}
