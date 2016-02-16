# wp-douban

可以以豆列的方式展示电影、图书、音乐和相册。

图片内容缓存到本地解决豆瓣防盗链。

读取API数据缓存时间为1个月。
### Shortcode
相册展示
<pre data-type="shortcode">[douban id="24043518" type="album"]</pre>
图书展示
<pre data-type="shortcode">[douban id="1065970" type="book"]</pre>
音乐专辑展示
<pre data-type="shortcode">[douban id="3110556" type="music"]</pre>
电影展示
<pre data-type="shortcode">[douban id="1417598,25769362"]</pre>
多个只需要用<code>,</code>隔开就可以了
### 使用
后台可直接输入地址添加内容，可同时添加多个地址，或者使用上面的Shortcode

![](https://static.fatesinger.com/wp-douban-ui.jpg)

