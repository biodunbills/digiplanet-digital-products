/**
 * Digiplanet Digital Products - Cart JavaScript
 */

(function($) {
    'use strict';
    
    // Cart Manager
    var DigiplanetCart = {
        
        /**
         * Initialize cart
         */
        init: function() {
            this.bindEvents();
            this.updateCartCount();
        },
        
        /**
         * Bind events
         */
        bindEvents: function() {
            var self = this;
            
            // Add to cart
            $(document).on('click', '.digiplanet-add-to-cart', function(e) {
                e.preventDefault();
                self.addToCart($(this));
            });
            
            // Update quantity
            $(document).on('click', '.digiplanet-qty-plus, .digiplanet-qty-minus', function() {
                self.updateQuantity($(this));
            });
            
            $(document).on('change', '.digiplanet-qty-input', function() {
                self.updateQuantityInput($(this));
            });
            
            // Remove item
            $(document).on('click', '.digiplanet-remove-item', function() {
                self.removeItem($(this));
            });
            
            // Clear cart
            $(document).on('click', '.digiplanet-clear-cart', function() {
                self.clearCart();
            });
            
            // Apply coupon
            $(document).on('click', '.digiplanet-apply-coupon', function() {
                self.applyCoupon();
            });
            
            // Update cart on page load
            $(document).ready(function() {
                self.updateCartDisplay();
            });
            
            // Mini cart toggle
            $(document).on('click', '.digiplanet-mini-cart-toggle', function(e) {
                e.stopPropagation();
                self.toggleMiniCart();
            });
            
            // Close mini cart on outside click
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.digiplanet-mini-cart').length) {
                    $('.digiplanet-mini-cart-dropdown').hide();
                }
            });
            
            // Prevent mini cart close when clicking inside
            $(document).on('click', '.digiplanet-mini-cart-dropdown', function(e) {
                e.stopPropagation();
            });
        },
        
        /**
         * Add product to cart
         */
        addToCart: function($button) {
            var productId = $button.data('product-id');
            var quantity = $button.data('quantity') || 1;
            
            if (!productId) {
                this.showMessage('Product ID is missing.', 'error');
                return;
            }
            
            // Show loading
            $button.addClass('loading').prop('disabled', true);
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_add_to_cart',
                    product_id: productId,
                    quantity: quantity,
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DigiplanetCart.updateCartCount(response.data.cart_count);
                        DigiplanetCart.updateCartTotal(response.data.cart_total);
                        DigiplanetCart.showMessage(response.data.message, 'success');
                        DigiplanetCart.updateMiniCart();
                        
                        // Update cart page if open
                        if ($('.digiplanet-cart').length) {
                            DigiplanetCart.updateCartDisplay();
                        }
                    } else {
                        DigiplanetCart.showMessage(response.data.message, 'error');
                    }
                },
                error: function() {
                    DigiplanetCart.showMessage(digiplanet_frontend.error_message, 'error');
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },
        
        /**
         * Update quantity via buttons
         */
        updateQuantity: function($button) {
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
            this.updateCartItem(productId, newVal);
        },
        
        /**
         * Update quantity via input
         */
        updateQuantityInput: function($input) {
            var productId = $input.data('product-id');
            var newVal = parseInt($input.val());
            
            if (newVal < 1) {
                newVal = 1;
                $input.val(1);
            }
            
            this.updateCartItem(productId, newVal);
        },
        
        /**
         * Update cart item quantity
         */
        updateCartItem: function(productId, quantity) {
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_update_cart',
                    product_id: productId,
                    quantity: quantity,
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DigiplanetCart.updateCartDisplay();
                        DigiplanetCart.showMessage(response.data.message, 'success');
                    } else {
                        DigiplanetCart.showMessage(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Remove item from cart
         */
        removeItem: function($button) {
            var productId = $button.data('product-id');
            
            if (!confirm('Are you sure you want to remove this item from cart?')) {
                return;
            }
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_remove_from_cart',
                    product_id: productId,
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DigiplanetCart.updateCartDisplay();
                        DigiplanetCart.updateCartCount(response.data.cart_count);
                        DigiplanetCart.updateCartTotal(response.data.cart_total);
                        DigiplanetCart.showMessage(response.data.message, 'success');
                        DigiplanetCart.updateMiniCart();
                    } else {
                        DigiplanetCart.showMessage(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Clear cart
         */
        clearCart: function() {
            if (!confirm('Are you sure you want to clear your cart?')) {
                return;
            }
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_clear_cart',
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DigiplanetCart.updateCartDisplay();
                        DigiplanetCart.updateCartCount(0);
                        DigiplanetCart.updateCartTotal('0.00');
                        DigiplanetCart.showMessage(response.data.message, 'success');
                        DigiplanetCart.updateMiniCart();
                        
                        // Redirect to products page if on cart page
                        if ($('.digiplanet-cart').length) {
                            setTimeout(function() {
                                window.location.href = digiplanet_frontend.home_url;
                            }, 1500);
                        }
                    } else {
                        DigiplanetCart.showMessage(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Apply coupon
         */
        applyCoupon: function() {
            var couponCode = $('.digiplanet-coupon-input').val();
            
            if (!couponCode) {
                this.showMessage('Please enter a coupon code.', 'error');
                return;
            }
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_apply_coupon',
                    coupon_code: couponCode,
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        DigiplanetCart.updateCartDisplay();
                        DigiplanetCart.showMessage(response.data.message, 'success');
                    } else {
                        DigiplanetCart.showMessage(response.data.message, 'error');
                    }
                }
            });
        },
        
        /**
         * Update cart display
         */
        updateCartDisplay: function() {
            if (!$('.digiplanet-cart').length) {
                return;
            }
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_get_cart',
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.digiplanet-cart').html(response.data.html);
                    }
                }
            });
        },
        
        /**
         * Update mini cart
         */
        updateMiniCart: function() {
            if (!$('.digiplanet-mini-cart-dropdown').length) {
                return;
            }
            
            $.ajax({
                url: digiplanet_frontend.ajax_url,
                type: 'POST',
                data: {
                    action: 'digiplanet_get_mini_cart',
                    nonce: digiplanet_frontend.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('.digiplanet-mini-cart-dropdown').html(response.data.html);
                    }
                }
            });
        },
        
        /**
         * Update cart count
         */
        updateCartCount: function(count) {
            var $count = $('.digiplanet-cart-count');
            
            if (count > 0) {
                if ($count.length) {
                    $count.text(count).show();
                } else {
                    $('.digiplanet-mini-cart-toggle').append('<span class="digiplanet-cart-count">' + count + '</span>');
                }
            } else {
                $count.hide();
            }
        },
        
        /**
         * Update cart total
         */
        updateCartTotal: function(total) {
            $('.digiplanet-cart-total').text(this.formatPrice(total));
        },
        
        /**
         * Toggle mini cart
         */
        toggleMiniCart: function() {
            var $dropdown = $('.digiplanet-mini-cart-dropdown');
            
            if ($dropdown.is(':visible')) {
                $dropdown.hide();
            } else {
                $dropdown.show();
                this.updateMiniCart();
            }
        },
        
        /**
         * Show message
         */
        showMessage: function(message, type) {
            // Remove existing messages
            $('.digiplanet-message').remove();
            
            // Create message element
            var $message = $('<div class="digiplanet-message digiplanet-message-' + type + '">' + message + '</div>');
            
            // Add to page
            $('body').append($message);
            
            // Show with animation
            setTimeout(function() {
                $message.addClass('show');
            }, 10);
            
            // Remove after delay
            setTimeout(function() {
                $message.removeClass('show');
                setTimeout(function() {
                    $message.remove();
                }, 300);
            }, 3000);
        },
        
        /**
         * Format price
         */
        formatPrice: function(price) {
            var currency = digiplanet_frontend.currency_symbol;
            var formatted = parseFloat(price).toFixed(2);
            
            return currency + formatted;
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        DigiplanetCart.init();
    });
    
})(jQuery);