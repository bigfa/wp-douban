<div class="wrap">
    <h2>日志</h2>
    <?php
    require_once WPD_PATH . '/src/log-table.php';
    $table = new Log_Table();
    $table->prepare_items();
    //$table->views(); 
    ?>
    <?php
    $table->display();
    ?>
</div>