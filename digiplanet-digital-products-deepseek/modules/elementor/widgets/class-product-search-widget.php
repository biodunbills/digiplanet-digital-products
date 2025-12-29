<?php
/**
 * Elementor Product Search Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Search_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'digiplanet_product_search';
    }
    
    public function get_title() {
        return __('Product Search', 'digiplanet-digital-products');
    }
    
    public function get_icon() {
        return 'eicon-search';
    }
    
    public function get_categories() {
        return ['digiplanet'];
    }
    
    public function get_keywords() {
        return ['search', 'product', 'digiplanet'];
    }
    
    protected function register_controls() {
        // Content Section
        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );
        
        $this->add_control(
            'placeholder',
            [
                'label' => __('Placeholder', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Search products...', 'digiplanet-digital-products'),
                'placeholder' => __('Enter placeholder text', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'button_text',
            [
                'label' => __('Button Text', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Search', 'digiplanet-digital-products'),
                'placeholder' => __('Enter button text', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'show_categories',
            [
                'label' => __('Show Categories Dropdown', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'ajax_search',
            [
                'label' => __('AJAX Live Search', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'min_chars',
            [
                'label' => __('Minimum Characters', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3,
                'min' => 1,
                'max' => 10,
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'results_limit',
            [
                'label' => __('Results Limit', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 20,
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            [
                'label' => __('Style', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        // Search Form Style
        $this->add_control(
            'form_style_heading',
            [
                'label' => __('Search Form', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_responsive_control(
            'form_width',
            [
                'label' => __('Form Width', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 200,
                        'max' => 1200,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-product-search-form' => 'width: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'form_height',
            [
                'label' => __('Form Height', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 40,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'height: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .digiplanet-search-button' => 'height: {{SIZE}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'form_bg_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'form_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'form_border_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'form_border_width',
            [
                'label' => __('Border Width', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_control(
            'form_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-input' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    '{{WRAPPER}} .digiplanet-search-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Button Style
        $this->add_control(
            'button_style_heading',
            [
                'label' => __('Button', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->start_controls_tabs('button_tabs');
        
        $this->start_controls_tab(
            'button_normal',
            [
                'label' => __('Normal', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'button_bg_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_border_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'button_hover',
            [
                'label' => __('Hover', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'button_bg_hover_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_text_hover_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'button_border_hover_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-button:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'button_typography',
                'selector' => '{{WRAPPER}} .digiplanet-search-button',
            ]
        );
        
        // Categories Dropdown Style
        $this->add_control(
            'categories_style_heading',
            [
                'label' => __('Categories Dropdown', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_categories' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'categories_width',
            [
                'label' => __('Width', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 100,
                        'max' => 300,
                    ],
                    '%' => [
                        'min' => 10,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-category' => 'width: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_categories' => 'yes',
                ],
            ]
        );
        
        // Results Style
        $this->add_control(
            'results_style_heading',
            [
                'label' => __('Search Results', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'results_bg_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-results' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'results_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-search-results' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'results_border',
                'selector' => '{{WRAPPER}} .digiplanet-search-results',
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'results_box_shadow',
                'selector' => '{{WRAPPER}} .digiplanet-search-results',
                'condition' => [
                    'ajax_search' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Get categories for dropdown
        $categories = [];
        if ($settings['show_categories'] === 'yes') {
            $product_manager = Digiplanet_Product_Manager::get_instance();
            $categories = $product_manager->get_categories();
        }
        
        ?>
        <div class="digiplanet-product-search-widget" 
             data-ajax="<?php echo $settings['ajax_search'] === 'yes' ? 'true' : 'false'; ?>"
             data-min-chars="<?php echo esc_attr($settings['min_chars']); ?>"
             data-limit="<?php echo esc_attr($settings['results_limit']); ?>">
            
            <form class="digiplanet-product-search-form" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                <input type="hidden" name="post_type" value="digiplanet_product">
                
                <div class="digiplanet-search-wrapper">
                    <?php if ($settings['show_categories'] === 'yes' && !empty($categories)): ?>
                        <select name="category" class="digiplanet-search-category">
                            <option value=""><?php _e('All Categories', 'digiplanet-digital-products'); ?></option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo esc_attr($category->slug); ?>">
                                    <?php echo esc_html($category->name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                    
                    <div class="digiplanet-search-input-wrapper">
                        <input type="text" 
                               name="s" 
                               class="digiplanet-search-input" 
                               placeholder="<?php echo esc_attr($settings['placeholder']); ?>"
                               autocomplete="off">
                        
                        <?php if ($settings['ajax_search'] === 'yes'): ?>
                            <div class="digiplanet-search-loader" style="display: none;">
                                <span class="dashicons dashicons-update"></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="digiplanet-search-button">
                        <?php echo esc_html($settings['button_text']); ?>
                    </button>
                </div>
            </form>
            
            <?php if ($settings['ajax_search'] === 'yes'): ?>
                <div class="digiplanet-search-results"></div>
            <?php endif; ?>
        </div>
        
        <?php if ($settings['ajax_search'] === 'yes'): ?>
        <script>
        jQuery(document).ready(function($) {
            var $widget = $('.digiplanet-product-search-widget[data-ajax="true"]');
            var $input = $widget.find('.digiplanet-search-input');
            var $results = $widget.find('.digiplanet-search-results');
            var $loader = $widget.find('.digiplanet-search-loader');
            var minChars = parseInt($widget.data('min-chars'));
            var limit = parseInt($widget.data('limit'));
            var searchTimeout;
            
            // Live search on input
            $input.on('keyup', function() {
                var searchTerm = $(this).val().trim();
                
                // Clear previous timeout
                clearTimeout(searchTimeout);
                
                // Hide results if search term is too short
                if (searchTerm.length < minChars) {
                    $results.hide().empty();
                    return;
                }
                
                // Show loader
                $loader.show();
                
                // Set new timeout
                searchTimeout = setTimeout(function() {
                    performSearch(searchTerm);
                }, 300);
            });
            
            // Hide results when clicking outside
            $(document).on('click', function(e) {
                if (!$widget.is(e.target) && $widget.has(e.target).length === 0) {
                    $results.hide();
                }
            });
            
            // Keep results visible when clicking inside
            $results.on('click', function(e) {
                e.stopPropagation();
            });
            
            function performSearch(searchTerm) {
                var category = $widget.find('.digiplanet-search-category').val();
                
                $.ajax({
                    url: digiplanet_frontend.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'digiplanet_product_search',
                        search_term: searchTerm,
                        category: category,
                        limit: limit,
                        nonce: digiplanet_frontend.nonce
                    },
                    success: function(response) {
                        $loader.hide();
                        
                        if (response.success && response.data.html) {
                            $results.html(response.data.html).show();
                        } else {
                            $results.hide();
                        }
                    },
                    error: function() {
                        $loader.hide();
                        $results.hide();
                    }
                });
            }
            
            // Handle form submission
            $widget.find('form').on('submit', function(e) {
                var searchTerm = $input.val().trim();
                
                if (searchTerm.length < minChars) {
                    e.preventDefault();
                    $input.focus();
                }
            });
        });
        </script>
        <?php endif; ?>
        
        <style>
        .digiplanet-product-search-widget {
            position: relative;
        }
        
        .digiplanet-search-wrapper {
            display: flex;
            width: 100%;
        }
        
        .digiplanet-search-category {
            border: 1px solid #dee2e6;
            border-right: none;
            border-radius: 4px 0 0 4px;
            padding: 0 15px;
            background: white;
            color: #495057;
            font-size: 14px;
            min-width: 150px;
            max-width: 200px;
        }
        
        .digiplanet-search-input-wrapper {
            position: relative;
            flex: 1;
        }
        
        .digiplanet-search-input {
            width: 100%;
            border: 1px solid #dee2e6;
            padding: 0 15px;
            font-size: 14px;
            background: white;
            color: #495057;
        }
        
        .digiplanet-search-loader {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
        }
        
        .digiplanet-search-loader .dashicons {
            animation: spin 1s linear infinite;
        }
        
        .digiplanet-search-button {
            border: 1px solid #dee2e6;
            border-left: none;
            border-radius: 0 4px 4px 0;
            padding: 0 20px;
            background: #3498db;
            color: white;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            white-space: nowrap;
        }
        
        .digiplanet-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 4px 4px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            z-index: 1000;
            display: none;
            max-height: 400px;
            overflow-y: auto;
        }
        
        .digiplanet-search-result-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #f8f9fa;
            transition: background-color 0.3s;
        }
        
        .digiplanet-search-result-item:hover {
            background-color: #f8f9fa;
        }
        
        .digiplanet-search-result-item:last-child {
            border-bottom: none;
        }
        
        .digiplanet-search-result-image {
            width: 50px;
            height: 50px;
            margin-right: 15px;
            flex-shrink: 0;
        }
        
        .digiplanet-search-result-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .digiplanet-search-result-content {
            flex: 1;
        }
        
        .digiplanet-search-result-title {
            margin: 0 0 5px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .digiplanet-search-result-title a {
            color: #333;
            text-decoration: none;
        }
        
        .digiplanet-search-result-price {
            font-size: 13px;
            color: #e74c3c;
            font-weight: 600;
        }
        
        .digiplanet-search-no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        
        @keyframes spin {
            0% { transform: translateY(-50%) rotate(0deg); }
            100% { transform: translateY(-50%) rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .digiplanet-search-wrapper {
                flex-direction: column;
            }
            
            .digiplanet-search-category {
                width: 100%;
                max-width: 100%;
                border-right: 1px solid #dee2e6;
                border-bottom: none;
                border-radius: 4px 4px 0 0;
                margin-bottom: -1px;
            }
            
            .digiplanet-search-input {
                border-radius: 0;
            }
            
            .digiplanet-search-button {
                border-left: 1px solid #dee2e6;
                border-radius: 0 0 4px 4px;
            }
        }
        </style>
        <?php
    }
}