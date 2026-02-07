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

    private function has_manage_permission()
    {
        return current_user_can('manage_options');
    }

    private function sanitize_datetime_input($value)
    {
        $value = trim((string) $value);
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value . ' 00:00:00';
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}(:\d{2})?$/', $value)) {
            return strlen($value) === 16 ? $value . ':00' : $value;
        }
        return '';
    }

    public function action_handle_posts()
    {
        $sendback = wp_get_referer();
        if (!$sendback) {
            $sendback = admin_url('admin.php?page=subject');
        }

        $action = isset($_GET['wpd_action']) ? sanitize_key(wp_unslash($_GET['wpd_action'])) : '';
        $post_action = isset($_POST['wpd_action']) ? sanitize_key(wp_unslash($_POST['wpd_action'])) : '';

        if ('cancel_mark' === $action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $subject_id = isset($_GET['subject_id']) ? absint($_GET['subject_id']) : 0;
            $subject_type = $this->sanitize_subject_type(isset($_GET['subject_type']) ? wp_unslash($_GET['subject_type']) : '', '');
            $nonce = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
            if (!$subject_id || !$subject_type || !wp_verify_nonce($nonce, 'wpd_subject_' . $subject_id)) {
                return;
            }
            global $wpdb;
            $wpdb->delete(
                $wpdb->douban_faves,
                [
                    'subject_id' => $subject_id,
                    'type' => $subject_type,
                ]
            );
            wp_safe_redirect($sendback);
            exit;
        }

        if ('mark' === $action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $subject_id = isset($_GET['subject_id']) ? absint($_GET['subject_id']) : 0;
            $subject_type = $this->sanitize_subject_type(isset($_GET['subject_type']) ? wp_unslash($_GET['subject_type']) : '', '');
            $nonce = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
            if (!$subject_id || !$subject_type || !wp_verify_nonce($nonce, 'wpd_subject_' . $subject_id)) {
                return;
            }
            global $wpdb;
            $wpdb->insert(
                $wpdb->douban_faves,
                [
                    'subject_id' => $subject_id,
                    'type' => $subject_type,
                    'create_time' => current_time('mysql'),
                    'status' => 'done'
                ]
            );
            wp_safe_redirect($sendback);
            exit;
        }


        if ('sync_subject' === $action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $subject_id = isset($_GET['subject_id']) ? absint($_GET['subject_id']) : 0;
            $subject_type = $this->sanitize_subject_type(isset($_GET['subject_type']) ? wp_unslash($_GET['subject_type']) : '', '');
            $nonce = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
            if (!$subject_id || !$subject_type || !wp_verify_nonce($nonce, 'wpd_subject_' . $subject_id)) {
                return;
            }
            $this->sync_subject($subject_id, $subject_type);
            // wp_redirect($sendback);
            //exit;
        }


        if ('empty_log' === $action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $nonce = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
            if (!wp_verify_nonce($nonce, 'wpd_empty_log')) {
                return;
            }
            global $wpdb;
            $wpdb->query("TRUNCATE TABLE $wpdb->douban_log");
            wp_safe_redirect($sendback);
            exit;
        }


        if ('edit_fave' === $post_action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $nonce = isset($_POST['wpd_subject_nonce']) ? wp_unslash($_POST['wpd_subject_nonce']) : '';
            if (!wp_verify_nonce($nonce, 'wpd_subject_edit')) {
                return;
            }
            global $wpdb;
            $subject_id = isset($_POST['subject_id']) ? absint($_POST['subject_id']) : 0;
            $subject_type = $this->sanitize_subject_type(isset($_POST['subject_type']) ? wp_unslash($_POST['subject_type']) : '', '');
            $status = $this->sanitize_subject_status(isset($_POST['status']) ? wp_unslash($_POST['status']) : '', '');
            $fave_id = isset($_POST['fave_id']) ? absint($_POST['fave_id']) : 0;

            if (!$subject_type && $subject_id) {
                $subject = $wpdb->get_row($wpdb->prepare("SELECT `type` FROM $wpdb->douban_movies WHERE id = %d", $subject_id));
                if ($subject) {
                    $subject_type = $this->sanitize_subject_type($subject->type, '');
                }
            }

            if ($status && $fave_id) {
                $score = isset($_POST['score']) ? wp_unslash($_POST['score']) : '';
                $score = is_numeric($score) ? (string) min(10, max(0, (float) $score)) : '';
                $create_time = $this->sanitize_datetime_input(isset($_POST['create_time']) ? wp_unslash($_POST['create_time']) : '');
                if (!$create_time) {
                    $create_time = current_time('mysql');
                }
                $wpdb->update(
                    $wpdb->douban_faves,
                    [
                        'remark' => isset($_POST['remark']) ? sanitize_textarea_field(wp_unslash($_POST['remark'])) : '',
                        'score' => $score,
                        'create_time' => $create_time,
                        'status' => $status,
                    ],
                    [
                        'id' => $fave_id,
                    ]
                );
            } elseif ($subject_id) {
                $delete_where = ['subject_id' => $subject_id];
                if ($subject_type) {
                    $delete_where['type'] = $subject_type;
                }
                $wpdb->delete($wpdb->douban_faves, $delete_where);
            }
            $link = array(
                'page'                  => 'subject',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_safe_redirect($link);
            exit;
        }

        if ('edit_subject' === $post_action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $nonce = isset($_POST['wpd_subject_nonce']) ? wp_unslash($_POST['wpd_subject_nonce']) : '';
            if (!wp_verify_nonce($nonce, 'wpd_subject_edit')) {
                return;
            }
            global $wpdb;
            $subject_id = isset($_POST['subject_id']) ? absint($_POST['subject_id']) : 0;
            if ($subject_id) {
                $subject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->douban_movies WHERE id = %d", $subject_id));
                if ($subject) {
                    $this->wpd_remove_images($subject->douban_id);
                    $score = isset($_POST['douban_score']) ? wp_unslash($_POST['douban_score']) : '';
                    $score = is_numeric($score) ? (string) $score : '';
                    $wpdb->update(
                        $wpdb->douban_movies,
                        [
                            'name' => isset($_POST['name']) ? sanitize_text_field(wp_unslash($_POST['name'])) : '',
                            'douban_score' => $score,
                            'card_subtitle' => isset($_POST['card_subtitle']) ? sanitize_text_field(wp_unslash($_POST['card_subtitle'])) : '',
                            'poster' => isset($_POST['poster']) ? esc_url_raw(wp_unslash($_POST['poster'])) : ''
                        ],
                        [
                            'id' => $subject_id,
                        ]
                    );
                }
            }
            $link = array(
                'page' => 'subject_all',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_safe_redirect($link);
            exit;
        }

        if ('delete_subject' === $action) {
            if (!$this->has_manage_permission()) {
                return;
            }
            $subject_id = isset($_GET['subject_id']) ? absint($_GET['subject_id']) : 0;
            $request_type = $this->sanitize_subject_type(isset($_GET['subject_type']) ? wp_unslash($_GET['subject_type']) : '', '');
            $nonce = isset($_GET['_wpnonce']) ? wp_unslash($_GET['_wpnonce']) : '';
            if (!$subject_id || !wp_verify_nonce($nonce, 'wpd_subject_' . $subject_id)) {
                return;
            }
            global $wpdb;
            $subject = $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->douban_movies WHERE id = %d", $subject_id));
            if (!$subject) {
                return;
            }
            $this->wpd_remove_images($subject->douban_id);
            $subject_type = $request_type ? $request_type : $this->sanitize_subject_type($subject->type, '');
            $delete_where = ['subject_id' => $subject_id];
            if ($subject_type) {
                $delete_where['type'] = $subject_type;
            }

            $wpdb->delete($wpdb->douban_faves, $delete_where);

            $wpdb->delete(
                $wpdb->douban_movies,
                [
                    'id' => $subject_id,
                ]
            );


            $link = array(
                'page'                  => 'subject_all',
            );
            $link = add_query_arg($link, admin_url('admin.php'));
            wp_safe_redirect($link);
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
