<?php defined('ABSPATH') || exit; ?>

<div class="wrap wc-rocket-allocations">
    <h1><?php _e('Site Allocations', 'wc-rocket'); ?></h1>

    <div class="notice notice-info">
        <p><?php _e('Manage customer site allocations. You can adjust the total number of sites allowed for each allocation.', 'wc-rocket'); ?></p>
    </div>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('ID', 'wc-rocket'); ?></th>
                <th><?php _e('Customer', 'wc-rocket'); ?></th>
                <th><?php _e('Product', 'wc-rocket'); ?></th>
                <th><?php _e('Order', 'wc-rocket'); ?></th>
                <th><?php _e('Sites Used', 'wc-rocket'); ?></th>
                <th><?php _e('Total Sites', 'wc-rocket'); ?></th>
                <th><?php _e('Created', 'wc-rocket'); ?></th>
                <th><?php _e('Actions', 'wc-rocket'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allocations as $allocation) : ?>
                <tr>
                    <td><?php echo esc_html($allocation->id); ?></td>
                    <td>
                        <?php
                        echo esc_html($allocation->customer_name);
                        printf(' (#%d)', $allocation->customer_id);
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo get_edit_post_link($allocation->product_id); ?>">
                            <?php echo esc_html($allocation->product_name); ?>
                        </a>
                    </td>
                    <td>
                        <a href="<?php echo get_edit_post_link($allocation->order_id); ?>">
                            <?php echo '#' . $allocation->order_id; ?>
                            <span class="order-status status-<?php echo esc_attr($allocation->order_status); ?>">
                                (<?php echo wc_get_order_status_name($allocation->order_status); ?>)
                            </span>
                        </a>
                    </td>
                    <td><?php echo esc_html($allocation->sites_created); ?></td>
                    <td class="total-sites-column">
                        <span class="total-sites-display"><?php echo esc_html($allocation->total_sites); ?></span>
                        <div class="total-sites-edit" style="display: none;">
                            <input type="number"
                                   class="total-sites-input"
                                   value="<?php echo esc_attr($allocation->total_sites); ?>"
                                   min="<?php echo esc_attr($allocation->sites_created); ?>"
                                   step="1">
                            <button class="button save-allocation" data-id="<?php echo esc_attr($allocation->id); ?>">
                                <?php _e('Save', 'wc-rocket'); ?>
                            </button>
                            <button class="button cancel-edit">
                                <?php _e('Cancel', 'wc-rocket'); ?>
                            </button>
                        </div>
                        <button class="button edit-allocation">
                            <?php _e('Edit', 'wc-rocket'); ?>
                        </button>
                    </td>
                    <td>
                        <?php echo esc_html(
                            date_i18n(
                                get_option('date_format') . ' ' . get_option('time_format'),
                                strtotime($allocation->created_at)
                            )
                        ); ?>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(add_query_arg(array(
                            'page' => 'wc-rocket-sites',
                            'customer_id' => $allocation->customer_id
                        ), admin_url('admin.php'))); ?>"
                           class="button">
                            <?php _e('View Sites', 'wc-rocket'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>