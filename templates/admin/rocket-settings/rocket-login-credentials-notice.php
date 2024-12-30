<div class="rocket-login-credentials-notice wc-rocket-admin-notice-wrap">
    <p><?php _e('Please enter rocket email and password to verify your support.', 'wc-rocket'); ?></p>
    <?php
    if ($validate_rocket_account == 'valid') {
        $message = __('Your rocket account is valid', 'wc-rocket');
        include WC_ROCKET_FILE . 'templates/admin/notice/admin-success-notice.php';
    } elseif ($validate_rocket_account == 'unvalid') {
        $message = __('Your rocket account not valid', 'wc-rocket');
        include WC_ROCKET_FILE . 'templates/admin/notice/admin-error-notice.php';
    }

    ?>
</div>