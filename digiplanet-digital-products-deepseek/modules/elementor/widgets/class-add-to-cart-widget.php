<?php
/**
 * Elementor Add to Cart Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Add_To_Cart_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'digiplanet_add_to_cart';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Add to Cart Button', 'digiplanet-digital-products');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-cart';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['digiplanet-elements'];
    }
    
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['cart', 'add to cart', 'buy', 'purchase', 'digital', 'product'];
    }
    
    /**
     * Register widget controls
     */
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        // Product selection
        $this->add_control(
            'product_id',
            [
                'label' => __('Product', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_products_list(),
                'label_block' => true,
                'default' => '',
            ]
        );
        
        // Button text
        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Add to Cart', 'digiplanet-digital-products'),
                'placeholder' => __('Add to Cart', 'digiplanet-digital-products'),
            ]
        );
        
        // Show quantity
        $this->add_control(
            'show_quantity',
            [
                'label' => __('Show Quantity Selector', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'digiplanet-digital-products'),
                'label_off' => __('Hide', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        // Show price
        $this->add_control(
            'show_price',
            [
                'label' => __('Show Price', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'digiplanet-digital-products'),
                'label_off' => __('Hide', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        // Show sale badge
        $this->add_control(
            'show_sale_badge',
            [
                'label' => __('Show Sale Badge', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'digiplanet-digital-products'),
                'label_off' => __('Hide', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section: Button
        $this->start_controls_section(
            'button_style_section',
            [
                'label' => __('Button', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Button typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .digiplanet-add-to-cart-btn',
            ]
        );
        
        // Button colors
        $this->start_controls_tabs('button_color_tabs');
        
        // Normal state
        $this->start_controls_tab(
            'button_normal_tab',
            [
                'label' => __('Normal', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_background_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2271b1',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'button_border',
                'selector' => '{{WRAPPER}} .digiplanet-add-to-cart-btn',
                'fields_options' => [
                    'border' => [
                        'default' => 'solid',
                    ],
                    'width' => [
                        'default' => [
                            'top' => '1',
                            'right' => '1',
                            'bottom' => '1',
                            'left' => '1',
                            'isLinked' => true,
                        ],
                    ],
                    'color' => [
                        'default' => '#2271b1',
                    ],
                ],
            ]
        );
        
        $this->add_control(
            'button_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => '4',
                    'right' => '4',
                    'bottom' => '4',
                    'left' => '4',
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_box_shadow',
                'selector' => '{{WRAPPER}} .digiplanet-add-to-cart-btn',
            ]
        );
        
        $this->end_controls_tab();
        
        // Hover state
        $this->start_controls_tab(
            'button_hover_tab',
            [
                'label' => __('Hover', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'button_hover_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_background_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#135e96',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_hover_border_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#135e96',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'button_hover_box_shadow',
                'selector' => '{{WRAPPER}} .digiplanet-add-to-cart-btn:hover',
            ]
        );
        
        $this->add_control(
            'button_hover_animation',
            [
                'label' => __('Hover Animation', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        // Button padding
        $this->add_responsive_control(
            'button_padding',
            [
                'label' => __('Padding', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'default' => [
                    'top' => '12',
                    'right' => '24',
                    'bottom' => '12',
                    'left' => '24',
                    'unit' => 'px',
                    'isLinked' => false,
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Button alignment
        $this->add_responsive_control(
            'button_alignment',
            [
                'label' => __('Alignment', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => __('Left', 'digiplanet-digital-products'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => __('Center', 'digiplanet-digital-products'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => __('Right', 'digiplanet-digital-products'),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-add-to-cart-widget' => 'text-align: {{VALUE}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section: Price
        $this->start_controls_section(
            'price_style_section',
            [
                'label' => __('Price', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_price' => 'yes',
                ],
            ]
        );
        
        // Price typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'price_typography',
                'selector' => '{{WRAPPER}} .digiplanet-product-price',
            ]
        );
        
        // Price color
        $this->add_control(
            'price_color',
            [
                'label' => __('Price Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1d2327',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-product-price' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        // Sale price color
        $this->add_control(
            'sale_price_color',
            [
                'label' => __('Sale Price Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#46b450',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-price' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        // Regular price color
        $this->add_control(
            'regular_price_color',
            [
                'label' => __('Regular Price Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#646970',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-regular-price' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        // Price margin
        $this->add_responsive_control(
            'price_margin',
            [
                'label' => __('Margin', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-product-price' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section: Quantity
        $this->start_controls_section(
            'quantity_style_section',
            [
                'label' => __('Quantity', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_quantity' => 'yes',
                ],
            ]
        );
        
        // Quantity input typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'quantity_typography',
                'selector' => '{{WRAPPER}} .digiplanet-quantity-input',
            ]
        );
        
        // Quantity input colors
        $this->start_controls_tabs('quantity_color_tabs');
        
        // Normal state
        $this->start_controls_tab(
            'quantity_normal_tab',
            [
                'label' => __('Normal', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'quantity_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1d2327',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'quantity_background_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'quantity_border_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#8c8f94',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        // Focus state
        $this->start_controls_tab(
            'quantity_focus_tab',
            [
                'label' => __('Focus', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'quantity_focus_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1d2327',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input:focus' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'quantity_focus_background_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input:focus' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'quantity_focus_border_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#2271b1',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input:focus' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        // Quantity input border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'quantity_border',
                'selector' => '{{WRAPPER}} .digiplanet-quantity-input',
                'separator' => 'before',
            ]
        );
        
        // Quantity input border radius
        $this->add_control(
            'quantity_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Quantity input padding
        $this->add_responsive_control(
            'quantity_padding',
            [
                'label' => __('Padding', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Quantity input width
        $this->add_responsive_control(
            'quantity_width',
            [
                'label' => __('Width', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 50,
                        'max' => 200,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 80,
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-quantity-input' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section: Sale Badge
        $this->start_controls_section(
            'sale_badge_style_section',
            [
                'label' => __('Sale Badge', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => [
                    'show_sale_badge' => 'yes',
                ],
            ]
        );
        
        // Badge typography
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .digiplanet-sale-badge',
            ]
        );
        
        // Badge colors
        $this->add_control(
            'badge_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'badge_background_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#46b450',
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        // Badge border
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'badge_border',
                'selector' => '{{WRAPPER}} .digiplanet-sale-badge',
            ]
        );
        
        // Badge border radius
        $this->add_control(
            'badge_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Badge padding
        $this->add_responsive_control(
            'badge_padding',
            [
                'label' => __('Padding', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Badge position
        $this->add_responsive_control(
            'badge_position_top',
            [
                'label' => __('Top Position', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -50,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'badge_position_right',
            [
                'label' => __('Right Position', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => -50,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => -50,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-sale-badge' => 'right: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Get products list for select control
     */
    private function get_products_list() {
        $products = Digiplanet_Product_Manager::get_instance()->get_products([
            'status' => 'published'
        ]);
        
        $options = [
            '' => __('Select a Product', 'digiplanet-digital-products')
        ];
        
        foreach ($products as $product) {
            $options[$product->id] = $product->name . ' ($' . number_format($product->price, 2) . ')';
        }
        
        return $options;
    }
    
    /**
     * Render widget output
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        if (empty($settings['product_id'])) {
            echo '<div class="digiplanet-alert digiplanet-alert-warning">';
            echo __('Please select a product in the widget settings.', 'digiplanet-digital-products');
            echo '</div>';
            return;
        }
        
        $product = Digiplanet_Product_Manager::get_instance()->get_product($settings['product_id']);
        
        if (!$product) {
            echo '<div class="digiplanet-alert digiplanet-alert-warning">';
            echo __('Selected product not found.', 'digiplanet-digital-products');
            echo '</div>';
            return;
        }
        
        $is_on_sale = !empty($product->sale_price) && $product->sale_price < $product->price;
        $display_price = $is_on_sale ? $product->sale_price : $product->price;
        $regular_price = $product->price;
        
        ?>
        <div class="digiplanet-add-to-cart-widget">
            <div class="digiplanet-product-card">
                <?php if ($settings['show_sale_badge'] === 'yes' && $is_on_sale): ?>
                    <span class="digiplanet-sale-badge">
                        <?php _e('Sale', 'digiplanet-digital-products'); ?>
                    </span>
                <?php endif; ?>
                
                <div class="digiplanet-product-info">
                    <?php if ($settings['show_price'] === 'yes'): ?>
                        <div class="digiplanet-product-price">
                            <span class="digiplanet-price <?php echo $is_on_sale ? 'digiplanet-sale-price' : ''; ?>">
                                <?php echo Digiplanet_Product_Manager::get_instance()->format_price($display_price); ?>
                            </span>
                            
                            <?php if ($is_on_sale): ?>
                                <del class="digiplanet-regular-price">
                                    <?php echo Digiplanet_Product_Manager::get_instance()->format_price($regular_price); ?>
                                </del>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="digiplanet-add-to-cart-form">
                        <?php if ($settings['show_quantity'] === 'yes'): ?>
                            <div class="digiplanet-quantity-selector">
                                <label for="digiplanet-quantity-<?php echo esc_attr($this->get_id()); ?>">
                                    <?php _e('Quantity:', 'digiplanet-digital-products'); ?>
                                </label>
                                <input type="number" 
                                       id="digiplanet-quantity-<?php echo esc_attr($this->get_id()); ?>" 
                                       class="digiplanet-quantity-input" 
                                       name="quantity" 
                                       value="1" 
                                       min="1" 
                                       max="99">
                            </div>
                        <?php endif; ?>
                        
                        <button type="button" 
                                class="digiplanet-add-to-cart-btn <?php echo $settings['button_hover_animation'] ? 'elementor-animation-' . $settings['button_hover_animation'] : ''; ?>" 
                                data-product-id="<?php echo esc_attr($product->id); ?>"
                                data-widget-id="<?php echo esc_attr($this->get_id()); ?>">
                            <span class="digiplanet-btn-icon">
                                <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 14C5.55228 14 6 13.5523 6 13C6 12.4477 5.55228 12 5 12C4.44772 12 4 12.4477 4 13C4 13.5523 4.44772 14 5 14Z" fill="currentColor"/>
                                    <path d="M12 14C12.5523 14 13 13.5523 13 13C13 12.4477 12.5523 12 12 12C11.4477 12 11 12.4477 11 13C11 13.5523 11.4477 14 12 14Z" fill="currentColor"/>
                                    <path d="M0.5 1H3.5L4.5 3M4.5 3H15L12 9H5M4.5 3L5 5M12 9L10.5 11.5H5M12 9C12 9.53043 11.7893 10.0391 11.4142 10.4142C11.0391 10.7893 10.5304 11 10 11C9.46957 11 8.96086 10.7893 8.58579 10.4142C8.21071 10.0391 8 9.53043 8 9M5 11C5 11.5304 4.78929 12.0391 4.41421 12.4142C4.03914 12.7893 3.53043 13 3 13C2.46957 13 1.96086 12.7893 1.58579 12.4142C1.21071 12.0391 1 11.5304 1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </span>
                            <?php echo esc_html($settings['button_text']); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Add to cart functionality
            $('.digiplanet-add-to-cart-btn[data-widget-id="<?php echo esc_js($this->get_id()); ?>"]').on('click', function() {
                var $button = $(this);
                var productId = $button.data('product-id');
                var quantity = $button.closest('.digiplanet-add-to-cart-form').find('.digiplanet-quantity-input').val() || 1;
                
                // Disable button and show loading
                var originalText = $button.html();
                $button.html('<span class="digiplanet-spinner"></span> <?php esc_js_e('Adding...', 'digiplanet-digital-products'); ?>');
                $button.prop('disabled', true);
                
                // AJAX request to add to cart
                $.ajax({
                    url: digiplanet_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'digiplanet_add_to_cart',
                        nonce: digiplanet_ajax.nonce,
                        product_id: productId,
                        quantity: quantity
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show success message
                            $button.html('<span class="dashicons dashicons-yes"></span> <?php esc_js_e('Added!', 'digiplanet-digital-products'); ?>');
                            
                            // Update cart count in header if exists
                            if (response.data.cart_count && $('.digiplanet-cart-count').length) {
                                $('.digiplanet-cart-count').text(response.data.cart_count);
                            }
                            
                            // Show cart notification
                            showCartNotification(response.data.message, 'success');
                            
                            // Reset button after delay
                            setTimeout(function() {
                                $button.html(originalText);
                                $button.prop('disabled', false);
                            }, 2000);
                        } else {
                            // Show error message
                            showCartNotification(response.data.message, 'error');
                            
                            // Reset button
                            $button.html(originalText);
                            $button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        // Show error message
                        showCartNotification('<?php esc_js_e('An error occurred. Please try again.', 'digiplanet-digital-products'); ?>', 'error');
                        
                        // Reset button
                        $button.html(originalText);
                        $button.prop('disabled', false);
                    }
                });
            });
            
            // Show cart notification
            function showCartNotification(message, type) {
                // Remove existing notification
                $('.digiplanet-cart-notification').remove();
                
                // Create notification
                var $notification = $('<div class="digiplanet-cart-notification digiplanet-notification-' + type + '">' + 
                    '<span class="digiplanet-notification-icon"></span>' +
                    '<span class="digiplanet-notification-text">' + message + '</span>' +
                    '</div>');
                
                // Add to body
                $('body').append($notification);
                
                // Show with animation
                setTimeout(function() {
                    $notification.addClass('show');
                }, 10);
                
                // Remove after delay
                setTimeout(function() {
                    $notification.removeClass('show');
                    setTimeout(function() {
                        $notification.remove();
                    }, 300);
                }, 3000);
            }
        });
        </script>
        
        <style>
        .digiplanet-add-to-cart-widget {
            display: inline-block;
            text-align: <?php echo esc_attr($settings['button_alignment']); ?>;
        }
        
        .digiplanet-product-card {
            position: relative;
            display: inline-block;
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .digiplanet-sale-badge {
            position: absolute;
            top: <?php echo esc_attr($settings['badge_position_top']['size'] . $settings['badge_position_top']['unit']); ?>;
            right: <?php echo esc_attr($settings['badge_position_right']['size'] . $settings['badge_position_right']['unit']); ?>;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 10;
        }
        
        .digiplanet-product-info {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .digiplanet-product-price {
            font-weight: 600;
        }
        
        .digiplanet-sale-price {
            color: <?php echo esc_attr($settings['sale_price_color']); ?>;
        }
        
        .digiplanet-regular-price {
            margin-left: 8px;
            text-decoration: line-through;
            color: <?php echo esc_attr($settings['regular_price_color']); ?>;
        }
        
        .digiplanet-add-to-cart-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .digiplanet-quantity-selector {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .digiplanet-quantity-selector label {
            font-weight: 500;
            color: #646970;
        }
        
        .digiplanet-quantity-input {
            text-align: center;
            border: 1px solid;
        }
        
        .digiplanet-add-to-cart-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .digiplanet-btn-icon {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .digiplanet-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: digiplanet-spin 1s ease-in-out infinite;
        }
        
        @keyframes digiplanet-spin {
            to { transform: rotate(360deg); }
        }
        
        /* Cart Notification */
        .digiplanet-cart-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(120%);
            transition: transform 0.3s ease;
            z-index: 9999;
        }
        
        .digiplanet-cart-notification.show {
            transform: translateX(0);
        }
        
        .digiplanet-notification-success {
            border-left: 4px solid #46b450;
        }
        
        .digiplanet-notification-error {
            border-left: 4px solid #dc3232;
        }
        
        .digiplanet-notification-icon {
            font-size: 20px;
        }
        
        .digiplanet-notification-success .digiplanet-notification-icon::before {
            content: '✓';
            color: #46b450;
        }
        
        .digiplanet-notification-error .digiplanet-notification-icon::before {
            content: '✗';
            color: #dc3232;
        }
        
        .digiplanet-notification-text {
            font-size: 14px;
            color: #1d2327;
        }
        </style>
        <?php
    }
    
    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <#
        if (settings.product_id) {
            var product = {
                id: settings.product_id,
                name: 'Sample Product',
                price: 49.99,
                sale_price: 39.99
            };
            
            var is_on_sale = product.sale_price && product.sale_price < product.price;
            var display_price = is_on_sale ? product.sale_price : product.price;
            var regular_price = product.price;
            #>
            
            <div class="digiplanet-add-to-cart-widget">
                <div class="digiplanet-product-card">
                    <# if (settings.show_sale_badge === 'yes' && is_on_sale) { #>
                        <span class="digiplanet-sale-badge">
                            <?php _e('Sale', 'digiplanet-digital-products'); ?>
                        </span>
                    <# } #>
                    
                    <div class="digiplanet-product-info">
                        <# if (settings.show_price === 'yes') { #>
                            <div class="digiplanet-product-price">
                                <span class="digiplanet-price <# if (is_on_sale) { #>digiplanet-sale-price<# } #>">
                                    ${{ display_price.toFixed(2) }}
                                </span>
                                
                                <# if (is_on_sale) { #>
                                    <del class="digiplanet-regular-price">
                                        ${{ regular_price.toFixed(2) }}
                                    </del>
                                <# } #>
                            </div>
                        <# } #>
                        
                        <div class="digiplanet-add-to-cart-form">
                            <# if (settings.show_quantity === 'yes') { #>
                                <div class="digiplanet-quantity-selector">
                                    <label for="digiplanet-quantity-{{ view.getID() }}">
                                        <?php _e('Quantity:', 'digiplanet-digital-products'); ?>
                                    </label>
                                    <input type="number" 
                                           id="digiplanet-quantity-{{ view.getID() }}" 
                                           class="digiplanet-quantity-input" 
                                           name="quantity" 
                                           value="1" 
                                           min="1" 
                                           max="99">
                                </div>
                            <# } #>
                            
                            <button type="button" 
                                    class="digiplanet-add-to-cart-btn elementor-animation-{{ settings.button_hover_animation }}"
                                    data-product-id="{{ product.id }}">
                                <span class="digiplanet-btn-icon">
                                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M5 14C5.55228 14 6 13.5523 6 13C6 12.4477 5.55228 12 5 12C4.44772 12 4 12.4477 4 13C4 13.5523 4.44772 14 5 14Z" fill="currentColor"/>
                                        <path d="M12 14C12.5523 14 13 13.5523 13 13C13 12.4477 12.5523 12 12 12C11.4477 12 11 12.4477 11 13C11 13.5523 11.4477 14 12 14Z" fill="currentColor"/>
                                        <path d="M0.5 1H3.5L4.5 3M4.5 3H15L12 9H5M4.5 3L5 5M12 9L10.5 11.5H5M12 9C12 9.53043 11.7893 10.0391 11.4142 10.4142C11.0391 10.7893 10.5304 11 10 11C9.46957 11 8.96086 10.7893 8.58579 10.4142C8.21071 10.0391 8 9.53043 8 9M5 11C5 11.5304 4.78929 12.0391 4.41421 12.4142C4.03914 12.7893 3.53043 13 3 13C2.46957 13 1.96086 12.7893 1.58579 12.4142C1.21071 12.0391 1 11.5304 1 11" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                                {{{ settings.button_text }}}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        <# } else { #>
            <div class="digiplanet-alert digiplanet-alert-warning">
                <?php _e('Please select a product in the widget settings.', 'digiplanet-digital-products'); ?>
            </div>
        <# } #>
        <?php
    }
}