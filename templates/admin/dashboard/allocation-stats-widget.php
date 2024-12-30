<div class="wc-rocket-dashboard-stats">
    <div class="stats-grid">
        <div class="stat-box">
            <span class="stat-label"><?php _e('Total Customers', 'wc-rocket'); ?></span>
            <span class="stat-value"><?php echo esc_html($stats->total_customers); ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label"><?php _e('Total Sites Allocated', 'wc-rocket'); ?></span>
            <span class="stat-value"><?php echo esc_html($stats->total_allocated); ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label"><?php _e('Sites Created', 'wc-rocket'); ?></span>
            <span class="stat-value"><?php echo esc_html($stats->total_created); ?></span>
        </div>
        <div class="stat-box">
            <span class="stat-label"><?php _e('Sites Remaining', 'wc-rocket'); ?></span>
            <span class="stat-value"><?php echo esc_html($stats->total_remaining); ?></span>
        </div>
    </div>

    <h3><?php _e('Recent Allocations', 'wc-rocket'); ?></h3>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th><?php _e('Customer', 'wc-rocket'); ?></th>
                <th><?php _e('Sites', 'wc-rocket'); ?></th>
                <th><?php _e('Created', 'wc-rocket'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($recent as $allocation) : ?>
                <tr>
                    <td><?php echo esc_html($allocation->customer_name); ?></td>
                    <td><?php echo sprintf(__('%d/%d used', 'wc-rocket'),
                        $allocation->sites_created,
                        $allocation->total_sites); ?></td>
                    <td><?php echo human_time_diff(strtotime($allocation->created_at), current_time('timestamp')) . ' ' . __('ago', 'wc-rocket'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p class="allocation-links">
        <a href="<?php echo admin_url('admin.php?page=wc-rocket-allocations'); ?>" class="button">
            <?php _e('View All Allocations', 'wc-rocket'); ?>
        </a>
    </p>
</div>