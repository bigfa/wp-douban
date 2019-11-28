<?php

function wpd_install(){
    $thumb_path = ABSPATH . "douban_cache/";
    if (file_exists ($thumb_path)) {
        if (! is_writeable ( $thumb_path )) {
            @chmod ( $thumb_path, '511' );
        }
    } else {
        @mkdir ( $thumb_path, '511', true );
    }
}

function movie_detail( $atts, $content = null ) {
    extract( shortcode_atts( array(
        'id' => '',
        'type' => ''
    ),$atts));
    $movieids =  explode(',', $id);
    if($type == 'music'){
        foreach ( $movieids as $movieid ){
            $output .= display_music_detail($movieid);
        }
    } elseif ($type == 'album'){
        foreach ( $movieids as $movieid ){
            $output .= display_album_detail($movieid);
        }
    } elseif ($type == 'book'){

        foreach ( $movieids as $movieid ){
            $output .= display_book_detail($movieid);
        }
    }else{
        foreach ( $movieids as $movieid ){
            $output .= display_movie_detail($movieid);
        }
    }
    return $output;
}

add_shortcode('douban', 'movie_detail');
add_filter('comment_text', 'do_shortcode');

function display_album_detail($id){
    $data = get_movie_detail($id,$type = 'album');
    $photodata = get_movie_detail($id,$type = 'photos');
    $photos = $photodata['photos'];
    $output = '<div class="doulist-item"><div class="doulist-album">';
    $output .= '<div class="title"><a href="'. $data["alt"] .'" class="cute" target="_blank" rel="external nofollow">'. $data["title"] .'</a></div>';
    $output .= '<div class="abstract">' . $data["desc"] . '</div>';
    $output .= '<div class="album-photo">';
    for ($i=0;$i < 4;$i++){
        $output .= '<img src="' . wpd_save_images($photos[$i]['id'],$photos[$i]['cover']) . '">';
    }
    $output .= '</div></div></div>';
    return $output;
}

function display_book_detail($id){
    $data = get_movie_detail($id,$type = 'book');
    $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img src="'.  wpd_save_images($id,$data['images']['medium']) .'"></div>';
    $output .= '<div class="content"><div class="title"><a href="'. $data["alt"] .'" class="cute" target="_blank" rel="external nofollow">'. $data["title"] .'</a></div>';
    $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["average"]*10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["average"]. ' </span><span>(' . $data["rating"]["numRaters"]. '人评价)</span></div>';
    $output .= '<div class="abstract">作者 : ';
    $authors = $data["author"];
    foreach ($authors as $key=>$author){
        $output .= $author;
        if($key != count($authors) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br>出版社 : ' . $data["publisher"] .'<br>出版年 : ';
    $output .= $data["pubdate"] .'<br>标签 : ';
    $tags = $data["tags"];
    foreach ($tags as $key=>$tag){
        $output .= $tag['name'];
        if($key != count($tags) - 1){
            $output .= ' / ';
        }
    }
    $output .= '</div></div></div></div>';
    return $output;
}

function display_music_detail($id){
    $data = get_movie_detail($id,$type = 'music');
    $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img src="'.  wpd_save_images($id,str_replace('spic','mpic',$data['image'])) .'"></div>';
    $output .= '<div class="content"><div class="title"><a href="'. $data["alt"] .'" class="cute" target="_blank" rel="external nofollow">'. $data["title"] .'</a></div>';
    $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["average"]*10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["average"]. ' </span><span>(' . $data["rating"]["numRaters"]. '人评价)</span></div>';
    $output .= '<div class="abstract">表演者 : ';
    $authors = $data["author"];
    foreach ($authors as $key=>$author){
        $output .= $author['name'];
        if($key != count($authors) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br>年份 : ' . $data["attrs"]["pubdate"][0] .'<br>标签 : ';
    $tags = $data["tags"];
    foreach ($tags as $key=>$tag){
        $output .= $tag['name'];
        if($key != count($tags) - 1){
            $output .= ' / ';
        }
    }
    $output .= '</div></div></div></div>';
    return $output;
}


function wpd_get_post_image($post_id) {
    $content         =  get_post_field('post_content', $post_id);
    $content         = apply_filters('the_content', $content);
    $defaltthubmnail = get_template_directory_uri() . '/build/images/default.jpeg';
    preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $content, $strResult, PREG_PATTERN_ORDER);
    $n = count($strResult[1]);
    if ($n > 0) {
        $output = $strResult[1][0];
    } else {
        $output = $defaltthubmnail;
    }
    return $output;
}

function get_movie_image( $id ) {
    $data = get_movie_detail($id,$type = 'movie');
    $image = wpd_save_images($id . 'large' ,$data['images']['large']);
    return $image;
}

function display_movie_detail($id){
    $data = get_movie_detail($id,$type = 'movie');
    $output = '<div class="doulist-item"><div class="doulist-subject"><div class="post"><img src="'.  wpd_save_images($id,$data['images']['medium']) .'"></div>';
    $output .= '<div class="content"><div class="title"><a href="'. $data["alt"] .'" class="cute" target="_blank" rel="external nofollow">'. $data["title"] .'</a></div>';
    $output .= '<div class="rating"><span class="allstardark"><span class="allstarlight" style="width:' . $data["rating"]["average"]*10 . '%"></span></span><span class="rating_nums"> ' . $data["rating"]["average"]. ' </span><span>(' . $data["ratings_count"]. '人评价)</span></div>';
    $output .= '<div class="abstract">导演 :';
    $directors = $data["directors"];
    foreach ($directors as $key=>$director){
        $output .= $director["name"];
        if($key != count($directors) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br >演员: ';
    $casts = $data["casts"];
    foreach ($casts as $key=>$cast){
        $output .= $cast["name"];
        if($key != count($casts) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br >';
    $output .= '类型: ';
    $genres = $data["genres"];
    foreach ($genres as $key=>$genre){
        $output .= $genre;
        if($key != count($genres) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br >制片国家/地区: ';
    $countries = $data["countries"];
    foreach ($countries as $key=>$country){
        $output .= $country;
        if($key != count($countries) - 1){
            $output .= ' / ';
        }
    }
    $output .= '<br>年份: ' . $data["year"] .'</div></div></div></div>';
    return $output;
}


function get_movie_detail($id,$type){
    $type = $type ? $type : 'movie';
    $cache_key = WPD_CACHE_KEY . $type . '_' . $id;
    $cache =  get_transient($cache_key);
    if($cache) return $cache;
    if( $type == 'movie'){
        $link = "https://api.douban.com/v2/movie/subject/".$id;
    } elseif ( $type == 'photos' ){
        $link = "https://api.douban.com/v2/album/" . $id ."/photos";
    } elseif ( $type == 'album' ){
        $link = "https://api.douban.com/v2/album/" . $id;
    } elseif ( $type == 'book' ){
        $link = "https://api.douban.com/v2/book/" . $id;
    } else{
        $link = "https://api.douban.com/v2/music/".$id;
    }
    $link .= '?apikey=0df993c66c0c636e29ecbb5344252a4a';
    delete_transient($cache_key);
    $ch=@curl_init($link);
    @curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $cexecute=@curl_exec($ch);
    @curl_close($ch);
    if ($cexecute) {
        $cache = json_decode($cexecute,true);
        set_transient($cache_key, $cache, WPD_CACHE_TIME );
    } else {
        return false;
    }
    return $cache;
}

function wpd_save_images($id,$url){
    $e = ABSPATH .'douban_cache/'. $id .'.jpg';
    $t = WPD_CACHE_TIME;
    if ( !is_file($e) ) copy(htmlspecialchars_decode($url), $e);
    $url = home_url('/').'douban_cache/'. $id .'.jpg';
    return $url;
}

function wpd_load_scripts(){
    wp_enqueue_style('wpd-css', WPD_URL . "/assets/css/style.css", array(), WPD_VERSION, 'screen');
}

add_action('wp_enqueue_scripts', 'wpd_load_scripts');

require('embed.php');