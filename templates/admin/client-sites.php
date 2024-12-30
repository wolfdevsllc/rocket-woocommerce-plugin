<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Client Sites', 'wc-rocket'); ?></h1>

    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Site ID', 'wc-rocket'); ?></th>
                <th><?php _e('Site Name', 'wc-rocket'); ?></th>
                <th><?php _e('Customer', 'wc-rocket'); ?></th>
                <th><?php _e('Order', 'wc-rocket'); ?></th>
                <th><?php _e('Status', 'wc-rocket'); ?></th>
                <th><?php _e('Created', 'wc-rocket'); ?></th>
                <th><?php _e('Actions', 'wc-rocket'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($sites as $site): ?>
                <tr>
                    <td><?php echo esc_html($site->id); ?></td>
                    <td><?php echo esc_html($site->site_name); ?></td>
                    <td><?php echo esc_html($site->user_email); ?></td>
                    <td>
                        <a href="<?php echo admin_url('post.php?post=' . $site->order_id . '&action=edit'); ?>">
                            #<?php echo esc_html($site->order_id); ?>
                        </a>
                    </td>
                    <td><?php echo esc_html($site->status); ?></td>
                    <td><?php echo esc_html($site->created_at); ?></td>
                    <td>
                        <a href="#" class="button view-site" data-site-id="<?php echo esc_attr($site->id); ?>">
                            <?php _e('View Site', 'wc-rocket'); ?>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>