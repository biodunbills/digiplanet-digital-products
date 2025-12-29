<?php
/**
 * Products admin management
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Products_Admin {
    
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
     * Constructor
     */
    private function __construct() {
        add_action('admin_init', [$this, 'handle_product_actions']);
        add_action('admin_post_digiplanet_save_product', [$this, 'save_product']);
        add_action('admin_post_digiplanet_delete_product', [$this, 'delete_product']);
    }
    
    /**
     * Handle product actions
     */
    public function handle_product_actions() {
        if (isset($_GET['page']) && $_GET['page'] === 'digiplanet-products') {
            $action = $_GET['action'] ?? '';
            
            switch ($action) {
                case 'edit':
                    $this->render_edit_product();
                    exit;
                case 'create':
                    $this->render_create_product();
                    exit;
            }
        }
    }
    
    /**
     * Render edit product page
     */
    private function render_edit_product() {
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $product_id = intval($_GET['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_die(__('Invalid product ID.', 'digiplanet-digital-products'));
        }
        
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $product = $product_manager->get_product($product_id);
        
        if (!$product) {
            wp_die(__('Product not found.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/edit-product.php';
    }
    
    /**
     * Render create product page
     */
    private function render_create_product() {
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        include DIGIPLANET_PLUGIN_DIR . 'admin/views/create-product.php';
    }
    
    /**
     * Save product
     */
    public function save_product() {
        check_admin_referer('digiplanet_save_product');
        
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $product_id = intval($_POST['product_id'] ?? 0);
        $data = $this->sanitize_product_data($_POST);
        
        global $wpdb;
        
        if ($product_id) {
            // Update existing product
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_products',
                $data,
                ['id' => $product_id],
                $this->get_data_formats($data),
                ['%d']
            );
            
            $message = __('Product updated successfully.', 'digiplanet-digital-products');
        } else {
            // Create new product
            $data['created_at'] = current_time('mysql');
            $data['slug'] = $this->generate_product_slug($data['name']);
            
            $wpdb->insert(
                $wpdb->prefix . 'digiplanet_products',
                $data,
                $this->get_data_formats($data)
            );
            
            $product_id = $wpdb->insert_id;
            $message = __('Product created successfully.', 'digiplanet-digital-products');
        }
        
        // Handle file upload
        if (!empty($_FILES['download_file']['name'])) {
            $this->handle_file_upload($product_id);
        }
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-products',
            'digiplanet_message' => urlencode($message),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Delete product
     */
    public function delete_product() {
        check_admin_referer('digiplanet_delete_product');
        
        if (!current_user_can('digiplanet_manage_products')) {
            wp_die(__('Unauthorized.', 'digiplanet-digital-products'));
        }
        
        $product_id = intval($_GET['product_id'] ?? 0);
        
        if (!$product_id) {
            wp_die(__('Invalid product ID.', 'digiplanet-digital-products'));
        }
        
        global $wpdb;
        
        $wpdb->delete(
            $wpdb->prefix . 'digiplanet_products',
            ['id' => $product_id],
            ['%d']
        );
        
        // Redirect with success message
        wp_redirect(add_query_arg([
            'page' => 'digiplanet-products',
            'digiplanet_message' => urlencode(__('Product deleted successfully.', 'digiplanet-digital-products')),
            'digiplanet_message_type' => 'success'
        ], admin_url('admin.php')));
        exit;
    }
    
    /**
     * Sanitize product data
     */
    private function sanitize_product_data($data) {
        $sanitized = [
            'name' => sanitize_text_field($data['name'] ?? ''),
            'description' => wp_kses_post($data['description'] ?? ''),
            'short_description' => sanitize_textarea_field($data['short_description'] ?? ''),
            'price' => floatval($data['price'] ?? 0),
            'sale_price' => !empty($data['sale_price']) ? floatval($data['sale_price']) : null,
            'sku' => sanitize_text_field($data['sku'] ?? ''),
            'download_limit' => intval($data['download_limit'] ?? 0),
            'license_type' => sanitize_text_field($data['license_type'] ?? 'single'),
            'version' => sanitize_text_field($data['version'] ?? '1.0.0'),
            'category_id' => intval($data['category_id'] ?? 0),
            'subcategory_id' => intval($data['subcategory_id'] ?? 0),
            'featured_image_id' => intval($data['featured_image_id'] ?? 0),
            'file_size' => sanitize_text_field($data['file_size'] ?? ''),
            'file_format' => sanitize_text_field($data['file_format'] ?? ''),
            'status' => sanitize_text_field($data['status'] ?? 'draft'),
        ];
        
        // Handle requirements
        if (!empty($data['requirements'])) {
            $requirements = [];
            foreach ((array)$data['requirements'] as $req) {
                if (!empty($req['name']) && !empty($req['value'])) {
                    $requirements[] = [
                        'name' => sanitize_text_field($req['name']),
                        'value' => sanitize_text_field($req['value'])
                    ];
                }
            }
            $sanitized['requirements'] = serialize($requirements);
        }
        
        // Handle tags
        if (!empty($data['tags'])) {
            $tags = array_map('trim', explode(',', $data['tags']));
            $tags = array_map('sanitize_text_field', $tags);
            $sanitized['tags'] = serialize($tags);
        }
        
        // Handle gallery images
        if (!empty($data['gallery_image_ids'])) {
            $gallery_ids = array_map('intval', (array)$data['gallery_image_ids']);
            $sanitized['gallery_image_ids'] = serialize($gallery_ids);
        }
        
        return $sanitized;
    }
    
    /**
     * Get data formats for database
     */
    private function get_data_formats($data) {
        $formats = [];
        
        foreach ($data as $key => $value) {
            if ($key === 'price' || $key === 'sale_price') {
                $formats[] = '%f';
            } elseif ($key === 'download_limit' || $key === 'category_id' || $key === 'subcategory_id' || $key === 'featured_image_id') {
                $formats[] = '%d';
            } else {
                $formats[] = '%s';
            }
        }
        
        return $formats;
    }
    
    /**
     * Generate product slug
     */
    private function generate_product_slug($name) {
        $slug = sanitize_title($name);
        $original_slug = $slug;
        $counter = 1;
        
        global $wpdb;
        
        while ($wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}digiplanet_products WHERE slug = %s", $slug))) {
            $slug = $original_slug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Handle file upload
     */
    private function handle_file_upload($product_id) {
        if (!function_exists('wp_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
        }
        
        $uploadedfile = $_FILES['download_file'];
        
        $upload_overrides = [
            'test_form' => false,
            'unique_filename_callback' => [$this, 'generate_unique_filename']
        ];
        
        $movefile = wp_handle_upload($uploadedfile, $upload_overrides);
        
        if ($movefile && !isset($movefile['error'])) {
            global $wpdb;
            
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_products',
                ['download_link' => $movefile['url']],
                ['id' => $product_id],
                ['%s'],
                ['%d']
            );
        }
    }
    
    /**
     * Generate unique filename
     */
    public function generate_unique_filename($dir, $name, $ext) {
        $name = sanitize_file_name($name);
        $counter = 1;
        
        while (file_exists($dir . "/$name")) {
            $name = pathinfo($name, PATHINFO_FILENAME) . "-$counter" . $ext;
            $counter++;
        }
        
        return $name;
    }
}