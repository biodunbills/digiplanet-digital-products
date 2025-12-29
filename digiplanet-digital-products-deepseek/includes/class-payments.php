<?php
/**
 * Payment gateway management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Payments {
    
    private static $instance = null;
    private $gateways = [];
    
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
        $this->load_gateways();
        add_action('init', [$this, 'handle_payment_callback']);
    }
    
    /**
     * Load payment gateways
     */
    private function load_gateways() {
        // Include gateway classes
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-stripe-gateway.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'includes/class-paystack-gateway.php';
        
        // Register gateways
        $this->gateways = [
            'stripe' => Digiplanet_Stripe_Gateway::get_instance(),
            'paystack' => Digiplanet_Paystack_Gateway::get_instance(),
        ];
        
        // Filter active gateways
        $this->gateways = array_filter($this->gateways, function($gateway) {
            return $gateway->is_enabled();
        });
    }
    
    /**
     * Get available payment gateways
     */
    public function get_available_gateways() {
        return $this->gateways;
    }
    
    /**
     * Get gateway by ID
     */
    public function get_gateway($gateway_id) {
        return $this->gateways[$gateway_id] ?? null;
    }
    
    /**
     * Process payment
     */
    public function process_payment($order_id, $gateway_id, $payment_data = []) {
        $gateway = $this->get_gateway($gateway_id);
        
        if (!$gateway) {
            return [
                'success' => false,
                'message' => __('Invalid payment gateway.', 'digiplanet-digital-products')
            ];
        }
        
        return $gateway->process_payment($order_id, $payment_data);
    }
    
    /**
     * Handle payment callback
     */
    public function handle_payment_callback() {
        if (isset($_GET['digiplanet_payment_callback'])) {
            $gateway = $_GET['gateway'] ?? '';
            $gateway_instance = $this->get_gateway($gateway);
            
            if ($gateway_instance) {
                $gateway_instance->handle_callback();
            }
        }
    }
    
    /**
     * Get payment method label
     */
    public function get_payment_method_label($method) {
        $labels = [
            'stripe' => __('Credit Card (Stripe)', 'digiplanet-digital-products'),
            'paystack' => __('Paystack', 'digiplanet-digital-products'),
            'bank_transfer' => __('Bank Transfer', 'digiplanet-digital-products'),
        ];
        
        return $labels[$method] ?? ucfirst(str_replace('_', ' ', $method));
    }
}