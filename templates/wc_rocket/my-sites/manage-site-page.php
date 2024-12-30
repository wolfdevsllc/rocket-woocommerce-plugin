<?php
/**
 * manage site page
 *
 * This template can be overridden by copying it to yourtheme/wc_rocket/my-sites/manage-site-page.php.
 */

defined( 'ABSPATH' ) || exit;
do_action( 'wc_rocket_add_logic_before_manage_site_content');
?>
<div class="wc-rocket-manage-site-wrap wc-rocket-loader-wrapper">

    <?php
    if($is_manage_page_allowed){
    ?>
        <div class="container" id="rocket-container">
	<?php include WC_ROCKET_FILE . '/templates/wc_rocket/general/wc-rocket-loader.php'; ?>

        </div>
    <?php
    }else{
        wc_add_notice( __( 'This page is not allowed to you!', 'wc-rocket' ),"error" );
        $error_message =wc_print_notices( true );
    ?>
        <div>
            <p><?= $error_message ?></p>
        </div>
    <?php
    }
    do_action( 'wc_rocket_add_logic_after_manage_site_content');
    ?>
</div>