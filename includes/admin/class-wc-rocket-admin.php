<?php

class WC_Rocket_Admin {
    public function install_tables() {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['wc-rocket-install-tables']) && check_admin_referer('wc-rocket-install-tables')) {
            WC_Rocket_Installer::get_instance()->install();
            wp_redirect(remove_query_arg('wc-rocket-install-tables'));
            exit;
        }
    }
}