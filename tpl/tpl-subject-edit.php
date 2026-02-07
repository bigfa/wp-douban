<div class="wrap">
    <h2>编辑条目</h2>
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
        <p>条目不存在或已删除。</p>
    <?php return;
    endif;

    $subject_source_type = !empty($subject->tmdb_id) ? 'tmdb' : '';
    $subject_source_id = !empty($subject->tmdb_id) ? $subject->tmdb_id : $subject->douban_id;
    $cache_file = ABSPATH . 'douban_cache/' . $subject_source_type . $subject_source_id . '.jpg';
    $cover_url = is_file($cache_file) ? home_url('/') . 'douban_cache/' . $subject_source_type . $subject_source_id . '.jpg' : $subject->poster;
    $subject_label = !empty($subject->tmdb_id) ? 'TMDB' : '豆瓣';
    $fave_type = $fave ? $fave->type : $subject->type;
    ?>
    <form method="post">
        <?php wp_nonce_field('wpd_subject_edit', 'wpd_subject_nonce'); ?>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="url">条目</label></th>
                    <td>
                        <p><img src="<?php echo esc_url($cover_url); ?>" width="100" /></p>
                        <p><?php echo esc_html(is_file($cache_file) ? '封面已缓存' : '因豆瓣原因，原始封面可能无法显示。'); ?></p>
                        <p><?php echo esc_html($subject->name); ?>
                            <span class="db--titletag"><?php echo esc_html($subject_label); ?></span>
                        </p>
                        <p><?php echo esc_html($subject->card_subtitle); ?></p>
                    </td>
                </tr>
                <?php if ($action == 'edit_subject') : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url">海报地址</label></th>
                        <td>
                            <input type="datetime" name="poster" value="<?php echo esc_attr($subject->poster); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">名称</label></th>
                        <td>
                            <input type="datetime" name="name" value="<?php echo esc_attr($subject->name); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">副标题</label></th>
                        <td>
                            <input type="datetime" name="card_subtitle" value="<?php echo esc_attr($subject->card_subtitle); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">豆瓣评分</label></th>
                        <td>
                            <input type="datetime" name="douban_score" value="<?php echo esc_attr($subject->douban_score); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url">观看时间</label></th>
                        <td>
                            <input type="datetime" name="create_time" value="<?php echo esc_attr($fave ? $fave->create_time : ''); ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="status">状态</label></th>
                        <td>
                            <select name="status" id="status">
                                <option <?php selected($fave && $fave->status === 'done'); ?>value="done">已看</option>
                                <option <?php selected($fave && $fave->status === 'doing'); ?>value="doing">在看</option>
                                <option <?php selected($fave && $fave->status === 'mark'); ?>value="mark">想看</option>
                                <option <?php selected(!$fave || $fave->status === ''); ?>value="">取消标记</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">短评</label></th>
                        <td>
                            <textarea name="remark" style="width: 600px;" rows="5" cols="30" placeholder="输入短评"><?php echo esc_textarea($fave ? $fave->remark : ''); ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">评分</label></th>
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
            <input type="submit" class="button-primary" name="save" value="<?php _e('Save Changes') ?>" />
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
