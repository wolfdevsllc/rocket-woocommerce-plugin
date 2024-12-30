<?php
/**
 * Rocket Product Site Details
 *
 * This template can be overridden by copying it to yourtheme/wc_rocket/single-product/tabs/product-rocket.php.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wc-product-rocket-details-wrap">
    <table>
        <tbody>
            <tr>
                <th>
                    <?php _e('Visitors', 'wc-rocket'); ?>
                </th>
                <td>
                    <?php echo $rocket_visitors; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e('Disk Space', 'wc-rocket'); ?>
                </th>
                <td>
                    <?php echo $rocket_disk_space; ?>
                </td>
            </tr>
            <tr>
                <th>
                    <?php _e('Bandwidth', 'wc-rocket'); ?>
                </th>
                <td>
                    <?php echo $rocket_bandwidth; ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>