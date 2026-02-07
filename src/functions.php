<?php

/***
 * Core Class
 */
class WPD_Douban
{
    const VERSION = '4.5.0';
    private $base_url = 'https://fatesinger.com/dbapi/';
    private $perpage = 70;
    private $uid;

    public function __construct()
    {
        $this->perpage = $this->db_get_setting('perpage') ? $this->db_get_setting('perpage') : 70;
        $plugin_file = plugin_basename(WPD_PATH . '/wp-douban.php');

        if (!$this->db_get_setting('disable_scripts')) add_action('wp_enqueue_scripts', [$this, 'wpd_load_scripts']);
        wp_embed_register_handler('doubanlist', '#https?:\/\/(\w+)\.douban\.com\/subject\/(\d+)#i', [$this, 'wp_embed_handler_doubanlist']);
        wp_embed_register_handler('doubanalbum', '#https?:\/\/www\.douban\.com\/(\w+)\/(\d+)#i', [$this, 'wp_embed_handler_doubanablum']);
        wp_embed_register_handler('doubandrama', '#https?:\/\/www\.douban\.com\/location\/(\w+)\/(\d+)#i', [$this, 'wp_embed_handler_doubandrama']);

        wp_embed_register_handler('themoviedb', '#https?:\/\/www\.themoviedb\.org\/(\w+)\/(\d+)#i', [$this, 'wp_embed_handler_the_movie_db']);

        add_action('rest_api_init', [$this, 'wpd_register_rest_routes']);
        add_filter("plugin_action_links_{$plugin_file}", [$this, 'plugin_action_links'], 10, 4);
        add_shortcode('wpd', [$this, 'list_shortcode']);
        add_shortcode('wpc', [$this, 'list_collection']);
        add_action('wp_head', [$this, 'db_custom_style']);
    }

    public function add_log($type = 'movie')
    {
        global $wpdb;
        $wpdb->insert($wpdb->douban_log, [
            'type' => $type,
            'action' => 'sync',
            'create_time' => date('Y-m-d H:i:s'),
            'status' => 'success',
            'message' => 'sync success',
            'account_id' => $this->uid
        ]);
    }

    public function db_custom_style()
    {
        if ($this->db_get_setting('css')) echo  '<style>' . $this->db_get_setting('css') . '</style>';
    }

    public function list_shortcode($atts, $content = null)
    {
        extract(shortcode_atts(
            array(
                'types' => '',
                'style' => ''
            ),
            $atts
        ));
        $types = explode(',', $types);
        if (empty($types)) {
            return;
        }
        return $this->render_template($types, $style);
    }

    public function list_collection($atts, $content = null)
    {
        extract(shortcode_atts(
            array(
                'type' => '',
                'start' => '',
                'end' => '',
                'status' => '',
                'style' => ''
            ),
            $atts
        ));
        return $this->render_collection($type, $start, $end, $status, $style);
    }

    function plugin_action_links($actions, $plugin_file, $plugin_data, $context)
    {
        $new = array(
            'crontrol-events'    => sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=wpdouban')),
                esc_html__('Settings', 'wp-douban')
            ),
            'crontrol-schedules' => sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=subject')),
                esc_html__('Items', 'wp-douban')
            ),
            'crontrol-help' => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://fatesinger.com/101050',
                esc_html__('Help', 'wp-douban')
            ),
        );

        return array_merge($new, $actions);
    }

    public function db_get_setting($key = NULL)
    {
        $setting = get_option('db_setting');
        if (isset($setting[$key])) {
            return $setting[$key];
        } else {
            return false;
        }
    }

    protected function sanitize_subject_type($type = '', $default = 'movie')
    {
        $allowed_types = ['movie', 'music', 'book', 'game', 'drama', 'tv'];
        $type = sanitize_key((string) $type);
        return in_array($type, $allowed_types, true) ? $type : $default;
    }

    protected function sanitize_subject_status($status = '', $default = 'done')
    {
        $allowed_status = ['done', 'doing', 'mark'];
        $status = sanitize_key((string) $status);
        return in_array($status, $allowed_status, true) ? $status : $default;
    }

    protected function sanitize_date_value($date = '')
    {
        $date = trim((string) $date);
        if (!$date) {
            return '';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return $date;
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2}$/', $date)) {
            return $date;
        }

        return '';
    }

    public function render_collection($type, $start, $end, $status, $style)
    {
        return '<div class="db--collection" data-status="' . $status . '" data-type="' . $type . '" data-start="' . $start . '" data-end="' . $end . '" ' . ($style ? 'data-style="' . $style . '"' : '') . '></div>
        ';
    }

    public function render_template($include_types = ['movie', 'music', 'book', 'game', 'drama'], $style = '')
    {
        $types = ['movie', 'music', 'book', 'game', 'drama'];
        $type_labels = [
            'movie' => __('Movies', 'wp-douban'),
            'music' => __('Music', 'wp-douban'),
            'book' => __('Books', 'wp-douban'),
            'game' => __('Games', 'wp-douban'),
            'drama' => __('Drama', 'wp-douban'),
        ];
        $nav = '';
        $i = 0;
        foreach ($types as $type) {
            if (in_array($type, $include_types)) {
                $label = isset($type_labels[$type]) ? $type_labels[$type] : $type;
                $nav .= '<div class="db--navItem JiEun' . ($i == 0 ? ' current' : '') . '" data-type="' . esc_attr($type) . '">' . esc_html($label) . '</div>';
                $i++;
            }
        }
        if (count($include_types) == 1) {
            $nav = '';
        }
        $only = count($include_types) == 1 ? " data-type='{$include_types[0]}'" : '';
        $show_type = $this->db_get_setting("show_type") ? '<div class="db--type">
        <div class="db--typeItem" data-status="mark">' . esc_html__('Want to watch', 'wp-douban') . '</div>
        <div class="db--typeItem" data-status="doing">' . esc_html__('Watching', 'wp-douban') . '</div>
        <div class="db--typeItem is-active" data-status="done">' . esc_html__('Watched', 'wp-douban') . '</div>
    </div>' : '';
        return '<section class="db--container"><nav class="db--nav">' . $nav . '
    </nav>
    <div class="db--genres u-hide">
    </div>
    ' . $show_type . '
    <div class="db--list' . ($style ? ' db--list__' . $style  : '') . '"' . $only . '>
    </div>
    <div class="block-more block-more__centered">
        <div class="lds-ripple">
        </div>
    </div><div class="db--copyright">Rendered by <a href="https://fatesinger.com/101005" target="_blank">WP-Douban</a></div></section>';
    }

    public function wpd_register_rest_routes()
    {
        register_rest_route('v1', '/movies', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_subjects'],
            'permission_callback' => '__return_true',
        ));

        register_rest_route('v1', '/movie/genres', array(
            'methods' => 'GET',
            'callback' => [$this, 'get_genres'],
            'permission_callback' => '__return_true',
        ));
    }

    public function get_genres($data)
    {
        $type = $this->sanitize_subject_type(isset($data['type']) ? $data['type'] : 'movie');
        global $wpdb;
        $query = $wpdb->prepare("SELECT name FROM $wpdb->douban_genres WHERE `type` = %s GROUP BY `name`", $type);
        $goods = $wpdb->get_results($query);
        $data = [];
        foreach ($goods as $good) {
            $data[] = $good;
        }
        return new WP_REST_Response($data);
    }

    public function get_collection($douban_id)
    {
        global $wpdb;
        $collection = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $wpdb->douban_collection WHERE `douban_id` = %s", (string) $douban_id)
        );
        if (!$collection) {
            return false;
        } else {
            return $collection;
        }
    }

    public function get_subjects($data)
    {
        global $wpdb;
        $paged = isset($data['paged']) ? absint($data['paged']) : 1;
        if ($paged < 1) {
            $paged = 1;
        }
        $limit = absint($this->perpage);
        if ($limit < 1) {
            $limit = 70;
        } elseif ($limit > 200) {
            $limit = 200;
        }
        $offset = ($paged - 1) * $limit;
        $type = $this->sanitize_subject_type(isset($data['type']) ? $data['type'] : 'movie');
        $status = $this->sanitize_subject_status(isset($data['status']) ? $data['status'] : 'done');
        $genres = [];
        if (!empty($data['genre'])) {
            $genre_data = json_decode($data['genre'], true);
            if (is_array($genre_data)) {
                foreach ($genre_data as $genre_name) {
                    $genre_name = sanitize_text_field((string) $genre_name);
                    if ($genre_name !== '') {
                        $genres[] = $genre_name;
                    }
                }
                $genres = array_values(array_unique($genres));
            }
        }
        $start_time = $this->sanitize_date_value(isset($data['start_time']) ? $data['start_time'] : '');
        $end_time = $this->sanitize_date_value(isset($data['end_time']) ? $data['end_time'] : '');
        if (!$end_time) {
            $end_time = date('Y-m-d');
        }
        $top250 = $type == 'book' ? $this->get_collection('book_top250') : $this->get_collection('movie_top250');
        if (!empty($genres)) {
            $genre_placeholders = implode(',', array_fill(0, count($genres), '%s'));
            $query_params = array_merge([$type, $status], $genres, [$limit, $offset]);
            $query = $wpdb->prepare(
                "SELECT m.*, f.create_time , f.remark , f.status
                 FROM ( $wpdb->douban_movies m LEFT JOIN $wpdb->douban_genres g ON m.id = g.movie_id )
                 LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id
                 WHERE f.type = %s AND f.status = %s AND g.name IN ($genre_placeholders)
                 GROUP BY m.id
                 ORDER BY f.create_time DESC
                 LIMIT %d OFFSET %d",
                $query_params
            );
            $goods = $wpdb->get_results($query);
        } else {
            $query = "SELECT m.*, f.create_time, f.remark, f.status
                      FROM $wpdb->douban_movies m
                      LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id
                      WHERE f.type = %s AND f.status = %s";
            $query_params = [$type, $status];

            if ($start_time) {
                $query .= " AND f.create_time BETWEEN %s AND %s";
                $query_params[] = $start_time;
                $query_params[] = $end_time;
            }

            $query .= " ORDER BY f.create_time DESC LIMIT %d OFFSET %d";
            $query_params[] = $limit;
            $query_params[] = $offset;

            $goods = $wpdb->get_results($wpdb->prepare($query, $query_params));
        }

        $data = [];
        foreach ($goods as $good) {
            if ($this->db_get_setting('download_image')) {
                $good->poster = $this->wpd_save_images($good->douban_id, $good->poster);
            } else if (in_array($good->type, ['movie', 'book', 'music'])) {
                $good->poster = "https://dou.img.lithub.cc/" . $type . "/" . $good->douban_id . ".jpg";
            }
            $good->create_time = date('Y-m-d', strtotime($good->create_time));
            if ($top250 && ($good->type == 'movie' || $good->type == 'book') && $this->db_get_setting('top250')) {
                $re = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $wpdb->douban_relation WHERE `collection_id` = %d AND `movie_id` = %d",
                        absint($top250->id),
                        absint($good->id)
                    )
                );
                $good->is_top250 = !empty($re);
            }
            $data[] = $good;
        }
        return new WP_REST_Response($data);
    }

    function wp_embed_handler_the_movie_db($matches, $attr, $url, $rawattr)
    {

        if ((!is_singular() && !$this->db_get_setting('home_render')) || !$this->db_get_setting('api_key')) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if (!in_array($type, ['tv', 'movie'])) return $url;
        $html = $this->get_the_movie_db_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    function wp_embed_handler_doubandrama($matches, $attr, $url, $rawattr)
    {
        if (!is_singular() && !$this->db_get_setting('home_render')) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if (!in_array($type, ['drama'])) return $url;
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    function wp_embed_handler_doubanablum($matches, $attr, $url, $rawattr)
    {
        if (!is_singular() && !$this->db_get_setting('home_render')) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if (!in_array($type, ['game'])) return $url;
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    public function wp_embed_handler_doubanlist($matches, $attr, $url, $rawattr)
    {
        if (!is_singular() && !$this->db_get_setting('home_render')) return $url;
        $type = $matches[1];
        if (!in_array($type, ['movie', 'book', 'music'])) return $url;
        $id = $matches[2];
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    public function get_the_movie_db_detail($id, $type)
    {
        $type = $type ? $type : 'movie';
        $data = $this->fetch_tmdb_subject($id, $type);
        if (!$data) return;
        $cover = $this->db_get_setting('download_image') ? $this->wpd_save_images($id, $data->poster, 'tmdb') : $data->poster;
        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="doulist-post"><img referrerpolicy="no-referrer" src="' .  $cover . '"></div>';
        if (db_get_setting("show_remark") && $data->fav_time) {
            $output .= '<div class="db--viewTime JiEun">Marked ' . $data->fav_time . '</div>';
        }
        $output .= '<div class="doulist-content"><div class="doulist-title"><a href="' . ($data->link ? $data->link : "https://www.themoviedb.org/" . $type . "/" . $id) . '" class="cute" target="_blank" rel="external nofollow">' . $data->name . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data->douban_score * 10 . '%"></span></span><span class="rating_nums"> ' . $data->douban_score . ' </span></div>';
        $output .= '<div class="abstract">';
        $output .= $this->db_get_setting("show_remark") && $data->remark ? $data->remark : $data->card_subtitle;
        $output .= '</div></div></div></div>';
        return $output;
    }

    public function get_subject_detail($id, $type)
    {
        $type = $type ? $type : 'movie';
        $data = $this->fetch_subject($id, $type);
        if (!$data) return;
        $cover = $this->db_get_setting('download_image') ? $this->wpd_save_images($id, $data->poster) : (in_array($data->type, ['movie', 'book', 'music']) ? "https://dou.img.lithub.cc/" . $type . "/" . $data->douban_id . ".jpg" : $data->poster);
        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="doulist-post"><img referrerpolicy="no-referrer" src="' .  $cover . '"></div>';
        if ($this->db_get_setting("show_remark") && $data->fav_time) {
            $output .= '<div class="db--viewTime JiEun">Marked ' . $data->fav_time . '</div>';
        }
        $output .= '<div class="doulist-content"><div class="doulist-title"><a href="' . $data->link . '" class="cute" target="_blank" rel="external nofollow">' . $data->name . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data->douban_score * 10 . '%"></span></span><span class="rating_nums"> ' . $data->douban_score . ' </span></div>';
        $output .= '<div class="abstract">';
        $output .= $this->db_get_setting("show_remark") && $data->remark ? $data->remark : $data->card_subtitle;
        $output .= '</div></div></div></div>';
        return $output;
    }

    public function sync_subject($id, $type)
    {
        $type = $type ? $type : 'movie';
        global $wpdb;
        $movie = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE `type` = '{$type}' AND id = '{$id}'");
        if (empty($movie)) {
            return false;
        }

        if ($movie->tmdb_id) {
            $response = wp_remote_get("https://hk.fatesinger.com/api/" . $movie->tmdb_type . "/" . $movie->tmdb_id . "?api_key=" . $this->db_get_setting('api_key') . "&language=zh-CN", ['sslverify' => false]);
            if (is_wp_error($response)) {
                return false;
            }

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if ($data) {
                $wpdb->update($wpdb->douban_movies, [
                    'name' => isset($data['title']) ? $data['title'] : $data['name'],
                    'poster' => "https://image.tmdb.org/t/p/original" . $data['poster_path'],
                    'douban_id' => $data['id'],
                    'douban_score' => $data['vote_average'],
                    'link' => $data['homepage'],
                    'year' => '',
                    'type' => 'movie',
                    'pubdate' => isset($data['release_date']) ? $data['release_date'] : $data['first_air_date'],
                    'card_subtitle' => $data['overview'],
                ], ['id' => $movie->id]);
                return true;
            }
        }

        if ($type == 'movie') {
            $link = $this->base_url . "movie/" . $movie->douban_id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'book') {
            $link = $this->base_url . "book/" . $movie->douban_id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'game') {
            $link = $this->base_url . "game/" . $movie->douban_id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'drama') {
            $link = $this->base_url . "drama/" . $movie->douban_id . "?ck=xgtY&for_mobile=1";
        } else {
            $link = $this->base_url . "music/" . $movie->douban_id . "?ck=xgtY&for_mobile=1";
        }

        $response = wp_remote_get($link, ['sslverify' => false]);
        if (is_wp_error($response)) {
            return false;
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!$data) {
            return false;
        }


        $wpdb->update($wpdb->douban_movies, [
            'name' => $data['title'],
            'poster' => $data['pic']['large'],
            'douban_id' => $data['id'],
            'douban_score' => $data['rating']['value'],
            'link' => $data['url'],
            'year' => $data['year'] ? $data['year'] : '',
            'type' => $type,
            'pubdate' => $data['pubdate'] ? $data['pubdate'][0] : '',
            'card_subtitle' => $data['card_subtitle']
        ], ['id' => $movie->id]);
    }

    public function fetch_tmdb_subject($id, $type)
    {
        $type = $type ? $type : 'movie';
        global $wpdb;
        $movie = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE `tmdb_type` = '{$type}' AND tmdb_id = '{$id}'");
        if ($movie) {
            $movie->genres = [];
            $genres = $wpdb->get_results("SELECT * FROM $wpdb->douban_genres WHERE `type` = '{$type}' AND `movie_id` = {$movie->id}");
            if (!empty($genres)) {
                foreach ($genres as $genre) {
                    $movie->genres[] = $genre->name;
                }
            }
            $fav = $wpdb->get_row("SELECT * FROM $wpdb->douban_faves WHERE `type` = '{$type}' AND `subject_id` = '{$movie->id}'");
            if ($fav) {
                $movie->fav_time = $fav->create_time;
                $movie->score = $fav->score;
                $movie->remark = $fav->remark;
            } else {
                $movie->fav_time = "";
                $movie->score = "";
                $movie->remark = "";
            }
            return $movie;
        }

        $response = wp_remote_get("https://hk.fatesinger.com/api/" . $type . "/" . $id . "?api_key=" . $this->db_get_setting('api_key') . "&language=zh-CN", ['sslverify' => false]);
        if (is_wp_error($response)) {
            return false;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($data) {
            $wpdb->insert($wpdb->douban_movies, [
                'name' => isset($data['title']) ? $data['title'] : $data['name'],
                'poster' => "https://image.tmdb.org/t/p/original" . $data['poster_path'],
                'douban_id' => $data['id'],
                'douban_score' => $data['vote_average'],
                'link' => $data['homepage'],
                'year' => '',
                'type' => 'movie',
                'pubdate' => isset($data['release_date']) ? $data['release_date'] : $data['first_air_date'],
                'card_subtitle' => $data['overview'],
                'tmdb_type' => $type,
                'tmdb_id' => $data['id']
            ]);
            $movie_id = '';
            if ($wpdb->insert_id) {
                $movie_id = $wpdb->insert_id;
                if ($data['genres']) foreach ($data['genres'] as $genre) {
                    $wpdb->insert(
                        $wpdb->douban_genres,
                        [
                            'movie_id' => $movie_id,
                            'name' => $genre['name'],
                            'type' => $type,
                        ]
                    );
                }
            }
            return (object) [
                'id' => $movie_id,
                'name' => isset($data['title']) ? $data['title'] : $data['name'],
                'poster' => "https://image.tmdb.org/t/p/original" . $data['poster_path'],
                'douban_id' => $data['id'],
                'douban_score' => $data['vote_average'],
                'link' => $data['homepage'],
                'year' => '',
                'type' => $type,
                'pubdate' => isset($data['release_date']) ? $data['release_date'] : $data['first_air_date'],
                'card_subtitle' => $data['overview'],
                'genres' => $data['genres'],
                'fav_time' => '',
                'remark' => '',
                'score' => ''
            ];
        } else {
            return false;
        }
    }

    public function fetch_subject($id, $type)
    {
        $type = $type ? $type : 'movie';
        global $wpdb;
        $movie = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE `type` = '{$type}' AND douban_id = '{$id}'");
        if ($movie) {
            $movie->genres = [];
            $genres = $wpdb->get_results("SELECT * FROM $wpdb->douban_genres WHERE `type` = '{$type}' AND `movie_id` = {$movie->id}");
            if (!empty($genres)) {
                foreach ($genres as $genre) {
                    $movie->genres[] = $genre->name;
                }
            }
            $fav = $wpdb->get_row("SELECT * FROM $wpdb->douban_faves WHERE `type` = '{$type}' AND `subject_id` = '{$movie->id}'");
            if ($fav) {
                $movie->fav_time = $fav->create_time;
                $movie->score = $fav->score;
                $movie->remark = $fav->remark;
            } else {
                $movie->fav_time = "";
                $movie->score = "";
                $movie->remark = "";
            }
            return $movie;
        }

        if ($type == 'movie') {
            $link = $this->base_url . "movie/" . $id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'book') {
            $link = $this->base_url . "book/" . $id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'game') {
            $link = $this->base_url . "game/" . $id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'drama') {
            $link = $this->base_url . "drama/" . $id . "?ck=xgtY&for_mobile=1";
        } else {
            $link = $this->base_url . "music/" . $id . "?ck=xgtY&for_mobile=1";
        }
        $response = wp_remote_get($link, ['sslverify' => false]);
        if (is_wp_error($response)) {
            return false;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($data) {
            $wpdb->insert($wpdb->douban_movies, [
                'name' => $data['title'],
                'poster' => $data['pic']['large'],
                'douban_id' => $data['id'],
                'douban_score' => $data['rating']['value'],
                'link' => $data['url'],
                'year' => $data['year'] ? $data['year'] : '',
                'type' => $type,
                'pubdate' => isset($data['pubdate']) ? $data['pubdate'][0] : '',
                'card_subtitle' => $data['card_subtitle']
            ]);
            $movie_id = '';
            if ($wpdb->insert_id) {
                $movie_id = $wpdb->insert_id;
                if ($data['genres']) foreach ($data['genres'] as $genre) {
                    $wpdb->insert(
                        $wpdb->douban_genres,
                        [
                            'movie_id' => $movie_id,
                            'name' => $genre,
                            'type' => $type,
                        ]
                    );
                }
            }
            return (object) [
                'id' => $movie_id,
                'name' => $data['title'],
                'poster' => $data['pic']['large'],
                'douban_id' => $data['id'],
                'douban_score' => $data['rating']['value'],
                'link' => $data['url'],
                'year' => $data['year'] ? $data['year'] : '',
                'type' => $type,
                'pubdate' => isset($data['pubdate']) ? $data['pubdate'][0] : '',
                'card_subtitle' => $data['card_subtitle'],
                'genres' => $data['genres'],
                'fav_time' => '',
                'remark' => '',
                'score' => ''
            ];
        } else {
            return false;
        }
    }

    private function wpd_save_images($id, $url, $type = "")
    {
        $e = ABSPATH . 'douban_cache/' . $type . $id . '.jpg';
        if (!is_file($e)) {

            $referer = 'https://m.douban.com';
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_REFERER, $referer); // 设置Referer头信息
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 12_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/12.0 Mobile/15A372 Safari/604.1');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $imageData = curl_exec($ch);
            curl_close($ch);
            file_put_contents($e, $imageData);
        }
        $url = home_url('/') . 'douban_cache/' . $type . $id . '.jpg';
        return $url;
    }

    public function wpd_load_scripts()
    {
        wp_enqueue_style('wpd-css', WPD_URL . "/assets/css/db.min.css", [], WPD_VERSION, 'screen');
        $dark = $this->db_get_setting('dark_mode') == 'auto'  ? "@media (prefers-color-scheme: dark) {
            :root {
            --db-main-color: rgba(0, 87, 217);
            --db-hover-color: rgba(255, 255, 255, 0.5);
            --db--text-color: rgba(255, 255, 255, 0.8);
            --db--text-color-light: rgba(255, 255, 255, 0.6);
            --db--background-gray: #3c3c3c;
            --db-border-color: rgba(255, 255, 255, 0.1);
        }
    }" : ":root {
        --db-main-color: rgba(0, 87, 217);
        --db-hover-color: rgba(255, 255, 255, 0.5);
        --db--text-color: rgba(255, 255, 255, 0.8);
        --db--text-color-light: rgba(255, 255, 255, 0.6);
        --db--background-gray: #3c3c3c;
        --db-border-color: rgba(255, 255, 255, 0.1);
    }";
        if ($this->db_get_setting('dark_mode') == 'auto' || $this->db_get_setting('dark_mode') == 'dark') wp_add_inline_style('wpd-css', $dark);
        wp_enqueue_script('wpdjs', WPD_URL . "/assets/js/db.min.js", [], WPD_VERSION, true);
        wp_localize_script('wpdjs', 'wpd_base', [
            'api' => get_rest_url(),
            'token' => $this->db_get_setting('token'),
        ]);
    }

    public function get_collections($name = 'movie_top250')
    {
        $url = "{$this->base_url}subject_collection/{$name}/items?start=0&count=250&items_only=1&ck=xgtY&for_mobile=1";
        $response = wp_remote_get($url);
        if (is_wp_error($response)) {
            return false;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        $interests = isset($data['subject_collection_items']) && is_array($data['subject_collection_items']) ? $data['subject_collection_items'] : [];
        if (empty($interests)) {
            return false;
        }
        global $wpdb;
        $collection = $this->get_collection($name);
        $collection_id = '';
        if (!$collection) {
            $wpdb->insert($wpdb->douban_collection, [
                'douban_id' => $name,
                'name' => $name,
            ]);
            $collection_id = $wpdb->insert_id;
        } else {
            $collection_id =  $collection->id;
        }

        foreach ($interests as $interest) {
            $subject_type = $this->sanitize_subject_type(isset($interest['type']) ? $interest['type'] : 'movie');
            $douban_id = isset($interest['id']) ? intval($interest['id']) : 0;
            if (!$douban_id) {
                continue;
            }
            $movie = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $wpdb->douban_movies WHERE `type` = %s AND douban_id = %d",
                    $subject_type,
                    $douban_id
                )
            );
            $movie_id = '';
            if (!$movie) {
                $wpdb->insert(
                    $wpdb->douban_movies,
                    array(
                        'name' => isset($interest['title']) ? $interest['title'] : '',
                        'poster' => isset($interest['pic']['large']) ? $interest['pic']['large'] : '',
                        'douban_id' => $douban_id,
                        'douban_score' => isset($interest['rating']['value']) ? $interest['rating']['value'] : '',
                        'link' => isset($interest['url']) ? $interest['url'] : '',
                        'year' => '',
                        'type' => $subject_type,
                        'pubdate' => '',
                        'card_subtitle' => isset($interest['card_subtitle']) ? $interest['card_subtitle'] : '',
                    )
                );
                $movie_id = $wpdb->insert_id;
            } else {
                $movie_id = $movie->id;
            }

            if (!$movie_id) {
                continue;
            }
            $relation = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $wpdb->douban_relation WHERE `movie_id` = %d AND `collection_id` = %d",
                    absint($movie_id),
                    absint($collection_id)
                )
            );

            if (!$relation) {
                $wpdb->insert($wpdb->douban_relation, [
                    'movie_id' => intval($movie_id),
                    'collection_id' => intval($collection_id)
                ]);
            }
        }
    }
}
