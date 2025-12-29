/**
 * Digiplanet Digital Products - Frontend JavaScript
 */

jQuery(document).ready(function($) {
    
    // Add to Cart Functionality
    $(document).on('click', '.digiplanet-add-to-cart', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var productId = $button.data('product-id');
        var quantity = $button.data('quantity') || 1;
        
        // Add loading state
        $button.addClass('loading').prop('disabled', true);
        
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
                    // Update cart count in header
                    if (response.data.cart_count > 0) {
                        $('.digiplanet-cart-count').text(response.data.cart_count).show();
                    }
                    
                    // Show success message
                    digiplanetShowMessage(response.data.message, 'success');
                    
                    // Update mini cart if exists
                    if ($('.digiplanet-mini-cart-dropdown').length) {
                        updateMiniCart();
                    }
                } else {
                    digiplanetShowMessage(response.data.message, 'error');
                }
            },
            error: function() {
                digiplanetShowMessage('An error occurred. Please try again.', 'error');
            },
            complete: function() {
                $button.removeClass('loading').prop('disabled', false);
            }
        });
    });
    
    // Update Cart Quantity
    $(document).on('click', '.digiplanet-qty-plus, .digiplanet-qty-minus', function() {
        var $button = $(this);
        var $input = $button.siblings('.digiplanet-qty-input');
        var productId = $button.data('product-id');
        var currentVal = parseInt($input.val());
        var newVal;
        
        if ($button.hasClass('digiplanet-qty-plus')) {
            newVal = currentVal + 1;
        } else {
            newVal = currentVal > 1 ? currentVal - 1 : 1;
        }
        
        $input.val(newVal);
        updateCartItem(productId, newVal);
    });
    
    $(document).on('change', '.digiplanet-qty-input', function() {
        var $input = $(this);
        var productId = $input.data('product-id');
        var newVal = parseInt($input.val());
        
        if (newVal < 1) newVal = 1;
        
        updateCartItem(productId, newVal);
    });
    
    // Remove Cart Item
    $(document).on('click', '.digiplanet-remove-item', function() {
        var productId = $(this).data('product-id');
        
        if (confirm('Are you sure you want to remove this item from cart?')) {
            removeCartItem(productId);
        }
    });
    
    // Update cart item quantity
    function updateCartItem(productId, quantity) {
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_update_cart',
                product_id: productId,
                quantity: quantity,
                nonce: digiplanet_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to update totals
                } else {
                    digiplanetShowMessage(response.data.message, 'error');
                }
            }
        });
    }
    
    // Remove cart item
    function removeCartItem(productId) {
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_remove_from_cart',
                product_id: productId,
                nonce: digiplanet_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to update cart
                } else {
                    digiplanetShowMessage(response.data.message, 'error');
                }
            }
        });
    }
    
    // Update mini cart
    function updateMiniCart() {
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_get_mini_cart',
                nonce: digiplanet_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.digiplanet-mini-cart-dropdown').html(response.data.html);
                }
            }
        });
    }
    
    // Product Carousel
    $('.digiplanet-carousel-nav').on('click', function() {
        var $carousel = $(this).closest('.digiplanet-product-carousel');
        var $track = $carousel.find('.digiplanet-carousel-track');
        var $items = $carousel.find('.digiplanet-carousel-item');
        var itemWidth = $items.outerWidth();
        var currentPosition = parseInt($track.css('transform').split(',')[4]) || 0;
        
        if ($(this).hasClass('digiplanet-carousel-next')) {
            var newPosition = currentPosition - itemWidth;
            var maxPosition = -($items.length * itemWidth) + ($carousel.width());
            
            if (newPosition < maxPosition) {
                newPosition = maxPosition;
            }
        } else {
            var newPosition = currentPosition + itemWidth;
            if (newPosition > 0) {
                newPosition = 0;
            }
        }
        
        $track.css('transform', 'translateX(' + newPosition + 'px)');
    });
    
    // Product Search
    $('.digiplanet-product-search-form').on('submit', function(e) {
        e.preventDefault();
        var searchTerm = $(this).find('input[name="s"]').val();
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_product_search',
                search_term: searchTerm,
                nonce: digiplanet_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.digiplanet-product-grid').html(response.data.html);
                }
            }
        });
    });
    
    // Show message function
    function digiplanetShowMessage(message, type) {
        var $message = $('<div class="digiplanet-alert digiplanet-alert-' + type + '">' + message + '</div>');
        
        $('body').append($message);
        
        setTimeout(function() {
            $message.addClass('show');
        }, 10);
        
        setTimeout(function() {
            $message.removeClass('show');
            setTimeout(function() {
                $message.remove();
            }, 300);
        }, 3000);
    }
    
    // Payment form handling
    $('input[name="payment_method"]').on('change', function() {
        $('.digiplanet-payment-form').hide();
        $('#' + $(this).val() + '-payment-form').show();
    });
    
    // Initialize
    $('.digiplanet-payment-form').hide();
    $('input[name="payment_method"]:checked').trigger('change');
});