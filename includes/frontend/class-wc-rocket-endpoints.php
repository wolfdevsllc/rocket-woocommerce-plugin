<?php

/**
 * WC Rocket Endpoints
 */
if (!class_exists('WC_Rocket_Endpoints')) {

    class WC_Rocket_Endpoints {

        public static $instance;

        public function __construct() {
            add_action('init',[$this,'wc_rocket_custom_endpoints']);
            add_action( 'wp_enqueue_scripts', [$this,'scripts_handler'] );
            add_action('wp_enqueue_scripts', [$this, 'style_handler']);
            add_action('wp_ajax_delete_rocket_site', [$this, 'delete_rocket_site_ajax']);
            add_action('wp_ajax_update_rocket_site', [$this, 'update_rocket_site_ajax']);
            // set wc rocket endpoint titles
            add_filter( 'the_title', array( $this, 'endpoint_title' ) );
            // woocommerce manage site page modify breadcrumb
            add_filter('woocommerce_get_breadcrumb', array($this, 'wc_rocket_manage_site_breadcrumb'), 10, 2);
            // remove wc account navigation from manage site page
            add_action( 'woocommerce_account_navigation', array($this, 'wc_rocket_manage_site_remove_navigation'), 1 );
        }

        public function scripts_handler(){
            // register scripts
            wp_register_script('manage_site_first_script', WC_ROCKET_URL . 'assets/js/frontend/manage-site-first-script.js',array('jquery'), false, true);
            wp_register_script('manage_site_second_script', WC_ROCKET_URL . 'assets/js/frontend/manage-site-second-script.js',array('jquery'), false, true);
            wp_register_script('my_sites_main_page_script', WC_ROCKET_URL . 'assets/js/frontend/my-sites-main-page-script.js',array('jquery'), false, true);
            if( $this->check_wc_rocket_my_site_page() ){
                wp_enqueue_script('my_sites_main_page_script');
                wp_localize_script('my_sites_main_page_script', 'ajax', array('ajax_url' => admin_url('admin-ajax.php')));
            }

        }

        /**
         * include the css
         */
        public function style_handler() {
            wp_register_style('my_sites_main_page_style', WC_ROCKET_URL . 'assets/css/frontend/my-sites-main-page.css');
            wp_register_style('rocket_manage_site_style', WC_ROCKET_URL . 'assets/css/frontend/wc-rocket-manage-site.css');
            wp_register_style('wc_rocket_loader_style', WC_ROCKET_URL . 'assets/css/general/wc-rocket-loader.css');
            if( $this->check_wc_rocket_my_site_page() || $this->check_wc_rocket_manage_site_page() ){
                wp_enqueue_style('my_sites_main_page_style');
                wp_enqueue_style('wc_rocket_loader_style');
            }
            // enqueue manage site style
            if( $this->check_wc_rocket_manage_site_page() ){
                wp_enqueue_style('rocket_manage_site_style');
            }
        }

        public function wc_rocket_custom_endpoints() {
            $this->wc_rocket_my_sites_endpoint();
            $this->wc_rocket_manage_site_endpoint();

            // by that fn we can open the new endpoint page without update the permalink from the backend :)
            flush_rewrite_rules();
        }

        public function wc_rocket_my_sites_endpoint() {
            //-------------------------start my sites endpoint--------------------------------//
            // init the endpoint
            add_rewrite_endpoint('my-sites', EP_ROOT | EP_PAGES);
            // // append this menu item to my account menu items
            add_filter('woocommerce_account_menu_items',[$this, 'add_my_sites_menu_item_to_my_account_menu_items']);
            // render the page of this new menu item
            add_action('woocommerce_account_my-sites_endpoint',[$this, 'render_my_sites_page']);
            //-------------------------end my sites endpoint--------------------------------//
        }

        public function wc_rocket_manage_site_endpoint() {
            $is_manage_page_allowed = WC_Rocket_Admin_Settings_Page::get_control_panel_accessibility();
            //-------------------------start my sites endpoint--------------------------------//
            if($is_manage_page_allowed){
            // init the endpoint
            add_rewrite_endpoint('manage-site', EP_ROOT | EP_PAGES);
            // render the page of this new menu item
            add_action('woocommerce_account_manage-site_endpoint',[$this, 'render_manage_site_page']);
            }
            //-------------------------end my sites endpoint--------------------------------//
        }


        /**
        * set wc rocket endpoint titles
        *
        * @param string $title
        * @return string
        */
        public function endpoint_title( $title ) {
            if( in_the_loop() ){
                if( $this->check_wc_rocket_my_site_page() ){
                    $title = __( 'My Sites', 'wc-rocket' );
                } elseif( $this->check_wc_rocket_manage_site_page() ){
                    $title = '';
                }
                remove_filter( 'the_title', array( $this, 'endpoint_title' ) );
            }

            return $title;
        }

        /**
         * add my site menu item to my account page menu items
         * @param array $items
         * @return array $items
         */
        public function add_my_sites_menu_item_to_my_account_menu_items($items){
            $logout = $items['customer-logout'];
            unset($items['customer-logout']);
            $items['my-sites'] = __('My Sites', 'wc-rocket');
            $items['customer-logout'] = $logout;
            return $items;
        }

        /**
         * render my sites page
         */
        public function render_my_sites_page(){
            $my_sites = WC_Rocket_Sites_Crud::get_instance()->get_sites_from_rocket_sites_table(get_current_user_id());
            $show_manage_btn = WC_Rocket_Admin_Settings_Page::get_control_panel_accessibility();
            wc_rocket_site_get_template(
                'my-sites/my-sites-main-page.php',
                array(
                    'my_sites' => $my_sites,
                    'show_manage_btn' => $show_manage_btn

                )
            );
        }

        /**
         * load the script code for rendering the manage site page
         * @param array $data
         */
        public function manage_site_scripts_handler($data){
            wp_enqueue_script('manage_site_first_script');
            wp_enqueue_script('manage_site_second_script');
            wp_localize_script('manage_site_second_script', 'data', $data);
        }

        /**
         * render manange site page
         */
        public function render_manage_site_page($site_id){
            $is_manage_page_allowed = false;
            $control_panel_is_accessed = WC_Rocket_Admin_Settings_Page::get_control_panel_accessibility();
            $is_a_valid_user_site = WC_Rocket_Sites_Crud::get_instance()->is_a_user_own_specific_site(get_current_user_id(),$site_id);
            if($is_a_valid_user_site && $control_panel_is_accessed){
                $site_access_token = WC_Rocket_Api_Site_Access_Token_Request::get_instance()->generate_site_access_token($site_id);
                $data = [
                    "site_id" => $site_id,
                    "site_access_token" => $site_access_token,
                    "bodyBackground_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_body_background_color(),
                    "icon_primary_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_icon_primary_color(),
                    "icon_secondary_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_icon_secondary_color(),
                    "primary_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_primary_color(),
                    "primary_hover_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_primary_hover_color(),
                    "primary_active_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_primary_active_color(),
                    "primary_menu_hover_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_primary_menu_hover_color(),
                    "primary_menu_active_color" => WC_Rocket_Admin_Settings_Page::get_portal_customization_primary_menu_active_color(),
                ];

                $this->manage_site_scripts_handler($data);
                $is_manage_page_allowed = true;
            }

            wc_rocket_site_get_template(
                'my-sites/manage-site-page.php',
                array(
                    'is_manage_page_allowed' => $is_manage_page_allowed,
                )
            );
        }

        public function delete_rocket_site_ajax(){
            $status = "success";
            $message = __("The site is deleted successfully.","wc-rocket");
            $site_id = isset($_POST['site_id']) ? $_POST['site_id'] : null;
            $user_id = get_current_user_id();

            if(isset($site_id) && $site_id != null){
                // delete the site from the server
                $reponse = WC_Rocket_Api_Site_Crud_Requests::get_instance()->delete_rocket_site($site_id);
                if(!empty($reponse) && isset($reponse['id']) && $reponse['id'] != ""){
                    // delete the site from the database
                    $site_is_deleted = WC_Rocket_Sites_Crud::get_instance()->delete_site_from_rocket_sites_table($user_id, $site_id);
                    if(!$site_is_deleted){
                        $status = "error";
                        wc_add_notice( __( 'Something wrong when trying delete this site from the database!', 'wc-rocket' ),"error" );
                        $message =wc_print_notices( true );
                    }
                }else{
                    $status = "error";
                    wc_add_notice( __( 'Something wrong when trying delete this site from the rocket server!', 'wc-rocket' ),"error" );
                    $message =wc_print_notices( true );
                }

            }else{
                $status = "error";
                wc_add_notice( __( 'The site id is required for deleting the site!', 'wc-rocket' ),"error" );
                $message =wc_print_notices( true );
            }


            $response = [
                'status' => $status,
                'message' => $message
            ];

            if($response['status'] == 'success'){
                wc_add_notice( __( 'The site is deleted successfully.', 'wc-rocket' ) );
                echo  wp_send_json_success($response);
            }else{
                echo wp_send_json_error($response);
            }
            wp_die();
        }

        /**
         * ajax to update rocket site data
         */
        public function update_rocket_site_ajax(){
            $success = false;
            $site_data = [];
            $site_data['site_id'] = isset($_POST['site_id']) ? $_POST['site_id'] : null;
            $site_data['site_name'] = isset($_POST['site_name']) ? $_POST['site_name'] : null;
            $site_data['user_id'] = get_current_user_id();

            if( $site_data['site_id'] && $site_data['site_name']){
                $success = WC_Rocket_Sites_Crud::get_instance()->update_site_from_rocket_sites_table($site_data);
            }

            if( $success){
                wc_add_notice( __( 'The site is updated successfully.', 'wc-rocket' ) );
                $message = wc_print_notices( true );
                echo  wp_send_json_success( array ('message' => $message) );
            } else{
                wc_add_notice( __( 'Something wrong when trying update site name!', 'wc-rocket' ),"error" );
                $message = wc_print_notices( true );
                echo  wp_send_json_error( array ('message' => $message) );
            }

        }

        /**
         * woocommerce manage site page modify breadcrumb
         * @global array $wp_query
         * @param array $crumbs
         * @param object $breadcrumbs
         * @return array
         */
        public function wc_rocket_manage_site_breadcrumb($crumbs, $breadcrumbs) {
            global $wp_query;

            if ( $this->check_wc_rocket_manage_site_page() ) {
                remove_filter('woocommerce_get_breadcrumb', array($this, 'wc_rocket_manage_site_breadcrumb'), 10, 2);
                //reset breadcrumbs
                $breadcrumbs->reset();
                // get my sites & manage site url
                $my_account_page = get_permalink(get_option('woocommerce_myaccount_page_id'));
                $my_sites_url = wc_get_endpoint_url('my-sites', '', $my_account_page);
                $manage_site_url = wc_get_endpoint_url('manage-site', $wp_query->query_vars[ 'manage-site' ], $my_account_page);
                // prepare breadcrumb
                $breadcrumbs->add_crumb( __('Home', 'wc-rocket'), apply_filters( 'woocommerce_breadcrumb_home_url', home_url() ) );
                $breadcrumbs->add_crumb( __('My Sites', 'wc-rocket'), apply_filters( 'woocommerce_breadcrumb_my_sites_url', $my_sites_url ) );
                $breadcrumbs->add_crumb( __('Manage Site', 'wc-rocket'), apply_filters( 'woocommerce_breadcrumb_manage_site_url', $manage_site_url ) );
                // generate bread crumb
                $crumbs = apply_filters('wc_rocket_manage_site_get_breadcrumb', $breadcrumbs->get_breadcrumb());
                add_filter('woocommerce_get_breadcrumb', array($this, 'wc_rocket_manage_site_breadcrumb'), 10, 2);
            }

            return $crumbs;
        }

        /**
         * remove wc account navigation from manage site page
         */
        public function wc_rocket_manage_site_remove_navigation(){
            if( $this->check_wc_rocket_manage_site_page() ){
                remove_action( 'woocommerce_account_navigation', 'woocommerce_account_navigation' );
            }
        }


        /**
         * check wc rocket my site page
         * @global array $wp_query
         * @return boolean
         */
        public function check_wc_rocket_my_site_page(){
            global $wp_query;
            if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && is_account_page() && isset( $wp_query->query_vars[ 'my-sites' ] ) ) {
                return true;
            }

            return false;
        }

        /**
         * check wc rocket my site page
         * @global array $wp_query
         * @return boolean
         */
        public function check_wc_rocket_manage_site_page(){
            global $wp_query;
            if ( ! is_null( $wp_query ) && ! is_admin() && is_main_query() && is_account_page() && isset( $wp_query->query_vars[ 'manage-site' ] ) ) {
                return true;
            }

            return false;
        }

        /**
         * WC_Rocket_Endpoints instance
         *
         * @return object
         */
        public static function get_instance() {
            if (!isset(self::$instance) || is_null(self::$instance))
                self::$instance = new self();

            return self::$instance;
        }

    }

    WC_Rocket_Endpoints::get_instance();
}