<?php
/**
 * Elementor Product Grid Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Grid_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'digiplanet_product_grid';
    }
    
    public function get_title() {
        return __('Product Grid', 'digiplanet-digital-products');
    }
    
    public function get_icon() {
        return 'eicon-products';
    }
    
    public function get_categories() {
        return ['digiplanet'];
    }
    
    protected function register_controls() {
        // Content Tab
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'category',
            [
                'label' => __('Category', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_categories_options(),
                'multiple' => true,
                'label_block' => true,
            ]
        );
        
        $this->add_control(
            'tag',
            [
                'label' => __('Tag', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Enter tags separated by comma', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Products Per Page', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 12,
                'min' => 1,
                'max' => 100,
            ]
        );
        
        $this->add_control(
            'columns',
            [
                'label' => __('Columns', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 4,
                'options' => [
                    1 => __('1 Column', 'digiplanet-digital-products'),
                    2 => __('2 Columns', 'digiplanet-digital-products'),
                    3 => __('3 Columns', 'digiplanet-digital-products'),
                    4 => __('4 Columns', 'digiplanet-digital-products'),
                    5 => __('5 Columns', 'digiplanet-digital-products'),
                    6 => __('6 Columns', 'digiplanet-digital-products'),
                ],
            ]
        );
        
        $this->add_control(
            'orderby',
            [
                'label' => __('Order By', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date' => __('Date', 'digiplanet-digital-products'),
                    'title' => __('Title', 'digiplanet-digital-products'),
                    'price' => __('Price', 'digiplanet-digital-products'),
                    'sales' => __('Sales', 'digiplanet-digital-products'),
                    'rating' => __('Rating', 'digiplanet-digital-products'),
                    'random' => __('Random', 'digiplanet-digital-products'),
                ],
            ]
        );
        
        $this->add_control(
            'order',
            [
                'label' => __('Order', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'ASC' => __('Ascending', 'digiplanet-digital-products'),
                    'DESC' => __('Descending', 'digiplanet-digital-products'),
                ],
            ]
        );
        
        $this->add_control(
            'show_featured',
            [
                'label' => __('Show Featured Products Only', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'show_onsale',
            [
                'label' => __('Show On Sale Products Only', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Tab
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Add style controls...
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $args = [
            'category' => $settings['category'] ? implode(',', $settings['category']) : '',
            'tag' => $settings['tag'],
            'limit' => $settings['limit'],
            'columns' => $settings['columns'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
            'featured' => $settings['show_featured'] === 'yes',
            'onsale' => $settings['show_onsale'] === 'yes',
        ];
        
        echo do_shortcode('[digiplanet_products ' . $this->build_shortcode_attrs($args) . ']');
    }
    
    private function get_categories_options() {
        $categories = Digiplanet_Product_Manager::get_instance()->get_categories();
        $options = [];
        
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }
        
        return $options;
    }
    
    private function build_shortcode_attrs($args) {
        $attrs = [];
        
        foreach ($args as $key => $value) {
            if (!empty($value)) {
                $attrs[] = $key . '="' . esc_attr($value) . '"';
            }
        }
        
        return implode(' ', $attrs);
    }
}