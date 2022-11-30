<div class="wrap">
    <h2>插件设置</h2>
    <form method="post" action="options.php">
        <?php
        settings_fields('db_setting_group');
        ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label>使用方法</label></th>
                    <td>
                        <p>请查看<a href="https://fatesinger.com/101050" target="_blank">帮助文章</a></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label>显示设置</label></th>
                    <td>
                        <ul class="wpd-color-ul">
                            <?php $color = array(
                                array(
                                    'title' => '帐号ID',
                                    'key' => 'id',
                                    'default' => ''
                                ),
                                array(
                                    'title' => '每页显示条目数',
                                    'key' => 'perpage',
                                    'default' => '70'
                                ),
                                // array(
                                //     'title' => '云token',
                                //     'key' => 'token',
                                //     'default' => ''
                                // )
                            );
                            foreach ($color as $key => $V) {
                            ?>
                                <li class="wpd-color-li">
                                    <code><?php echo $V['title']; ?></code>
                                    <?php $color = db_get_setting($V['key']) ? db_get_setting($V['key']) : $V['default']; ?>
                                    <input name="<?php echo db_setting_key($V['key']); ?>" type="text" value="<?php echo $color; ?>" class="regular-text wpd-color-picker" />
                                </li>
                            <?php }
                            ?>
                        </ul>
                        <p class="description">点击你的个人主页，URL类似为<code>https://www.douban.com/people/54529369/</code>，<code>54529369</code>就是你的ID</p>
                        <!-- <p class="description">设置<code>token</code>后将从云端获取数据，无token 请勿设置</p> -->
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('dark_mode');
                                                $mode = db_get_setting("dark_mode") ? db_get_setting("dark_mode") : 'light'; ?>">暗黑模式</label></th>
                    <td>
                        <label for="mode-light">
                            <input type="radio" name="<?php echo db_setting_key('dark_mode'); ?>" id="mode-light" value="light" <?php if ($mode == 'light') echo 'checked="checked"'; ?>>浅色模式</label>
                        <label for="mode-dark">
                            <input type="radio" name="<?php echo db_setting_key('dark_mode'); ?>" id="mode-dark" value="dark" <?php if ($mode == 'dark') echo 'checked="checked"'; ?>>深色模式</label>
                        <label for="mode-auto">
                            <input type="radio" name="<?php echo db_setting_key('dark_mode'); ?>" id="mode-auto" value="auto" <?php if ($mode == 'auto') echo 'checked="checked"'; ?>>跟随系统</label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="url">自定义CSS</label></th>
                    <td>
                        <textarea name="<?php echo db_setting_key('css'); ?>" class="wpd-textarea"><?php echo db_get_setting('css'); ?></textarea>
                        <p class="description">请输入合法的CSS。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('show_remark'); ?>">展示短评</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('show_remark'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('show_remark'); ?>" id="show_remark" value="1" <?php if (db_get_setting("show_remark")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启后文章引入单条目时如果标记过则展示短评和标记时间</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('show_type'); ?>">开启分类</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('show_type'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('show_type'); ?>" id="show_remark" value="1" <?php if (db_get_setting("show_type")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">默认只展示看过的条目，开启后会展示想看/在看/看过</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('download_image'); ?>">下载图片</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('download_image'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('download_image'); ?>" id="download_image" value="1" <?php if (db_get_setting("download_image")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启后将封面图片下载到本地。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('disable_scripts'); ?>">静态文件</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('disable_scripts'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('disable_scripts'); ?>" id="disable_scripts" value="1" <?php if (db_get_setting("disable_scripts")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启后将不加载插件自带的静态文件。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('top250'); ?>">豆瓣电影Top250</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('top250'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('top250'); ?>" id="top250" value="1" <?php if (db_get_setting("top250")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启该选项则会定期同步豆瓣电影<code>top250</code> 清单，当条目在清单中时展示<code>top250</code> 标识。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('book_top250'); ?>">豆瓣图书Top250</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('top250'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('book_top250'); ?>" id="top250" value="1" <?php if (db_get_setting("book_top250")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启该选项则会定期同步豆瓣图书<code>top250</code> 清单，当条目在清单中时展示<code>top250</code> 标识。</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="wpd-submit-form">
            <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
        </div>
    </form>
    <style>
        .wpd-color-li {
            position: relative;
            padding-left: 120px;
        }

        .wpd-color-li code {
            position: absolute;
            left: 0;
            top: 1px;
        }

        .wpd-textarea {
            width: 600px;
            height: 120px;
        }
    </style>
</div>