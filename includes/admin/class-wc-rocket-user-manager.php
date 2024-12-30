<?php

if (!class_exists('WC_Rocket_User_Manager')) {
    class WC_Rocket_User_Manager {
        private static $instance;

        public function __construct() {
            // Add column to users table
            add_filter('manage_users_columns', array($this, 'add_site_access_column'));
            add_filter('manage_users_custom_column', array($this, 'site_access_column_content'), 10, 3);

            // Add AJAX handler for toggling access
            add_action('wp_ajax_toggle_site_access', array($this, 'ajax_toggle_site_access'));

            // Enqueue admin scripts
            add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        }

        public function add_site_access_column($columns) {
            $columns['site_access'] = __('Site Access', 'wc-rocket');
            return $columns;
        }

        public function site_access_column_content($value, $column_name, $user_id) {
            if ($column_name !== 'site_access') {
                return $value;
            }

            $has_access = get_user_meta($user_id, 'wc_rocket_site_access', true);
            $has_access = $has_access !== 'disabled'; // Default to enabled if not set

            $button_class = $has_access ? 'button-primary' : 'button';
            $button_text = $has_access ? __('Enabled', 'wc-rocket') : __('Disabled', 'wc-rocket');

            return sprintf(
                '<button class="button toggle-site-access %s" data-user-id="%d" data-status="%s">%s</button>',
                esc_attr($button_class),
                esc_attr($user_id),
                esc_attr($has_access ? 'enabled' : 'disabled'),
                esc_html($button_text)
            );
        }

        public function ajax_toggle_site_access() {
            check_ajax_referer('wc_rocket_admin_nonce', 'nonce');

            if (!current_user_can('manage_options')) {
                wp_send_json_error(__('Permission denied.', 'wc-rocket'));
            }

            $user_id = intval($_POST['user_id']);
            $new_status = $_POST['status'] === 'enable' ? '' : 'disabled';

            update_user_meta($user_id, 'wc_rocket_site_access', $new_status);

            wp_send_json_success(array(
                'message' => __('Access updated successfully.', 'wc-rocket'),
                'new_status' => $new_status === '' ? 'enabled' : 'disabled'
            ));
        }

        public function enqueue_admin_scripts($hook) {
            if ($hook !== 'users.php') {
                return;
            }

            wp_enqueue_script(
                'wc-rocket-user-manager',
                WC_ROCKET_URL . 'assets/js/admin/user-manager.js',
                array('jquery'),
                WC_ROCKET_VERSION,
                true
            );

            wp_localize_script('wc-rocket-user-manager', 'wcRocketUserManager', array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wc_rocket_admin_nonce'),
                'strings' => array(
                    'confirmToggle' => __('Are you sure you want to toggle this user\'s site access?', 'wc-rocket'),
                    'error' => __('Error updating access.', 'wc-rocket')
                )
            ));
        }

        public static function get_instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }
}

// Initialize the class
WC_Rocket_User_Manager::get_instance();