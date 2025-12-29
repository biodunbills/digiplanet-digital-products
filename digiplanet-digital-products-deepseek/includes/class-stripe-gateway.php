<?php
/**
 * Stripe payment gateway
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Stripe_Gateway {
    
    private static $instance = null;
    private $test_mode = false;
    private $secret_key = '';
    private $publishable_key = '';
    
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
        $this->test_mode = get_option('digiplanet_stripe_test_mode', 'yes') === 'yes';
        $this->secret_key = $this->test_mode ? 
            get_option('digiplanet_stripe_test_secret_key', '') : 
            get_option('digiplanet_stripe_live_secret_key', '');
        $this->publishable_key = $this->test_mode ? 
            get_option('digiplanet_stripe_test_publishable_key', '') : 
            get_option('digiplanet_stripe_live_publishable_key', '');
    }
    
    /**
     * Check if gateway is enabled
     */
    public function is_enabled() {
        return get_option('digiplanet_enable_stripe', 'no') === 'yes' && 
               !empty($this->secret_key) && 
               !empty($this->publishable_key);
    }
    
    /**
     * Process payment
     */
    public function process_payment($order_id, $payment_data) {
        if (!class_exists('Stripe\Stripe')) {
            require_once DIGIPLANET_PLUGIN_DIR . 'vendor/autoload.php';
        }
        
        try {
            \Stripe\Stripe::setApiKey($this->secret_key);
            
            $order_manager = Digiplanet_Order_Manager::get_instance();
            $order = $order_manager->get_order($order_id);
            
            if (!$order) {
                throw new Exception(__('Order not found.', 'digiplanet-digital-products'));
            }
            
            // Create Stripe payment intent
            $intent = \Stripe\PaymentIntent::create([
                'amount' => $this->convert_to_cents($order->total_amount),
                'currency' => strtolower(get_option('digiplanet_currency', 'usd')),
                'metadata' => [
                    'order_id' => $order_id,
                    'order_number' => $order->order_number
                ],
                'description' => sprintf(__('Order #%s', 'digiplanet-digital-products'), $order->order_number),
            ]);
            
            return [
                'success' => true,
                'requires_action' => true,
                'payment_intent_secret' => $intent->client_secret,
                'publishable_key' => $this->publishable_key,
                'order_id' => $order_id,
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Handle payment callback
     */
    public function handle_callback() {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = get_option('digiplanet_stripe_webhook_secret', '');
        
        if (!$endpoint_secret) {
            wp_die('Webhook secret not configured.');
        }
        
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            http_response_code(400);
            exit();
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            http_response_code(400);
            exit();
        }
        
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $payment_intent = $event->data->object;
                $this->handle_payment_success($payment_intent);
                break;
                
            case 'payment_intent.payment_failed':
                $payment_intent = $event->data->object;
                $this->handle_payment_failure($payment_intent);
                break;
        }
        
        http_response_code(200);
    }
    
    /**
     * Handle successful payment
     */
    private function handle_payment_success($payment_intent) {
        $order_id = $payment_intent->metadata->order_id ?? 0;
        
        if ($order_id) {
            $order_manager = Digiplanet_Order_Manager::get_instance();
            $order_manager->update_payment_status(
                $order_id, 
                'completed', 
                $payment_intent->id
            );
        }
    }
    
    /**
     * Handle failed payment
     */
    private function handle_payment_failure($payment_intent) {
        $order_id = $payment_intent->metadata->order_id ?? 0;
        
        if ($order_id) {
            $order_manager = Digiplanet_Order_Manager::get_instance();
            $order_manager->update_payment_status($order_id, 'failed');
        }
    }
    
    /**
     * Convert amount to cents
     */
    private function convert_to_cents($amount) {
        return round($amount * 100);
    }
    
    /**
     * Get gateway settings
     */
    public function get_settings() {
        return [
            'test_mode' => $this->test_mode,
            'publishable_key' => $this->publishable_key,
        ];
    }
}