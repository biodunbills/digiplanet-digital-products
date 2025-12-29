<?php
/**
 * Product Carousel Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$query = isset($args['query']) ? $args['query'] : false;
$settings = isset($args['settings']) ? $args['settings'] : [];
$title = isset($settings['title']) ? $settings['title'] : '';
$columns = isset($settings['columns']) ? absint($settings['columns']) : 4;
$autoplay = isset($settings['autoplay']) ? $settings['autoplay'] : true;
$autoplay_speed = isset($settings['autoplay_speed']) ? absint($settings['autoplay_speed']) : 3000;
$loop = isset($settings['loop']) ? $settings['loop'] : true;
$arrows = isset($settings['arrows']) ? $settings['arrows'] : true;
$dots = isset($settings['dots']) ? $settings['dots'] : false;

if (!$query || !$query->have_posts()) {
    echo '<p class="digiplanet-no-products">' . __('No products found.', 'digiplanet-digital-products') . '</p>';
    return;
}

$carousel_settings = [
    'columns' => $columns,
    'autoplay' => $autoplay,
    'autoplaySpeed' => $autoplay_speed,
    'infinite' => $loop,
    'arrows' => $arrows,
    'dots' => $dots,
];
?>

<div class="digiplanet-product-carousel" data-settings='<?php echo json_encode($carousel_settings); ?>'>
    <?php if ($title): ?>
        <div class="digiplanet-carousel-header">
            <h2 class="digiplanet-carousel-title"><?php echo esc_html($title); ?></h2>
            <?php if ($arrows): ?>
                <div class="digiplanet-carousel-nav">
                    <button class="digiplanet-carousel-arrow digiplanet-carousel-prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="digiplanet-carousel-arrow digiplanet-carousel-next">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <div class="digiplanet-carousel-container">
        <div class="digiplanet-carousel-wrapper">
            <?php while ($query->have_posts()): $query->the_post(); ?>
                <div class="digiplanet-carousel-slide">
                    <?php include DIGIPLANET_PLUGIN_DIR . 'modules/product-display/templates/product-card.php'; ?>
                </div>
            <?php endwhile; wp_reset_postdata(); ?>
        </div>
        
        <?php if ($dots): ?>
            <div class="digiplanet-carousel-dots"></div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize carousel
    function initCarousel($carousel) {
        var settings = $carousel.data('settings');
        var $wrapper = $carousel.find('.digiplanet-carousel-wrapper');
        var $slides = $carousel.find('.digiplanet-carousel-slide');
        var $prevBtn = $carousel.find('.digiplanet-carousel-prev');
        var $nextBtn = $carousel.find('.digiplanet-carousel-next');
        var $dotsContainer = $carousel.find('.digiplanet-carousel-dots');
        
        var slideWidth = 0;
        var currentSlide = 0;
        var totalSlides = $slides.length;
        var slidesPerView = Math.min(settings.columns, totalSlides);
        var isAnimating = false;
        var autoplayInterval;
        
        // Calculate slide width
        function calculateSlideWidth() {
            var containerWidth = $carousel.find('.digiplanet-carousel-container').width();
            slideWidth = containerWidth / slidesPerView;
            $slides.css('width', slideWidth + 'px');
            
            // Update wrapper position
            var newPosition = -currentSlide * slideWidth;
            $wrapper.css('transform', 'translateX(' + newPosition + 'px)');
        }
        
        // Create dots if enabled
        if (settings.dots && $dotsContainer.length) {
            $dotsContainer.empty();
            for (var i = 0; i < Math.ceil(totalSlides / slidesPerView); i++) {
                var $dot = $('<button class="digiplanet-carousel-dot"></button>');
                if (i === 0) {
                    $dot.addClass('active');
                }
                $dot.on('click', function(index) {
                    return function() {
                        goToSlide(index * slidesPerView);
                    };
                }(i));
                $dotsContainer.append($dot);
            }
            var $dots = $dotsContainer.find('.digiplanet-carousel-dot');
        }
        
        // Navigation functions
        function goToSlide(index) {
            if (isAnimating || index === currentSlide) return;
            
            isAnimating = true;
            var newPosition = -index * slideWidth;
            
            $wrapper.css({
                'transform': 'translateX(' + newPosition + 'px)',
                'transition': 'transform 0.5s ease'
            });
            
            currentSlide = index;
            
            // Update active dot
            if ($dots) {
                $dots.removeClass('active');
                var dotIndex = Math.floor(currentSlide / slidesPerView);
                $dots.eq(dotIndex).addClass('active');
            }
            
            setTimeout(function() {
                isAnimating = false;
            }, 500);
        }
        
        function nextSlide() {
            var nextIndex = currentSlide + slidesPerView;
            if (nextIndex >= totalSlides) {
                if (settings.infinite) {
                    nextIndex = 0;
                } else {
                    return;
                }
            }
            goToSlide(nextIndex);
        }
        
        function prevSlide() {
            var prevIndex = currentSlide - slidesPerView;
            if (prevIndex < 0) {
                if (settings.infinite) {
                    prevIndex = totalSlides - slidesPerView;
                    if (prevIndex < 0) prevIndex = 0;
                } else {
                    return;
                }
            }
            goToSlide(prevIndex);
        }
        
        // Event listeners
        $nextBtn.on('click', nextSlide);
        $prevBtn.on('click', prevSlide);
        
        // Touch/swipe support
        var startX = 0;
        var currentX = 0;
        var isDragging = false;
        
        $wrapper.on('touchstart mousedown', function(e) {
            isDragging = true;
            startX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
            currentX = startX;
            $wrapper.css('transition', 'none');
        });
        
        $(document).on('touchmove mousemove', function(e) {
            if (!isDragging) return;
            
            e.preventDefault();
            currentX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
            var diff = currentX - startX;
            var currentPosition = -currentSlide * slideWidth;
            var newPosition = currentPosition + diff;
            
            $wrapper.css('transform', 'translateX(' + newPosition + 'px)');
        });
        
        $(document).on('touchend mouseup', function(e) {
            if (!isDragging) return;
            
            isDragging = false;
            $wrapper.css('transition', 'transform 0.5s ease');
            
            var diff = currentX - startX;
            var threshold = slideWidth * 0.3;
            
            if (Math.abs(diff) > threshold) {
                if (diff > 0) {
                    prevSlide();
                } else {
                    nextSlide();
                }
            } else {
                // Return to current slide
                goToSlide(currentSlide);
            }
        });
        
        // Autoplay
        if (settings.autoplay) {
            function startAutoplay() {
                autoplayInterval = setInterval(nextSlide, settings.autoplaySpeed);
            }
            
            function stopAutoplay() {
                clearInterval(autoplayInterval);
            }
            
            startAutoplay();
            
            // Pause on hover
            $carousel.on('mouseenter', stopAutoplay)
                     .on('mouseleave', startAutoplay);
            
            // Pause on touch
            $wrapper.on('touchstart', stopAutoplay)
                    .on('touchend', startAutoplay);
        }
        
        // Handle window resize
        $(window).on('resize', function() {
            calculateSlideWidth();
        });
        
        // Initial calculation
        calculateSlideWidth();
    }
    
    // Initialize all carousels
    $('.digiplanet-product-carousel').each(function() {
        initCarousel($(this));
    });
});
</script>

<style>
.digiplanet-product-carousel {
    position: relative;
    margin: 40px 0;
}

.digiplanet-carousel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding: 0 20px;
}

.digiplanet-carousel-title {
    margin: 0;
    font-size: 32px;
    font-weight: 700;
    color: #333;
}

.digiplanet-carousel-nav {
    display: flex;
    gap: 10px;
}

.digiplanet-carousel-container {
    position: relative;
    overflow: hidden;
    padding: 0 20px;
}

.digiplanet-carousel-wrapper {
    display: flex;
    transition: transform 0.5s ease;
    will-change: transform;
}

.digiplanet-carousel-slide {
    flex-shrink: 0;
    padding: 0 10px;
    box-sizing: border-box;
    transition: opacity 0.3s ease;
}

.digiplanet-carousel-arrow {
    width: 50px;
    height: 50px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    color: #333;
    font-size: 18px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.digiplanet-carousel-arrow:hover {
    background: #3742fa;
    color: white;
    border-color: #3742fa;
    transform: scale(1.1);
}

.digiplanet-carousel-arrow:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

.digiplanet-carousel-arrow:disabled:hover {
    background: white;
    color: #333;
    border-color: #e9ecef;
}

.digiplanet-carousel-dots {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 30px;
    padding: 0 20px;
}

.digiplanet-carousel-dot {
    width: 12px;
    height: 12px;
    background: #dee2e6;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
}

.digiplanet-carousel-dot:hover {
    background: #adb5bd;
}

.digiplanet-carousel-dot.active {
    background: #3742fa;
    transform: scale(1.2);
}

/* Responsive */
@media (max-width: 1200px) {
    .digiplanet-carousel-slide {
        width: calc(100% / 3) !important;
    }
}

@media (max-width: 992px) {
    .digiplanet-carousel-slide {
        width: calc(100% / 2) !important;
    }
    
    .digiplanet-carousel-title {
        font-size: 28px;
    }
}

@media (max-width: 768px) {
    .digiplanet-carousel-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 20px;
    }
    
    .digiplanet-carousel-nav {
        align-self: flex-end;
    }
}

@media (max-width: 576px) {
    .digiplanet-carousel-slide {
        width: 100% !important;
    }
    
    .digiplanet-carousel-title {
        font-size: 24px;
    }
    
    .digiplanet-carousel-arrow {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}
</style>