<div class="wrap">
    <h2><?php echo esc_html__('All Items', 'wp-douban'); ?></h2>
    <?php
    require_once WPD_PATH . '/src/subject-all-table.php';
    $table = new Subject_ALL_Table();
    $table->prepare_items();
    $table->views(); ?>
    <form id="posts-filter" method="get" action="admin.php">
        <input type="hidden" name="page" value="subject_all" />
        <?php $table->search_box(__('Search Items', 'wp-douban'), 'subject-name'); ?>
    </form>
    <?php
    $table->display();
    ?>
</div>
