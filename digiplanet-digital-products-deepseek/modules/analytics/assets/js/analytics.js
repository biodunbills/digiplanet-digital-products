/**
 * Analytics Module JavaScript
 */

jQuery(document).ready(function($) {
    
    // Initialize Date Range Picker
    function initDateRangePicker() {
        var $dateRange = $('#digiplanet-date-range');
        
        if ($dateRange.length) {
            $dateRange.daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'YYYY-MM-DD',
                    cancelLabel: digiplanet_analytics.clear_text || 'Clear',
                    applyLabel: digiplanet_analytics.apply_text || 'Apply',
                    fromLabel: digiplanet_analytics.from_text || 'From',
                    toLabel: digiplanet_analytics.to_text || 'To',
                    customRangeLabel: digiplanet_analytics.custom_range_text || 'Custom Range',
                    weekLabel: 'W',
                    daysOfWeek: [
                        digiplanet_analytics.sunday || 'Su',
                        digiplanet_analytics.monday || 'Mo',
                        digiplanet_analytics.tuesday || 'Tu',
                        digiplanet_analytics.wednesday || 'We',
                        digiplanet_analytics.thursday || 'Th',
                        digiplanet_analytics.friday || 'Fr',
                        digiplanet_analytics.saturday || 'Sa'
                    ],
                    monthNames: [
                        digiplanet_analytics.january || 'January',
                        digiplanet_analytics.february || 'February',
                        digiplanet_analytics.march || 'March',
                        digiplanet_analytics.april || 'April',
                        digiplanet_analytics.may || 'May',
                        digiplanet_analytics.june || 'June',
                        digiplanet_analytics.july || 'July',
                        digiplanet_analytics.august || 'August',
                        digiplanet_analytics.september || 'September',
                        digiplanet_analytics.october || 'October',
                        digiplanet_analytics.november || 'November',
                        digiplanet_analytics.december || 'December'
                    ]
                },
                ranges: digiplanet_analytics.date_ranges || {}
            });
            
            $dateRange.on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');
                
                // Update URL with new date range
                var url = new URL(window.location.href);
                url.searchParams.set('start_date', startDate);
                url.searchParams.set('end_date', endDate);
                window.location.href = url.toString();
            });
        }
    }
    
    // Initialize Revenue Chart
    function initRevenueChart() {
        var $canvas = $('#digiplanet-revenue-chart');
        
        if ($canvas.length && typeof Chart !== 'undefined') {
            var ctx = $canvas[0].getContext('2d');
            
            // Get current date range from URL
            var urlParams = new URLSearchParams(window.location.search);
            var startDate = urlParams.get('start_date') || moment().subtract(30, 'days').format('YYYY-MM-DD');
            var endDate = urlParams.get('end_date') || moment().format('YYYY-MM-DD');
            var period = $('#digiplanet-chart-period').val() || 'daily';
            
            // Load chart data via AJAX
            loadChartData(period, startDate, endDate);
            
            // Period selector change event
            $('#digiplanet-chart-period').on('change', function() {
                period = $(this).val();
                loadChartData(period, startDate, endDate);
            });
        }
    }
    
    // Load chart data via AJAX
    function loadChartData(period, startDate, endDate) {
        $.ajax({
            url: digiplanet_analytics.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_get_analytics_data',
                nonce: digiplanet_analytics.nonce,
                period: period,
                start_date: startDate,
                end_date: endDate
            },
            beforeSend: function() {
                $('#digiplanet-revenue-chart').closest('.digiplanet-chart-wrapper').addClass('digiplanet-loading');
            },
            success: function(response) {
                if (response.success && response.data) {
                    renderRevenueChart(response.data);
                } else {
                    console.error('Failed to load chart data:', response);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            },
            complete: function() {
                $('#digiplanet-revenue-chart').closest('.digiplanet-chart-wrapper').removeClass('digiplanet-loading');
            }
        });
    }
    
    // Render revenue chart
    function renderRevenueChart(chartData) {
        var ctx = document.getElementById('digiplanet-revenue-chart').getContext('2d');
        
        // Destroy existing chart instance
        if (window.digiplanetRevenueChart instanceof Chart) {
            window.digiplanetRevenueChart.destroy();
        }
        
        window.digiplanetRevenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: chartData.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false,
                        callbacks: {
                            label: function(context) {
                                var label = context.dataset.label || '';
                                var value = context.parsed.y;
                                
                                if (context.dataset.yAxisID === 'y') {
                                    // Revenue
                                    return label + ': ' + formatCurrency(value);
                                } else {
                                    // Orders
                                    return label + ': ' + value;
                                }
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        title: {
                            display: true,
                            text: digiplanet_analytics.currency || 'USD'
                        },
                        ticks: {
                            callback: function(value) {
                                return formatCurrency(value);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: digiplanet_analytics.orders_text || 'Orders'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return value;
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Format currency
    function formatCurrency(amount) {
        var currency = digiplanet_analytics.currency || 'USD';
        var symbol = digiplanet_analytics.currency_symbol || '$';
        var decimalSeparator = '.';
        var thousandSeparator = ',';
        var decimalPlaces = 2;
        
        // Format number
        var formatted = parseFloat(amount).toFixed(decimalPlaces);
        var parts = formatted.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, thousandSeparator);
        
        return symbol + parts.join(decimalSeparator);
    }
    
    // Quick date buttons
    function initQuickDateButtons() {
        $('.digiplanet-quick-dates .button').on('click', function() {
            var range = $(this).data('range');
            var dateRanges = digiplanet_analytics.date_ranges;
            
            if (dateRanges && dateRanges[range]) {
                var url = new URL(window.location.href);
                url.searchParams.set('start_date', dateRanges[range].start);
                url.searchParams.set('end_date', dateRanges[range].end);
                window.location.href = url.toString();
            }
        });
    }
    
    // Export chart
    function initExportButton() {
        $('#digiplanet-export-chart').on('click', function() {
            if (window.digiplanetRevenueChart instanceof Chart) {
                var link = document.createElement('a');
                link.download = 'digiplanet-revenue-chart-' + moment().format('YYYY-MM-DD') + '.png';
                link.href = window.digiplanetRevenueChart.toBase64Image();
                link.click();
            }
        });
    }
    
    // Export analytics data
    function initExportAnalytics() {
        $('.digiplanet-export-analytics').on('click', function(e) {
            e.preventDefault();
            
            var reportType = $(this).data('report-type');
            var startDate = $(this).data('start-date');
            var endDate = $(this).data('end-date');
            
            // Create form for export
            var form = $('<form>', {
                method: 'POST',
                action: digiplanet_analytics.ajax_url,
                target: '_blank'
            });
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'action',
                value: 'digiplanet_export_analytics'
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'nonce',
                value: digiplanet_analytics.nonce
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'report_type',
                value: reportType
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'start_date',
                value: startDate
            }));
            
            form.append($('<input>', {
                type: 'hidden',
                name: 'end_date',
                value: endDate
            }));
            
            $('body').append(form);
            form.submit();
            form.remove();
        });
    }
    
    // Initialize all components
    function init() {
        initDateRangePicker();
        initRevenueChart();
        initQuickDateButtons();
        initExportButton();
        initExportAnalytics();
    }
    
    // Initialize when document is ready
    init();
});