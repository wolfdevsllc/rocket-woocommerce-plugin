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
                        <?php echo esc_html($allocation->customer_name); ?>
                        <br>
                        <small><?php echo esc_html($allocation->customer_email); ?></small>
                    </td>
                    <td><?php echo esc_html($allocation->product_name); ?></td>
                    <td>
                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $allocation->order_id . '&action=edit')); ?>">
                            #<?php echo esc_html($allocation->order_id); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($allocation->sites_created); ?></td>
                    <td><?php echo esc_html($allocation->total_sites); ?></td>
                    <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($allocation->created_at))); ?></td>
                    <td class="actions">
                        <?php echo $this->get_row_actions($allocation); ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>