<div class="rocket-allocation-info">
    <h3><?php _e('Site Allocations', 'wc-rocket'); ?></h3>
    <table class="widefat fixed">
        <thead>
            <tr>
                <th><?php _e('ID', 'wc-rocket'); ?></th>
                <th><?php _e('Total Sites', 'wc-rocket'); ?></th>
                <th><?php _e('Sites Created', 'wc-rocket'); ?></th>
                <th><?php _e('Remaining', 'wc-rocket'); ?></th>
                <th><?php _e('Created', 'wc-rocket'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allocations as $allocation) : ?>
                <tr>
                    <td><?php echo esc_html($allocation->id); ?></td>
                    <td><?php echo esc_html($allocation->total_sites); ?></td>
                    <td><?php echo esc_html($allocation->sites_created); ?></td>
                    <td><?php echo esc_html($allocation->total_sites - $allocation->sites_created); ?></td>
                    <td><?php echo esc_html(date_i18n(
                        get_option('date_format') . ' ' . get_option('time_format'),
                        strtotime($allocation->created_at)
                    )); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>