<?php
/**
 * Elementor Product Categories Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Categories_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'digiplanet_product_categories';
    }
    
    public function get_title() {
        return __('Product Categories', 'digiplanet-digital-products');
    }
    
    public function get_icon() {
        return 'eicon-product-categories';
    }
    
    public function get_categories() {
        return ['digiplanet'];
    }
    
    public function get_keywords() {
        return ['categories', 'product', 'digiplanet'];
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
            'title',
            [
                'label' => __('Title', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => __('Product Categories', 'digiplanet-digital-products'),
                'placeholder' => __('Enter title', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'parent',
            [
                'label' => __('Parent Category', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT2,
                'options' => $this->get_categories_options(),
                'label_block' => true,
                'default' => 0,
            ]
        );
        
        $this->add_control(
            'show_empty',
            [
                'label' => __('Show Empty Categories', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->add_control(
            'show_count',
            [
                'label' => __('Show Product Count', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_image',
            [
                'label' => __('Show Category Image', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
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
            'layout',
            [
                'label' => __('Layout', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => 'grid',
                'options' => [
                    'grid' => __('Grid', 'digiplanet-digital-products'),
                    'list' => __('List', 'digiplanet-digital-products'),
                    'carousel' => __('Carousel', 'digiplanet-digital-products'),
                ],
            ]
        );
        
        $this->add_control(
            'limit',
            [
                'label' => __('Limit', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 0,
                'min' => 0,
                'description' => __('0 for unlimited', 'digiplanet-digital-products'),
            ]
        );
        
        $this->end_controls_section();
        
        // Carousel Settings (if layout is carousel)
        $this->start_controls_section(
            'carousel_section',
            [
                'label' => __('Carousel Settings', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                'condition' => [
                    'layout' => 'carousel',
                ],
            ]
        );
        
        $this->add_control(
            'slides_to_show',
            [
                'label' => __('Slides to Show', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 4,
                'min' => 1,
                'max' => 6,
            ]
        );
        
        $this->add_control(
            'autoplay',
            [
                'label' => __('Autoplay', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_arrows',
            [
                'label' => __('Show Arrows', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'show_dots',
            [
                'label' => __('Show Dots', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
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
        
        // Title Style
        $this->add_control(
            'title_style_heading',
            [
                'label' => __('Title', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'title_color',
            [
                'label' => __('Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-categories-title' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .digiplanet-categories-title',
            ]
        );
        
        // Category Item Style
        $this->add_control(
            'category_item_heading',
            [
                'label' => __('Category Item', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->start_controls_tabs('category_item_tabs');
        
        $this->start_controls_tab(
            'category_item_normal',
            [
                'label' => __('Normal', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'category_bg_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'category_text_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'category_item_hover',
            [
                'label' => __('Hover', 'digiplanet-digital-products'),
            ]
        );
        
        $this->add_control(
            'category_bg_hover_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item:hover' => 'background-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'category_text_hover_color',
            [
                'label' => __('Text Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item:hover' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_control(
            'category_border_hover_color',
            [
                'label' => __('Border Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item:hover' => 'border-color: {{VALUE}}',
                ],
            ]
        );
        
        $this->end_controls_tab();
        $this->end_controls_tabs();
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'category_border',
                'selector' => '{{WRAPPER}} .digiplanet-category-item',
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'category_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'category_box_shadow',
                'selector' => '{{WRAPPER}} .digiplanet-category-item',
            ]
        );
        
        $this->add_responsive_control(
            'category_padding',
            [
                'label' => __('Padding', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'category_margin',
            [
                'label' => __('Margin', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-item' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        
        // Category Image Style
        $this->add_control(
            'category_image_heading',
            [
                'label' => __('Category Image', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'category_image_size',
            [
                'label' => __('Size', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px', '%'],
                'range' => [
                    'px' => [
                        'min' => 20,
                        'max' => 200,
                    ],
                    '%' => [
                        'min' => 1,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-image' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );
        
        $this->add_responsive_control(
            'category_image_spacing',
            [
                'label' => __('Spacing', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-image' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'category_image_border',
                'selector' => '{{WRAPPER}} .digiplanet-category-image',
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'category_image_border_radius',
            [
                'label' => __('Border Radius', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-image' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'condition' => [
                    'show_image' => 'yes',
                ],
            ]
        );
        
        // Category Count Style
        $this->add_control(
            'category_count_heading',
            [
                'label' => __('Product Count', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'category_count_color',
            [
                'label' => __('Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-category-count' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'category_count_typography',
                'selector' => '{{WRAPPER}} .digiplanet-category-count',
                'condition' => [
                    'show_count' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $categories = $product_manager->get_categories($settings['parent']);
        
        // Apply limit
        if ($settings['limit'] > 0) {
            $categories = array_slice($categories, 0, $settings['limit']);
        }
        
        // Filter empty categories
        if ($settings['show_empty'] !== 'yes') {
            $categories = array_filter($categories, function($category) {
                return $category->product_count > 0;
            });
        }
        
        if (empty($categories)) {
            echo '<p>' . __('No categories found.', 'digiplanet-digital-products') . '</p>';
            return;
        }
        
        $layout_class = 'digiplanet-categories-' . $settings['layout'];
        $columns_class = 'digiplanet-cols-' . $settings['columns'];
        
        ?>
        <div class="digiplanet-categories-widget">
            <?php if ($settings['title']): ?>
                <h2 class="digiplanet-categories-title"><?php echo esc_html($settings['title']); ?></h2>
            <?php endif; ?>
            
            <?php if ($settings['layout'] === 'carousel'): ?>
                <div class="digiplanet-categories-carousel" 
                     data-settings='<?php echo json_encode([
                         'slidesToShow' => $settings['slides_to_show'],
                         'autoplay' => $settings['autoplay'] === 'yes',
                         'arrows' => $settings['show_arrows'] === 'yes',
                         'dots' => $settings['show_dots'] === 'yes',
                     ]); ?>'>
                    <div class="digiplanet-carousel-container swiper-container">
                        <div class="digiplanet-carousel-track swiper-wrapper">
                            <?php foreach ($categories as $category): ?>
                                <div class="swiper-slide">
                                    <?php echo $this->get_category_item_html($category, $settings); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($settings['show_arrows'] === 'yes'): ?>
                            <div class="digiplanet-carousel-arrows">
                                <button class="digiplanet-carousel-arrow digiplanet-carousel-prev">
                                    <span class="dashicons dashicons-arrow-left-alt2"></span>
                                </button>
                                <button class="digiplanet-carousel-arrow digiplanet-carousel-next">
                                    <span class="dashicons dashicons-arrow-right-alt2"></span>
                                </button>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($settings['show_dots'] === 'yes'): ?>
                            <div class="digiplanet-carousel-dots swiper-pagination"></div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <script>
                jQuery(document).ready(function($) {
                    var $carousel = $('.digiplanet-categories-carousel[data-settings]');
                    var settings = $carousel.data('settings');
                    
                    if ($carousel.length && typeof Swiper !== 'undefined') {
                        new Swiper($carousel.find('.swiper-container')[0], {
                            slidesPerView: settings.slidesToShow,
                            spaceBetween: 30,
                            speed: 500,
                            autoplay: settings.autoplay ? {
                                delay: 3000,
                                disableOnInteraction: false,
                            } : false,
                            navigation: settings.arrows ? {
                                nextEl: '.digiplanet-carousel-next',
                                prevEl: '.digiplanet-carousel-prev',
                            } : false,
                            pagination: settings.dots ? {
                                el: '.swiper-pagination',
                                clickable: true,
                            } : false,
                            breakpoints: {
                                320: {
                                    slidesPerView: 1,
                                    spaceBetween: 15,
                                },
                                576: {
                                    slidesPerView: Math.min(2, settings.slidesToShow),
                                    spaceBetween: 20,
                                },
                                768: {
                                    slidesPerView: Math.min(3, settings.slidesToShow),
                                    spaceBetween: 25,
                                },
                                992: {
                                    slidesPerView: Math.min(4, settings.slidesToShow),
                                    spaceBetween: 30,
                                },
                            },
                        });
                    }
                });
                </script>
                
            <?php else: ?>
                <div class="digiplanet-categories-container <?php echo $layout_class; ?> <?php echo $columns_class; ?>">
                    <?php foreach ($categories as $category): ?>
                        <?php echo $this->get_category_item_html($category, $settings); ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    protected function get_category_item_html($category, $settings) {
        $category_url = home_url('/product-category/' . $category->slug);
        $image_url = $category->image_id ? wp_get_attachment_url($category->image_id) : '';
        
        ob_start();
        ?>
        <div class="digiplanet-category-item">
            <a href="<?php echo esc_url($category_url); ?>" class="digiplanet-category-link">
                <?php if ($settings['show_image'] === 'yes' && $image_url): ?>
                    <div class="digiplanet-category-image">
                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($category->name); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="digiplanet-category-content">
                    <h3 class="digiplanet-category-name"><?php echo esc_html($category->name); ?></h3>
                    
                    <?php if ($settings['show_count'] === 'yes'): ?>
                        <div class="digiplanet-category-count">
                            <?php echo sprintf(_n('%d product', '%d products', $category->product_count, 'digiplanet-digital-products'), $category->product_count); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($category->description): ?>
                        <div class="digiplanet-category-description">
                            <?php echo wp_kses_post(wp_trim_words($category->description, 15)); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function get_categories_options() {
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $categories = $product_manager->get_categories();
        $options = [
            0 => __('All Categories', 'digiplanet-digital-products'),
        ];
        
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }
        
        return $options;
    }
}