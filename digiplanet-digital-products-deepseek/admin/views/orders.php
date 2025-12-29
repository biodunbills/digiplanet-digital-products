<?php
/**
 * Orders admin view
 */

if (!defined('ABSPATH')) {
    exit;
}

$order_manager = Digiplanet_Order_Manager::get_instance();
$current_page = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
$per_page = 20;
$orders = $order_manager->get_orders($current_page, $per_page);
$total_orders = $order_manager->get_total_orders_count();
$total_pages = ceil($total_orders / $per_page);
$status_counts = $order_manager->get_order_status_counts();
$payment_status_counts = $order_manager->get_payment_status_counts();
$recent_orders = $order_manager->get_recent_orders(5);

// Handle bulk actions
if (isset($_POST['action']) && isset($_POST['order_ids'])) {
    $action = sanitize_text_field($_POST['action']);
    $order_ids = array_map('absint', $_POST['order_ids']);
    
    switch ($action) {
        case 'delete':
            foreach ($order_ids as $order_id) {
                $order_manager->delete_order($order_id);
            }
            break;
        case 'mark_completed':
            foreach ($order_ids as $order_id) {
                $order_manager->update_order_status($order_id, 'completed');
            }
            break;
        case 'mark_processing':
            foreach ($order_ids as $order_id) {
                $order_manager->update_order_status($order_id, 'processing');
            }
            break;
        case 'mark_cancelled':
            foreach ($order_ids as $order_id) {
                $order_manager->update_order_status($order_id, 'cancelled');
            }
            break;
    }
    
    echo '<div class="notice notice-success is-dismissible"><p>' . __('Orders updated successfully.', 'digiplanet-digital-products') . '</p></div>';
}
?>

<div class="wrap digiplanet-admin-wrap">
    <h1 class="wp-heading-inline"><?php _e('Orders', 'digiplanet-digital-products'); ?></h1>
    
    <div class="digiplanet-admin-stats">
        <div class="digiplanet-stat-card">
            <h3><?php echo number_format($total_orders); ?></h3>
            <p><?php _e('Total Orders', 'digiplanet-digital-products'); ?></p>
        </div>
        
        <div class="digiplanet-stat-card">
            <h3><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order_manager->get_total_revenue()); ?></h3>
            <p><?php _e('Total Revenue', 'digiplanet-digital-products'); ?></p>
        </div>
        
        <div class="digiplanet-stat-card">
            <h3><?php echo isset($status_counts['completed']) ? $status_counts['completed'] : 0; ?></h3>
            <p><?php _e('Completed', 'digiplanet-digital-products'); ?></p>
        </div>
        
        <div class="digiplanet-stat-card">
            <h3><?php echo isset($status_counts['pending']) ? $status_counts['pending'] : 0; ?></h3>
            <p><?php _e('Pending', 'digiplanet-digital-products'); ?></p>
        </div>
    </div>
    
    <div class="digiplanet-admin-content">
        <div class="digiplanet-orders-table">
            <form method="post" action="">
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="action" id="bulk-action-selector-top">
                            <option value="-1"><?php _e('Bulk Actions', 'digiplanet-digital-products'); ?></option>
                            <option value="mark_processing"><?php _e('Mark as Processing', 'digiplanet-digital-products'); ?></option>
                            <option value="mark_completed"><?php _e('Mark as Completed', 'digiplanet-digital-products'); ?></option>
                            <option value="mark_cancelled"><?php _e('Mark as Cancelled', 'digiplanet-digital-products'); ?></option>
                            <option value="delete"><?php _e('Delete', 'digiplanet-digital-products'); ?></option>
                        </select>
                        <input type="submit" id="doaction" class="button action" value="<?php _e('Apply', 'digiplanet-digital-products'); ?>">
                    </div>
                    
                    <div class="tablenav-pages">
                        <span class="displaying-num"><?php printf(__('%d items', 'digiplanet-digital-products'), $total_orders); ?></span>
                        <?php if ($total_pages > 1): ?>
                            <span class="pagination-links">
                                <?php
                                echo paginate_links(array(
                                    'base' => add_query_arg('paged', '%#%'),
                                    'format' => '',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'total' => $total_pages,
                                    'current' => $current_page,
                                ));
                                ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td id="cb" class="manage-column column-cb check-column">
                                <input type="checkbox" id="cb-select-all-1">
                            </td>
                            <th scope="col"><?php _e('Order', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Customer', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Payment', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                            <th scope="col"><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="8"><?php _e('No orders found.', 'digiplanet-digital-products'); ?></td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <th scope="row" class="check-column">
                                        <input type="checkbox" name="order_ids[]" value="<?php echo $order->id; ?>">
                                    </th>
                                    <td>
                                        <strong>#<?php echo esc_html($order->order_number); ?></strong>
                                        <?php if ($order->notes): ?>
                                            <br>
                                            <small class="digiplanet-order-notes"><?php echo esc_html($order->notes); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?php echo esc_html($order->customer_name); ?></strong><br>
                                        <small><?php echo esc_html($order->customer_email); ?></small>
                                    </td>
                                    <td><?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($order->created_at)); ?></td>
                                    <td>
                                        <span class="digiplanet-status-badge digiplanet-status-<?php echo $order->status; ?>">
                                            <?php echo ucfirst($order->status); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="digiplanet-status-badge digiplanet-payment-<?php echo $order->payment_status; ?>">
                                            <?php echo ucfirst($order->payment_status); ?>
                                        </span>
                                        <?php if ($order->transaction_id): ?>
                                            <br>
                                            <small><?php echo esc_html($order->transaction_id); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                                    <td>
                                        <a href="<?php echo admin_url('admin.php?page=digiplanet-orders&action=view&id=' . $order->id); ?>" class="button button-small">
                                            <?php _e('View', 'digiplanet-digital-products'); ?>
                                        </a>
                                        <a href="<?php echo admin_url('admin.php?page=digiplanet-orders&action=edit&id=' . $order->id); ?>" class="button button-small">
                                            <?php _e('Edit', 'digiplanet-digital-products'); ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
        
        <div class="digiplanet-recent-orders">
            <h3><?php _e('Recent Orders', 'digiplanet-digital-products'); ?></h3>
            <ul>
                <?php foreach ($recent_orders as $order): ?>
                    <li>
                        <strong>#<?php echo $order->order_number; ?></strong>
                        <span><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></span>
                        <small><?php echo human_time_diff(strtotime($order->created_at), current_time('timestamp')); ?> ago</small>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>