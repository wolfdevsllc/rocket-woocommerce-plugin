<div class="notice notice-info wc-import-product-notice-wrap wc-rocket-admin-loader-wrap">
    <?php
    include WC_ROCKET_FILE . "templates/admin/general/wc-admin-loader.php";
    $message = '';
    $classes = 'hide';
    include WC_ROCKET_FILE . 'templates/admin/notice/admin-error-notice.php';
    ?>
    <a href="<?php echo $hide_notice_url; ?>" class="woocommerce-message-close notice-dismiss" style="position:relative;float:right;padding:9px 0px 9px 9px 9px;text-decoration:none;"></a>
    <h2><?php _e('Import sample rocket product.', 'wc-rocket'); ?></h2>
    <p>
<?php echo sprintf(__('Can Import sample rocket product from %s here %s', 'wc-rocket'), '<a href="#" class="import-rocket-sample-product" data-nonce="' . $import_nonce . '">', '</a>'); ?>
    </p>
</div>