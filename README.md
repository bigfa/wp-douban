# wp-douban

可以以豆列的方式展示电影、图书、音乐和相册。

图片内容缓存到本地解决豆瓣防盗链。

读取API数据缓存时间为1个月。

### 调用电影封面

使用函数`get_movie_image($id)`,id为豆瓣电影数字id

使用插件内置的缩略图函数`wpd_get_post_image($id)`,id为文章id


### 插入方式

直接在文章中粘贴豆瓣url 即可。

相册展示
<pre data-type="shortcode">https://www.douban.com/photos/album/1693226620</pre>
图书展示
<pre data-type="shortcode">https://book.douban.com/subject/34481379</pre>
音乐专辑展示
<pre data-type="shortcode">https://music.douban.com/subject/6816154</pre>
电影展示
<pre data-type="shortcode">https://movie.douban.com/subject/1292001</pre>


