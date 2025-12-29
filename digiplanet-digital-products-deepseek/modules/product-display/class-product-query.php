<?php
/**
 * Product Query Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Query {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get products with filters
     */
    public function get_products($args = []) {
        $defaults = [
            'post_type' => 'digiplanet_product',
            'post_status' => 'publish',
            'posts_per_page' => 12,
            'paged' => 1,
            'orderby' => 'date',
            'order' => 'DESC',
            'tax_query' => [],
            'meta_query' => [],
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        // Handle search
        if (!empty($args['search'])) {
            $args['s'] = sanitize_text_field($args['search']);
        }
        
        // Handle category filter
        if (!empty($args['category'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_category',
                'field' => is_numeric($args['category']) ? 'term_id' : 'slug',
                'terms' => $args['category'],
            ];
        }
        
        // Handle tag filter
        if (!empty($args['tag'])) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_tag',
                'field' => is_numeric($args['tag']) ? 'term_id' : 'slug',
                'terms' => $args['tag'],
            ];
        }
        
        // Handle featured filter
        if (!empty($args['featured'])) {
            $args['meta_query'][] = [
                'key' => '_digiplanet_featured',
                'value' => '1',
                'compare' => '=',
            ];
        }
        
        // Handle on sale filter
        if (!empty($args['on_sale'])) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key' => '_digiplanet_sale_price',
                    'value' => 0,
                    'compare' => '>',
                    'type' => 'NUMERIC',
                ],
                [
                    'key' => '_digiplanet_sale_price',
                    'value' => '',
                    'compare' => '!=',
                ],
            ];
        }
        
        // Handle price range filter
        if (!empty($args['min_price']) || !empty($args['max_price'])) {
            $price_query = ['relation' => 'AND'];
            
            if (!empty($args['min_price'])) {
                $price_query[] = [
                    'key' => '_digiplanet_price',
                    'value' => floatval($args['min_price']),
                    'compare' => '>=',
                    'type' => 'DECIMAL',
                ];
            }
            
            if (!empty($args['max_price'])) {
                $price_query[] = [
                    'key' => '_digiplanet_price',
                    'value' => floatval($args['max_price']),
                    'compare' => '<=',
                    'type' => 'DECIMAL',
                ];
            }
            
            $args['meta_query'][] = $price_query;
        }
        
        // Handle rating filter
        if (!empty($args['min_rating'])) {
            $args['meta_query'][] = [
                'key' => '_digiplanet_rating',
                'value' => floatval($args['min_rating']),
                'compare' => '>=',
                'type' => 'DECIMAL',
            ];
        }
        
        // Handle specific orderby
        switch ($args['orderby']) {
            case 'price':
                $args['meta_key'] = '_digiplanet_price';
                $args['orderby'] = 'meta_value_num';
                break;
                
            case 'sales':
                $args['meta_key'] = '_digiplanet_sales_count';
                $args['orderby'] = 'meta_value_num';
                break;
                
            case 'rating':
                $args['meta_key'] = '_digiplanet_rating';
                $args['orderby'] = 'meta_value_num';
                break;
                
            case 'date':
                $args['orderby'] = 'date';
                break;
                
            case 'title':
                $args['orderby'] = 'title';
                break;
                
            case 'rand':
                $args['orderby'] = 'rand';
                break;
        }
        
        // Handle multiple tax queries
        if (count($args['tax_query']) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
        
        // Handle multiple meta queries
        if (count($args['meta_query']) > 1) {
            $args['meta_query']['relation'] = 'AND';
        }
        
        return new WP_Query($args);
    }
    
    /**
     * Get products by category
     */
    public function get_products_by_category($category_id, $limit = 12) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'category' => $category_id,
        ]);
    }
    
    /**
     * Get featured products
     */
    public function get_featured_products($limit = 6) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'featured' => true,
            'orderby' => 'rand',
        ]);
    }
    
    /**
     * Get best selling products
     */
    public function get_best_selling_products($limit = 6) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'orderby' => 'sales',
            'order' => 'DESC',
        ]);
    }
    
    /**
     * Get top rated products
     */
    public function get_top_rated_products($limit = 6) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'orderby' => 'rating',
            'order' => 'DESC',
        ]);
    }
    
    /**
     * Get on sale products
     */
    public function get_on_sale_products($limit = 6) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'on_sale' => true,
            'orderby' => 'rand',
        ]);
    }
    
    /**
     * Get recent products
     */
    public function get_recent_products($limit = 6) {
        return $this->get_products([
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);
    }
    
    /**
     * Get related products
     */
    public function get_related_products($product_id, $limit = 4) {
        $product = get_post($product_id);
        if (!$product) {
            return false;
        }
        
        $categories = wp_get_post_terms($product_id, 'digiplanet_category', ['fields' => 'ids']);
        $tags = wp_get_post_terms($product_id, 'digiplanet_tag', ['fields' => 'ids']);
        
        $args = [
            'posts_per_page' => $limit,
            'post__not_in' => [$product_id],
            'orderby' => 'rand',
        ];
        
        if (!empty($categories)) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_category',
                'field' => 'term_id',
                'terms' => $categories,
            ];
        }
        
        if (!empty($tags)) {
            $args['tax_query'][] = [
                'taxonomy' => 'digiplanet_tag',
                'field' => 'term_id',
                'terms' => $tags,
            ];
        }
        
        // If no categories or tags, get random products
        if (empty($categories) && empty($tags)) {
            $args['orderby'] = 'rand';
        }
        
        return $this->get_products($args);
    }
    
    /**
     * Get product categories
     */
    public function get_categories($args = []) {
        $defaults = [
            'taxonomy' => 'digiplanet_category',
            'hide_empty' => true,
            'orderby' => 'name',
            'order' => 'ASC',
            'parent' => 0,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        return get_terms($args);
    }
    
    /**
     * Get category with product count
     */
    public function get_category_with_count($category_id) {
        $category = get_term($category_id, 'digiplanet_category');
        if (!$category || is_wp_error($category)) {
            return false;
        }
        
        $category->product_count = $category->count;
        $category->image_id = get_term_meta($category_id, 'digiplanet_category_image', true);
        
        return $category;
    }
    
    /**
     * Get product tags
     */
    public function get_tags($args = []) {
        $defaults = [
            'taxonomy' => 'digiplanet_tag',
            'hide_empty' => true,
            'orderby' => 'count',
            'order' => 'DESC',
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        return get_terms($args);
    }
    
    /**
     * Search products
     */
    public function search_products($search_term, $args = []) {
        $defaults = [
            'posts_per_page' => 12,
            'paged' => 1,
            'search' => $search_term,
        ];
        
        $args = wp_parse_args($args, $defaults);
        
        return $this->get_products($args);
    }
    
    /**
     * Get filtered products count
     */
    public function get_filtered_count($args = []) {
        $args['posts_per_page'] = -1;
        $args['fields'] = 'ids';
        
        $query = $this->get_products($args);
        
        return $query->found_posts;
    }
    
    /**
     * Get price statistics
     */
    public function get_price_stats() {
        global $wpdb;
        
        $stats = $wpdb->get_row("
            SELECT 
                MIN(CAST(meta_value AS DECIMAL(10,2))) as min_price,
                MAX(CAST(meta_value AS DECIMAL(10,2))) as max_price,
                AVG(CAST(meta_value AS DECIMAL(10,2))) as avg_price
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type = 'digiplanet_product'
            AND p.post_status = 'publish'
            AND pm.meta_key = '_digiplanet_price'
            AND pm.meta_value != ''
        ");
        
        return [
            'min' => floatval($stats->min_price ?: 0),
            'max' => floatval($stats->max_price ?: 0),
            'avg' => floatval($stats->avg_price ?: 0),
        ];
    }
    
    /**
     * Get popular search terms
     */
    public function get_popular_search_terms($limit = 10) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT search_term, COUNT(*) as search_count
            FROM {$wpdb->prefix}digiplanet_search_logs
            WHERE searched_at > DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY search_term
            ORDER BY search_count DESC
            LIMIT %d
        ", $limit));
    }
    
    /**
     * Log search term
     */
    public function log_search($search_term, $user_id = 0, $ip_address = '') {
        global $wpdb;
        
        if (empty($search_term)) {
            return;
        }
        
        $wpdb->insert(
            $wpdb->prefix . 'digiplanet_search_logs',
            [
                'search_term' => sanitize_text_field($search_term),
                'user_id' => $user_id,
                'ip_address' => $ip_address ?: $_SERVER['REMOTE_ADDR'],
                'searched_at' => current_time('mysql'),
            ]
        );
    }
    
    /**
     * Get recently viewed products
     */
    public function get_recently_viewed($user_id = 0, $limit = 10) {
        $viewed_products = [];
        
        if ($user_id) {
            // Get from user meta
            $viewed = get_user_meta($user_id, 'digiplanet_recently_viewed', true);
            if (is_array($viewed)) {
                $viewed_products = array_slice(array_reverse($viewed), 0, $limit);
            }
        } else {
            // Get from cookies
            $cookie_name = 'digiplanet_recently_viewed';
            if (isset($_COOKIE[$cookie_name])) {
                $viewed = json_decode(stripslashes($_COOKIE[$cookie_name]), true);
                if (is_array($viewed)) {
                    $viewed_products = array_slice(array_reverse($viewed), 0, $limit);
                }
            }
        }
        
        if (empty($viewed_products)) {
            return false;
        }
        
        return $this->get_products([
            'posts_per_page' => $limit,
            'post__in' => $viewed_products,
            'orderby' => 'post__in',
        ]);
    }
    
    /**
     * Add product to recently viewed
     */
    public function add_to_recently_viewed($product_id, $user_id = 0) {
        $product_id = absint($product_id);
        
        if ($user_id) {
            // Store in user meta
            $viewed = get_user_meta($user_id, 'digiplanet_recently_viewed', true);
            if (!is_array($viewed)) {
                $viewed = [];
            }
            
            // Remove if already exists
            $key = array_search($product_id, $viewed);
            if ($key !== false) {
                unset($viewed[$key]);
            }
            
            // Add to beginning
            array_unshift($viewed, $product_id);
            
            // Keep only last 50
            $viewed = array_slice($viewed, 0, 50);
            
            update_user_meta($user_id, 'digiplanet_recently_viewed', $viewed);
        } else {
            // Store in cookie
            $cookie_name = 'digiplanet_recently_viewed';
            $viewed = [];
            
            if (isset($_COOKIE[$cookie_name])) {
                $viewed = json_decode(stripslashes($_COOKIE[$cookie_name]), true);
                if (!is_array($viewed)) {
                    $viewed = [];
                }
            }
            
            // Remove if already exists
            $key = array_search($product_id, $viewed);
            if ($key !== false) {
                unset($viewed[$key]);
            }
            
            // Add to beginning
            array_unshift($viewed, $product_id);
            
            // Keep only last 20
            $viewed = array_slice($viewed, 0, 20);
            
            setcookie($cookie_name, json_encode($viewed), time() + (30 * DAY_IN_SECONDS), COOKIEPATH, COOKIE_DOMAIN);
        }
    }
    
    /**
     * Clear recently viewed
     */
    public function clear_recently_viewed($user_id = 0) {
        if ($user_id) {
            delete_user_meta($user_id, 'digiplanet_recently_viewed');
        } else {
            $cookie_name = 'digiplanet_recently_viewed';
            setcookie($cookie_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN);
        }
    }
    
    /**
     * Get product sales data
     */
    public function get_product_sales_data($product_id, $period = '30days') {
        global $wpdb;
        
        $date_format = '';
        switch ($period) {
            case '7days':
                $date_where = 'DATE(oi.created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)';
                $date_format = '%Y-%m-%d';
                break;
                
            case '30days':
                $date_where = 'DATE(oi.created_at) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)';
                $date_format = '%Y-%m-%d';
                break;
                
            case '12months':
                $date_where = 'DATE(oi.created_at) >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)';
                $date_format = '%Y-%m';
                break;
                
            default:
                $date_where = '1=1';
                $date_format = '%Y-%m-%d';
        }
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT 
                DATE_FORMAT(oi.created_at, %s) as date,
                COUNT(*) as sales_count,
                SUM(oi.subtotal) as revenue
            FROM {$wpdb->prefix}digiplanet_order_items oi
            INNER JOIN {$wpdb->prefix}digiplanet_orders o ON oi.order_id = o.id
            WHERE oi.product_id = %d
            AND o.payment_status = 'completed'
            AND {$date_where}
            GROUP BY DATE_FORMAT(oi.created_at, %s)
            ORDER BY oi.created_at ASC
        ", $date_format, $product_id, $date_format));
    }
}