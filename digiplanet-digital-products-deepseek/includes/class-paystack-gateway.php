<?php
/**
 * Paystack payment gateway
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Paystack_Gateway {
    
    private static $instance = null;
    private $test_mode = false;
    private $secret_key = '';
    private $public_key = '';
    
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
     * Constructor
     */
    private function __construct() {
        $this->test_mode = get_option('digiplanet_paystack_test_mode', 'yes') === 'yes';
        $this->secret_key = $this->test_mode ? 
            get_option('digiplanet_paystack_test_secret_key', '') : 
            get_option('digiplanet_paystack_live_secret_key', '');
        $this->public_key = $this->test_mode ? 
            get_option('digiplanet_paystack_test_public_key', '') : 
            get_option('digiplanet_paystack_live_public_key', '');
    }
    
    /**
     * Check if gateway is enabled
     */
    public function is_enabled() {
        return get_option('digiplanet_enable_paystack', 'no') === 'yes' && 
               !empty($this->secret_key) && 
               !empty($this->public_key);
    }
    
    /**
     * Process payment
     */
    public function process_payment($order_id, $payment_data) {
        $order_manager = Digiplanet_Order_Manager::get_instance();
        $order = $order_manager->get_order($order_id);
        
        if (!$order) {
            return [
                'success' => false,
                'message' => __('Order not found.', 'digiplanet-digital-products')
            ];
        }
        
        $callback_url = add_query_arg([
            'digiplanet_payment_callback' => 1,
            'gateway' => 'paystack',
            'order_id' => $order_id
        ], home_url('/'));
        
        $payment_data = [
            'amount' => $this->convert_to_kobo($order->total_amount),
            'email' => $order->customer_email,
            'currency' => strtoupper(get_option('digiplanet_currency', 'NGN')),
            'reference' => 'DP' . time() . $order_id,
            'callback_url' => $callback_url,
            'metadata' => [
                'order_id' => $order_id,
                'order_number' => $order->order_number,
                'customer_name' => $order->customer_name
            ]
        ];
        
        // Initialize payment
        $response = $this->make_paystack_request('transaction/initialize', $payment_data);
        
        if ($response && isset($response->status) && $response->status === true) {
            return [
                'success' => true,
                'requires_action' => true,
                'payment_url' => $response->data->authorization_url,
                'reference' => $response->data->reference,
                'order_id' => $order_id,
            ];
        }
        
        return [
            'success' => false,
            'message' => __('Failed to initialize payment.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Verify payment
     */
    public function verify_payment($reference) {
        $response = $this->make_paystack_request('transaction/verify/' . $reference);
        
        if ($response && isset($response->status) && $response->status === true) {
            return [
                'success' => true,
                'data' => $response->data
            ];
        }
        
        return [
            'success' => false,
            'message' => __('Payment verification failed.', 'digiplanet-digital-products')
        ];
    }
    
    /**
     * Handle payment callback
     */
    public function handle_callback() {
        if (!isset($_GET['reference'])) {
            wp_die('Invalid payment reference.');
        }
        
        $reference = sanitize_text_field($_GET['reference']);
        $verification = $this->verify_payment($reference);
        
        if ($verification['success']) {
            $payment_data = $verification['data'];
            $order_id = $payment_data->metadata->order_id ?? 0;
            
            if ($order_id && $payment_data->status === 'success') {
                $order_manager = Digiplanet_Order_Manager::get_instance();
                $order_manager->update_payment_status(
                    $order_id,
                    'completed',
                    $reference
                );
                
                // Redirect to success page
                wp_redirect(add_query_arg([
                    'order_complete' => 1,
                    'order_id' => $order_id
                ], get_permalink(get_option('digiplanet_checkout_page_id'))));
                exit;
            }
        }
        
        // Redirect to failure page
        wp_redirect(add_query_arg([
            'payment_failed' => 1
        ], get_permalink(get_option('digiplanet_checkout_page_id'))));
        exit;
    }
    
    /**
     * Make request to Paystack API
     */
    private function make_paystack_request($endpoint, $data = null) {
        $url = 'https://api.paystack.co/' . $endpoint;
        
        $args = [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secret_key,
                'Content-Type' => 'application/json',
            ],
            'timeout' => 30,
        ];
        
        if ($data) {
            $args['body'] = json_encode($data);
            $args['method'] = 'POST';
        }
        
        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        return json_decode($body);
    }
    
    /**
     * Convert amount to kobo (smallest currency unit)
     */
    private function convert_to_kobo($amount) {
        return intval($amount * 100);
    }
    
    /**
     * Get gateway settings
     */
    public function get_settings() {
        return [
            'test_mode' => $this->test_mode,
            'public_key' => $this->public_key,
        ];
    }
}