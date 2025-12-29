<?php
/**
 * Plugin autoloader for Composer dependencies
 * 
 * @package Digiplanet_Digital_Products
 * @since 1.0.0
 */

// Don't allow direct access
if (!defined('ABSPATH')) {
    exit;
}

// Composer autoloader
if (file_exists(__DIR__ . '/autoload.php')) {
    require_once __DIR__ . '/autoload.php';
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// Check for required PHP extensions
function digiplanet_check_php_extensions() {
    $required_extensions = [
        'curl' => 'cURL',
        'json' => 'JSON',
        'mbstring' => 'Multibyte String',
        'openssl' => 'OpenSSL',
    ];
    
    $missing = [];
    
    foreach ($required_extensions as $extension => $name) {
        if (!extension_loaded($extension)) {
            $missing[] = $name;
        }
    }
    
    if (!empty($missing)) {
        add_action('admin_notices', function() use ($missing) {
            ?>
            <div class="notice notice-error">
                <p>
                    <strong>Digiplanet Digital Products:</strong> 
                    The following PHP extensions are required but missing: 
                    <?php echo esc_html(implode(', ', $missing)); ?>.
                    Please contact your hosting provider to enable these extensions.
                </p>
            </div>
            <?php
        });
        
        return false;
    }
    
    return true;
}

// Initialize autoloader
add_action('plugins_loaded', function() {
    if (!digiplanet_check_php_extensions()) {
        return;
    }
    
    // Register custom namespaces
    spl_autoload_register(function($class) {
        // Digiplanet namespace
        $prefix = 'Digiplanet\\DigitalProducts\\';
        $base_dir = DIGIPLANET_PLUGIN_DIR . 'includes/';
        
        // Does the class use the namespace prefix?
        $len = strlen($prefix);
        if (strncmp($prefix, $class, $len) !== 0) {
            return;
        }
        
        // Get the relative class name
        $relative_class = substr($class, $len);
        
        // Replace namespace separators with directory separators
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
        
        // If the file exists, require it
        if (file_exists($file)) {
            require $file;
        }
    });
    
    // Load vendor dependencies if available
    $vendor_autoload = DIGIPLANET_PLUGIN_DIR . 'vendor/autoload.php';
    if (file_exists($vendor_autoload)) {
        require_once $vendor_autoload;
    }
});

// Load helper functions
require_once DIGIPLANET_PLUGIN_DIR . 'includes/helpers.php';

// Load deprecated functions for backward compatibility
if (file_exists(DIGIPLANET_PLUGIN_DIR . 'includes/deprecated.php')) {
    require_once DIGIPLANET_PLUGIN_DIR . 'includes/deprecated.php';
}