<div class="wrap">
    <h2><?php echo esc_html__('Plugin Settings', 'wp-douban'); ?></h2>
    <form method="post" action="options.php">
        <?php settings_fields('db_setting_group'); ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label><?php echo esc_html__('Usage', 'wp-douban'); ?></label></th>
                    <td>
                        <p>
                            <?php echo esc_html__('Please read the', 'wp-douban'); ?>
                            <a href="https://fatesinger.com/101050" target="_blank"><?php echo esc_html__('help document', 'wp-douban'); ?></a>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label><?php echo esc_html__('Display Settings', 'wp-douban'); ?></label></th>
                    <td>
                        <ul class="wpd-color-ul">
                            <?php
                            $fields = array(
                                array(
                                    'title' => __('Account ID', 'wp-douban'),
                                    'key' => 'id',
                                    'default' => ''
                                ),
                                array(
                                    'title' => __('Items Per Page', 'wp-douban'),
                                    'key' => 'perpage',
                                    'default' => '70'
                                ),
                                array(
                                    'title' => __('TMDB API Key', 'wp-douban'),
                                    'key' => 'api_key',
                                    'default' => ''
                                )
                            );
                            foreach ($fields as $field) :
                                $value = db_get_setting($field['key']) ? db_get_setting($field['key']) : $field['default'];
                            ?>
                                <li class="wpd-color-li">
                                    <code><?php echo esc_html($field['title']); ?></code>
                                    <input name="<?php echo esc_attr(db_setting_key($field['key'])); ?>" type="text" value="<?php echo esc_attr($value); ?>" class="regular-text wpd-color-picker" />
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <p class="description">
                            <?php /* translators: %s: Example Douban profile URL. */ ?>
                            <?php echo wp_kses_post(sprintf(__('Open your profile URL like <code>%s</code>, and the numeric part is your account ID.', 'wp-douban'), 'https://www.douban.com/people/54529369/')); ?>
                        </p>
                        <p class="description">
                            <?php echo wp_kses_post(__('Create your <code>TMDB API Key</code> at https://www.themoviedb.org/settings/api', 'wp-douban')); ?>
                        </p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label for="<?php echo esc_attr(db_setting_key('dark_mode'));
                                    $mode = db_get_setting('dark_mode') ? db_get_setting('dark_mode') : 'light'; ?>">
                            <?php echo esc_html__('Theme Mode', 'wp-douban'); ?>
                        </label>
                    </th>
                    <td>
                        <label for="mode-light">
                            <input type="radio" name="<?php echo esc_attr(db_setting_key('dark_mode')); ?>" id="mode-light" value="light" <?php checked($mode, 'light'); ?>>
                            <?php echo esc_html__('Light', 'wp-douban'); ?>
                        </label>
                        <label for="mode-dark">
                            <input type="radio" name="<?php echo esc_attr(db_setting_key('dark_mode')); ?>" id="mode-dark" value="dark" <?php checked($mode, 'dark'); ?>>
                            <?php echo esc_html__('Dark', 'wp-douban'); ?>
                        </label>
                        <label for="mode-auto">
                            <input type="radio" name="<?php echo esc_attr(db_setting_key('dark_mode')); ?>" id="mode-auto" value="auto" <?php checked($mode, 'auto'); ?>>
                            <?php echo esc_html__('Follow System', 'wp-douban'); ?>
                        </label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="url"><?php echo esc_html__('Custom CSS', 'wp-douban'); ?></label></th>
                    <td>
                        <textarea name="<?php echo esc_attr(db_setting_key('css')); ?>" class="wpd-textarea"><?php echo esc_textarea(db_get_setting('css')); ?></textarea>
                        <p class="description"><?php echo esc_html__('Please input valid CSS.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('show_remark')); ?>"><?php echo esc_html__('Show Comment', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('show_remark')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('show_remark')); ?>" id="show_remark" value="1" <?php checked(db_get_setting('show_remark')); ?>>
                        </label>
                        <p class="description"><?php echo esc_html__('Show comment and marked date when rendering a single item if it is marked.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('show_type')); ?>"><?php echo esc_html__('Show Status Tabs', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('show_type')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('show_type')); ?>" id="show_type" value="1" <?php checked(db_get_setting('show_type')); ?>>
                        </label>
                        <p class="description"><?php echo esc_html__('By default only watched items are shown. Enable this to show want-to-watch / watching / watched.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('home_render')); ?>"><?php echo esc_html__('Render on Home', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('home_render')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('home_render')); ?>" id="home_render" value="1" <?php checked(db_get_setting('home_render')); ?>>
                        </label>
                        <p class="description"><?php echo esc_html__('By default links are auto-rendered only on single posts. Enable this to render on non-single pages too.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('download_image')); ?>"><?php echo esc_html__('Download Images', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('download_image')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('download_image')); ?>" id="download_image" value="1" <?php checked(db_get_setting('download_image')); ?>>
                        </label>
                        <p class="description"><?php echo esc_html__('Download poster images to local storage.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('disable_scripts')); ?>"><?php echo esc_html__('Disable Assets', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('disable_scripts')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('disable_scripts')); ?>" id="disable_scripts" value="1" <?php checked(db_get_setting('disable_scripts')); ?>>
                        </label>
                        <p class="description"><?php echo esc_html__('Do not load plugin bundled CSS/JS.', 'wp-douban'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('top250')); ?>"><?php echo esc_html__('Douban Movie Top 250', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('top250')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('top250')); ?>" id="top250" value="1" <?php checked(db_get_setting('top250')); ?>>
                        </label>
                        <p class="description"><?php echo wp_kses_post(__('Enable to sync the Douban movie <code>top250</code> list periodically and show the <code>top250</code> badge on matched items.', 'wp-douban')); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="<?php echo esc_attr(db_setting_key('book_top250')); ?>"><?php echo esc_html__('Douban Book Top 250', 'wp-douban'); ?></label></th>
                    <td>
                        <label for="<?php echo esc_attr(db_setting_key('book_top250')); ?>">
                            <input type="checkbox" name="<?php echo esc_attr(db_setting_key('book_top250')); ?>" id="book_top250" value="1" <?php checked(db_get_setting('book_top250')); ?>>
                        </label>
                        <p class="description"><?php echo wp_kses_post(__('Enable to sync the Douban book <code>top250</code> list periodically and show the <code>top250</code> badge on matched items.', 'wp-douban')); ?></p>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="wpd-submit-form">
            <input type="submit" class="button-primary" name="save" value="<?php echo esc_attr__('Save Changes', 'wp-douban'); ?>" />
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
