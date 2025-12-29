<?php
/**
 * Product management class
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Product_Manager {
    
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
     * Get product by ID
     */
    public function get_product($product_id) {
        global $wpdb;
        
        $product = $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_products 
            WHERE id = %d AND status = 'published'
        ", $product_id));
        
        if ($product) {
            $product->categories = $this->get_product_categories($product_id);
            $product->tags = $this->get_product_tags($product_id);
            $product->gallery = $this->get_product_gallery($product_id);
            $product->requirements = $product->requirements ? unserialize($product->requirements) : [];
            $product->tags = $product->tags ? unserialize($product->tags) : [];
        }
        
        return $product;
    }
    
    /**
     * Get products by category
     */
    public function get_products_by_category($category_id, $limit = 12, $offset = 0) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_products 
            WHERE category_id = %d AND status = 'published'
            ORDER BY created_at DESC
            LIMIT %d OFFSET %d
        ", $category_id, $limit, $offset));
    }
    
    /**
     * Get featured products
     */
    public function get_featured_products($limit = 8) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdp->prefix}digiplanet_products 
            WHERE status = 'published' 
            ORDER BY sales_count DESC, rating DESC
            LIMIT %d
        ", $limit));
    }
    
    /**
     * Get products on sale
     */
    public function get_products_on_sale($limit = 8) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_products 
            WHERE status = 'published' AND sale_price IS NOT NULL
            ORDER BY created_at DESC
            LIMIT %d
        ", $limit));
    }
    
    /**
     * Search products
     */
    public function search_products($query, $limit = 20) {
        global $wpdb;
        
        $search_query = '%' . $wpdb->esc_like($query) . '%';
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_products 
            WHERE (name LIKE %s OR description LIKE %s OR short_description LIKE %s)
            AND status = 'published'
            ORDER BY 
                CASE 
                    WHEN name LIKE %s THEN 1
                    WHEN description LIKE %s THEN 2
                    ELSE 3
                END
            LIMIT %d
        ", $search_query, $search_query, $search_query, $search_query, $search_query, $limit));
    }
    
    /**
     * Get product categories
     */
    public function get_categories($parent_id = 0) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_categories 
            WHERE parent_id = %d AND status = 'active'
            ORDER BY sort_order, name
        ", $parent_id));
    }
    
    /**
     * Get all categories with hierarchy
     */
    public function get_categories_hierarchical() {
        $categories = $this->get_categories();
        $result = [];
        
        foreach ($categories as $category) {
            $category->children = $this->get_categories($category->id);
            $result[] = $category;
        }
        
        return $result;
    }
    
    /**
     * Get product categories
     */
    private function get_product_categories($product_id) {
        global $wpdb;
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT c.* FROM {$wpdb->prefix}digiplanet_categories c
            INNER JOIN {$wpdb->prefix}digiplanet_products p ON p.category_id = c.id
            WHERE p.id = %d
        ", $product_id));
    }
    
    /**
     * Get product tags
     */
    private function get_product_tags($product_id) {
        global $wpdb;
        
        $tags = $wpdb->get_var($wpdb->prepare("
            SELECT tags FROM {$wpdb->prefix}digiplanet_products 
            WHERE id = %d
        ", $product_id));
        
        return $tags ? unserialize($tags) : [];
    }
    
    /**
     * Get product gallery
     */
    private function get_product_gallery($product_id) {
        global $wpdb;
        
        $gallery_ids = $wpdb->get_var($wpdb->prepare("
            SELECT gallery_image_ids FROM {$wpdb->prefix}digiplanet_products 
            WHERE id = %d
        ", $product_id));
        
        if (!$gallery_ids) {
            return [];
        }
        
        $ids = unserialize($gallery_ids);
        $gallery = [];
        
        foreach ($ids as $id) {
            $image_url = wp_get_attachment_url($id);
            if ($image_url) {
                $gallery[] = [
                    'id' => $id,
                    'url' => $image_url,
                    'thumbnail' => wp_get_attachment_image_url($id, 'thumbnail'),
                    'medium' => wp_get_attachment_image_url($id, 'medium'),
                    'large' => wp_get_attachment_image_url($id, 'large'),
                ];
            }
        }
        
        return $gallery;
    }
    
    /**
     * Increment view count
     */
    public function increment_view_count($product_id) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->prefix}digiplanet_products 
            SET view_count = view_count + 1 
            WHERE id = %d
        ", $product_id));
    }
    
    /**
     * Increment sales count
     */
    public function increment_sales_count($product_id) {
        global $wpdb;
        
        $wpdb->query($wpdb->prepare("
            UPDATE {$wpdb->prefix}digiplanet_products 
            SET sales_count = sales_count + 1 
            WHERE id = %d
        ", $product_id));
    }
    
    /**
     * Update product rating
     */
    public function update_product_rating($product_id) {
        global $wpdb;
        
        $rating_data = $wpdb->get_row($wpdb->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as review_count 
            FROM {$wpdb->prefix}digiplanet_reviews 
            WHERE product_id = %d AND status = 'approved'
        ", $product_id));
        
        if ($rating_data) {
            $wpdb->update(
                $wpdb->prefix . 'digiplanet_products',
                [
                    'rating' => round($rating_data->avg_rating, 2),
                    'review_count' => $rating_data->review_count
                ],
                ['id' => $product_id],
                ['%f', '%d'],
                ['%d']
            );
        }
    }
    
    /**
     * Get related products
     */
    public function get_related_products($product_id, $limit = 4) {
        global $wpdb;
        
        $product = $this->get_product($product_id);
        if (!$product) {
            return [];
        }
        
        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}digiplanet_products 
            WHERE category_id = %d 
            AND id != %d 
            AND status = 'published'
            ORDER BY RAND()
            LIMIT %d
        ", $product->category_id, $product_id, $limit));
    }
    
    /**
     * Format price
     */
    public function format_price($price, $sale_price = null) {
        $currency = get_option('digiplanet_currency', 'USD');
        $position = get_option('digiplanet_currency_position', 'left');
        $decimal_places = get_option('digiplanet_decimal_places', 2);
        $thousand_separator = get_option('digiplanet_thousand_separator', ',');
        $decimal_separator = get_option('digiplanet_decimal_separator', '.');
        
        $price = number_format(
            $price, 
            $decimal_places, 
            $decimal_separator, 
            $thousand_separator
        );
        
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'CAD' => 'C$',
            'AUD' => 'A$',
            'JPY' => '¥',
            'NGN' => '₦',
        ];
        
        $symbol = $symbols[$currency] ?? $currency;
        
        switch ($position) {
            case 'left':
                return $symbol . $price;
            case 'right':
                return $price . $symbol;
            case 'left_space':
                return $symbol . ' ' . $price;
            case 'right_space':
                return $price . ' ' . $symbol;
            default:
                return $symbol . $price;
        }
    }
    
    /**
     * Calculate sale percentage
     */
    public function calculate_sale_percentage($price, $sale_price) {
        if (!$sale_price || $sale_price >= $price) {
            return 0;
        }
        
        $percentage = (($price - $sale_price) / $price) * 100;
        return round($percentage);
    }
}