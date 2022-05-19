<div class="wrap">
    <h2>所有条目</h2>
    <?php
    require_once WPD_PATH . '/src/subject-all-table.php';
    $table = new Subject_ALL_Table();
    $table->prepare_items();
    $table->views(); ?>
    <?php
    $table->display();
    ?>
</div>