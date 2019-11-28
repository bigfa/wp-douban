<?php

wp_embed_register_handler( 'doubanlist', '#https?:\/\/(\w+)\.douban\.com\/subject\/(\d+)#i', 'wp_embed_handler_doubanlist' );
wp_embed_register_handler( 'doubanalbum', '#https?:\/\/www\.douban\.com\/photos\/album\/(\d+)#i', 'wp_embed_handler_doubanablum' );

function wp_embed_handler_doubanablum( $matches, $attr, $url, $rawattr ){
    if(! is_singular() ) return $url;
    $type = $matches[1];
    $html = display_album_detail($type);
    return apply_filters( 'embed_forbes', $html, $matches, $attr, $url, $rawattr );
}

function wp_embed_handler_doubanlist( $matches, $attr, $url, $rawattr ){
    if(! is_singular() ) return $url;
    $type = $matches[1];
    $id = $matches[2];
    if ( $type == 'music' ) {
        $html = display_music_detail($id);
    } elseif ( $type == 'movie' ) {
        $html = display_movie_detail($id);
    } elseif ( $type == 'book' ) {
        $html = display_book_detail($id);
    }

    return apply_filters( 'embed_forbes', $html, $matches, $attr, $url, $rawattr );
}
