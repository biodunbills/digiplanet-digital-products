<?php
/**
 * Email management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Email_Manager {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Send order confirmation email
     */
    public function send_order_confirmation($order_id) {
        $order_manager = Digiplanet_Order_Manager::get_instance();
        $order = $order_manager->get_order($order_id);
        
        if (!$order) {
            return false;
        }
        
        $subject = get_option('digiplanet_order_email_subject', sprintf(__('Order Confirmation #%s', 'digiplanet-digital-products'), $order->order_number));
        
        $to = $order->customer_email;
        $headers = $this->get_email_headers();
        
        ob_start();
        include DIGIPLANET_TEMPLATES_DIR . 'email/order-confirmation.php';
        $message = ob_get_clean();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send license details email
     */
    public function send_license_details($order_item_id, $customer_id) {
        global $wpdb;
        
        $order_item = $wpdb->get_row($wpdb->prepare("
            SELECT oi.*, o.customer_email, o.customer_name, p.name as product_name 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.id = %d AND o.customer_id = %d
        ", $order_item_id, $customer_id));
        
        if (!$order_item) {
            return false;
        }
        
        $subject = sprintf(__('Your License Key for %s', 'digiplanet-digital-products'), $order_item->product_name);
        $to = $order_item->customer_email;
        $headers = $this->get_email_headers();
        
        ob_start();
        include DIGIPLANET_TEMPLATES_DIR . 'email/license-details.php';
        $message = ob_get_clean();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send download instructions email
     */
    public function send_download_instructions($order_item_id, $customer_id) {
        global $wpdb;
        
        $order_item = $wpdb->get_row($wpdb->prepare("
            SELECT oi.*, o.customer_email, o.customer_name, p.name as product_name 
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            WHERE oi.id = %d AND o.customer_id = %d
        ", $order_item_id, $customer_id));
        
        if (!$order_item) {
            return false;
        }
        
        $subject = sprintf(__('Download Instructions for %s', 'digiplanet-digital-products'), $order_item->product_name);
        $to = $order_item->customer_email;
        $headers = $this->get_email_headers();
        
        ob_start();
        include DIGIPLANET_TEMPLATES_DIR . 'email/download-instructions.php';
        $message = ob_get_clean();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send password reset email for custom login
     */
    public function send_password_reset($user_id, $reset_key) {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $reset_url = add_query_arg([
            'action' => 'rp',
            'key' => $reset_key,
            'login' => rawurlencode($user->user_login)
        ], wp_login_url());
        
        $subject = __('Password Reset Request', 'digiplanet-digital-products');
        $to = $user->user_email;
        $headers = $this->get_email_headers();
        
        $message = sprintf(__('Someone requested a password reset for your account. To reset your password, visit the following address: %s', 'digiplanet-digital-products'), $reset_url);
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send new account email
     */
    public function send_new_account_email($user_id, $password = '') {
        $user = get_userdata($user_id);
        
        if (!$user) {
            return false;
        }
        
        $subject = __('Your Account Has Been Created', 'digiplanet-digital-products');
        $to = $user->user_email;
        $headers = $this->get_email_headers();
        
        $message = sprintf(__('Welcome! Your account has been created. You can log in here: %s', 'digiplanet-digital-products'), wp_login_url());
        
        if ($password) {
            $message .= "\n\n" . sprintf(__('Your password is: %s', 'digiplanet-digital-products'), $password);
        }
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get email headers
     */
    private function get_email_headers() {
        $from_name = get_option('digiplanet_email_from_name', get_bloginfo('name'));
        $from_address = get_option('digiplanet_email_from_address', get_bloginfo('admin_email'));
        
        return [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_address . '>',
            'Reply-To: ' . $from_address
        ];
    }
    
    /**
     * Get email template
     */
    public function get_email_template($template_name, $data = []) {
        $template_path = DIGIPLANET_TEMPLATES_DIR . 'email/' . $template_name . '.php';
        
        if (file_exists($template_path)) {
            ob_start();
            extract($data);
            include $template_path;
            return ob_get_clean();
        }
        
        return '';
    }
}