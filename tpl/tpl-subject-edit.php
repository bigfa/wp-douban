<div class="wrap">
    <h2><?php echo esc_html__('Edit Item', 'wp-douban'); ?></h2>
    <?php
    $subject_id = isset($_GET['subject_id']) ? absint($_GET['subject_id']) : 0;
    global $wpdb;
    $subject = $subject_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->douban_movies WHERE id = %d", $subject_id)) : null;
    $fave = $subject_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $wpdb->douban_faves WHERE subject_id = %d", $subject_id)) : null;
    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : 'edit_fave';
    if (!in_array($action, ['edit_fave', 'edit_subject'], true)) {
        $action = 'edit_fave';
    }

    if (!$subject) :
    ?>
        <p><?php echo esc_html__('The item does not exist or has been deleted.', 'wp-douban'); ?></p>
    <?php return;
    endif;

    $subject_source_type = !empty($subject->tmdb_id) ? 'tmdb' : '';
    $subject_source_id = !empty($subject->tmdb_id) ? $subject->tmdb_id : $subject->douban_id;
    $cache_file = ABSPATH . 'douban_cache/' . $subject_source_type . $subject_source_id . '.jpg';
    $cover_url = is_file($cache_file) ? home_url('/') . 'douban_cache/' . $subject_source_type . $subject_source_id . '.jpg' : $subject->poster;
    $subject_label = !empty($subject->tmdb_id) ? 'TMDB' : __('Douban', 'wp-douban');
    $fave_type = $fave ? $fave->type : $subject->type;
    ?>
    <form method="post">
        <?php wp_nonce_field('wpd_subject_edit', 'wpd_subject_nonce'); ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="url"><?php echo esc_html__('Item', 'wp-douban'); ?></label></th>
                    <td>
                        <p><img src="<?php echo esc_url($cover_url); ?>" width="100" /></p>
                        <p><?php echo esc_html(is_file($cache_file) ? __('Poster cached.', 'wp-douban') : __('Original poster may not be available due to Douban restrictions.', 'wp-douban')); ?></p>
                        <p><?php echo esc_html($subject->name); ?>
                            <span class="db--titletag"><?php echo esc_html($subject_label); ?></span>
                        </p>
                        <p><?php echo esc_html($subject->card_subtitle); ?></p>
                    </td>
                </tr>
                <?php if ($action == 'edit_subject') : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Poster URL', 'wp-douban'); ?></label></th>
                        <td>
                            <input type="datetime" name="poster" value="<?php echo esc_attr($subject->poster); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Title', 'wp-douban'); ?></label></th>
                        <td>
                            <input type="datetime" name="name" value="<?php echo esc_attr($subject->name); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Subtitle', 'wp-douban'); ?></label></th>
                        <td>
                            <input type="datetime" name="card_subtitle" value="<?php echo esc_attr($subject->card_subtitle); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Douban Rating', 'wp-douban'); ?></label></th>
                        <td>
                            <input type="datetime" name="douban_score" value="<?php echo esc_attr($subject->douban_score); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Watched At', 'wp-douban'); ?></label></th>
                        <td>
                            <input type="datetime" name="create_time" value="<?php echo esc_attr($fave ? $fave->create_time : ''); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="status"><?php echo esc_html__('Status', 'wp-douban'); ?></label></th>
                        <td>
                            <select name="status" id="status">
                                <option <?php selected($fave && $fave->status === 'done'); ?>value="done"><?php echo esc_html__('Watched', 'wp-douban'); ?></option>
                                <option <?php selected($fave && $fave->status === 'doing'); ?>value="doing"><?php echo esc_html__('Watching', 'wp-douban'); ?></option>
                                <option <?php selected($fave && $fave->status === 'mark'); ?>value="mark"><?php echo esc_html__('Want to watch', 'wp-douban'); ?></option>
                                <option <?php selected(!$fave || $fave->status === ''); ?>value=""><?php echo esc_html__('Unmark', 'wp-douban'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Comment', 'wp-douban'); ?></label></th>
                        <td>
                            <textarea name="remark" style="width: 600px;" rows="5" cols="30" placeholder="<?php echo esc_attr__('Write a comment', 'wp-douban'); ?>"><?php echo esc_textarea($fave ? $fave->remark : ''); ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url"><?php echo esc_html__('Score', 'wp-douban'); ?></label></th>
                        <td>
                            <input name="score" type="number" value="<?php echo esc_attr($fave && $fave->score ? $fave->score : ''); ?>" min="0" max="10"></input>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <input type="hidden" name="wpd_action" value="<?php echo esc_attr($action); ?>" />
        <input type="hidden" name="subject_id" value="<?php echo esc_attr($subject->id); ?>" />
        <input type="hidden" name="fave_id" value="<?php echo esc_attr($fave ? $fave->id : ''); ?>" />
        <input type="hidden" name="subject_type" value="<?php echo esc_attr($fave_type); ?>" />
        <div class="nm-submit-form">
            <input type="submit" class="button-primary" name="save" value="<?php echo esc_attr__('Save Changes', 'wp-douban'); ?>" />
        </div>
        <style>
            .db--titletag {
                font-size: 12px;
                display: inline-block;
                color: #fff;
                background-color: green;
                border-radius: 2px;
                line-height: 1;
                padding: 2px 3px;
                margin-left: 4px;
            }
        </style>
    </form>
</div>
