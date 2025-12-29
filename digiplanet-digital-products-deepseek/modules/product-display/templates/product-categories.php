<?php
/**
 * Product Categories Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$query = isset($args['query']) ? $args['query'] : false;
$settings = isset($args['settings']) ? $args['settings'] : [];
$title = isset($settings['title']) ? $settings['title'] : '';
$layout = isset($settings['layout']) ? $settings['layout'] : 'grid'; // grid, list, carousel
$columns = isset($settings['columns']) ? absint($settings['columns']) : 4;
$show_count = isset($settings['show_count']) ? $settings['show_count'] : true;
$show_description = isset($settings['show_description']) ? $settings['show_description'] : false;
$show_image = isset($settings['show_image']) ? $settings['show_image'] : true;
$parent_only = isset($settings['parent_only']) ? $settings['parent_only'] : true;

if (!$query || empty($query)) {
    echo '<p class="digiplanet-no-categories">' . __('No categories found.', 'digiplanet-digital-products') . '</p>';
    return;
}

// Determine wrapper class based on layout
$wrapper_class = 'digiplanet-categories-' . $layout;
if ($layout === 'grid') {
    $wrapper_class .= ' digiplanet-grid-columns-' . $columns;
}
?>

<div class="digiplanet-categories-section">
    <?php if ($title): ?>
        <h2 class="digiplanet-categories-title"><?php echo esc_html($title); ?></h2>
    <?php endif; ?>
    
    <div class="<?php echo esc_attr($wrapper_class); ?>">
        <?php foreach ($query as $category): ?>
            <?php
            $category_id = $category->term_id;
            $category_name = $category->name;
            $category_description = $category->description;
            $category_count = $category->count;
            $category_link = get_term_link($category);
            $category_image_id = get_term_meta($category_id, 'digiplanet_category_image', true);
            $category_image_url = $category_image_id ? wp_get_attachment_image_url($category_image_id, 'medium') : DIGIPLANET_ASSETS_URL . 'images/placeholder-category.png';
            
            // Get subcategories if not parent_only
            $subcategories = [];
            if (!$parent_only) {
                $subcategories = get_terms([
                    'taxonomy' => 'digiplanet_category',
                    'parent' => $category_id,
                    'hide_empty' => false,
                    'number' => 3,
                ]);
            }
            ?>
            
            <div class="digiplanet-category-item digiplanet-category-layout-<?php echo $layout; ?>">
                <?php if ($show_image): ?>
                    <div class="digiplanet-category-image">
                        <a href="<?php echo esc_url($category_link); ?>">
                            <img src="<?php echo esc_url($category_image_url); ?>" alt="<?php echo esc_attr($category_name); ?>" loading="lazy">
                            <div class="digiplanet-category-overlay"></div>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="digiplanet-category-content">
                    <h3 class="digiplanet-category-name">
                        <a href="<?php echo esc_url($category_link); ?>">
                            <?php echo esc_html($category_name); ?>
                        </a>
                        <?php if ($show_count && $category_count > 0): ?>
                            <span class="digiplanet-category-count">
                                (<?php echo number_format($category_count); ?>)
                            </span>
                        <?php endif; ?>
                    </h3>
                    
                    <?php if ($show_description && !empty($category_description)): ?>
                        <div class="digiplanet-category-description">
                            <?php echo wp_trim_words($category_description, 20, '...'); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($subcategories) && !is_wp_error($subcategories)): ?>
                        <div class="digiplanet-subcategories">
                            <h4><?php _e('Subcategories:', 'digiplanet-digital-products'); ?></h4>
                            <ul class="digiplanet-subcategories-list">
                                <?php foreach ($subcategories as $subcategory): ?>
                                    <li>
                                        <a href="<?php echo get_term_link($subcategory); ?>">
                                            <?php echo esc_html($subcategory->name); ?>
                                            <?php if ($show_count): ?>
                                                <span class="digiplanet-subcategory-count">
                                                    (<?php echo $subcategory->count; ?>)
                                                </span>
                                            <?php endif; ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="digiplanet-category-actions">
                        <a href="<?php echo esc_url($category_link); ?>" class="digiplanet-btn digiplanet-btn-primary digiplanet-btn-sm">
                            <i class="fas fa-arrow-right"></i>
                            <?php _e('View Products', 'digiplanet-digital-products'); ?>
                        </a>
                        
                        <?php if (is_user_logged_in()): ?>
                            <button type="button" class="digiplanet-btn digiplanet-btn-outline digiplanet-btn-sm digiplanet-follow-category" data-category-id="<?php echo $category_id; ?>">
                                <i class="far fa-bell"></i>
                                <?php _e('Follow', 'digiplanet-digital-products'); ?>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Follow category
    $('.digiplanet-follow-category').on('click', function() {
        var $button = $(this);
        var categoryId = $button.data('category-id');
        var isFollowing = $button.hasClass('following');
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: isFollowing ? 'digiplanet_unfollow_category' : 'digiplanet_follow_category',
                category_id: categoryId,
                nonce: digiplanet_ajax.nonce
            },
            beforeSend: function() {
                $button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            },
            success: function(response) {
                if (response.success) {
                    if (isFollowing) {
                        $button.html('<i class="far fa-bell"></i> ' + response.data.follow_text);
                        $button.removeClass('following');
                        showNotification(response.data.message, 'info');
                    } else {
                        $button.html('<i class="fas fa-bell"></i> ' + response.data.follow_text);
                        $button.addClass('following');
                        showNotification(response.data.message, 'success');
                    }
                } else {
                    showNotification(response.data.message, 'error');
                }
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    // Check if categories are already followed
    $('.digiplanet-follow-category').each(function() {
        var $button = $(this);
        var categoryId = $button.data('category-id');
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_check_category_follow',
                category_id: categoryId,
                nonce: digiplanet_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.is_following) {
                    $button.html('<i class="fas fa-bell"></i> ' + response.data.follow_text);
                    $button.addClass('following');
                }
            }
        });
    });
});

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
</script>

<style>
.digiplanet-categories-section {
    padding: 40px 0;
}

.digiplanet-categories-title {
    text-align: center;
    margin-bottom: 40px;
    font-size: 36px;
    font-weight: 700;
    color: #333;
}

/* Grid Layout */
.digiplanet-categories-grid {
    display: grid;
    gap: 30px;
}

.digiplanet-grid-columns-1 { grid-template-columns: repeat(1, 1fr); }
.digiplanet-grid-columns-2 { grid-template-columns: repeat(2, 1fr); }
.digiplanet-grid-columns-3 { grid-template-columns: repeat(3, 1fr); }
.digiplanet-grid-columns-4 { grid-template-columns: repeat(4, 1fr); }
.digiplanet-grid-columns-5 { grid-template-columns: repeat(5, 1fr); }
.digiplanet-grid-columns-6 { grid-template-columns: repeat(6, 1fr); }

.digiplanet-category-item.digiplanet-category-layout-grid {
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
}

.digiplanet-category-item.digiplanet-category-layout-grid:hover {
    box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
    border-color: #dee2e6;
}

.digiplanet-category-image {
    position: relative;
    height: 200px;
    overflow: hidden;
}

.digiplanet-category-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.digiplanet-category-item:hover .digiplanet-category-image img {
    transform: scale(1.05);
}

.digiplanet-category-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.3));
    opacity: 0;
    transition: opacity 0.3s ease;
}

.digiplanet-category-item:hover .digiplanet-category-overlay {
    opacity: 1;
}

.digiplanet-category-content {
    padding: 20px;
}

.digiplanet-category-name {
    margin: 0 0 10px 0;
    font-size: 20px;
    font-weight: 600;
}

.digiplanet-category-name a {
    color: #333;
    text-decoration: none;
    transition: color 0.3s ease;
}

.digiplanet-category-name a:hover {
    color: #3742fa;
}

.digiplanet-category-count {
    font-size: 14px;
    color: #6c757d;
    font-weight: normal;
    margin-left: 5px;
}

.digiplanet-category-description {
    color: #6c757d;
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.digiplanet-subcategories {
    margin-bottom: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.digiplanet-subcategories h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #495057;
}

.digiplanet-subcategories-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.digiplanet-subcategories-list li {
    margin-bottom: 8px;
}

.digiplanet-subcategories-list li:last-child {
    margin-bottom: 0;
}

.digiplanet-subcategories-list a {
    color: #6c757d;
    text-decoration: none;
    font-size: 14px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: color 0.3s ease;
}

.digiplanet-subcategories-list a:hover {
    color: #3742fa;
}

.digiplanet-subcategory-count {
    font-size: 12px;
    color: #adb5bd;
}

.digiplanet-category-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.digiplanet-follow-category.following {
    background: #28a745;
    color: white;
    border-color: #28a745;
}

.digiplanet-follow-category.following:hover {
    background: #218838;
    border-color: #1e7e34;
}

/* List Layout */
.digiplanet-categories-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.digiplanet-category-item.digiplanet-category-layout-list {
    display: flex;
    background: white;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.digiplanet-category-item.digiplanet-category-layout-list:hover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transform: translateY(-3px);
    border-color: #dee2e6;
}

.digiplanet-category-item.digiplanet-category-layout-list .digiplanet-category-image {
    width: 300px;
    height: auto;
    flex-shrink: 0;
}

.digiplanet-category-item.digiplanet-category-layout-list .digiplanet-category-content {
    flex: 1;
    padding: 30px;
}

/* Carousel Layout */
.digiplanet-categories-carousel {
    position: relative;
    padding: 0 60px;
}

.digiplanet-categories-carousel .digiplanet-categories-grid {
    display: flex;
    transition: transform 0.5s ease;
}

.digiplanet-categories-carousel .digiplanet-category-item {
    flex-shrink: 0;
    margin: 0 15px;
}

.digiplanet-carousel-nav {
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    transform: translateY(-50%);
    display: flex;
    justify-content: space-between;
    pointer-events: none;
    z-index: 10;
}

.digiplanet-carousel-prev,
.digiplanet-carousel-next {
    pointer-events: auto;
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
}

.digiplanet-carousel-prev:hover,
.digiplanet-carousel-next:hover {
    background: #3742fa;
    color: white;
    border-color: #3742fa;
    transform: scale(1.1);
}

.digiplanet-carousel-prev {
    margin-left: -25px;
}

.digiplanet-carousel-next {
    margin-right: -25px;
}

/* Responsive */
@media (max-width: 1200px) {
    .digiplanet-grid-columns-5,
    .digiplanet-grid-columns-6 {
        grid-template-columns: repeat(4, 1fr);
    }
}

@media (max-width: 992px) {
    .digiplanet-grid-columns-4,
    .digiplanet-grid-columns-5,
    .digiplanet-grid-columns-6 {
        grid-template-columns: repeat(3, 1fr);
    }
    
    .digiplanet-category-item.digiplanet-category-layout-list {
        flex-direction: column;
    }
    
    .digiplanet-category-item.digiplanet-category-layout-list .digiplanet-category-image {
        width: 100%;
        height: 200px;
    }
}

@media (max-width: 768px) {
    .digiplanet-grid-columns-3,
    .digiplanet-grid-columns-4,
    .digiplanet-grid-columns-5,
    .digiplanet-grid-columns-6 {
        grid-template-columns: repeat(2, 1fr);
    }
    
    .digiplanet-categories-title {
        font-size: 28px;
    }
    
    .digiplanet-categories-carousel {
        padding: 0 40px;
    }
}

@media (max-width: 576px) {
    .digiplanet-grid-columns-2,
    .digiplanet-grid-columns-3,
    .digiplanet-grid-columns-4,
    .digiplanet-grid-columns-5,
    .digiplanet-grid-columns-6 {
        grid-template-columns: repeat(1, 1fr);
    }
    
    .digiplanet-categories-title {
        font-size: 24px;
    }
    
    .digiplanet-category-actions {
        flex-direction: column;
    }
    
    .digiplanet-categories-carousel {
        padding: 0 20px;
    }
    
    .digiplanet-carousel-prev,
    .digiplanet-carousel-next {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
    
    .digiplanet-carousel-prev {
        margin-left: -20px;
    }
    
    .digiplanet-carousel-next {
        margin-right: -20px;
    }
}
</style>