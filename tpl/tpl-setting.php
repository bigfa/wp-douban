<div class="wrap">
    <div id="icon-options-general" class="icon32"><br></div>
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
                        <ul class="nm-color-ul">
                            <?php $color = array(
                                array(
                                    'title' => '帐号ID',
                                    'key' => 'id',
                                    'default' => ''
                                ),
                                array(
                                    'title' => '每页显示条目数量',
                                    'key' => 'perpage',
                                    'default' => '70'
                                )
                            );
                            foreach ($color as $key => $V) {
                            ?>
                                <li class="nm-color-li">
                                    <code><?php echo $V['title']; ?></code>
                                    <?php $color = db_get_setting($V['key']) ? db_get_setting($V['key']) : $V['default']; ?>
                                    <input name="<?php echo db_setting_key($V['key']); ?>" type="text" value="<?php echo $color; ?>" id="nm-default-color" class="regular-text nm-color-picker" />
                                </li>
                            <?php }
                            ?>
                        </ul>
                        <p class="description">点击你的个人主页，URL类似为<code>https://www.douban.com/people/54529369/</code>，<code>54529369</code>就是你的ID</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="url">自定义CSS</label></th>
                    <td>
                        <textarea name="<?php echo db_setting_key('css'); ?>" class="nm-textarea"><?php echo db_get_setting('css'); ?></textarea>
                        <p class="description">请输入合法的CSS。</p>
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
                        <p class="description">开启不加载插件静态文件。</p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo db_setting_key('top250'); ?>">豆瓣Top250</label></th>
                    <td>
                        <label for="<?php echo db_setting_key('top250'); ?>">
                            <input type="checkbox" name="<?php echo db_setting_key('top250'); ?>" id="top250" value="1" <?php if (db_get_setting("top250")) echo 'checked="checked"'; ?>>
                        </label>
                        <p class="description">开启该选项则会定期同步豆瓣top250 清单，当条目在清单中时展示top250 标识。</p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="nm-submit-form">
            <input type="submit" class="button-primary muhermit_submit_form_btn" name="save" value="<?php _e('Save Changes') ?>" />
        </div>
    </form>
    <style>
        .nm-color-li {
            position: relative;
            padding-left: 120px;
        }

        .nm-color-li code {
            position: absolute;
            left: 0;
            top: 1px;
        }

        .nm-textarea {
            width: 600px;
            height: 120px;
        }
    </style>
</div>