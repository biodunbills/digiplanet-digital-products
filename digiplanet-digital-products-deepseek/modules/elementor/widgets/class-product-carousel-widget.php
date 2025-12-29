<?php
/**
 * Elementor Product Carousel Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Carousel_Widget extends \Elementor\Widget_Base {
    
    public function get_name() {
        return 'digiplanet_product_carousel';
    }
    
    public function get_title() {
        return __('Product Carousel', 'digiplanet-digital-products');
    }
    
    public function get_icon() {
        return 'eicon-slider-push';
    }
    
    public function get_categories() {
        return ['digiplanet'];
    }
    
    public function get_keywords() {
        return ['product', 'carousel', 'slider', 'digiplanet'];
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
                'default' => __('Featured Products', 'digiplanet-digital-products'),
                'placeholder' => __('Enter carousel title', 'digiplanet-digital-products'),
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
            'limit',
            [
                'label' => __('Products Limit', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 8,
                'min' => 1,
                'max' => 50,
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
                'label' => __('Show Featured Only', 'digiplanet-digital-products'),
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
                'label' => __('Show On Sale Only', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'no',
            ]
        );
        
        $this->end_controls_section();
        
        // Carousel Settings
        $this->start_controls_section(
            'carousel_section',
            [
                'label' => __('Carousel Settings', 'digiplanet-digital-products'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
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
            'slides_to_scroll',
            [
                'label' => __('Slides to Scroll', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 1,
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
            'autoplay_speed',
            [
                'label' => __('Autoplay Speed (ms)', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 3000,
                'min' => 1000,
                'max' => 10000,
                'step' => 500,
                'condition' => [
                    'autoplay' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'infinite',
            [
                'label' => __('Infinite Loop', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
            ]
        );
        
        $this->add_control(
            'speed',
            [
                'label' => __('Animation Speed (ms)', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 500,
                'min' => 100,
                'max' => 2000,
                'step' => 100,
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
        
        $this->add_control(
            'pause_on_hover',
            [
                'label' => __('Pause on Hover', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Yes', 'digiplanet-digital-products'),
                'label_off' => __('No', 'digiplanet-digital-products'),
                'return_value' => 'yes',
                'default' => 'yes',
                'condition' => [
                    'autoplay' => 'yes',
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
                    '{{WRAPPER}} .digiplanet-carousel-title' => 'color: {{VALUE}}',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'selector' => '{{WRAPPER}} .digiplanet-carousel-title',
            ]
        );
        
        $this->add_responsive_control(
            'title_spacing',
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
                    '{{WRAPPER}} .digiplanet-carousel-title' => 'margin-bottom: {{SIZE}}{{UNIT}}',
                ],
            ]
        );
        
        // Arrows Style
        $this->add_control(
            'arrows_style_heading',
            [
                'label' => __('Arrows', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'arrows_color',
            [
                'label' => __('Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-arrow' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'arrows_bg_color',
            [
                'label' => __('Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-arrow' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'arrows_hover_color',
            [
                'label' => __('Hover Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-arrow:hover' => 'color: {{VALUE}}',
                ],
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'arrows_hover_bg_color',
            [
                'label' => __('Hover Background Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-arrow:hover' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_arrows' => 'yes',
                ],
            ]
        );
        
        // Dots Style
        $this->add_control(
            'dots_style_heading',
            [
                'label' => __('Dots', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
                'condition' => [
                    'show_dots' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'dots_color',
            [
                'label' => __('Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-dots .swiper-pagination-bullet' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_dots' => 'yes',
                ],
            ]
        );
        
        $this->add_control(
            'dots_active_color',
            [
                'label' => __('Active Color', 'digiplanet-digital-products'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .digiplanet-carousel-dots .swiper-pagination-bullet-active' => 'background-color: {{VALUE}}',
                ],
                'condition' => [
                    'show_dots' => 'yes',
                ],
            ]
        );
        
        $this->end_controls_section();
    }
    
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        // Build product query args
        $args = [
            'limit' => $settings['limit'],
            'orderby' => $settings['orderby'],
            'order' => $settings['order'],
        ];
        
        if (!empty($settings['category'])) {
            $args['category'] = implode(',', $settings['category']);
        }
        
        if ($settings['show_featured'] === 'yes') {
            $args['featured'] = true;
        }
        
        if ($settings['show_onsale'] === 'yes') {
            $args['onsale'] = true;
        }
        
        // Get products
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $products = $product_manager->search_products($args);
        
        // Carousel settings
        $carousel_settings = [
            'slidesToShow' => $settings['slides_to_show'],
            'slidesToScroll' => $settings['slides_to_scroll'],
            'autoplay' => $settings['autoplay'] === 'yes',
            'autoplaySpeed' => $settings['autoplay_speed'],
            'infinite' => $settings['infinite'] === 'yes',
            'speed' => $settings['speed'],
            'arrows' => $settings['show_arrows'] === 'yes',
            'dots' => $settings['show_dots'] === 'yes',
            'pauseOnHover' => $settings['pause_on_hover'] === 'yes',
        ];
        
        ?>
        <div class="digiplanet-product-carousel-widget">
            <?php if ($settings['title']): ?>
                <h2 class="digiplanet-carousel-title"><?php echo esc_html($settings['title']); ?></h2>
            <?php endif; ?>
            
            <div class="digiplanet-product-carousel" 
                 data-settings='<?php echo json_encode($carousel_settings); ?>'>
                
                <div class="digiplanet-carousel-container swiper-container">
                    <div class="digiplanet-carousel-track swiper-wrapper">
                        <?php foreach ($products as $product): ?>
                            <?php echo $this->get_product_card_html($product); ?>
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
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            var $carousel = $('.digiplanet-product-carousel[data-settings]');
            var settings = $carousel.data('settings');
            
            if ($carousel.length && typeof Swiper !== 'undefined') {
                new Swiper($carousel.find('.swiper-container')[0], {
                    slidesPerView: settings.slidesToShow,
                    slidesPerGroup: settings.slidesToScroll,
                    spaceBetween: 30,
                    loop: settings.infinite,
                    speed: settings.speed,
                    autoplay: settings.autoplay ? {
                        delay: settings.autoplaySpeed,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: settings.pauseOnHover,
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
                        1200: {
                            slidesPerView: settings.slidesToShow,
                            spaceBetween: 30,
                        },
                    },
                });
            }
        });
        </script>
        <?php
    }
    
    protected function get_product_card_html($product) {
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $price_html = $product_manager->format_price($product->price, $product->sale_price);
        $thumbnail = $product->featured_image_id ? wp_get_attachment_url($product->featured_image_id) : DIGIPLANET_ASSETS_URL . 'images/placeholder-product.png';
        
        ob_start();
        ?>
        <div class="digiplanet-carousel-slide swiper-slide">
            <div class="digiplanet-product-card">
                <div class="digiplanet-product-image">
                    <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>">
                        <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($product->name); ?>">
                        
                        <?php if ($product->sale_price): ?>
                            <span class="digiplanet-product-badge digiplanet-badge-sale">
                                <?php echo $product_manager->calculate_sale_percentage($product->price, $product->sale_price); ?>% OFF
                            </span>
                        <?php endif; ?>
                    </a>
                </div>
                
                <div class="digiplanet-product-content">
                    <h3 class="digiplanet-product-title">
                        <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>">
                            <?php echo esc_html($product->name); ?>
                        </a>
                    </h3>
                    
                    <div class="digiplanet-product-price">
                        <span class="digiplanet-current-price"><?php echo $price_html; ?></span>
                        <?php if ($product->sale_price): ?>
                            <span class="digiplanet-original-price"><?php echo $product_manager->format_price($product->price); ?></span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="digiplanet-product-meta">
                        <?php if ($product->rating > 0): ?>
                            <div class="digiplanet-rating">
                                <span class="digiplanet-rating-stars">
                                    <?php echo str_repeat('★', floor($product->rating)); ?>
                                    <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="button" 
                            class="digiplanet-btn digiplanet-btn-primary digiplanet-add-to-cart" 
                            data-product-id="<?php echo $product->id; ?>">
                        <?php _e('Add to Cart', 'digiplanet-digital-products'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function get_categories_options() {
        $product_manager = Digiplanet_Product_Manager::get_instance();
        $categories = $product_manager->get_categories();
        $options = [];
        
        foreach ($categories as $category) {
            $options[$category->id] = $category->name;
        }
        
        return $options;
    }
}