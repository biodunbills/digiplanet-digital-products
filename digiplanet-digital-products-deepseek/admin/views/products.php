<?php
/**
 * Products admin view
 */

if (!defined('ABSPATH')) {
    exit;
}

$products_admin = Digiplanet_Products_Admin::get_instance();
$product_manager = Digiplanet_Product_Manager::get_instance();

// Get filters
$filters = [
    'category' => $_GET['category'] ?? '',
    'status' => $_GET['status'] ?? '',
    'search' => $_GET['s'] ?? '',
];

// Pagination
$per_page = 20;
$current_page = max(1, $_GET['paged'] ?? 1);
$offset = ($current_page - 1) * $per_page;

// Get products
global $wpdb;
$where = ['1=1'];
$params = [];

if (!empty($filters['category'])) {
    $where[] = 'category_id = %d';
    $params[] = $filters['category'];
}

if (!empty($filters['status'])) {
    $where[] = 'status = %s';
    $params[] = $filters['status'];
}

if (!empty($filters['search'])) {
    $where[] = '(name LIKE %s OR description LIKE %s OR sku LIKE %s)';
    $search_term = '%' . $wpdb->esc_like($filters['search']) . '%';
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$where_clause = implode(' AND ', $where);

// Get total count
$count_query = "SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_products WHERE {$where_clause}";
if (!empty($params)) {
    $count_query = $wpdb->prepare($count_query, $params);
}
$total_items = $wpdb->get_var($count_query);
$total_pages = ceil($total_items / $per_page);

// Get products
$query = $wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}digiplanet_products 
    WHERE {$where_clause}
    ORDER BY created_at DESC
    LIMIT %d OFFSET %d
", array_merge($params, [$per_page, $offset]));

$products = $wpdb->get_results($query);

// Get categories
$categories = $product_manager->get_categories();
?>

<div class="wrap digiplanet-products">
    <h1 class="wp-heading-inline"><?php _e('Products', 'digiplanet-digital-products'); ?></h1>
    <a href="<?php echo admin_url('admin.php?page=digiplanet-products&action=create'); ?>" class="page-title-action">
        <?php _e('Add New', 'digiplanet-digital-products'); ?>
    </a>
    
    <hr class="wp-header-end">
    
    <!-- Filters -->
    <div class="digiplanet-filters">
        <form method="get" action="<?php echo admin_url('admin.php'); ?>">
            <input type="hidden" name="page" value="digiplanet-products">
            
            <div class="tablenav top">
                <div class="alignleft actions">
                    <select name="category" class="postform">
                        <option value=""><?php _e('All Categories', 'digiplanet-digital-products'); ?></option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category->id; ?>" <?php selected($filters['category'], $category->id); ?>>
                                <?php echo esc_html($category->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <select name="status" class="postform">
                        <option value=""><?php _e('All Statuses', 'digiplanet-digital-products'); ?></option>
                        <option value="published" <?php selected($filters['status'], 'published'); ?>>
                            <?php _e('Published', 'digiplanet-digital-products'); ?>
                        </option>
                        <option value="draft" <?php selected($filters['status'], 'draft'); ?>>
                            <?php _e('Draft', 'digiplanet-digital-products'); ?>
                        </option>
                        <option value="archived" <?php selected($filters['status'], 'archived'); ?>>
                            <?php _e('Archived', 'digiplanet-digital-products'); ?>
                        </option>
                    </select>
                    
                    <input type="text" name="s" value="<?php echo esc_attr($filters['search']); ?>" placeholder="<?php _e('Search products...', 'digiplanet-digital-products'); ?>">
                    
                    <button type="submit" class="button"><?php _e('Filter', 'digiplanet-digital-products'); ?></button>
                    
                    <?php if ($filters['category'] || $filters['status'] || $filters['search']): ?>
                        <a href="<?php echo admin_url('admin.php?page=digiplanet-products'); ?>" class="button">
                            <?php _e('Clear Filters', 'digiplanet-digital-products'); ?>
                        </a>
                    <?php endif; ?>
                </div>
                
                <div class="tablenav-pages">
                    <span class="displaying-num"><?php echo sprintf(_n('%s item', '%s items', $total_items), number_format_i18n($total_items)); ?></span>
                    
                    <?php if ($total_pages > 1): ?>
                        <span class="pagination-links">
                            <?php
                            echo paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                                'total' => $total_pages,
                                'current' => $current_page,
                            ]);
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Bulk Actions -->
    <div class="digiplanet-bulk-actions">
        <select class="bulk-action-select">
            <option value=""><?php _e('Bulk Actions', 'digiplanet-digital-products'); ?></option>
            <option value="publish"><?php _e('Publish', 'digiplanet-digital-products'); ?></option>
            <option value="draft"><?php _e('Move to Draft', 'digiplanet-digital-products'); ?></option>
            <option value="delete"><?php _e('Delete', 'digiplanet-digital-products'); ?></option>
        </select>
        <button type="button" class="button digiplanet-bulk-action" data-action=""><?php _e('Apply', 'digiplanet-digital-products'); ?></button>
    </div>
    
    <!-- Products Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" class="digiplanet-toggle-all">
                </td>
                <th class="manage-column column-thumbnail"><?php _e('Image', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-name"><?php _e('Name', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-price"><?php _e('Price', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-sales"><?php _e('Sales', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-rating"><?php _e('Rating', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-status"><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-date"><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-actions"><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="9" class="no-items"><?php _e('No products found.', 'digiplanet-digital-products'); ?></td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <?php
                    $thumbnail = $product->featured_image_id ? wp_get_attachment_url($product->featured_image_id) : '';
                    $price = $product_manager->format_price($product->price, $product->sale_price);
                    ?>
                    <tr>
                        <th scope="row" class="check-column">
                            <input type="checkbox" class="digiplanet-product-checkbox" value="<?php echo $product->id; ?>">
                        </th>
                        <td class="column-thumbnail">
                            <?php if ($thumbnail): ?>
                                <img src="<?php echo esc_url($thumbnail); ?>" alt="<?php echo esc_attr($product->name); ?>" class="digiplanet-product-thumbnail">
                            <?php endif; ?>
                        </td>
                        <td class="column-name">
                            <strong>
                                <a href="<?php echo admin_url('admin.php?page=digiplanet-products&action=edit&product_id=' . $product->id); ?>">
                                    <?php echo esc_html($product->name); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=digiplanet-products&action=edit&product_id=' . $product->id); ?>">
                                        <?php _e('Edit', 'digiplanet-digital-products'); ?>
                                    </a> |
                                </span>
                                <span class="view">
                                    <a href="<?php echo home_url('/digital-product/' . $product->slug); ?>" target="_blank">
                                        <?php _e('View', 'digiplanet-digital-products'); ?>
                                    </a> |
                                </span>
                                <span class="duplicate">
                                    <a href="#" class="digiplanet-duplicate-product" data-product-id="<?php echo $product->id; ?>">
                                        <?php _e('Duplicate', 'digiplanet-digital-products'); ?>
                                    </a> |
                                </span>
                                <span class="trash">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin-post.php?action=digiplanet_delete_product&product_id=' . $product->id), 'digiplanet_delete_product'); ?>" class="submitdelete">
                                        <?php _e('Delete', 'digiplanet-digital-products'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                        <td class="column-price"><?php echo $price; ?></td>
                        <td class="column-sales"><?php echo $product->sales_count; ?></td>
                        <td class="column-rating">
                            <?php if ($product->rating > 0): ?>
                                <span class="digiplanet-rating">
                                    <?php echo str_repeat('★', floor($product->rating)); ?>
                                    <?php echo str_repeat('☆', 5 - floor($product->rating)); ?>
                                    <span class="rating-number">(<?php echo $product->rating; ?>)</span>
                                </span>
                            <?php else: ?>
                                <span class="no-rating"><?php _e('No ratings', 'digiplanet-digital-products'); ?></span>
                            <?php endif; ?>
                        </td>
                        <td class="column-status">
                            <span class="digiplanet-status digiplanet-status-<?php echo $product->status; ?>">
                                <?php echo ucfirst($product->status); ?>
                            </span>
                        </td>
                        <td class="column-date">
                            <?php echo date_i18n(get_option('date_format'), strtotime($product->created_at)); ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small digiplanet-quick-edit" data-product-id="<?php echo $product->id; ?>">
                                <?php _e('Quick Edit', 'digiplanet-digital-products'); ?>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td class="manage-column column-cb check-column">
                    <input type="checkbox" class="digiplanet-toggle-all">
                </td>
                <th class="manage-column column-thumbnail"><?php _e('Image', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-name"><?php _e('Name', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-price"><?php _e('Price', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-sales"><?php _e('Sales', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-rating"><?php _e('Rating', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-status"><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-date"><?php _e('Date', 'digiplanet-digital-products'); ?></th>
                <th class="manage-column column-actions"><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
            </tr>
        </tfoot>
    </table>
    
    <!-- Quick Edit Modal -->
    <div id="digiplanet-quick-edit-modal" class="digiplanet-modal" style="display: none;">
        <div class="digiplanet-modal-content">
            <div class="digiplanet-modal-header">
                <h3><?php _e('Quick Edit', 'digiplanet-digital-products'); ?></h3>
                <span class="digiplanet-modal-close">&times;</span>
            </div>
            <div class="digiplanet-modal-body">
                <input type="hidden" id="digiplanet-quick-edit-product-id">
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-quick-edit-price"><?php _e('Price', 'digiplanet-digital-products'); ?></label>
                    <input type="number" id="digiplanet-quick-edit-price" step="0.01" min="0">
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-quick-edit-sale-price"><?php _e('Sale Price', 'digiplanet-digital-products'); ?></label>
                    <input type="number" id="digiplanet-quick-edit-sale-price" step="0.01" min="0">
                </div>
                
                <div class="digiplanet-form-row">
                    <label for="digiplanet-quick-edit-status"><?php _e('Status', 'digiplanet-digital-products'); ?></label>
                    <select id="digiplanet-quick-edit-status">
                        <option value="published"><?php _e('Published', 'digiplanet-digital-products'); ?></option>
                        <option value="draft"><?php _e('Draft', 'digiplanet-digital-products'); ?></option>
                        <option value="archived"><?php _e('Archived', 'digiplanet-digital-products'); ?></option>
                    </select>
                </div>
            </div>
            <div class="digiplanet-modal-footer">
                <button type="button" class="button button-primary" id="digiplanet-quick-edit-save">
                    <?php _e('Save Changes', 'digiplanet-digital-products'); ?>
                </button>
                <button type="button" class="button digiplanet-modal-close">
                    <?php _e('Cancel', 'digiplanet-digital-products'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Close modal
    $('.digiplanet-modal-close').on('click', function() {
        $('#digiplanet-quick-edit-modal').hide();
    });
    
    // Close modal on outside click
    $(window).on('click', function(e) {
        if ($(e.target).hasClass('digiplanet-modal')) {
            $('#digiplanet-quick-edit-modal').hide();
        }
    });
    
    // Bulk action
    $('.digiplanet-bulk-action').on('click', function() {
        var action = $('.bulk-action-select').val();
        if (!action) {
            alert('Please select a bulk action.');
            return;
        }
        $(this).data('action', action);
    });
    
    // Duplicate product
    $('.digiplanet-duplicate-product').on('click', function(e) {
        e.preventDefault();
        var productId = $(this).data('product-id');
        
        if (confirm('Duplicate this product?')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'digiplanet_duplicate_product',
                    product_id: productId,
                    nonce: digiplanet_admin.nonce
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    }
                }
            });
        }
    });
});
</script>