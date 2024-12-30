<?php
/**
 * WC Rocket Settings
 */
if (!class_exists('WC_Rocket_Admin_Settings_Page')) {
    class WC_Rocket_Admin_Settings_Page {

        public static $instance;
        public static $my_sites_page_id = "woocommerce_page_rocket-settings";
        public static $rocket_setting_capability = "rocket_setting_capability";
        public static $validate_rocket_account = "validate_rocket_account";

    
        public function __construct() {
            add_action( 'init', [$this, 'add_rocket_setting_capability'], 11);
            add_action('admin_menu', array($this, 'add_rocket_settings_page'), 99);
            add_action("admin_init", array($this, "settings_fields"));
            // add js code
            add_action('admin_enqueue_scripts', array($this, 'scripts_handler'));
            //Add constant 
            if (!defined('WC_ROCKET_SETTINGS_PAGE_NAME')) {
                define('WC_ROCKET_SETTINGS_PAGE_NAME', 'rocket-settings');
            }
        }
    
        /**
         * include the script
         */
        public function scripts_handler() {
            $current_page = get_current_screen();
            if(isset($current_page) && isset($current_page->id) &&  $current_page->id == self::$my_sites_page_id){
                wp_enqueue_style('rocket-admin-notices');
                wp_enqueue_script('admin-settings-color-picker-script', WC_ROCKET_URL . 'assets/js/admin/admin-settings-color-picker.js',array('jquery'), false, true);
            }
        }

        function add_rocket_setting_capability() {
            $role = get_role( 'administrator' );
            $role->add_cap( self::$rocket_setting_capability , true );
        }
    
    
        /**
         * Add menu page called Rocket Settings in Admin
         */
        public function add_rocket_settings_page() {
            add_menu_page(__('Rocket.net', 'wc-rocket'), __('Rocket.net', 'wc-rocket'), self::$rocket_setting_capability , 'rocket-settings', array($this, 'rocket_settings'));
        }
    
        /**
         * Add the registered settings to Rocket Settings page
         */
        public function rocket_settings() {
            ?>
            <div class="wrap">
                <form method="post" action="options.php">
                    <h2><?php _e('Settings', 'wc-rocket'); ?></h2>
                    <?php
                    settings_fields(WC_ROCKET_SETTINGS_PAGE_NAME);
                    ?>
                    <div class="rocket-settings">
                        <?php do_settings_sections(WC_ROCKET_SETTINGS_PAGE_NAME); ?>
                    </div>
                    <?php
                    submit_button();
                    ?>
                </form>
            </div>
            <?php
        }

        /**
         * Add and register the needed settings
         */
        public function settings_fields() {
            // -----------------------------------Start login section----------------------------------------------//
            //Login Credentials Section
            add_settings_section(
                    'login-credentials-settings-section',
                    __('Login Credentials', 'wc-rocket'),
                    array($this, "rocket_login_credentials_callback"),
                    WC_ROCKET_SETTINGS_PAGE_NAME
            );
    
            // Rocket email field
            add_settings_field(
                "rocket-email",
                __("Email", 'wc-rocket'),
                array($this, "rocket_email_field_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "login-credentials-settings-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "rocket_email");
    
            // Rocket password field
            add_settings_field(
                "rocket-password",
                __("Password", 'wc-rocket'),
                array($this, "rocket_password_field_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "login-credentials-settings-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "rocket_password", array( $this, 'encrybt_rocket_password' ));
            // -----------------------------------End login section----------------------------------------------//
    
            // --------------------------Start wordpress control panel section------------------------------------//
            //wordpress control panel section
            add_settings_section(
                'wordpress-control-panel-section',
                __('WordPress Control Panel', 'wc-rocket'),
                false,
                WC_ROCKET_SETTINGS_PAGE_NAME
            );
    
            // control panel accessibility checkbox field
            add_settings_field(
                "control-panel-accessibility",
                __("On/Off", 'wc-rocket'),
                array($this, "control_panel_accessibility_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "wordpress-control-panel-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "control_panel_accessibility");
    
            //wordpress control panel section
            add_settings_section(
                'portal-customization-section',
                "",
                [$this , "control_panel_portal_customization_colors_label_callback"],
                WC_ROCKET_SETTINGS_PAGE_NAME
            );
            
            // portal customization icon primary color
            add_settings_field(
                "portal-customization-body-background-color",
                __("BodyBackground", 'wc-rocket'),
                array($this, "portal_customization_body_background_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_body_background_color");

            // portal customization icon primary color
            add_settings_field(
                "portal-customization-icon-primary-color",
                __("IconPrimary", 'wc-rocket'),
                array($this, "portal_customization_icon_primary_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_icon_primary_color");
    
            // portal customization icon secondary color
            add_settings_field(
                "portal-customization-icon-secondary-color",
                __("IconSecondary", 'wc-rocket'),
                array($this, "portal_customization_icon_secondary_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_icon_secondary_color");
    
            // portal customization primary color
            add_settings_field(
                "portal-customization-primary-color",
                __("Primary", 'wc-rocket'),
                array($this, "portal_customization_primary_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_primary_color");
    
            // portal customization primary hover color
            add_settings_field(
                "portal-customization-primary-hover-color",
                __("PrimaryHover", 'wc-rocket'),
                array($this, "portal_customization_primary_hover_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_primary_hover_color");
    
            // portal customization primary active color
            add_settings_field(
                "portal-customization-primary-active-color",
                __("PrimaryActive", 'wc-rocket'),
                array($this, "portal_customization_primary_active_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_primary_active_color");
    
            // portal customization primary menu hover color
            add_settings_field(
                "portal-customization-primary-menu-hover-color",
                __("PrimaryMenuHover", 'wc-rocket'),
                array($this, "portal_customization_primary_menu_hover_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_primary_menu_hover_color");
    
            // portal customization primary menu active color
            add_settings_field(
                "portal-customization-primary-menu-active-color",
                __("PrimaryMenuActive", 'wc-rocket'),
                array($this, "portal_customization_primary_menu_active_color_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "portal-customization-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "portal_customization_primary_menu_active_color");
    
            // --------------------------End WordPress Control Panel section--------------------------------------//
            // --------------------------Start Default plugins install section------------------------------------//
            //Login Credentials Section
            add_settings_section(
                'default-plugins-install-settings-section',
                __('Default Plugins install', 'wc-rocket'),
                false,
                WC_ROCKET_SETTINGS_PAGE_NAME
            );
    
            // Rocket email field
            add_settings_field(
                "rocket-email",
                __("Default Plugins Install", 'wc-rocket'),
                array($this, "default_plugins_install_field_callback"),
                WC_ROCKET_SETTINGS_PAGE_NAME,
                "default-plugins-install-settings-section");
            register_setting(WC_ROCKET_SETTINGS_PAGE_NAME, "default_plugins_install");
            
            // --------------------------End Default plugins install section--------------------------------------//
            // validate rocket account in saving
            register_setting( WC_ROCKET_SETTINGS_PAGE_NAME, 'validate_rocket_account', array( $this, 'validate_rocket_account' ) );
            
        }
        
        /**
         * validate rocket account
         */
        public function validate_rocket_account($input){
            $input = get_option(self::$validate_rocket_account);
            $rocket_email = (isset($_POST['rocket_email']) && $_POST['rocket_email']) ? $_POST['rocket_email'] : '';
            $old_rocket_email = (isset($_POST['old_rocket_email']) && $_POST['old_rocket_email']) ? $_POST['old_rocket_email'] : '';
            $rocket_password = (isset($_POST['rocket_password']) && $_POST['rocket_password']) ? $_POST['rocket_password'] : '';
            $old_rocket_password = (isset($_POST['old_rocket_password']) && $_POST['old_rocket_password']) ? $_POST['old_rocket_password'] : '';
            // if user not insert rocket credentials
            if(!$rocket_email && !$old_rocket_email && !$rocket_password && !$old_rocket_password) {
                return;
            }
            elseif($rocket_email != $old_rocket_email || $rocket_password != $old_rocket_password) { // validate new credentials
                $rocket_token = WC_Rocket_Api_Login_Request::get_instance()->refresh_rocket_auth_token();
                if($rocket_token){
                    $input = 'valid';
                }else{
                    $input = 'unvalid';
                }
            }
            
            return $input;
        }
        
        /**
         * rocket login credentials notices
         */
        public function rocket_login_credentials_callback(){
            $validate_rocket_account = get_option(WC_Rocket_Admin_Settings_Page::$validate_rocket_account, '');
            include  WC_ROCKET_FILE . 'templates/admin/rocket-settings/rocket-login-credentials-notice.php';
            if($validate_rocket_account)
                update_option (WC_Rocket_Admin_Settings_Page::$validate_rocket_account, '');
        }


        /**
         * login rocket email field
         */
        public function rocket_email_field_callback() {
            $rocket_email = self::get_rocket_email();
            include  WC_ROCKET_FILE . 'templates/admin/rocket-settings/rocket-email-field.php';
        }
    
        /**
         * login rocket password field
         */
        public function rocket_password_field_callback() {
            $rocket_password = self::get_rocket_password();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/rocket-password-field.php';
        }
        
        /**
         * encrypt rocket password
         * 
         * @param string $val
         * @return string
         */
        public function encrybt_rocket_password($val) {
            if($val){
                $sodium_crypto_data = wc_rocket_sodium_crypto_data();
                $encryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($sodium_crypto_data['keypair1_secret'], $sodium_crypto_data['keypair2_public']);
                $encrypted = sodium_crypto_box($val, $sodium_crypto_data['nonce'], $encryption_key);
                $val = base64_encode($encrypted);
                // save rocket password public an secret keys
                update_option('wc_rocketp_key1', base64_encode($sodium_crypto_data['keypair1_public']));
                update_option('wc_rocketp_key2', base64_encode($sodium_crypto_data['keypair2_secret']));
                update_option('wc_rocketp_nonce', base64_encode($sodium_crypto_data['nonce']));
            }
            return $val;
        }


        /**
         * login enable or disable the control panel
         */
        public function control_panel_accessibility_callback() {
            $control_panel_accessibility = self::get_control_panel_accessibility();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/control-panel-accessibility-field.php';
        }
    
        /**
         * get control panel accessibility
         * @return bool
         */
        public static function get_control_panel_accessibility(){
            $control_panel_accessibility = get_option('control_panel_accessibility');
    
            // the option is not save yet (default == checked)
            if($control_panel_accessibility === false){
                $is_checked = true;
    
            // the checkbox is unchecked
            }elseif(($control_panel_accessibility === "")){
                $is_checked = false;
    
            // the checkbox is checked
            }else{
                $is_checked = true;
            }

            return $is_checked;
        }

        /**
         * portal customization colors lable
         */
        public function control_panel_portal_customization_colors_label_callback() {
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/portal-customization-colors-label.php';
        }
        
        /**
         * portal customization body background color
         */
        public function portal_customization_body_background_color_callback() {
            $portal_customization_body_background_color = self::get_portal_customization_body_background_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/icon-body-background.php';
        }
        
        /**
         * get portal customization body background color
         * @return string
         */
        public static function get_portal_customization_body_background_color(){
            return get_option("portal_customization_body_background_color") ? get_option("portal_customization_body_background_color") : "#FFFFFF";
        }


        /**
         * portal customization icon primary color
         */
        public function portal_customization_icon_primary_color_callback() {
            $portal_customization_icon_primary_color = self::get_portal_customization_icon_primary_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/icon-primary-color.php';
        }

        /**
         * get portal customization icon primary color
         * @return string
         */
        public static function get_portal_customization_icon_primary_color(){
            return get_option("portal_customization_icon_primary_color") ? get_option("portal_customization_icon_primary_color") : "#000000";
        }
    
        /**
         * portal customization icon secondary color
         */
        public function portal_customization_icon_secondary_color_callback() {
            $portal_customization_icon_secondary_color = self::get_portal_customization_icon_secondary_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/icon-secondary-color.php';
        }

        /**
         * get portal customization icon secondary color
         * @return string
         */
        public static function get_portal_customization_icon_secondary_color(){
            return get_option("portal_customization_icon_secondary_color") ? get_option("portal_customization_icon_secondary_color") : "#000000";
        }
    
        /**
         * portal customization primary color
         */
        public function portal_customization_primary_color_callback() {
            $portal_customization_primary_color = self::get_portal_customization_primary_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/primary-color.php';
        }

        /**
         * get  portal customization primary color
         * @return string
         */
        public static function get_portal_customization_primary_color(){
            return get_option("portal_customization_primary_color") ? get_option("portal_customization_primary_color") : "#000000";
        }
    
        /**
         * portal customization primary hover color
         */
        public function portal_customization_primary_hover_color_callback() {
            $portal_customization_primary_hover_color = self::get_portal_customization_primary_hover_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/primary-hover-color.php';
        }

        /**
         * get portal customization primary hover color
         * @return string
         */
        public static function get_portal_customization_primary_hover_color(){
            return get_option("portal_customization_primary_hover_color") ? get_option("portal_customization_primary_hover_color") : "#000000";
        }
    
        /**
         * portal customization primary active color
         */
        public function portal_customization_primary_active_color_callback() {
            $portal_customization_primary_active_color = self::get_portal_customization_primary_active_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/primary-active-color.php';
        }

        /**
         * get portal customization primary active color
         * @return string
         */
        public static function get_portal_customization_primary_active_color(){
            return get_option("portal_customization_primary_active_color") ? get_option("portal_customization_primary_active_color") : "#000000";
        }
    
        /**
         * portal customization primary menu hover color
         */
        public function portal_customization_primary_menu_hover_color_callback() {
            $portal_customization_primary_menu_hover_color = self::get_portal_customization_primary_menu_hover_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/primary-menu-hover-color.php';
        }
    
        /**
         * get portal customization primary menu hover color
         * @return string
         */
        public static function get_portal_customization_primary_menu_hover_color(){
            return get_option("portal_customization_primary_menu_hover_color") ? get_option("portal_customization_primary_menu_hover_color") : "#000000";
        }

        /**
         * portal customization primary menu active color
         */
        public function portal_customization_primary_menu_active_color_callback() {
            $portal_customization_primary_menu_active_color = self::get_portal_customization_primary_menu_active_color();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/portal-customization-colors/primary-menu-active-color.php';
        }

        /**
         * get portal customization primary menu active color
         * @return string
         */
        public static function get_portal_customization_primary_menu_active_color(){
            return get_option("portal_customization_primary_menu_active_color") ? get_option("portal_customization_primary_menu_active_color") : "#000000";
        }
    
        /**
         * default plugins install
         */
        public function default_plugins_install_field_callback() {
            $default_plugins_install = self::get_default_plugins_install();
            include WC_ROCKET_FILE . 'templates/admin/rocket-settings/default-plugins-install-field.php';
        }
        
        /**
         * get default plugins install
         * @return string
         */
        public static function get_default_plugins_install(){
            return get_option("default_plugins_install") ? get_option("default_plugins_install") : "";
        }

        /**
         * get rocket email
         * @return string
         */
        public static function get_rocket_email(){
            $rocket_email = get_option('rocket_email');
            
            return $rocket_email;
        }
        
        /**
         * get rocket password
         * @return string
         */
        public static function get_rocket_password(){
            try {
                $rocket_password = get_option('rocket_password');
                if($rocket_password){
                    $rocket_password = base64_decode($rocket_password);
                    // decrypt saved password
                    $keypair1_public = base64_decode(get_option('wc_rocketp_key1'));
                    $keypair2_secret = base64_decode(get_option('wc_rocketp_key2'));
                    $nonce = base64_decode(get_option('wc_rocketp_nonce', true));
                    if($keypair1_public && $keypair2_secret && $nonce){
                        $decryption_key = sodium_crypto_box_keypair_from_secretkey_and_publickey($keypair2_secret, $keypair1_public);
                        $rocket_password = sodium_crypto_box_open($rocket_password, $nonce, $decryption_key);
                    }
                }
                return $rocket_password;
            } catch (Exception $e) {
                return '';
            }
            
        }

        /**
         * WC_Rocket_Admin_Settings_Page instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();
    
            return self::$instance;
        }
    
    }
    
    WC_Rocket_Admin_Settings_Page::get_instance();
}
