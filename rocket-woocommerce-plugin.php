<?php
/**
 * Plugin Name: Rocket WooCommerce Plugin
 * Description: An eCommerce plugin that integrate with rocket.net to Resell WordPress Hosting
 * Version: 2.0.1
 * Author: Rocket.net
 * Author URI: https://rocket.net/
 * Text Domain: wc-rocket
 */

defined( 'ABSPATH' ) || exit;

if (!defined('WC_ROCKET_FILE')) {
    define('WC_ROCKET_FILE', plugin_dir_path(__FILE__));
}

if (!defined('WC_ROCKET_URL')) {
    define('WC_ROCKET_URL', plugin_dir_url(__FILE__));
}

if (!defined('WC_ROCKET_VERSION')) {
    define('WC_ROCKET_VERSION', '2.0.1');
}

// ----------------------- START SITE STATUS----------//
if (!defined('WC_ROCKET_ACTIVE_SITE_STATUS')) {
    define('WC_ROCKET_ACTIVE_SITE_STATUS', 1);
}
if (!defined('WC_ROCKET_DELETED_SITE_STATUS')) {
    define('WC_ROCKET_DELETED_SITE_STATUS', 2);
}
// ----------------------- END SITE STATUS----------//

// ----------------------- START WC ORDER STATUS----------//
if (!defined('WC_CANCELLED_ORDER_STATUS')) {
    define('WC_CANCELLED_ORDER_STATUS', 'cancelled');
}
// ----------------------- END WC ORDER STATUS----------//

// Register activation hook early
require_once plugin_dir_path(__FILE__) . 'includes/class-wc-rocket-installer.php';
register_activation_hook(__FILE__, array('WC_Rocket_Installer', 'install'));

/**
 * Adds notice in case of WC and WC Subscriptions being inactive
 */
function wc_rocket_inactive_notice() {
    $class = 'notice notice-error';
    $headline = __('Rocket WooCommerce Plugin requires Woocommerce to be active.', 'wc-rocket');
    $message = __('Go to the plugins page to activate Woocommerce', 'wc-rocket');
    printf('<div class="%1$s"><h2>%2$s</h2><p>%3$s</p></div>', esc_attr($class), esc_html($headline), esc_html($message));
}

/**
 * Check for plugin dependencies
 */
if (!class_exists('WC_Rocket_Dependencies')) {
    require_once WC_ROCKET_FILE . 'includes/general/class-wc-rocket-dependencies.php';
}


if ( WC_Rocket_Dependencies::wc_active_check() ) :
    WC_Rocket_Plugin::get_instance();

    // Add our new includes
    require_once WC_ROCKET_FILE . 'includes/class-wc-rocket-order-handler.php';

    if (is_admin()) {
        require_once WC_ROCKET_FILE . 'includes/admin/class-wc-rocket-allocation-manager.php';
    }

    WC_Rocket_Order_Handler::get_instance();

    // After your other includes, before the plugin class definition
    require_once WC_ROCKET_FILE . 'includes/admin/class-wc-rocket-user-manager.php';

else :
    add_action('admin_notices', 'wc_rocket_inactive_notice');
    return;
endif;


/**
 * The main wc rocket class.
 *
 * @since 1.0
 */
class WC_Rocket_Plugin {
    /**
     * @var WC_Subscription_Box
     */
    public static $instance;

    /**
     * The prefix for wc subscription box settings
     */
    public static $option_prefix = 'wc_rocket';

    public function __construct()
    {
        require_once WC_ROCKET_FILE . 'includes/class-wc-rocket-installer.php';

        require_once WC_ROCKET_FILE . 'includes/index.php';

        add_action( 'init', __CLASS__ . '::maybe_activate_wc_rocket', 11 );

        register_deactivation_hook( __FILE__, __CLASS__ . '::deactivate_wc_rocket' );

        // redirect to settings page after plugin active
        add_action( 'activated_plugin', array($this, 'redirect_rocket_settings_page'), 99 );

        // Inside your plugin's main class initialization (likely in __construct or init)
        add_action('init', function() {
            // Initialize User Manager
            WC_Rocket_User_Manager::get_instance();
        });

    }

    /**
     * Checks on each admin page load if woocommerce and woocommerce subscription box plugin is activated.
     */
    public static function maybe_activate_wc_rocket() {

        $is_active = get_option( self::$option_prefix . '_is_active', false );

        if ( false == $is_active ) {

            // install dummy product

            update_option( self::$option_prefix . '_is_active', true );
            flush_rewrite_rules();

            do_action( 'wc_rocket_plugin_activated' );

        }

    }

    /**
     * Called when the plugin is deactivated. fires deactive action.
     */
    public static function deactivate_wc_rocket() {

        flush_rewrite_rules();

        do_action( 'wc_rocket_plugin_deactivated' );
    }

    /**
     * redirect to settings page after plugin active
     *
     * @param string $plugin
     */
    public function redirect_rocket_settings_page($plugin) {

        if( $plugin == plugin_basename( __FILE__ ) ) {
            WC_Rocket_Admin_Settings_Page::get_instance()->add_rocket_setting_capability();
            exit( wp_redirect( admin_url( 'admin.php?page=rocket-settings' ) ) );
        }

    }

    /**
     * WC_Rocket_Plugin instance
     *
     * @return object
     */
    public static function get_instance()
    {
        if (!isset(self::$instance) || is_null(self::$instance))
            self::$instance = new self();

        return self::$instance;
    }

}
