add_action('init', function() {
    add_rewrite_endpoint('my-sites', EP_ROOT | EP_PAGES);
    error_log('WC Rocket: my-sites endpoint registered');
});

// Force refresh permalinks if needed
add_action('admin_init', function() {
    $version_option = 'wc_rocket_version';
    $current_version = WC_ROCKET_VERSION;
    $stored_version = get_option($version_option);

    if ($stored_version !== $current_version) {
        flush_rewrite_rules();
        update_option($version_option, $current_version);
        error_log('WC Rocket: Flushed rewrite rules due to version update');
    }
});