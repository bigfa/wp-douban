<div class="wrap">
    <h2>所有条目</h2>
    <?php
    require_once WPD_PATH . '/src/subject-all-table.php';
    $table = new Subject_ALL_Table();
    $table->prepare_items();
    $table->views(); ?>
    <form id="posts-filter" method="get" action="admin.php">
        <input type="hidden" name="page" value="subject_all" />
        <?php $table->search_box('搜索条目', 'subject-name'); ?>
    </form>
    <?php
    $table->display();
    ?>
</div>