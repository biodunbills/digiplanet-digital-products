<?php
/**
 * Elementor integration class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Elementor_Integration {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('elementor/widgets/register', [__CLASS__, 'register_widgets']);
        add_action('elementor/elements/categories_registered', [__CLASS__, 'add_widget_category']);
        add_action('elementor/frontend/after_enqueue_styles', [__CLASS__, 'enqueue_styles']);
        add_action('elementor/frontend/after_enqueue_scripts', [__CLASS__, 'enqueue_scripts']);
    }
    
    /**
     * Register widgets
     */
    public static function register_widgets($widgets_manager) {
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/widgets/class-product-grid-widget.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/widgets/class-product-carousel-widget.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/widgets/class-product-categories-widget.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/widgets/class-product-search-widget.php';
        require_once DIGIPLANET_PLUGIN_DIR . 'modules/elementor/widgets/class-add-to-cart-widget.php';
        
        $widgets_manager->register(new \Digiplanet_Product_Grid_Widget());
        $widgets_manager->register(new \Digiplanet_Product_Carousel_Widget());
        $widgets_manager->register(new \Digiplanet_Product_Categories_Widget());
        $widgets_manager->register(new \Digiplanet_Product_Search_Widget());
        $widgets_manager->register(new \Digiplanet_Add_To_Cart_Widget());
    }
    
    /**
     * Add widget category
     */
    public static function add_widget_category($elements_manager) {
        $elements_manager->add_category(
            'digiplanet',
            [
                'title' => __('Digiplanet Digital Products', 'digiplanet-digital-products'),
                'icon' => 'fa fa-plug',
            ]
        );
    }
    
    /**
     * Enqueue styles
     */
    public static function enqueue_styles() {
        wp_enqueue_style(
            'digiplanet-elementor',
            DIGIPLANET_PLUGIN_URL . 'modules/elementor/assets/css/elementor-widgets.css',
            [],
            DIGIPLANET_VERSION
        );
    }
    
    /**
     * Enqueue scripts
     */
    public static function enqueue_scripts() {
        wp_enqueue_script(
            'digiplanet-elementor',
            DIGIPLANET_PLUGIN_URL . 'modules/elementor/assets/js/elementor-widgets.js',
            ['jquery', 'elementor-frontend'],
            DIGIPLANET_VERSION,
            true
        );
        
        wp_localize_script('digiplanet-elementor', 'digiplanet_elementor', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('digiplanet_nonce')
        ]);
    }
}