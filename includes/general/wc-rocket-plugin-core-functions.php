<?php

/**
 * WC Rocket Plugin Core Functions
 */
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Like wc_rocket_site_get_template, but returns the HTML instead of outputting.
 *
 * @see wc_rocket_site_get_template
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 *
 * @return string
 */
function wc_rocket_site_get_template_html($template_name, $args = array(), $template_path = '', $default_path = '') {
    ob_start();
    wc_rocket_site_get_template($template_name, $args, $template_path, $default_path);
    return ob_get_clean();
}

/**
 * Get wc rocket template
 *
 * @param string $template_name Template name.
 * @param array  $args          Arguments. (default: array).
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 */
function wc_rocket_site_get_template($template_name, $args = array(), $template_path = '', $default_path = '') {
    if (!empty($args) && is_array($args)) {
        extract($args, EXTR_SKIP); // @codingStandardsIgnoreLine
    }

    $located = wc_rocket_site_locate_template($template_name, $template_path, $default_path);

    if (!file_exists($located)) {
        /* translators: %s template */
        wc_doing_it_wrong(__FUNCTION__, sprintf(__('%s does not exist.', 'wc-rocket'), '<code>' . $located . '</code>'), '2.1');
        return;
    }

    // Allow 3rd party plugin filter template file from their plugin.
    $located = apply_filters('wc_rocket_site_get_template', $located, $template_name, $args, $template_path, $default_path);

    do_action('wc_rocket_site_before_template_part', $template_name, $template_path, $located, $args);

    include $located;

    do_action('wc_rocket_site_after_template_part', $template_name, $template_path, $located, $args);
}

/**
 * Locate wc rocket plugin template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @param string $template_name Template name.
 * @param string $template_path Template path. (default: '').
 * @param string $default_path  Default path. (default: '').
 * @return string
 */
function wc_rocket_site_locate_template($template_name, $template_path = '', $default_path = '') {
    if (!$template_path) {
        $template_path = apply_filters('wc_rocket_site_template_path', 'wc_rocket/');
    }

    if (!$default_path) {
        $default_path = WC_ROCKET_FILE . '/templates/wc_rocket/';
    }

    // Look within passed path within the theme - this is priority.
    $template = locate_template(
            array(
                trailingslashit($template_path) . $template_name,
                $template_name,
            )
    );

    // Get default template/.
    if (!$template) {
        $template = $default_path . $template_name;
    }

    // Return what we found.
    return apply_filters('wc_rocket_site_locate_template', $template, $template_name, $template_path);
}

/**
 * wc rocket encrypt data
 * 
 * @return array
 */
function wc_rocket_sodium_crypto_data() {
    // keypair1 public and secret
    $keypair1 = sodium_crypto_box_keypair();
    $keypair1_public = sodium_crypto_box_publickey($keypair1);
    $keypair1_secret = sodium_crypto_box_secretkey($keypair1);

    // keypair2 public and secret
    $keypair2 = sodium_crypto_box_keypair();
    $keypair2_public = sodium_crypto_box_publickey($keypair2);
    $keypair2_secret = sodium_crypto_box_secretkey($keypair2);

    // sodium nonce
    $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);

    return array(
        'keypair1_public' => $keypair1_public,
        'keypair1_secret' => $keypair1_secret,
        'keypair2_public' => $keypair2_public,
        'keypair2_secret' => $keypair2_secret,
        'nonce' => $nonce,
    );
}
