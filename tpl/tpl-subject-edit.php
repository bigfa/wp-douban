<div class="wrap">
    <h2>编辑条目</h2>
    <?php $subject_id = $_GET['subject_id'];
    global $wpdb;
    $subject = $subject_id ? $wpdb->get_row("SELECT * FROM $wpdb->douban_movies WHERE id = '{$subject_id}'") : "";
    $fave = $subject_id ? $wpdb->get_row("SELECT * FROM $wpdb->douban_faves WHERE subject_id = '{$subject_id}'") : "";
    $action = $_GET['action'] ? $_GET['action'] : 'edit_fave';
    ?>
    <form method="post">
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="url">条目</label></th>
                    <td>
                        <p><img src="<?php
                                        $type = $subject->tmdb_id ? 'tmdb' : '';
                                        $id = $subject->tmdb_id ? $subject->tmdb_id : $subject->douban_id;
                                        $e = ABSPATH . 'douban_cache/' . $type . $id . '.jpg';
                                        echo (is_file($e) ? home_url('/') . 'douban_cache/' . $type . $id . '.jpg' : $subject->poster); ?>" width="100" /></p>
                        <p><?php echo (is_file($e) ? '封面已缓存' : '因豆瓣原因，原始封面可能无法显示。'); ?></p>
                        <p><?php echo $subject->name; ?>
                            <span class="db--titletag"><?php echo ($subject->tmdb_id ? 'TMDB' : '豆瓣'); ?></span>
                        </p>
                        <p><?php echo $subject->card_subtitle; ?></p>
                    </td>
                </tr>
                <?php if ($action == 'edit_subject') : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url">海报地址</label></th>
                        <td>
                            <input type="datetime" name="poster" value="<?php echo $subject->poster ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">名称</label></th>
                        <td>
                            <input type="datetime" name="name" value="<?php echo $subject->name ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">副标题</label></th>
                        <td>
                            <input type="datetime" name="card_subtitle" value="<?php echo $subject->card_subtitle ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">豆瓣评分</label></th>
                        <td>
                            <input type="datetime" name="douban_score" value="<?php echo $subject->douban_score ?>" class="regular-text"></input>
                        </td>
                    </tr>
                <?php else : ?>
                    <tr valign="top">
                        <th scope="row"><label for="url">观看时间</label></th>
                        <td>
                            <input type="datetime" name="create_time" value="<?php echo $fave->create_time ?>" class="regular-text"></input>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="status">状态</label></th>
                        <td>
                            <select name="status" id="status">
                                <option <?php if ($fave->status == 'done') echo 'selected="selected" '; ?>value="done">已看</option>
                                <option <?php if ($fave->status == 'doing') echo 'selected="doing" '; ?>value="doing">在看</option>
                                <option <?php if ($fave->status == 'mark') echo 'selected="mark" '; ?>value="mark">想看</option>
                                <option <?php if ($fave->status == '') echo 'selected="selected" '; ?>value="">取消标记</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">短评</label></th>
                        <td>
                            <textarea name="remark" style="width: 600px;" rows="5" cols="30" placeholder="输入短评"><?php echo $fave->remark ?></textarea>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row"><label for="url">评分</label></th>
                        <td>
                            <input name="score" type="number" value="<?php echo $fave->score ? $fave->score : '' ?>" min="0" max="10"></input>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        <input type="hidden" name="wpd_action" value="<?php echo $action; ?>" />
        <input type="hidden" name="subject_id" value="<?php echo $subject->id ?>" />
        <input type="hidden" name="fave_id" value="<?php echo $fave->id ?>" />
        <input type="hidden" name="subject_type" value="<?php echo $fave->type ?>" />
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