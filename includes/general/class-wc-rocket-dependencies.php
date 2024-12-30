<?php
/**
 * WC Rocket Dependency Checker
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Checks if Woocommerce and Woocommerce Subscriptions is enabled.
 */
class WC_Rocket_Dependencies {


	/**
	 * Active plugins
	 *
	 * @var static
	 */
	private static $active_plugins;

	/**
	 * Init the Dependencies.
	 */
	public static function init() {

		self::$active_plugins = (array) get_option( 'active_plugins', array() );

		if ( is_multisite() ) {
			self::$active_plugins = array_merge( self::$active_plugins, get_site_option( 'active_sitewide_plugins', array() ) );
		}
	}

	/**
	 * ERP active checker.
	 */
	public static function wc_active_check() {

		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'woocommerce/woocommerce.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce/woocommerce.php', self::$active_plugins );
	}
        
        /**
	 * ERP Pro active checker.
	 */
	public static function wc_subscriptions_active_check() {

		if ( ! self::$active_plugins ) {
			self::init();
		}

		return in_array( 'woocommerce-subscriptions/woocommerce-subscriptions.php', self::$active_plugins, true ) || array_key_exists( 'woocommerce-subscriptions/woocommerce-subscriptions.php', self::$active_plugins );
	}
}
