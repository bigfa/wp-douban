<div class="wrap">
    <h2>标记的条目</h2>
    <?php
    require_once WPD_PATH . '/src/subject-table.php';
    $table = new Subject_List_Table();
    $table->prepare_items();
    $table->views(); ?>
    <?php
    $table->display();
    ?>
</div>