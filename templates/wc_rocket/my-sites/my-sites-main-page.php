<?php
/**
 * MY Sites main page in my account page
 *
 * This template can be overridden by copying it to yourtheme/wc_rocket/my-sites/my-sites-main-page.php.
 */
defined('ABSPATH') || exit;

$ajax_params = array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('wc_rocket_nonce')
);
?>
<div class="wc-rocket-my-sites-wrap wc-rocket-loader-wrapper">
    <?php include WC_ROCKET_FILE . '/templates/wc_rocket/general/wc-rocket-loader.php'; ?>
    <div class="error-msg-container" id="error_div" hidden>
    </div>
    <?php
    // Get available allocations
    $available_allocations = WC_Rocket_Site_Allocations::get_instance()->get_customer_available_allocations(get_current_user_id());

    // Show create new site button if allocations available
    if ($available_allocations > 0) : ?>
        <div class="wc-rocket-available-allocations">
            <p>
                <?php printf(
                    _n(
                        'You have %d site allocation available.',
                        'You have %d site allocations available.',
                        $available_allocations,
                        'wc-rocket'
                    ),
                    $available_allocations
                ); ?>
            </p>
            <button class="button create-new-site-btn">
                <?php _e('Create New Site', 'wc-rocket'); ?>
            </button>
        </div>

        <script type="text/javascript">
            /* <![CDATA[ */
            var wc_rocket_params = <?php echo json_encode($ajax_params); ?>;
            /* ]]> */
        </script>

        <!-- Site Creation Form (Initially Hidden) -->
        <div class="wc-rocket-create-site-form hide">
            <h3><?php _e('Create New Site', 'wc-rocket'); ?></h3>

            <div id="allocation_details" class="allocation-details"></div>

            <form id="rocket-create-site-form">
                <?php wp_nonce_field('wc_rocket_nonce', 'nonce'); ?>
                <input type="hidden" id="allocation_id" name="allocation_id" value="">

                <p class="form-row">
                    <label for="site_name"><?php _e('Site Name', 'wc-rocket'); ?> <span class="required">*</span></label>
                    <input type="text" class="input-text" name="site_name" id="site_name" required>
                </p>

                <p class="form-row">
                    <label for="site_location"><?php _e('Site Location', 'wc-rocket'); ?> <span class="required">*</span></label>
                    <select name="site_location" id="site_location" required>
                        <?php foreach (WC_Rocket_Locations::get_instance()->get_rocket_site_locations() as $id => $name) : ?>
                            <option value="<?php echo esc_attr($id); ?>"><?php echo esc_html($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>

                <p class="form-row button-container">
                    <button type="submit" class="button"><?php _e('Create Site', 'wc-rocket'); ?></button>
                    <button type="button" class="button cancel-create-site"><?php _e('Cancel', 'wc-rocket'); ?></button>
                </p>
            </form>
        </div>
    <?php endif; ?>

    <div class="wc-rocket-my-sites-content">
        <?php do_action('wc_rocket_before_my_sites_table'); ?>

        <?php if (isset($my_sites) && count($my_sites) > 0) : ?>
            <table>
                <thead>
                    <tr>
                        <th><?php _e('ID', 'wc-rocket'); ?></th>
                        <th><?php _e('Site Name', 'wc-rocket'); ?></th>
                        <th><?php _e('Created at', 'wc-rocket'); ?></th>
                        <?php do_action('wc_rocket_add_header_of_new_col_to_my_sites_table'); ?>
                        <th><?php _e('Actions', 'wc-rocket'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($my_sites as $my_site) : ?>
                        <tr>
                            <td><?php echo esc_html($my_site->site_id); ?></td>
                            <td class="rocket-site-name-wrapper">
                                <?php $my_site_name = ($my_site->site_name) ? $my_site->site_name : ""; ?>
                                <div class="rocket-site-msg-container" hidden> </div>
                                <span class="site-name-display">
                                    <?= $my_site_name; ?>
                                </span>
                                <div class="rocket-site-name-wrap">
                                    <input type="text" class="rocket-site-name hide" value="<?= $my_site_name ?>">
                                    <a href="#" class="rocket-site-name-edit button"><?php _e('Edit', 'wc-rocket'); ?></a>
                                    <a href="#" class="rocket-site-name-save button hide" data-site-id="<?= isset($my_site->site_id) ? $my_site->site_id : "";  ?>">
                                        <?php _e('save', 'wc-rocket'); ?>
                                    </a>
                                    <a href="#" class="rocket-site-name-cancel button hide">
                                        <?php _e('Cancel', 'wc-rocket'); ?>
                                    </a>
                                </div>
                            </td>
                            <td>
                                <?php echo esc_html(date("F j, Y, g:i a", strtotime($my_site->created_at))); ?>
                            </td>
                            <?php do_action('wc_rocket_add_data_of_new_col_to_my_sites_table'); ?>
                            <td>
                                <?php if (isset($show_manage_btn) && $show_manage_btn) : ?>
                                    <a class="button" href="<?php echo esc_url(wc_get_endpoint_url('manage-site', $my_site->site_id, get_permalink(get_option('woocommerce_myaccount_page_id')))); ?>">
                                        <?php _e('Manage', 'wc-rocket'); ?>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <div class="no-sites-container">
                <div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                    <?php _e('No sites exist!', 'wc-rocket'); ?>
                </div>
                <?php
                $total_allocations = WC_Rocket_Site_Allocations::get_instance()->get_customer_total_allocations(get_current_user_id());
                $available_allocations = WC_Rocket_Site_Allocations::get_instance()->get_customer_available_allocations(get_current_user_id());

                if ($total_allocations === 0) {
                    // No allocations - show View Hosting Plans
                    $button_text = __('View Hosting Plans', 'wc-rocket');
                } elseif ($available_allocations === 0) {
                    // Has allocations but all used - show Upgrade Plan
                    $button_text = __('Upgrade Plan', 'wc-rocket');
                } else {
                    // Has available allocations - show Add New Site
                    $button_text = __('View Hosting Plans', 'wc-rocket');
                }
                if ( $available_allocations < 1 || $total_allocations === 0 ) { ?>
                    <a href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>" class="button no-sites-cta">
                        <?php echo $button_text; ?>
                    </a>
                <?php } ?>
            </div>
        <?php endif; ?>

        <?php do_action('wc_rocket_after_my_sites_table'); ?>
    </div>
</div>

<?php
if (current_user_can('administrator')) {
    // Debug output
    echo "<!-- Debug Info -->\n";
    echo "<!-- WC Rocket Scripts: -->\n";
    global $wp_scripts;
    foreach ($wp_scripts->registered as $handle => $script) {
        if (strpos($handle, 'wc-rocket') !== false) {
            echo "<!-- $handle: {$script->src} -->\n";
        }
    }
}
?>
