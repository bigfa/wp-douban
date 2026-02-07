<div class="wrap">
    <h2><?php echo esc_html__('Marked Items', 'wp-douban'); ?></h2>
    <?php
    require_once WPD_PATH . '/src/subject-table.php';
    $table = new Subject_List_Table();
    $table->prepare_items();
    $table->views(); ?>
    <form id="posts-filter" method="get" action="admin.php">
        <input type="hidden" name="page" value="subject" />
        <?php $table->search_box(__('Search Items', 'wp-douban'), 'subject-name'); ?>
    </form>
    <?php
    $table->display();
    ?>
</div>
