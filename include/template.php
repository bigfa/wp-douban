<script id="wpd-template" type="text/x-handlebars-template">
    <div id="wpd-shell">
        <div id="wpd-shell-content" class="media-modal">
            <div class="media-modal-content">
                <a id="wpd-shell-close" class="media-modal-close" href="javascript:;"><span class="media-modal-icon"><span class="screen-reader-text">关闭媒体面板</span></span></a>
                <div id="wpd-shell-body">
                    <div class="media-frame-title">
                        <h1>WP-Douban<span class="dashicons dashicons-arrow-down"></span></h1>
                    </div>
                    <div class="media-frame-router clearfix">
                        <div class="media-router">
                            <a href="javascript:;" class="media-menu-item active">豆瓣相册</a>
                            <a href="javascript:;" class="media-menu-item">豆瓣图书</a>
                            <a href="javascript:;" class="media-menu-item">豆瓣电影</a>
							<a href="javascript:;" class="media-menu-item">豆瓣音乐</a>
                        </div>
                        <a class="wpd-help" href="http://fatesinger.com/75003" target="_blank">帮助?</a>
                    </div>
                    <div class="media-frame-content">
                        <ul class="wpd-ul">
                            <li class="wpd-li active" data-type="album">
                                <textarea class="wpd-textarea large-text code" cols="30" rows="9" placeholder="输入豆瓣相册地址。。。"></textarea>
                            </li>
                            <li class="wpd-li" data-type="book">
                                <textarea class="wpd-textarea large-text code" cols="30" rows="9" placeholder="输入豆瓣图书地址。。。"></textarea>
                            </li>
                            <li class="wpd-li" data-type="movie">
                                <textarea class="wpd-textarea large-text code" cols="30" rows="9" placeholder="输入豆瓣电影地址。。。"></textarea>
                            </li>
							<li class="wpd-li" data-type="music">
                                <textarea class="wpd-textarea large-text code" cols="30" rows="9" placeholder="输入豆瓣音乐地址。。。"></textarea>
                            </li>
                        </ul>
                        <div id="wpd-preview">
                        </div>
                    </div>
                    <div class="media-frame-toolbar">
                        <div class="media-toolbar">
                            <div class="media-toolbar-primary search-form">
                                <a id="wpd-shell-insert" href="javascript:;" class="button media-button button-primary button-large media-button-insert" disabled="disabled">插入至文章</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="wpd-shell-backdrop" class="media-modal-backdrop">
        </div>
    </div>
</script>
<script id="wpd-remote-template" type="text/x-handlebars-template">
    {{#data}}
    <li data-id="{{id}}">{{song_name}} - {{song_author}}</li>
    {{/data}}
</script>