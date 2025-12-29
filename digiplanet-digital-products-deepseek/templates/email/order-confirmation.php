<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?php echo esc_html($subject); ?></title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: #3498db; color: white; padding: 20px; text-align: center; }
        .content { background: #f9f9f9; padding: 30px; }
        .order-details { background: white; border: 1px solid #ddd; padding: 20px; margin: 20px 0; }
        .footer { background: #f1f1f1; padding: 20px; text-align: center; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }
        .total-row { font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <h2><?php _e('Order Confirmation', 'digiplanet-digital-products'); ?></h2>
        </div>
        
        <div class="content">
            <p><?php _e('Dear', 'digiplanet-digital-products'); ?> <?php echo esc_html($order->customer_name); ?>,</p>
            
            <p><?php _e('Thank you for your purchase! Your order has been received and is now being processed.', 'digiplanet-digital-products'); ?></p>
            
            <div class="order-details">
                <h3><?php _e('Order Details', 'digiplanet-digital-products'); ?></h3>
                
                <table>
                    <tr>
                        <td><strong><?php _e('Order Number:', 'digiplanet-digital-products'); ?></strong></td>
                        <td>#<?php echo esc_html($order->order_number); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Date:', 'digiplanet-digital-products'); ?></strong></td>
                        <td><?php echo date_i18n(get_option('date_format'), strtotime($order->created_at)); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Total:', 'digiplanet-digital-products'); ?></strong></td>
                        <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Payment Method:', 'digiplanet-digital-products'); ?></strong></td>
                        <td><?php echo esc_html($order->payment_method); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Status:', 'digiplanet-digital-products'); ?></strong></td>
                        <td><?php echo esc_html($order->payment_status); ?></td>
                    </tr>
                </table>
                
                <h4><?php _e('Order Items', 'digiplanet-digital-products'); ?></h4>
                <table>
                    <thead>
                        <tr>
                            <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                            <th><?php _e('Price', 'digiplanet-digital-products'); ?></th>
                            <th><?php _e('License Key', 'digiplanet-digital-products'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order->items as $item): ?>
                        <tr>
                            <td><?php echo esc_html($item->product_name); ?></td>
                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($item->product_price); ?></td>
                            <td><code><?php echo esc_html($item->license_key); ?></code></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2"><?php _e('Total', 'digiplanet-digital-products'); ?></td>
                            <td><?php echo Digiplanet_Product_Manager::get_instance()->format_price($order->total_amount); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <p><?php _e('You can view your order and download your products from your account dashboard:', 'digiplanet-digital-products'); ?></p>
            <p><a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url(); ?>"><?php _e('View My Account', 'digiplanet-digital-products'); ?></a></p>
            
            <p><?php _e('If you have any questions, please contact our support team.', 'digiplanet-digital-products'); ?></p>
            
            <p><?php _e('Best regards,', 'digiplanet-digital-products'); ?><br>
            <?php echo esc_html(get_bloginfo('name')); ?></p>
        </div>
        
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> <?php echo esc_html(get_bloginfo('name')); ?>. <?php _e('All rights reserved.', 'digiplanet-digital-products'); ?></p>
            <p><?php _e('This email was sent to', 'digiplanet-digital-products'); ?> <?php echo esc_html($order->customer_email); ?></p>
        </div>
    </div>
</body>
</html>