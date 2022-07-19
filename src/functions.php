<?php

/***
 * Core Class
 */
class WPD_Douban
{
    const VERSION = '4.1.3';
    private $base_url = 'https://fatesinger.com/dbapi/';

    public function __construct()
    {
        $this->perpage = db_get_setting('perpage') ? db_get_setting('perpage') : 70;
        $plugin_file = plugin_basename(WPD_PATH . '/wp-douban.php');

        if (!$this->db_get_setting('disable_scripts')) add_action('wp_enqueue_scripts', [$this, 'wpd_load_scripts']);
        wp_embed_register_handler('doubanlist', '#https?:\/\/(\w+)\.douban\.com\/subject\/(\d+)#i', [$this, 'wp_embed_handler_doubanlist']);
        wp_embed_register_handler('doubanalbum', '#https?:\/\/www\.douban\.com\/(\w+)\/(\d+)#i', [$this, 'wp_embed_handler_doubanablum']);
        wp_embed_register_handler('doubandrama', '#https?:\/\/www\.douban\.com\/location\/(\w+)\/(\d+)#i', [$this, 'wp_embed_handler_doubandrama']);
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
                'style' => ''
            ),
            $atts
        ));
        return $this->render_collection($type, $start, $end, $style);
    }

    function plugin_action_links($actions, $plugin_file, $plugin_data, $context)
    {
        $new = array(
            'crontrol-events'    => sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=wpdouban')),
                '设置'
            ),
            'crontrol-schedules' => sprintf(
                '<a href="%s">%s</a>',
                esc_url(admin_url('admin.php?page=subject')),
                '条目'
            ),
            'crontrol-help' => sprintf(
                '<a href="%s" target="_blank">%s</a>',
                'https://fatesinger.com/101050',
                '帮助'
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

    public function render_collection($type, $start, $end, $style)
    {
        return '<div class="db--collection" data-type="' . $type . '" data-start="' . $start . '" data-end="' . $end . '" ' . ($style ? 'data-style="' . $style . '"' : '') . '></div>
        ';
    }

    public function render_template($include_types = ['movie', 'music', 'book', 'game', 'drama'], $style)
    {
        $types = ['movie', 'music', 'book', 'game', 'drama'];
        $nav = '';
        $i = 0;
        foreach ($types as $type) {
            if (in_array($type, $include_types)) {
                $nav .= '<div class="db--navItem JiEun' . ($i == 0 ? ' current' : '') . '" data-type="' . $type . '">' . $type . '</div>';
                $i++;
            }
        }
        if (count($include_types) == 1) {
            $nav = '';
        }
        $only = count($include_types) == 1 ? " data-type='{$include_types[0]}'" : '';
        return '<section class="db--container"><nav class="db--nav">' . $nav . '
    </nav>
    <div class="db--genres u-hide">
    </div>
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
        $type = $data['type'] ? $data['type'] : 'movie';
        global $wpdb;
        $goods = $wpdb->get_results("SELECT name FROM $wpdb->douban_genres WHERE `type` = '{$type}' GROUP BY `name`");
        $data = [];
        foreach ($goods as $good) {
            $data[] = $good;
        }
        return new WP_REST_Response($data);
    }

    public function get_collection($douban_id)
    {
        global $wpdb;
        $collection = $wpdb->get_row("SELECT * FROM $wpdb->douban_collection WHERE `douban_id` = '{$douban_id}'");
        if (!$collection) {
            return false;
        } else {
            return $collection;
        }
    }

    public function get_subjects($data)
    {
        global $wpdb;
        $offset = $data['paged'] ? ($data['paged'] - 1) * $this->perpage : 0;
        $type = $data['type'] ? $data['type'] : 'movie';
        $genre = $data['genre'] ? implode("','", json_decode($data['genre'], true)) : '';
        $filterTime = ($data['start_time'] && $data['end_time']) ? " AND f.create_time BETWEEN '{$data['start_time']}' AND '{$data['end_time']}'" : '';
        $top250 = $type == 'book' ? $this->get_collection('book_top250') : $this->get_collection('movie_top250');

        if ($genre) {
            $goods = $wpdb->get_results("SELECT m.*, f.create_time , f.remark FROM ( $wpdb->douban_movies m LEFT JOIN $wpdb->douban_genres g ON m.id = g.movie_id ) LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id WHERE f.type = '{$type}' AND f.status = 'done' AND g.name IN ('{$genre}') GROUP BY m.id ORDER BY f.create_time DESC LIMIT {$this->perpage} OFFSET {$offset}");
        } else {
            $goods = $wpdb->get_results("SELECT m.*, f.create_time, f.remark FROM $wpdb->douban_movies m LEFT JOIN $wpdb->douban_faves f ON m.id = f.subject_id WHERE f.type = '{$type}' AND f.status = 'done' {$filterTime} ORDER BY f.create_time DESC LIMIT {$this->perpage} OFFSET {$offset}");
        }

        $data = [];
        foreach ($goods as $good) {
            if ($this->db_get_setting('download_image')) $good->poster = $this->wpd_save_images($good->douban_id, $good->poster);
            $good->create_time = date('Y-m-d', strtotime($good->create_time));
            if ($top250 && ($good->type == 'movie' || $good->type == 'book') && $this->db_get_setting('top250')) {
                $re = $wpdb->get_results("SELECT * FROM $wpdb->douban_relation WHERE `collection_id` = {$top250->id} AND  `movie_id` = {$good->id}");
                $good->is_top250 = !empty($re);
            }
            $data[] = $good;
        }
        return new WP_REST_Response($data);
    }

    function wp_embed_handler_doubandrama($matches, $attr, $url, $rawattr)
    {
        if (!is_singular()) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if (!in_array($type, ['drama'])) return $url;
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    function wp_embed_handler_doubanablum($matches, $attr, $url, $rawattr)
    {
        if (!is_singular()) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if (!in_array($type, ['game'])) return $url;
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    public function wp_embed_handler_doubanlist($matches, $attr, $url, $rawattr)
    {
        if (!is_singular()) return $url;
        $type = $matches[1];
        if (!in_array($type, ['movie', 'book', 'music'])) return $url;
        $id = $matches[2];
        $html = $this->get_subject_detail($id, $type);
        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    public function get_subject_detail($id, $type)
    {
        $type = $type ? $type : 'movie';
        $data = $this->fetch_subject($id, $type);
        if (!$data) return;
        $cover = $this->db_get_setting('download_image') ? $this->wpd_save_images($id, $data->poster) : $data->poster;
        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="doulist-post"><img referrerpolicy="no-referrer" src="' .  $cover . '"></div>';
        if (db_get_setting("show_remark") && $data->fav_time) {
            $output .= '<div class="db--viewTime JiEun">Marked ' . $data->fav_time . '</div>';
        }
        $output .= '<div class="doulist-content"><div class="doulist-title"><a href="' . $data->link . '" class="cute" target="_blank" rel="external nofollow">' . $data->name . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data->douban_score * 10 . '%"></span></span><span class="rating_nums"> ' . $data->douban_score . ' </span></div>';
        $output .= '<div class="abstract">';
        $output .= db_get_setting("show_remark") && $data->remark ? $data->remark : $data->card_subtitle;
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
            'year' => '',
            'type' => $type,
            'pubdate' => $data['pubdate'] ? $data['pubdate'][0] : '',
            'card_subtitle' => $data['card_subtitle']
        ], ['id' => $movie->id]);
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
                'year' => '',
                'type' => $type,
                'pubdate' => $data['pubdate'] ? $data['pubdate'][0] : '',
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
                'year' => '',
                'type' => $type,
                'pubdate' => $data['pubdate'] ? $data['pubdate'][0] : '',
                'card_subtitle' => $data['card_subtitle'],
                'genres' => $data['genres']
            ];
        } else {
            return false;
        }
    }

    private function wpd_save_images($id, $url)
    {
        $e = ABSPATH . 'douban_cache/' . $id . '.jpg';
        if (!is_file($e)) copy(htmlspecialchars_decode($url), $e);
        $url = home_url('/') . 'douban_cache/' . $id . '.jpg';
        return $url;
    }

    public function wpd_load_scripts()
    {
        wp_enqueue_style('wpd-css', WPD_URL . "/assets/css/db.min.css", array(), WPD_VERSION, 'screen');
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
        wp_enqueue_script('wpdjs', WPD_URL . "/assets/js/db.min.js", array(), WPD_VERSION, true);
        wp_localize_script('wpdjs', 'wpd_base', array(
            'api' => get_rest_url(),
            'token' => $this->db_get_setting('token'),
        ));
    }

    public function get_collections($name = 'movie_top250')
    {
        $url = "{$this->base_url}subject_collection/{$name}/items?start=0&count=250&items_only=1&ck=xgtY&for_mobile=1";
        $response = wp_remote_get($url);
        $data = json_decode(wp_remote_retrieve_body($response), true);
        $interests = $data['subject_collection_items'];
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
            $movie = $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE `type` = '{$interest['type']}' AND douban_id = {$interest['id']}");
            $movie_id = '';
            if (!$movie) {
                $wpdb->insert(
                    $wpdb->douban_douban_movies,
                    array(
                        'name' => $interest['title'],
                        'poster' => $interest['pic']['large'],
                        'douban_id' => $interest['id'],
                        'douban_score' => $interest['rating']['value'],
                        'link' => $interest['url'],
                        'year' => '',
                        'type' => $interest['type'],
                        'pubdate' => '',
                        'card_subtitle' => $interest['card_subtitle'],
                    )
                );
                $movie_id = $wpdb->insert_id;
            } else {
                $movie_id = $movie->id;
            }

            $relation = $wpdb->get_row("SELECT * FROM $wpdb->douban_relation WHERE `movie_id` = {$movie_id} AND `collection_id` = {$collection_id}");

            if (!$relation) {
                $wpdb->insert($wpdb->douban_relation, [
                    'movie_id' => intval($movie_id),
                    'collection_id' => intval($collection_id)
                ]);
            }
        }
    }
}
