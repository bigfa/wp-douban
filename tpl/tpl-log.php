<div class="wrap">
    <h2><?php echo esc_html__('Logs', 'wp-douban'); ?></h2>
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
