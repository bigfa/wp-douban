<?php

class WPD_ADMIN extends WPD_Douban
{
    public function __construct()
    {
        add_action('wp_ajax_wpd_import', [$this, 'import']);
        add_action('init', [$this, 'action_handle_posts']);
    }

    private function wpd_remove_images($id)
    {
        $e = ABSPATH . 'douban_cache/' . $id . '.jpg';
        if (!is_file($e)) return;
        unlink($e);
    }

    public function action_handle_posts()
    {
        $sendback = wp_get_referer();
        if (isset($_GET['wpd_action'])  && 'cancel_mark' === $_GET['wpd_action'] && wp_verify_nonce($_GET['_wpnonce'], 'wpd_subject_' . $_GET['subject_id'])) {
            global $wpdb;
            $wpdb->delete(
                $wpdb->douban_faves,
                [
                    'subject_id' => $_GET['subject_id'],
                    'type' => $_GET['subject_type'],
                ]
            );
            wp_redirect($sendback);
            exit;
        }

        if (isset($_GET['wpd_action'])  && 'mark' === $_GET['wpd_action'] && wp_verify_nonce($_GET['_wpnonce'], 'wpd_subject_' . $_GET['subject_id'])) {
            global $wpdb;
            $wpdb->insert(
                $wpdb->douban_faves,
                [
                    'subject_id' => $_GET['subject_id'],
                    'type' => $_GET['subject_type'],
                    'create_time' => current_time('mysql'),
                    'status' => 'done'
                ]
            );
            wp_redirect($sendback);
            exit;
        }


        if (isset($_GET['wpd_action'])  && 'sync_subject' === $_GET['wpd_action'] && wp_verify_nonce($_GET['_wpnonce'], 'wpd_subject_' . $_GET['subject_id'])) {
            $this->sync_subject($_GET['subject_id'], $_GET['subject_type']);
            // wp_redirect($sendback);
            //exit;
        }


        if (isset($_GET['wpd_action']) && 'empty_log' === $_GET['wpd_action']) {
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE $wpdb->douban_log");
            wp_redirect($sendback);
            exit;
        }


        if (isset($_POST['wpd_action']) && 'edit_fave' === $_POST['wpd_action']) {
            global $wpdb;
            if (isset($_POST['status']) && $_POST['status']) {
                $wpdb->update(
                    $wpdb->douban_faves,
                    [
                        'remark' => $_POST['remark'],
                        'score' => $_POST['score'],
                        'create_time' => $_POST['create_time'],
                        'status' => $_POST['status'],
                    ],
                    [
                        'id' => $_POST['fave_id'],
                    ]
                );
            } else {
                $wpdb->delete(
                    $wpdb->douban_faves,
                    [
                        'subject_id' => $_GET['subject_id'],
                        'type' => $_GET['subject_type'],
                    ]
                );
            }
            $link = array(
                'page'                  => 'subject',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_redirect($link);
            exit;
        }

        if (isset($_POST['wpd_action']) && 'edit_subject' === $_POST['wpd_action']) {
            global $wpdb;
            $subject = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE id = '{$_POST['subject_id']}'");
            $this->wpd_remove_images($subject->douban_id);
            $wpdb->update(
                $wpdb->douban_movies,
                [
                    'name' => $_POST['name'],
                    'douban_score' => $_POST['douban_score'],
                    'card_subtitle' => $_POST['card_subtitle'],
                    'poster' => $_POST['poster']
                ],
                [
                    'id' => $_POST['subject_id'],
                ]
            );
            $link = array(
                'page' => 'subject_all',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_redirect($link);
            exit;
        }

        if (isset($_GET['wpd_action']) && 'delete_subject' === $_GET['wpd_action']) {
            global $wpdb;
            $subject = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE id = '{$_GET['subject_id']}'");
            $this->wpd_remove_images($subject->douban_id);

            $wpdb->delete(
                $wpdb->douban_faves,
                [
                    'subject_id' => $_GET['subject_id'],
                    'type' => 'movie',
                ]
            );

            $wpdb->delete(
                $wpdb->douban_movies,
                [
                    'id' => $_GET['subject_id'],
                ]
            );


            $link = array(
                'page'                  => 'subject_all',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_redirect($link);
            exit;
        }



        // if (isset($_POST['crontrol_action']) && 'export-event-csv' === $_POST['crontrol_action']) {

        //     $type = isset($_POST['crontrol_hooks_type']) ? $_POST['crontrol_hooks_type'] : 'all';
        //     $headers = array(
        //         'hook',
        //         'arguments',
        //         'next_run',
        //         'next_run_gmt',
        //         'action',
        //         'recurrence',
        //         'interval',
        //     );
        //     $filename = sprintf(
        //         'cron-events-%s-%s.csv',
        //         $type,
        //         gmdate('Y-m-d-H.i.s')
        //     );
        //     $csv = fopen('php://output', 'w');

        //     if (false === $csv) {
        //         wp_die(esc_html__('Could not save CSV file.', 'wp-crontrol'));
        //     }

        //     header('Content-Type: text/csv; charset=utf-8');
        //     header(
        //         sprintf(
        //             'Content-Disposition: attachment; filename="%s"',
        //             esc_attr($filename)
        //         )
        //     );

        //     fputcsv($csv, $headers);

        //     if (isset($events[$type])) {
        //         foreach ($events[$type] as $event) {
        //             $row = array();
        //             fputcsv($csv, $row);
        //         }
        //     }

        //     fclose($csv);

        //     exit;
        // }
    }

    // public function import()
    // {
    //     global $wpdb;
    //     if (!isset($_FILES['file'])) {
    //         wp_send_json_error(esc_html__('File missing', 'mmp'));
    //     }

    //     $details = array();
    //     $file = $_FILES['file']['tmp_name'];
    //     $handle = fopen($file, 'r');
    //     while (($data = fgetcsv($handle)) !== false) {
    //         $douban_id = explode('/', $data['6'])[4];
    //         if ($douban_id) {
    //             $movie = $wpdb->get_results("SELECT * FROM wp_douban_movies WHERE douban_id = '{$douban_id}'");
    //             $movie = $movie[0];
    //             if ($movie->name == '未知电影' || $movie->name == '未知电视剧') {
    //                 $wpdb->update('wp_douban_movies', ['name' => trim(explode('/',  $data['0'])[0]), 'poster' => str_replace('webp', 'jpg', $data['7'])], ['douban_id' => $douban_id]);
    //             }
    //         }
    //         $details[] = $data;
    //     }
    //     fclose($handle);

    //     wp_send_json_success(array(
    //         'details' => $details
    //     ));
    // }
}
