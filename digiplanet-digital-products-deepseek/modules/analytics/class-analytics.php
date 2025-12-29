<?php
/**
 * Analytics Module
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Analytics {
    
    private static $instance = null;
    
    /**
     * Get singleton instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('admin_menu', [$this, 'add_analytics_menu']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widget']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_analytics_assets']);
        add_action('wp_ajax_digiplanet_get_analytics_data', [$this, 'ajax_get_analytics_data']);
        add_action('wp_ajax_digiplanet_export_analytics', [$this, 'ajax_export_analytics']);
    }
    
    /**
     * Add analytics menu
     */
    public function add_analytics_menu() {
        add_submenu_page(
            'digiplanet-dashboard',
            __('Analytics', 'digiplanet-digital-products'),
            __('Analytics', 'digiplanet-digital-products'),
            'digiplanet_view_reports',
            'digiplanet-analytics',
            [$this, 'render_analytics_page']
        );
    }
    
    /**
     * Add dashboard widget
     */
    public function add_dashboard_widget() {
        if (current_user_can('digiplanet_view_reports')) {
            wp_add_dashboard_widget(
                'digiplanet_sales_widget',
                __('Digiplanet Sales Overview', 'digiplanet-digital-products'),
                [$this, 'render_dashboard_widget']
            );
        }
    }
    
    /**
     * Enqueue analytics assets
     */
    public function enqueue_analytics_assets($hook) {
        if (strpos($hook, 'digiplanet-analytics') !== false) {
            // Chart.js
            wp_enqueue_script(
                'chart-js',
                'https://cdn.jsdelivr.net/npm/chart.js',
                [],
                '3.9.1',
                true
            );
            
            // Date Range Picker
            wp_enqueue_style(
                'daterangepicker',
                'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css',
                [],
                '3.1'
            );
            
            wp_enqueue_script(
                'moment-js',
                'https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js',
                [],
                '2.29.4',
                true
            );
            
            wp_enqueue_script(
                'daterangepicker',
                'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js',
                ['jquery', 'moment-js'],
                '3.1',
                true
            );
            
            // Analytics JS
            wp_enqueue_script(
                'digiplanet-analytics',
                DIGIPLANET_PLUGIN_URL . 'modules/analytics/assets/js/analytics.js',
                ['jquery', 'chart-js', 'daterangepicker'],
                DIGIPLANET_VERSION,
                true
            );
            
            wp_localize_script('digiplanet-analytics', 'digiplanet_analytics', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('digiplanet_analytics_nonce'),
                'date_format' => get_option('date_format'),
                'currency' => get_option('digiplanet_currency', 'USD'),
                'currency_symbol' => $this->get_currency_symbol(get_option('digiplanet_currency', 'USD')),
                'date_ranges' => $this->get_date_ranges(),
            ]);
            
            // Analytics CSS
            wp_enqueue_style(
                'digiplanet-analytics',
                DIGIPLANET_PLUGIN_URL . 'modules/analytics/assets/css/analytics.css',
                [],
                DIGIPLANET_VERSION
            );
        }
    }
    
    /**
     * Render analytics page
     */
    public function render_analytics_page() {
        if (!current_user_can('digiplanet_view_reports')) {
            wp_die(__('You do not have permission to access this page.', 'digiplanet-digital-products'));
        }
        
        $current_date = current_time('Y-m-d');
        $start_date = isset($_GET['start_date']) ? sanitize_text_field($_GET['start_date']) : date('Y-m-01', strtotime($current_date));
        $end_date = isset($_GET['end_date']) ? sanitize_text_field($_GET['end_date']) : $current_date;
        $report_type = isset($_GET['report_type']) ? sanitize_text_field($_GET['report_type']) : 'overview';
        
        ?>
        <div class="wrap digiplanet-analytics">
            <h1><?php _e('Sales Analytics', 'digiplanet-digital-products'); ?></h1>
            
            <!-- Date Range Picker -->
            <div class="digiplanet-date-range">
                <div class="digiplanet-date-range-picker">
                    <input type="text" name="date_range" id="digiplanet-date-range" 
                           value="<?php echo esc_attr($start_date . ' - ' . $end_date); ?>">
                </div>
                <div class="digiplanet-quick-dates">
                    <button type="button" class="button" data-range="today"><?php _e('Today', 'digiplanet-digital-products'); ?></button>
                    <button type="button" class="button" data-range="yesterday"><?php _e('Yesterday', 'digiplanet-digital-products'); ?></button>
                    <button type="button" class="button" data-range="last7"><?php _e('Last 7 Days', 'digiplanet-digital-products'); ?></button>
                    <button type="button" class="button" data-range="last30"><?php _e('Last 30 Days', 'digiplanet-digital-products'); ?></button>
                    <button type="button" class="button" data-range="thismonth"><?php _e('This Month', 'digiplanet-digital-products'); ?></button>
                    <button type="button" class="button" data-range="lastmonth"><?php _e('Last Month', 'digiplanet-digital-products'); ?></button>
                </div>
            </div>
            
            <!-- Report Type Navigation -->
            <nav class="digiplanet-report-nav">
                <a href="?page=digiplanet-analytics&report_type=overview&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" 
                   class="<?php echo $report_type === 'overview' ? 'active' : ''; ?>">
                    <?php _e('Overview', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-analytics&report_type=sales&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" 
                   class="<?php echo $report_type === 'sales' ? 'active' : ''; ?>">
                    <?php _e('Sales', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-analytics&report_type=products&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" 
                   class="<?php echo $report_type === 'products' ? 'active' : ''; ?>">
                    <?php _e('Products', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-analytics&report_type=customers&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" 
                   class="<?php echo $report_type === 'customers' ? 'active' : ''; ?>">
                    <?php _e('Customers', 'digiplanet-digital-products'); ?>
                </a>
                <a href="?page=digiplanet-analytics&report_type=licenses&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" 
                   class="<?php echo $report_type === 'licenses' ? 'active' : ''; ?>">
                    <?php _e('Licenses', 'digiplanet-digital-products'); ?>
                </a>
            </nav>
            
            <!-- Stats Overview -->
            <div class="digiplanet-stats-overview">
                <?php $overview_stats = $this->get_overview_stats($start_date, $end_date); ?>
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo $this->format_currency($overview_stats['total_revenue']); ?></h3>
                        <p><?php _e('Total Revenue', 'digiplanet-digital-products'); ?></p>
                        <span class="digiplanet-stat-change <?php echo $overview_stats['revenue_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $overview_stats['revenue_change'] >= 0 ? '+' : ''; ?>
                            <?php echo number_format($overview_stats['revenue_change'], 1); ?>%
                        </span>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <span class="dashicons dashicons-cart"></span>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo esc_html($overview_stats['total_orders']); ?></h3>
                        <p><?php _e('Total Orders', 'digiplanet-digital-products'); ?></p>
                        <span class="digiplanet-stat-change <?php echo $overview_stats['orders_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $overview_stats['orders_change'] >= 0 ? '+' : ''; ?>
                            <?php echo number_format($overview_stats['orders_change'], 1); ?>%
                        </span>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <span class="dashicons dashicons-products"></span>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo esc_html($overview_stats['total_products']); ?></h3>
                        <p><?php _e('Products Sold', 'digiplanet-digital-products'); ?></p>
                        <span class="digiplanet-stat-change <?php echo $overview_stats['products_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $overview_stats['products_change'] >= 0 ? '+' : ''; ?>
                            <?php echo number_format($overview_stats['products_change'], 1); ?>%
                        </span>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <span class="dashicons dashicons-admin-users"></span>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo esc_html($overview_stats['new_customers']); ?></h3>
                        <p><?php _e('New Customers', 'digiplanet-digital-products'); ?></p>
                        <span class="digiplanet-stat-change <?php echo $overview_stats['customers_change'] >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $overview_stats['customers_change'] >= 0 ? '+' : ''; ?>
                            <?php echo number_format($overview_stats['customers_change'], 1); ?>%
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="digiplanet-analytics-main">
                <div class="digiplanet-chart-container">
                    <div class="digiplanet-chart-header">
                        <h3><?php _e('Revenue Overview', 'digiplanet-digital-products'); ?></h3>
                        <div class="digiplanet-chart-actions">
                            <select id="digiplanet-chart-period">
                                <option value="daily"><?php _e('Daily', 'digiplanet-digital-products'); ?></option>
                                <option value="weekly"><?php _e('Weekly', 'digiplanet-digital-products'); ?></option>
                                <option value="monthly"><?php _e('Monthly', 'digiplanet-digital-products'); ?></option>
                            </select>
                            <button type="button" class="button button-secondary" id="digiplanet-export-chart">
                                <?php _e('Export', 'digiplanet-digital-products'); ?>
                            </button>
                        </div>
                    </div>
                    <div class="digiplanet-chart-wrapper">
                        <canvas id="digiplanet-revenue-chart"></canvas>
                    </div>
                </div>
                
                <!-- Report Specific Content -->
                <div class="digiplanet-report-content">
                    <?php
                    switch ($report_type) {
                        case 'sales':
                            $this->render_sales_report($start_date, $end_date);
                            break;
                        case 'products':
                            $this->render_products_report($start_date, $end_date);
                            break;
                        case 'customers':
                            $this->render_customers_report($start_date, $end_date);
                            break;
                        case 'licenses':
                            $this->render_licenses_report($start_date, $end_date);
                            break;
                        default:
                            $this->render_overview_report($start_date, $end_date);
                            break;
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render dashboard widget
     */
    public function render_dashboard_widget() {
        $today = current_time('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $stats_today = $this->get_overview_stats($today, $today);
        $stats_yesterday = $this->get_overview_stats($yesterday, $yesterday);
        
        ?>
        <div class="digiplanet-dashboard-widget">
            <div class="digiplanet-widget-stats">
                <div class="digiplanet-widget-stat">
                    <span class="digiplanet-widget-label"><?php _e('Today\'s Revenue', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-widget-value"><?php echo $this->format_currency($stats_today['total_revenue']); ?></span>
                    <?php if ($stats_yesterday['total_revenue'] > 0): ?>
                        <?php $change = (($stats_today['total_revenue'] - $stats_yesterday['total_revenue']) / $stats_yesterday['total_revenue']) * 100; ?>
                        <span class="digiplanet-widget-change <?php echo $change >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change >= 0 ? '+' : ''; ?>
                            <?php echo number_format($change, 1); ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="digiplanet-widget-stat">
                    <span class="digiplanet-widget-label"><?php _e('Today\'s Orders', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-widget-value"><?php echo esc_html($stats_today['total_orders']); ?></span>
                    <?php if ($stats_yesterday['total_orders'] > 0): ?>
                        <?php $change = (($stats_today['total_orders'] - $stats_yesterday['total_orders']) / $stats_yesterday['total_orders']) * 100; ?>
                        <span class="digiplanet-widget-change <?php echo $change >= 0 ? 'positive' : 'negative'; ?>">
                            <?php echo $change >= 0 ? '+' : ''; ?>
                            <?php echo number_format($change, 1); ?>%
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="digiplanet-widget-stat">
                    <span class="digiplanet-widget-label"><?php _e('Conversion Rate', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-widget-value"><?php echo number_format($this->get_conversion_rate($today, $today), 1); ?>%</span>
                </div>
            </div>
            
            <div class="digiplanet-widget-actions">
                <a href="<?php echo admin_url('admin.php?page=digiplanet-analytics'); ?>" class="button button-primary">
                    <?php _e('View Full Analytics', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo admin_url('admin.php?page=digiplanet-orders'); ?>" class="button">
                    <?php _e('View Orders', 'digiplanet-digital-products'); ?>
                </a>
            </div>
        </div>
        
        <style>
        .digiplanet-dashboard-widget {
            padding: 10px 0;
        }
        
        .digiplanet-widget-stats {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .digiplanet-widget-stat {
            background: #f6f7f7;
            border: 1px solid #dcdcde;
            border-radius: 4px;
            padding: 12px;
        }
        
        .digiplanet-widget-label {
            display: block;
            font-size: 11px;
            color: #646970;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        
        .digiplanet-widget-value {
            display: block;
            font-size: 18px;
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 4px;
        }
        
        .digiplanet-widget-change {
            display: block;
            font-size: 12px;
            font-weight: 500;
        }
        
        .digiplanet-widget-change.positive {
            color: #46b450;
        }
        
        .digiplanet-widget-change.negative {
            color: #dc3232;
        }
        
        .digiplanet-widget-actions {
            display: flex;
            gap: 8px;
        }
        </style>
        <?php
    }
    
    /**
     * Render overview report
     */
    private function render_overview_report($start_date, $end_date) {
        $top_products = $this->get_top_products($start_date, $end_date, 5);
        $top_customers = $this->get_top_customers($start_date, $end_date, 5);
        $recent_orders = $this->get_recent_orders($start_date, $end_date, 5);
        
        ?>
        <div class="digiplanet-report-grid">
            <!-- Top Products -->
            <div class="digiplanet-report-card">
                <div class="digiplanet-report-header">
                    <h3><?php _e('Top Products', 'digiplanet-digital-products'); ?></h3>
                    <a href="?page=digiplanet-analytics&report_type=products&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" class="digiplanet-view-all">
                        <?php _e('View All', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
                <div class="digiplanet-report-body">
                    <?php if (!empty($top_products)): ?>
                        <table class="digiplanet-report-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Sales', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Revenue', 'digiplanet-digital-products'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_products as $product): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=digiplanet-products&action=edit&product_id=' . $product['id']); ?>">
                                                <?php echo esc_html($product['name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo esc_html($product['sales_count']); ?></td>
                                        <td><?php echo $this->format_currency($product['revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No product sales data available.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Top Customers -->
            <div class="digiplanet-report-card">
                <div class="digiplanet-report-header">
                    <h3><?php _e('Top Customers', 'digiplanet-digital-products'); ?></h3>
                    <a href="?page=digiplanet-analytics&report_type=customers&start_date=<?php echo esc_attr($start_date); ?>&end_date=<?php echo esc_attr($end_date); ?>" class="digiplanet-view-all">
                        <?php _e('View All', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
                <div class="digiplanet-report-body">
                    <?php if (!empty($top_customers)): ?>
                        <table class="digiplanet-report-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Customer', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Orders', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Total Spent', 'digiplanet-digital-products'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($top_customers as $customer): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=digiplanet-customers&action=edit&customer_id=' . $customer['id']); ?>">
                                                <?php echo esc_html($customer['name']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo esc_html($customer['order_count']); ?></td>
                                        <td><?php echo $this->format_currency($customer['total_spent']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No customer data available.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Orders -->
            <div class="digiplanet-report-card">
                <div class="digiplanet-report-header">
                    <h3><?php _e('Recent Orders', 'digiplanet-digital-products'); ?></h3>
                    <a href="?page=digiplanet-orders" class="digiplanet-view-all">
                        <?php _e('View All', 'digiplanet-digital-products'); ?>
                    </a>
                </div>
                <div class="digiplanet-report-body">
                    <?php if (!empty($recent_orders)): ?>
                        <table class="digiplanet-report-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Order', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Customer', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Amount', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_orders as $order): ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('admin.php?page=digiplanet-orders&action=view&order_id=' . $order['id']); ?>">
                                                #<?php echo esc_html($order['order_number']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo esc_html($order['customer_name']); ?></td>
                                        <td><?php echo $this->format_currency($order['total_amount']); ?></td>
                                        <td>
                                            <span class="digiplanet-status-badge digiplanet-status-<?php echo esc_attr($order['status']); ?>">
                                                <?php echo ucfirst($order['status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No recent orders found.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Render sales report
     */
    private function render_sales_report($start_date, $end_date) {
        $sales_data = $this->get_sales_data($start_date, $end_date);
        $payment_methods = $this->get_payment_methods_data($start_date, $end_date);
        
        ?>
        <div class="digiplanet-report-grid">
            <!-- Sales Data -->
            <div class="digiplanet-report-card digiplanet-full-width">
                <div class="digiplanet-report-header">
                    <h3><?php _e('Sales Data', 'digiplanet-digital-products'); ?></h3>
                </div>
                <div class="digiplanet-report-body">
                    <?php if (!empty($sales_data)): ?>
                        <table class="digiplanet-report-table digiplanet-data-table">
                            <thead>
                                <tr>
                                    <th><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Orders', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Revenue', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Average Order Value', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('Products Sold', 'digiplanet-digital-products'); ?></th>
                                    <th><?php _e('New Customers', 'digiplanet-digital-products'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($sales_data as $data): ?>
                                    <tr>
                                        <td><?php echo date_i18n(get_option('date_format'), strtotime($data['date'])); ?></td>
                                        <td><?php echo esc_html($data['orders']); ?></td>
                                        <td><?php echo $this->format_currency($data['revenue']); ?></td>
                                        <td><?php echo $this->format_currency($data['avg_order_value']); ?></td>
                                        <td><?php echo esc_html($data['products_sold']); ?></td>
                                        <td><?php echo esc_html($data['new_customers']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th><?php _e('Total', 'digiplanet-digital-products'); ?></th>
                                    <th><?php echo array_sum(array_column($sales_data, 'orders')); ?></th>
                                    <th><?php echo $this->format_currency(array_sum(array_column($sales_data, 'revenue'))); ?></th>
                                    <th><?php echo $this->format_currency($this->calculate_average(array_column($sales_data, 'avg_order_value'))); ?></th>
                                    <th><?php echo array_sum(array_column($sales_data, 'products_sold')); ?></th>
                                    <th><?php echo array_sum(array_column($sales_data, 'new_customers')); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    <?php else: ?>
                        <p><?php _e('No sales data available for the selected period.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Payment Methods -->
            <div class="digiplanet-report-card">
                <div class="digiplanet-report-header">
                    <h3><?php _e('Payment Methods', 'digiplanet-digital-products'); ?></h3>
                </div>
                <div class="digiplanet-report-body">
                    <?php if (!empty($payment_methods)): ?>
                        <div class="digiplanet-chart-wrapper">
                            <canvas id="digiplanet-payment-methods-chart"></canvas>
                        </div>
                        <script>
                        jQuery(document).ready(function($) {
                            var ctx = document.getElementById('digiplanet-payment-methods-chart').getContext('2d');
                            var chartData = {
                                labels: <?php echo json_encode(array_column($payment_methods, 'method')); ?>,
                                datasets: [{
                                    data: <?php echo json_encode(array_column($payment_methods, 'count')); ?>,
                                    backgroundColor: [
                                        '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', 
                                        '#9966FF', '#FF9F40', '#C9CBCF', '#FF6384'
                                    ]
                                }]
                            };
                            
                            new Chart(ctx, {
                                type: 'pie',
                                data: chartData,
                                options: {
                                    responsive: true,
                                    plugins: {
                                        legend: {
                                            position: 'right',
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    var label = context.label || '';
                                                    var value = context.raw || 0;
                                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                                    var percentage = Math.round((value / total) * 100);
                                                    return label + ': ' + value + ' (' + percentage + '%)';
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        });
                        </script>
                    <?php else: ?>
                        <p><?php _e('No payment method data available.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get overview stats
     */
    public function get_overview_stats($start_date, $end_date) {
        global $wpdb;
        
        // Previous period for comparison
        $days_diff = (strtotime($end_date) - strtotime($start_date)) / DAY_IN_SECONDS;
        $prev_start_date = date('Y-m-d', strtotime($start_date . ' -' . ($days_diff + 1) . ' days'));
        $prev_end_date = date('Y-m-d', strtotime($start_date . ' -1 day'));
        
        $stats = [
            'total_revenue' => 0,
            'total_orders' => 0,
            'total_products' => 0,
            'new_customers' => 0,
            'revenue_change' => 0,
            'orders_change' => 0,
            'products_change' => 0,
            'customers_change' => 0,
        ];
        
        // Current period stats
        $current_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COUNT(*) as total_orders,
                COALESCE(SUM(
                    SELECT COUNT(*) 
                    FROM {$wpdb->prefix}digiplanet_order_items oi 
                    WHERE oi.order_id = o.id
                ), 0) as total_products
            FROM {$wpdb->prefix}digiplanet_orders o
            WHERE DATE(o.created_at) BETWEEN %s AND %s
            AND o.payment_status = 'completed'
        ", $start_date, $end_date));
        
        if ($current_stats) {
            $stats['total_revenue'] = floatval($current_stats->total_revenue);
            $stats['total_orders'] = intval($current_stats->total_orders);
            $stats['total_products'] = intval($current_stats->total_products);
        }
        
        // New customers in current period
        $new_customers = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT customer_id)
            FROM {$wpdb->prefix}digiplanet_orders
            WHERE DATE(created_at) BETWEEN %s AND %s
            AND payment_status = 'completed'
        ", $start_date, $end_date));
        
        $stats['new_customers'] = intval($new_customers);
        
        // Previous period stats for comparison
        $prev_stats = $wpdb->get_row($wpdb->prepare("
            SELECT 
                COALESCE(SUM(total_amount), 0) as total_revenue,
                COUNT(*) as total_orders,
                COALESCE(SUM(
                    SELECT COUNT(*) 
                    FROM {$wpdb->prefix}digiplanet_order_items oi 
                    WHERE oi.order_id = o.id
                ), 0) as total_products
            FROM {$wpdb->prefix}digiplanet_orders o
            WHERE DATE(o.created_at) BETWEEN %s AND %s
            AND o.payment_status = 'completed'
        ", $prev_start_date, $prev_end_date));
        
        $prev_new_customers = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(DISTINCT customer_id)
            FROM {$wpdb->prefix}digiplanet_orders
            WHERE DATE(created_at) BETWEEN %s AND %s
            AND payment_status = 'completed'
        ", $prev_start_date, $prev_end_date));
        
        // Calculate percentage changes
        if ($prev_stats) {
            if ($prev_stats->total_revenue > 0) {
                $stats['revenue_change'] = (($stats['total_revenue'] - floatval($prev_stats->total_revenue)) / floatval($prev_stats->total_revenue)) * 100;
            }
            
            if ($prev_stats->total_orders > 0) {
                $stats['orders_change'] = (($stats['total_orders'] - intval($prev_stats->total_orders)) / intval($prev_stats->total_orders)) * 100;
            }
            
            if ($prev_stats->total_products > 0) {
                $stats['products_change'] = (($stats['total_products'] - intval($prev_stats->total_products)) / intval($prev_stats->total_products)) * 100;
            }
        }
        
        if ($prev_new_customers > 0) {
            $stats['customers_change'] = (($stats['new_customers'] - intval($prev_new_customers)) / intval($prev_new_customers)) * 100;
        }
        
        return $stats;
    }
    
    /**
     * Get top products
     */
    private function get_top_products($start_date, $end_date, $limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                p.id,
                p.name,
                COUNT(oi.id) as sales_count,
                SUM(oi.product_price * oi.quantity) as revenue
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON oi.product_id = p.id
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE DATE(o.created_at) BETWEEN %s AND %s
            AND o.payment_status = 'completed'
            GROUP BY p.id, p.name
            ORDER BY revenue DESC
            LIMIT %d
        ", $start_date, $end_date, $limit), ARRAY_A);
    }
    
    /**
     * Get conversion rate
     */
    private function get_conversion_rate($start_date, $end_date) {
        global $wpdb;
        
        // Get total visits (this would need integration with analytics)
        $total_visits = 0; // Placeholder
        
        $completed_orders = $wpdb->get_var($wpdb->prepare("
            SELECT COUNT(*)
            FROM {$wpdb->prefix}digiplanet_orders
            WHERE DATE(created_at) BETWEEN %s AND %s
            AND payment_status = 'completed'
        ", $start_date, $end_date));
        
        if ($total_visits > 0) {
            return ($completed_orders / $total_visits) * 100;
        }
        
        return 0;
    }
    
    /**
     * Format currency
     */
    private function format_currency($amount) {
        $currency = get_option('digiplanet_currency', 'USD');
        $position = get_option('digiplanet_currency_position', 'left');
        $symbol = $this->get_currency_symbol($currency);
        
        $formatted = number_format(
            floatval($amount),
            get_option('digiplanet_decimal_places', 2),
            get_option('digiplanet_decimal_separator', '.'),
            get_option('digiplanet_thousand_separator', ',')
        );
        
        switch ($position) {
            case 'left':
                return $symbol . $formatted;
            case 'right':
                return $formatted . $symbol;
            case 'left_space':
                return $symbol . ' ' . $formatted;
            case 'right_space':
                return $formatted . ' ' . $symbol;
            default:
                return $symbol . $formatted;
        }
    }
    
    /**
     * Get currency symbol
     */
    private function get_currency_symbol($currency) {
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'NGN' => '₦',
            'CAD' => 'C$',
            'AUD' => 'A$',
        ];
        
        return $symbols[$currency] ?? $currency;
    }
    
    /**
     * Get date ranges
     */
    private function get_date_ranges() {
        return [
            'today' => [
                'start' => current_time('Y-m-d'),
                'end' => current_time('Y-m-d'),
            ],
            'yesterday' => [
                'start' => date('Y-m-d', strtotime('-1 day')),
                'end' => date('Y-m-d', strtotime('-1 day')),
            ],
            'last7' => [
                'start' => date('Y-m-d', strtotime('-6 days')),
                'end' => current_time('Y-m-d'),
            ],
            'last30' => [
                'start' => date('Y-m-d', strtotime('-29 days')),
                'end' => current_time('Y-m-d'),
            ],
            'thismonth' => [
                'start' => date('Y-m-01'),
                'end' => date('Y-m-t'),
            ],
            'lastmonth' => [
                'start' => date('Y-m-01', strtotime('-1 month')),
                'end' => date('Y-m-t', strtotime('-1 month')),
            ],
        ];
    }
    
    /**
     * AJAX: Get analytics data
     */
    public function ajax_get_analytics_data() {
        check_ajax_referer('digiplanet_analytics_nonce', 'nonce');
        
        if (!current_user_can('digiplanet_view_reports')) {
            wp_send_json_error(['message' => __('Unauthorized.', 'digiplanet-digital-products')]);
        }
        
        $period = sanitize_text_field($_POST['period'] ?? 'daily');
        $start_date = sanitize_text_field($_POST['start_date'] ?? date('Y-m-01'));
        $end_date = sanitize_text_field($_POST['end_date'] ?? date('Y-m-d'));
        
        $data = $this->get_chart_data($period, $start_date, $end_date);
        
        wp_send_json_success($data);
    }
    
    /**
     * Get chart data
     */
    private function get_chart_data($period, $start_date, $end_date) {
        global $wpdb;
        
        switch ($period) {
            case 'weekly':
                $date_format = '%Y-%u';
                $group_by = 'YEARWEEK(o.created_at)';
                break;
            case 'monthly':
                $date_format = '%Y-%m';
                $group_by = 'DATE_FORMAT(o.created_at, "%Y-%m")';
                break;
            default:
                $date_format = '%Y-%m-%d';
                $group_by = 'DATE(o.created_at)';
                break;
        }
        
        $results = $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE_FORMAT(o.created_at, %s) as period,
                COUNT(*) as orders,
                SUM(o.total_amount) as revenue,
                AVG(o.total_amount) as avg_order_value
            FROM {$wpdb->prefix}digiplanet_orders o
            WHERE DATE(o.created_at) BETWEEN %s AND %s
            AND o.payment_status = 'completed'
            GROUP BY {$group_by}
            ORDER BY o.created_at ASC
        ", $date_format, $start_date, $end_date));
        
        $labels = [];
        $orders_data = [];
        $revenue_data = [];
        $avg_order_data = [];
        
        foreach ($results as $row) {
            $labels[] = $row->period;
            $orders_data[] = intval($row->orders);
            $revenue_data[] = floatval($row->revenue);
            $avg_order_data[] = floatval($row->avg_order_value);
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => __('Revenue', 'digiplanet-digital-products'),
                    'data' => $revenue_data,
                    'borderColor' => '#2271b1',
                    'backgroundColor' => 'rgba(34, 113, 177, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y',
                ],
                [
                    'label' => __('Orders', 'digiplanet-digital-products'),
                    'data' => $orders_data,
                    'borderColor' => '#46b450',
                    'backgroundColor' => 'rgba(70, 180, 80, 0.1)',
                    'fill' => true,
                    'yAxisID' => 'y1',
                ],
            ],
        ];
    }
}