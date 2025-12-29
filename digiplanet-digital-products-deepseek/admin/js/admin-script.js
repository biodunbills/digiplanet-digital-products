/**
 * Digiplanet Digital Products - Admin JavaScript
 */

jQuery(document).ready(function($) {
    
    // Product Management
    $(document).on('click', '.digiplanet-bulk-action', function() {
        var action = $(this).data('action');
        var productIds = [];
        
        $('.digiplanet-product-checkbox:checked').each(function() {
            productIds.push($(this).val());
        });
        
        if (productIds.length === 0) {
            alert('Please select at least one product.');
            return;
        }
        
        if (confirm('Are you sure you want to ' + action + ' selected products?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'digiplanet_bulk_action',
                    product_ids: productIds,
                    bulk_action: action,
                    nonce: digiplanet_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message);
                    }
                }
            });
        }
    });
    
    // Toggle all checkboxes
    $(document).on('change', '.digiplanet-toggle-all', function() {
        $('.digiplanet-product-checkbox').prop('checked', $(this).prop('checked'));
    });
    
    // Quick edit product
    $(document).on('click', '.digiplanet-quick-edit', function() {
        var productId = $(this).data('product-id');
        var $row = $(this).closest('tr');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_get_product_data',
                product_id: productId,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Populate quick edit form
                    $('#digiplanet-quick-edit-price').val(response.data.price);
                    $('#digiplanet-quick-edit-sale-price').val(response.data.sale_price);
                    $('#digiplanet-quick-edit-status').val(response.data.status);
                    
                    // Show modal
                    $('#digiplanet-quick-edit-modal').show();
                    $('#digiplanet-quick-edit-product-id').val(productId);
                }
            }
        });
    });
    
    // Save quick edit
    $(document).on('click', '#digiplanet-quick-edit-save', function() {
        var productId = $('#digiplanet-quick-edit-product-id').val();
        var data = {
            price: $('#digiplanet-quick-edit-price').val(),
            sale_price: $('#digiplanet-quick-edit-sale-price').val(),
            status: $('#digiplanet-quick-edit-status').val()
        };
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_update_product',
                product_id: productId,
                product_data: data,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
    
    // Order Management
    $(document).on('change', '.digiplanet-order-status', function() {
        var orderId = $(this).data('order-id');
        var newStatus = $(this).val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_update_order_status',
                order_id: orderId,
                status: newStatus,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update status badge
                    var $badge = $('.digiplanet-order-status-badge[data-order-id="' + orderId + '"]');
                    $badge.removeClass().addClass('digiplanet-order-status-badge digiplanet-status-' + newStatus);
                    $badge.text(response.data.status_label);
                }
            }
        });
    });
    
    // File upload handling
    $(document).on('click', '.digiplanet-upload-file', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $input = $button.siblings('input[type="file"]');
        var $preview = $button.siblings('.digiplanet-file-preview');
        
        $input.click();
    });
    
    $(document).on('change', '.digiplanet-file-input', function() {
        var $input = $(this);
        var $preview = $input.siblings('.digiplanet-file-preview');
        var file = $input[0].files[0];
        
        if (file) {
            var reader = new FileReader();
            
            reader.onload = function(e) {
                if (file.type.startsWith('image/')) {
                    $preview.html('<img src="' + e.target.result + '">');
                } else {
                    $preview.html('<i class="dashicons dashicons-media-default"></i><br>' + file.name);
                }
                $preview.addClass('has-file');
            }
            
            reader.readAsDataURL(file);
        }
    });
    
    // Remove file
    $(document).on('click', '.digiplanet-remove-file', function() {
        var $preview = $(this).closest('.digiplanet-file-preview');
        var $input = $preview.siblings('.digiplanet-file-input');
        
        $preview.html('<i class="dashicons dashicons-upload"></i><br>No file selected');
        $preview.removeClass('has-file');
        $input.val('');
    });
    
    // Tab functionality
    $(document).on('click', '.digiplanet-tab', function() {
        var tabId = $(this).data('tab');
        
        // Update active tab
        $('.digiplanet-tab').removeClass('active');
        $(this).addClass('active');
        
        // Show active content
        $('.digiplanet-tab-content').removeClass('active');
        $('#' + tabId).addClass('active');
    });
    
    // License management
    $(document).on('click', '.digiplanet-generate-license', function() {
        var productId = $(this).data('product-id');
        
        if (confirm('Generate new license key for this product?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'digiplanet_generate_license',
                    product_id: productId,
                    nonce: digiplanet_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $('#digiplanet-license-key').val(response.data.license_key);
                    }
                }
            });
        }
    });
    
    // Validate license
    $(document).on('click', '.digiplanet-validate-license', function() {
        var licenseKey = $('#digiplanet-license-key').val();
        
        if (!licenseKey) {
            alert('Please enter a license key.');
            return;
        }
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_validate_license',
                license_key: licenseKey,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('License is valid. Status: ' + response.data.status);
                } else {
                    alert('License is invalid: ' + response.data.message);
                }
            }
        });
    });
    
    // Analytics date range picker
    $(document).on('change', '#digiplanet-analytics-range', function() {
        var range = $(this).val();
        var today = new Date();
        var startDate, endDate;
        
        switch(range) {
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'yesterday':
                startDate = new Date(today.setDate(today.getDate() - 1));
                endDate = startDate;
                break;
            case 'last7':
                startDate = new Date(today.setDate(today.getDate() - 7));
                endDate = new Date();
                break;
            case 'last30':
                startDate = new Date(today.setDate(today.getDate() - 30));
                endDate = new Date();
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
        }
        
        $('#digiplanet-start-date').val(startDate.toISOString().split('T')[0]);
        $('#digiplanet-end-date').val(endDate.toISOString().split('T')[0]);
        
        // Trigger analytics update
        $('#digiplanet-update-analytics').click();
    });
    
    // Update analytics
    $(document).on('click', '#digiplanet-update-analytics', function() {
        var startDate = $('#digiplanet-start-date').val();
        var endDate = $('#digiplanet-end-date').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_get_analytics',
                start_date: startDate,
                end_date: endDate,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update charts and stats
                    updateAnalyticsCharts(response.data);
                }
            }
        });
    });
    
    // Export data
    $(document).on('click', '.digiplanet-export-data', function() {
        var exportType = $(this).data('export-type');
        var filters = {};
        
        // Collect filters based on current page
        if (exportType === 'products') {
            filters.category = $('#filter-category').val();
            filters.status = $('#filter-status').val();
        } else if (exportType === 'orders') {
            filters.status = $('#filter-order-status').val();
            filters.payment_status = $('#filter-payment-status').val();
            filters.date_from = $('#filter-date-from').val();
            filters.date_to = $('#filter-date-to').val();
        }
        
        // Build export URL
        var exportUrl = ajaxurl + '?action=digiplanet_export_' + exportType + '&nonce=' + digiplanet_admin.nonce;
        
        for (var key in filters) {
            if (filters[key]) {
                exportUrl += '&' + key + '=' + encodeURIComponent(filters[key]);
            }
        }
        
        // Trigger download
        window.location.href = exportUrl;
    });
    
    // Import data
    $(document).on('change', '#digiplanet-import-file', function() {
        var file = $(this)[0].files[0];
        var importType = $('#digiplanet-import-type').val();
        
        if (!file) {
            return;
        }
        
        var formData = new FormData();
        formData.append('action', 'digiplanet_import_' + importType);
        formData.append('nonce', digiplanet_admin.nonce);
        formData.append('import_file', file);
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    alert('Import completed: ' + response.data.message);
                    location.reload();
                } else {
                    alert('Import failed: ' + response.data.message);
                }
            }
        });
    });
    
    // Settings tabs
    $(document).on('click', '.nav-tab', function(e) {
        e.preventDefault();
        
        var tab = $(this).attr('href').substring(1);
        
        // Update active tab
        $('.nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        
        // Show active tab content
        $('.digiplanet-settings-tab').hide();
        $('#' + tab).show();
        
        // Update URL
        var url = new URL(window.location);
        url.searchParams.set('tab', tab);
        history.pushState({}, '', url);
    });
    
    // Test email sending
    $(document).on('click', '#digiplanet-test-email', function() {
        var emailType = $('#digiplanet-test-email-type').val();
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_test_email',
                email_type: emailType,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Test email sent successfully.');
                } else {
                    alert('Failed to send test email: ' + response.data.message);
                }
            }
        });
    });
    
    // Payment gateway test
    $(document).on('click', '.digiplanet-test-gateway', function() {
        var gateway = $(this).data('gateway');
        
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'digiplanet_test_payment_gateway',
                gateway: gateway,
                nonce: digiplanet_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Gateway test successful: ' + response.data.message);
                } else {
                    alert('Gateway test failed: ' + response.data.message);
                }
            }
        });
    });
});

// Update analytics charts
function updateAnalyticsCharts(data) {
    // Update summary stats
    $('#digiplanet-total-revenue').text(data.summary.total_revenue);
    $('#digiplanet-total-orders').text(data.summary.total_orders);
    $('#digiplanet-total-products').text(data.summary.total_products);
    $('#digiplanet-conversion-rate').text(data.summary.conversion_rate + '%');
    
    // Update charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        updateRevenueChart(data.charts.revenue);
        updateSalesChart(data.charts.sales);
        updateTopProductsChart(data.charts.top_products);
    }
}