/**
 * Digiplanet Elementor Widgets JavaScript
 */

(function($) {
    'use strict';
    
    // Product Carousel
    $(document).ready(function() {
        // Initialize carousels
        $('.digiplanet-product-carousel').each(function() {
            var $carousel = $(this);
            var settings = $carousel.data('settings');
            var $wrapper = $carousel.find('.digiplanet-carousel-wrapper');
            var $slides = $carousel.find('.digiplanet-carousel-slide');
            var $prevBtn = $carousel.find('.digiplanet-carousel-prev');
            var $nextBtn = $carousel.find('.digiplanet-carousel-next');
            var $dotsContainer = $carousel.find('.digiplanet-carousel-dots');
            
            var slideWidth = $slides.outerWidth();
            var currentSlide = 0;
            var totalSlides = $slides.length;
            var isAnimating = false;
            
            // Create dots if enabled
            if (settings.dots && $dotsContainer.length) {
                for (var i = 0; i < totalSlides; i++) {
                    var $dot = $('<button class="digiplanet-carousel-dot"></button>');
                    if (i === 0) {
                        $dot.addClass('active');
                    }
                    $dot.on('click', function(index) {
                        return function() {
                            goToSlide(index);
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
                    $dots.eq(currentSlide).addClass('active');
                }
                
                setTimeout(function() {
                    isAnimating = false;
                }, 500);
            }
            
            function nextSlide() {
                var nextIndex = (currentSlide + 1) % totalSlides;
                if (!settings.infinite && nextIndex === 0) return;
                goToSlide(nextIndex);
            }
            
            function prevSlide() {
                var prevIndex = (currentSlide - 1 + totalSlides) % totalSlides;
                if (!settings.infinite && prevIndex === totalSlides - 1) return;
                goToSlide(prevIndex);
            }
            
            // Event listeners
            $nextBtn.on('click', nextSlide);
            $prevBtn.on('click', prevSlide);
            
            // Autoplay
            var autoplayInterval;
            if (settings.autoplay) {
                autoplayInterval = setInterval(nextSlide, settings.autoplaySpeed);
                
                // Pause on hover
                $carousel.on('mouseenter', function() {
                    clearInterval(autoplayInterval);
                }).on('mouseleave', function() {
                    autoplayInterval = setInterval(nextSlide, settings.autoplaySpeed);
                });
            }
            
            // Handle window resize
            $(window).on('resize', function() {
                slideWidth = $slides.outerWidth();
                var newPosition = -currentSlide * slideWidth;
                $wrapper.css({
                    'transform': 'translateX(' + newPosition + 'px)',
                    'transition': 'none'
                });
            });
        });
        
        // Add to Cart functionality
        $(document).on('click', '.digiplanet-add-to-cart', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var productId = $button.data('product-id');
            var $quantityInput = $button.siblings('.digiplanet-quantity-input');
            var quantity = $quantityInput ? $quantityInput.val() : 1;
            
            // Disable button and show loading
            $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');
            
            $.ajax({
                url: digiplanet_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_add_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    nonce: digiplanet_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        // Update cart count
                        updateCartCount(response.data.cart_count);
                        
                        // Show success message
                        showNotification(response.data.message, 'success');
                        
                        // Reset button
                        setTimeout(function() {
                            $button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                        }, 1000);
                    } else {
                        showNotification(response.data.message, 'error');
                        $button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                    }
                },
                error: function() {
                    showNotification('An error occurred. Please try again.', 'error');
                    $button.prop('disabled', false).html('<i class="fas fa-shopping-cart"></i> Add to Cart');
                }
            });
        });
        
        // Quantity controls
        $(document).on('click', '.digiplanet-quantity-minus', function() {
            var $input = $(this).siblings('.digiplanet-quantity-input');
            var currentValue = parseInt($input.val()) || 1;
            if (currentValue > 1) {
                $input.val(currentValue - 1);
            }
        });
        
        $(document).on('click', '.digiplanet-quantity-plus', function() {
            var $input = $(this).siblings('.digiplanet-quantity-input');
            var currentValue = parseInt($input.val()) || 1;
            $input.val(currentValue + 1);
        });
        
        // Search form
        $('.digiplanet-search-form').on('submit', function(e) {
            var $form = $(this);
            var $input = $form.find('.digiplanet-search-input');
            
            if ($input.val().trim() === '') {
                e.preventDefault();
                $input.focus();
                showNotification('Please enter a search term.', 'warning');
            }
        });
        
        // Helper functions
        function updateCartCount(count) {
            $('.digiplanet-cart-count').text(count);
        }
        
        function showNotification(message, type) {
            var $notification = $('<div class="digiplanet-notification digiplanet-notification-' + type + '">' + message + '</div>');
            
            $('body').append($notification);
            
            setTimeout(function() {
                $notification.addClass('show');
            }, 10);
            
            setTimeout(function() {
                $notification.removeClass('show');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);
        }
        
        // Copy license key
        $(document).on('click', '.digiplanet-copy-license', function(e) {
            e.preventDefault();
            
            var $button = $(this);
            var licenseKey = $button.data('license');
            
            // Create temporary input element
            var $tempInput = $('<input>');
            $('body').append($tempInput);
            $tempInput.val(licenseKey).select();
            
            // Copy to clipboard
            try {
                document.execCommand('copy');
                showNotification('License key copied to clipboard!', 'success');
                
                // Change button icon temporarily
                var originalHTML = $button.html();
                $button.html('<i class="fas fa-check"></i> Copied');
                
                setTimeout(function() {
                    $button.html(originalHTML);
                }, 2000);
            } catch (err) {
                showNotification('Failed to copy license key.', 'error');
            }
            
            $tempInput.remove();
        });
        
        // Lazy loading for images
        if ('IntersectionObserver' in window) {
            var lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        var $img = $(entry.target);
                        var src = $img.data('src');
                        
                        if (src) {
                            $img.attr('src', src);
                            $img.removeAttr('data-src');
                        }
                        
                        lazyImageObserver.unobserve(entry.target);
                    }
                });
            });
            
            $('img[data-src]').each(function() {
                lazyImageObserver.observe(this);
            });
        }
    });
    
    // Product filter functionality
    $(document).on('change', '.digiplanet-product-filters select, .digiplanet-product-filters input', function() {
        var $form = $(this).closest('form');
        var $results = $form.siblings('.digiplanet-filter-results');
        
        if ($results.length) {
            var formData = $form.serialize();
            
            $.ajax({
                url: digiplanet_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_filter_products',
                    data: formData,
                    nonce: digiplanet_ajax.nonce
                },
                beforeSend: function() {
                    $results.addClass('loading');
                },
                success: function(response) {
                    if (response.success) {
                        $results.html(response.data.html);
                    } else {
                        $results.html('<p class="digiplanet-no-results">' + response.data.message + '</p>');
                    }
                },
                complete: function() {
                    $results.removeClass('loading');
                }
            });
        }
    });
    
})(jQuery);