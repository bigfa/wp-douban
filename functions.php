<?php

class WPD_Douban
{
    const VERSION = '4.0.0';
    private $base_url = 'https://fatesinger.com/dbapi/';

    public function __construct()
    {
        add_shortcode('douban', [$this, 'movie_detail']);
        add_filter('comment_text', [$this, 'do_shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'wpd_load_scripts']);
        wp_embed_register_handler('doubanlist', '#https?:\/\/(\w+)\.douban\.com\/subject\/(\d+)#i', [$this, 'wp_embed_handler_doubanlist']);
    }

    public function wp_embed_handler_doubanlist($matches, $attr, $url, $rawattr)
    {
        if (!is_singular()) return $url;
        $type = $matches[1];
        $id = $matches[2];
        if ($type == 'music') {
            $html = $this->display_music_detail($id);
        } elseif ($type == 'movie') {
            $html = $this->display_movie_detail($id);
        } elseif ($type == 'book') {
            $html = $this->display_book_detail($id);
        }

        return apply_filters('embed_forbes', $html, $matches, $attr, $url, $rawattr);
    }

    public function movie_detail($atts, $content = null)
    {
        extract(shortcode_atts(array(
            'id' => '',
            'type' => ''
        ), $atts));
        $movieids =  explode(',', $id);
        if ($type == 'music') {
            foreach ($movieids as $movieid) {
                $output .= $this->display_music_detail($movieid);
            }
        } elseif ($type == 'book') {

            foreach ($movieids as $movieid) {
                $output .= $this->display_book_detail($movieid);
            }
        } else {
            foreach ($movieids as $movieid) {
                $output .= $this->display_movie_detail($movieid);
            }
        }
        return $output;
    }


    public function display_book_detail($id)
    {
        $data = $this->get_movie_detail($id, 'book');
        $cover =  WPD_CACHE_IMAGE ? $this->wpd_save_images($id, $data['pic']['large']) : $data['pic']['large'];
        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img referrerpolicy="no-referrer" src="' .  $cover . '"></div>';
        $output .= '<div class="content"><div class="title"><a href="' . $data["url"] . '" class="cute" target="_blank" rel="external nofollow">' . $data["title"] . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["value"] * 10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["value"] . ' </span><span>(' . $data["rating"]["count"] . '人评价)</span></div>';
        $output .= '<div class="abstract">作者 : ';
        $authors = $data["author"];
        foreach ($authors as $key => $author) {
            $output .= $author;
            if ($key != count($authors) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br>出版社 : ' . $data["press"][0] . '<br>出版年 : ';
        $output .= $data["pubdate"][0];
        $output .= '</div></div></div></div>';
        return $output;
    }

    public function display_music_detail($id)
    {
        $data = $this->get_movie_detail($id, 'music');
        $cover =  WPD_CACHE_IMAGE ? $this->wpd_save_images($id, $data['pic']['large']) : $data['pic']['large'];
        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img referrerpolicy="no-referrer" src="' . $cover . '"></div>';
        $output .= '<div class="content"><div class="title"><a href="' . $data["url"] . '" class="cute" target="_blank" rel="external nofollow">' . $data["title"] . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["value"] * 10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["value"] . ' </span><span>(' . $data["rating"]["count"] . '人评价)</span></div>';
        $output .= '<div class="abstract">表演者 : ';
        $authors = $data["singer"];
        foreach ($authors as $key => $author) {
            $output .= $author['name'];
            if ($key != count($authors) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br>年份 : ' . $data["pubdate"][0] . '<br>风格 : ';
        $tags = $data["genres"];
        foreach ($tags as $key => $tag) {
            $output .= $tag;
            if ($key != count($tags) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '</div></div></div></div>';
        return $output;
    }

    public function display_movie_detail($id)
    {
        $data = $this->get_movie_detail($id, 'movie');
        $cover =  WPD_CACHE_IMAGE ? $this->wpd_save_images($id, $data['pic']['large']) : $data['pic']['large'];

        $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img referrerpolicy="no-referrer" src="' .  $cover . '"></div>';
        $output .= '<div class="content"><div class="title"><a href="' . $data["url"] . '" class="cute" target="_blank" rel="external nofollow">' . $data["title"] . '</a></div>';
        $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["value"] * 10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["value"] . ' </span><span>(' . $data["rating"]["count"] . '人评价)</span></div>';
        $output .= '<div class="abstract">导演 :';
        $directors = $data["directors"];
        foreach ($directors as $key => $director) {
            $output .= $director["name"];
            if ($key != count($directors) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br >演员: ';
        $casts = $data["actors"];
        foreach ($casts as $key => $cast) {
            $output .= $cast["name"];
            if ($key != count($casts) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br >';
        $output .= '类型: ';
        $genres = $data["genres"];
        foreach ($genres as $key => $genre) {
            $output .= $genre;
            if ($key != count($genres) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br >制片国家/地区: ';
        $countries = $data["countries"];
        foreach ($countries as $key => $country) {
            $output .= $country;
            if ($key != count($countries) - 1) {
                $output .= ' / ';
            }
        }
        $output .= '<br>年份: ' . $data["year"] . '</div></div></div></div>';
        return $output;
    }


    public function get_movie_detail($id, $type)
    {
        $type = $type ? $type : 'movie';
        $cache_key = WPD_CACHE_KEY . $type . '_' . $id;
        $cache =  get_transient($cache_key);
        if ($cache) return $cache;
        if ($type == 'movie') {
            $link = $this->base_url . "movie/" . $id . "?ck=xgtY&for_mobile=1";
        } elseif ($type == 'book') {
            $link = $this->base_url . "book/" . $id . "?ck=xgtY&for_mobile=1";
        } else {
            $link = $this->base_url . "music/" . $id . "?ck=xgtY&for_mobile=1";
        }
        delete_transient($cache_key);
        $response = wp_remote_get($link);
        if (is_wp_error($response)) {
            return false;
        }
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if ($data) {
            set_transient($cache_key, $data, WPD_CACHE_TIME);
        } else {
            return false;
        }
        return $data;
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
        wp_enqueue_style('wpd-css', WPD_URL . "/assets/css/style.css", array(), WPD_VERSION, 'screen');
    }
}
