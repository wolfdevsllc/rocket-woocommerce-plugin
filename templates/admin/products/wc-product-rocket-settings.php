<!-- id below must match target registered in above add_my_custom_product_data_tab function -->
<div id="wc_product_rocket_settings" class="panel woocommerce_options_panel ">
    <div class="options_group">
        <?php
        do_action('wc_rocket_product_before_settings');
        // settings to enable rocket to this product
        woocommerce_wp_checkbox(array(
            'id' => 'enable_rocket',
            'label' => __('Enable Rocket', 'wc-rocket'),
            'description' => __('Allow create site when user purchase this product', 'wc-rocket'),
            'default' => '0',
            'desc_tip' => true,
        ));
        ?>
        <div class="rocket_group_settings">
            <?php
            // settings to rocket visitors
            woocommerce_wp_text_input(array(
                'type' => 'number',
                'id' => 'rocket_visitors',
                'label' => __('Rocket Visitors', 'wc-rocket'),
                'description' => '',
                'default' => '',
                'desc_tip' => false,
            ));
            // settings to rocket disk space
            woocommerce_wp_text_input(array(
                'type' => 'number',
                'id' => 'rocket_disk_space',
                'label' => __('Rocket Disk Space', 'wc-rocket'),
                'description' => __('Enter Rocket Disk Space In MB', 'wc-rocket'),
                'default' => '',
                'desc_tip' => true,
            ));
            // settings to rocket bandwidth
            woocommerce_wp_text_input(array(
                'type' => 'number',
                'id' => 'rocket_bandwidth',
                'label' => __('Rocket Bandwidth', 'wc-rocket'),
                'description' => __('Enter Rocket Bandwidth In MB', 'wc-rocket'),
                'default' => '',
                'desc_tip' => true,
            ));
            // settings to rocket bandwidth
            woocommerce_wp_textarea_input(array(
                'id' => 'rocket_plugins_install',
                'label' => __('Default Plugins Install', 'wc-rocket'),
                'description' => __('Enter the plugins that will be installed in each site with comma separated', 'wc-rocket'),
                'default' => '',
                'desc_tip' => true,
            ));
            // settings to rocket sites limit
            woocommerce_wp_text_input(array(
                'type' => 'number',
                'id' => 'rocket_sites_limit',
                'label' => __('Number of Sites Allowed', 'wc-rocket'),
                'description' => __('Maximum number of sites customer can create with this product', 'wc-rocket'),
                'default' => '1',
                'custom_attributes' => array(
                    'min' => '1',
                    'step' => '1'
                ),
                'desc_tip' => true,
            ));
            ?>
        </div>

        <?php
        do_action('wc_rocket_product_after_settings');
        ?>
    </div>
</div>