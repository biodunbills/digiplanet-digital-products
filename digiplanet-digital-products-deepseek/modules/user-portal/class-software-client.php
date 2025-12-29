<?php
/**
 * Software Client Portal
 */

if (!defined('ABSPATH')) {
    exit;
}

class Digiplanet_Software_Client {
    
    private static $instance = null;
    
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        add_action('init', [$this, 'init']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
    }
    
    public function init() {
        // Register custom user role
        $this->register_software_client_role();
        
        // Prevent self-registration for software clients
        add_filter('wp_pre_insert_user_data', [$this, 'prevent_software_client_self_registration'], 10, 3);
    }
    
    private function register_software_client_role() {
        $capabilities = [
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
            'upload_files' => true,
            'digiplanet_view_projects' => true,
            'digiplanet_view_support' => true,
            'digiplanet_view_documents' => true,
            'digiplanet_submit_support_tickets' => true,
            'digiplanet_view_billing' => true,
        ];
        
        add_role('software_client', __('Software Client', 'digiplanet-digital-products'), $capabilities);
    }
    
    public function prevent_software_client_self_registration($data, $update, $user_id) {
        // If this is a new user (not an update) and trying to set software_client role
        if (!$update && isset($data['role']) && $data['role'] === 'software_client') {
            // Only allow administrators to create software_client accounts
            if (!current_user_can('create_users')) {
                $data['role'] = get_option('default_role');
            }
        }
        
        return $data;
    }
    
    public function add_admin_menu() {
        add_submenu_page(
            'digiplanet-dashboard',
            __('Software Clients', 'digiplanet-digital-products'),
            __('Software Clients', 'digiplanet-digital-products'),
            'manage_options',
            'digiplanet-software-clients',
            [$this, 'render_software_clients_page']
        );
    }
    
    public function render_software_clients_page() {
        ?>
        <div class="wrap digiplanet-admin-wrap">
            <h1><?php _e('Software Clients', 'digiplanet-digital-products'); ?></h1>
            
            <div class="digiplanet-admin-actions">
                <button type="button" class="button button-primary" onclick="jQuery('#digiplanet-add-client-modal').show();">
                    <?php _e('Add New Client', 'digiplanet-digital-products'); ?>
                </button>
            </div>
            
            <div class="digiplanet-software-clients-table">
                <?php $this->render_clients_table(); ?>
            </div>
        </div>
        
        <!-- Add Client Modal -->
        <div id="digiplanet-add-client-modal" class="digiplanet-modal" style="display: none;">
            <div class="digiplanet-modal-content">
                <div class="digiplanet-modal-header">
                    <h2><?php _e('Add New Software Client', 'digiplanet-digital-products'); ?></h2>
                    <button type="button" class="digiplanet-modal-close" onclick="jQuery('#digiplanet-add-client-modal').hide();">&times;</button>
                </div>
                <div class="digiplanet-modal-body">
                    <form id="digiplanet-add-client-form" method="post">
                        <?php wp_nonce_field('digiplanet_add_software_client', 'digiplanet_client_nonce'); ?>
                        
                        <div class="digiplanet-form-group">
                            <label for="client_email"><?php _e('Email Address', 'digiplanet-digital-products'); ?> *</label>
                            <input type="email" id="client_email" name="client_email" required>
                        </div>
                        
                        <div class="digiplanet-form-row">
                            <div class="digiplanet-form-group">
                                <label for="first_name"><?php _e('First Name', 'digiplanet-digital-products'); ?></label>
                                <input type="text" id="first_name" name="first_name">
                            </div>
                            
                            <div class="digiplanet-form-group">
                                <label for="last_name"><?php _e('Last Name', 'digiplanet-digital-products'); ?></label>
                                <input type="text" id="last_name" name="last_name">
                            </div>
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="company"><?php _e('Company', 'digiplanet-digital-products'); ?></label>
                            <input type="text" id="company" name="company">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label for="phone"><?php _e('Phone Number', 'digiplanet-digital-products'); ?></label>
                            <input type="tel" id="phone" name="phone">
                        </div>
                        
                        <div class="digiplanet-form-group">
                            <label>
                                <input type="checkbox" name="send_welcome_email" value="1" checked>
                                <?php _e('Send welcome email with login details', 'digiplanet-digital-products'); ?>
                            </label>
                        </div>
                        
                        <div class="digiplanet-modal-footer">
                            <button type="submit" class="button button-primary"><?php _e('Create Client', 'digiplanet-digital-products'); ?></button>
                            <button type="button" class="button" onclick="jQuery('#digiplanet-add-client-modal').hide();"><?php _e('Cancel', 'digiplanet-digital-products'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
    
    private function render_clients_table() {
        global $wpdb;
        
        // Get all software clients
        $clients = get_users([
            'role' => 'software_client',
            'orderby' => 'registered',
            'order' => 'DESC',
        ]);
        
        ?>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Client', 'digiplanet-digital-products'); ?></th>
                    <th><?php _e('Email', 'digiplanet-digital-products'); ?></th>
                    <th><?php _e('Company', 'digiplanet-digital-products'); ?></th>
                    <th><?php _e('Projects', 'digiplanet-digital-products'); ?></th>
                    <th><?php _e('Last Active', 'digiplanet-digital-products'); ?></th>
                    <th><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="6"><?php _e('No software clients found.', 'digiplanet-digital-products'); ?></td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $client): ?>
                        <?php
                        $projects_count = $this->get_client_projects_count($client->ID);
                        $last_active = get_user_meta($client->ID, 'digiplanet_last_active', true);
                        $company = get_user_meta($client->ID, 'digiplanet_company', true);
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($client->display_name); ?></strong>
                                <br>
                                <small><?php echo esc_html($client->first_name . ' ' . $client->last_name); ?></small>
                            </td>
                            <td><?php echo esc_html($client->user_email); ?></td>
                            <td><?php echo esc_html($company); ?></td>
                            <td><?php echo number_format($projects_count); ?></td>
                            <td>
                                <?php if ($last_active): ?>
                                    <?php echo human_time_diff(strtotime($last_active), current_time('timestamp')); ?> ago
                                <?php else: ?>
                                    <?php _e('Never', 'digiplanet-digital-products'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="<?php echo admin_url('admin.php?page=digiplanet-software-clients&action=edit&id=' . $client->ID); ?>" class="button button-small">
                                    <?php _e('Edit', 'digiplanet-digital-products'); ?>
                                </a>
                                <a href="<?php echo admin_url('admin.php?page=digiplanet-software-clients&action=projects&id=' . $client->ID); ?>" class="button button-small">
                                    <?php _e('Projects', 'digiplanet-digital-products'); ?>
                                </a>
                                <button type="button" class="button button-small button-link-delete" onclick="if(confirm('Are you sure?')) { window.location.href='<?php echo wp_nonce_url(admin_url('admin.php?page=digiplanet-software-clients&action=delete&id=' . $client->ID), 'delete_client_' . $client->ID); ?>'; }">
                                    <?php _e('Delete', 'digiplanet-digital-products'); ?>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
    
    public function get_dashboard_content($user_id) {
        $projects = $this->get_client_projects($user_id);
        $support_tickets = $this->get_client_support_tickets($user_id);
        $documents = $this->get_client_documents($user_id);
        
        ob_start();
        ?>
        <div class="digiplanet-software-client-dashboard">
            <h2><?php _e('Software Client Dashboard', 'digiplanet-digital-products'); ?></h2>
            
            <!-- Welcome Message -->
            <div class="digiplanet-welcome-message">
                <h3><?php _e('Welcome back!', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Manage your software projects, support tickets, and documents from this dashboard.', 'digiplanet-digital-products'); ?></p>
            </div>
            
            <!-- Quick Stats -->
            <div class="digiplanet-dashboard-stats">
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo count($projects); ?></h3>
                        <p><?php _e('Active Projects', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo count($support_tickets); ?></h3>
                        <p><?php _e('Support Tickets', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo count($documents); ?></h3>
                        <p><?php _e('Documents', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
                
                <div class="digiplanet-stat-card">
                    <div class="digiplanet-stat-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="digiplanet-stat-content">
                        <h3><?php echo $this->get_upcoming_milestones_count($user_id); ?></h3>
                        <p><?php _e('Upcoming Milestones', 'digiplanet-digital-products'); ?></p>
                    </div>
                </div>
            </div>
            
            <div class="digiplanet-dashboard-content">
                <!-- Active Projects -->
                <div class="digiplanet-dashboard-section">
                    <div class="digiplanet-section-header">
                        <h3><?php _e('Active Projects', 'digiplanet-digital-products'); ?></h3>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('projects'); ?>" class="digiplanet-view-all">
                            <?php _e('View All Projects', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($projects)): ?>
                        <div class="digiplanet-projects-grid">
                            <?php foreach ($projects as $project): ?>
                                <div class="digiplanet-project-card">
                                    <div class="digiplanet-project-header">
                                        <h4><?php echo esc_html($project->title); ?></h4>
                                        <span class="digiplanet-project-status digiplanet-status-<?php echo $project->status; ?>">
                                            <?php echo ucfirst($project->status); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="digiplanet-project-meta">
                                        <span class="digiplanet-project-meta-item">
                                            <i class="fas fa-calendar"></i>
                                            <?php echo date_i18n(get_option('date_format'), strtotime($project->start_date)); ?>
                                        </span>
                                        
                                        <span class="digiplanet-project-meta-item">
                                            <i class="fas fa-flag-checkered"></i>
                                            <?php echo date_i18n(get_option('date_format'), strtotime($project->deadline)); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="digiplanet-project-progress">
                                        <div class="digiplanet-progress-bar">
                                            <div class="digiplanet-progress-fill" style="width: <?php echo $project->progress; ?>%;"></div>
                                        </div>
                                        <span class="digiplanet-progress-text"><?php echo $project->progress; ?>% <?php _e('Complete', 'digiplanet-digital-products'); ?></span>
                                    </div>
                                    
                                    <div class="digiplanet-project-actions">
                                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('projects') . '?id=' . $project->id; ?>" class="digiplanet-btn digiplanet-btn-sm">
                                            <?php _e('View Details', 'digiplanet-digital-products'); ?>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="digiplanet-no-data"><?php _e('No active projects found.', 'digiplanet-digital-products'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Recent Support Tickets -->
                <div class="digiplanet-dashboard-section">
                    <div class="digiplanet-section-header">
                        <h3><?php _e('Recent Support Tickets', 'digiplanet-digital-products'); ?></h3>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('support'); ?>" class="digiplanet-view-all">
                            <?php _e('View All Tickets', 'digiplanet-digital-products'); ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($support_tickets)): ?>
                        <div class="digiplanet-tickets-list">
                            <table>
                                <thead>
                                    <tr>
                                        <th><?php _e('Ticket #', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Subject', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Priority', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Last Update', 'digiplanet-digital-products'); ?></th>
                                        <th><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($support_tickets as $ticket): ?>
                                        <tr>
                                            <td>#<?php echo $ticket->id; ?></td>
                                            <td><?php echo esc_html($ticket->subject); ?></td>
                                            <td>
                                                <span class="digiplanet-status-badge digiplanet-status-<?php echo $ticket->status; ?>">
                                                    <?php echo ucfirst($ticket->status); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="digiplanet-priority-badge digiplanet-priority-<?php echo $ticket->priority; ?>">
                                                    <?php echo ucfirst($ticket->priority); ?>
                                                </span>
                                            </td>
                                            <td><?php echo human_time_diff(strtotime($ticket->updated_at), current_time('timestamp')); ?> ago</td>
                                            <td>
                                                <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('support') . '?ticket=' . $ticket->id; ?>" class="digiplanet-btn digiplanet-btn-sm">
                                                    <?php _e('View', 'digiplanet-digital-products'); ?>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="digiplanet-no-data"><?php _e('No support tickets found.', 'digiplanet-digital-products'); ?></p>
                        <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('support'); ?>?action=new" class="digiplanet-btn digiplanet-btn-primary">
                                            <i class="fas fa-plus"></i>
                                            <?php _e('Create New Ticket', 'digiplanet-digital-products'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        return ob_get_clean();
                    }
                    
                    private function get_client_projects($user_id) {
                        global $wpdb;
                        
                        return $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM {$wpdb->prefix}digiplanet_projects 
                            WHERE client_id = %d 
                            AND status IN ('active', 'in_progress', 'pending') 
                            ORDER BY deadline ASC 
                            LIMIT 5
                        ", $user_id));
                    }
                    
                    private function get_client_projects_count($user_id) {
                        global $wpdb;
                        
                        return $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_projects 
                            WHERE client_id = %d
                        ", $user_id));
                    }
                    
                    private function get_client_support_tickets($user_id) {
                        global $wpdb;
                        
                        return $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM {$wpdb->prefix}digiplanet_support_tickets 
                            WHERE client_id = %d 
                            ORDER BY updated_at DESC 
                            LIMIT 5
                        ", $user_id));
                    }
                    
                    private function get_client_documents($user_id) {
                        global $wpdb;
                        
                        return $wpdb->get_results($wpdb->prepare("
                            SELECT * FROM {$wpdb->prefix}digiplanet_documents 
                            WHERE client_id = %d 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ", $user_id));
                    }
                    
                    private function get_upcoming_milestones_count($user_id) {
                        global $wpdb;
                        
                        return $wpdb->get_var($wpdb->prepare("
                            SELECT COUNT(*) FROM {$wpdb->prefix}digiplanet_project_milestones 
                            WHERE project_id IN (
                                SELECT id FROM {$wpdb->prefix}digiplanet_projects 
                                WHERE client_id = %d
                            ) 
                            AND due_date >= CURDATE() 
                            AND status = 'pending'
                        ", $user_id));
                    }
                    
                    public function create_support_ticket($user_id, $data) {
                        global $wpdb;
                        
                        $ticket_data = [
                            'client_id' => $user_id,
                            'subject' => sanitize_text_field($data['subject']),
                            'description' => wp_kses_post($data['description']),
                            'priority' => sanitize_text_field($data['priority']),
                            'category' => sanitize_text_field($data['category']),
                            'status' => 'open',
                            'created_at' => current_time('mysql'),
                            'updated_at' => current_time('mysql'),
                        ];
                        
                        $result = $wpdb->insert(
                            $wpdb->prefix . 'digiplanet_support_tickets',
                            $ticket_data
                        );
                        
                        if ($result) {
                            $ticket_id = $wpdb->insert_id;
                            
                            // Send notification email
                            $this->send_ticket_created_notification($ticket_id);
                            
                            return [
                                'success' => true,
                                'ticket_id' => $ticket_id,
                                'message' => __('Support ticket created successfully.', 'digiplanet-digital-products')
                            ];
                        }
                        
                        return [
                            'success' => false,
                            'message' => __('Failed to create support ticket.', 'digiplanet-digital-products')
                        ];
                    }
                    
                    private function send_ticket_created_notification($ticket_id) {
                        global $wpdb;
                        
                        $ticket = $wpdb->get_row($wpdb->prepare("
                            SELECT t.*, u.user_email, u.display_name 
                            FROM {$wpdb->prefix}digiplanet_support_tickets t
                            INNER JOIN {$wpdb->users} u ON t.client_id = u.ID
                            WHERE t.id = %d
                        ", $ticket_id));
                        
                        if (!$ticket) return;
                        
                        $admin_email = get_option('admin_email');
                        $subject = sprintf(__('New Support Ticket #%d: %s', 'digiplanet-digital-products'), $ticket_id, $ticket->subject);
                        
                        $message = sprintf(__('A new support ticket has been created by %s.', 'digiplanet-digital-products'), $ticket->display_name) . "\n\n";
                        $message .= sprintf(__('Ticket #: %d', 'digiplanet-digital-products'), $ticket_id) . "\n";
                        $message .= sprintf(__('Subject: %s', 'digiplanet-digital-products'), $ticket->subject) . "\n";
                        $message .= sprintf(__('Priority: %s', 'digiplanet-digital-products'), ucfirst($ticket->priority)) . "\n";
                        $message .= sprintf(__('Category: %s', 'digiplanet-digital-products'), $ticket->category) . "\n\n";
                        $message .= __('Description:', 'digiplanet-digital-products') . "\n";
                        $message .= $ticket->description . "\n\n";
                        $message .= __('Please log in to the admin area to respond to this ticket.', 'digiplanet-digital-products');
                        
                        wp_mail($admin_email, $subject, $message);
                    }
                }
                ```

## 4. modules/user-portal/templates/my-licenses.php

```php
<?php
/**
 * My Licenses Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$account_manager = Digiplanet_Account_Manager::get_instance();
$licenses = $account_manager->get_customer_licenses($user_id);
?>

<div class="digiplanet-my-licenses">
    <div class="digiplanet-page-header">
        <h2><?php _e('My Licenses', 'digiplanet-digital-products'); ?></h2>
        <p><?php _e('Manage your software licenses and activation details.', 'digiplanet-digital-products'); ?></p>
    </div>
    
    <?php if (!empty($licenses)): ?>
        <div class="digiplanet-licenses-table-wrapper">
            <table class="digiplanet-licenses-table">
                <thead>
                    <tr>
                        <th><?php _e('Product', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('License Key', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Status', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Type', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Activations', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Created', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Expires', 'digiplanet-digital-products'); ?></th>
                        <th><?php _e('Actions', 'digiplanet-digital-products'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($licenses as $license): ?>
                        <?php
                        $product = get_post($license->product_id);
                        $license_type = get_post_meta($license->product_id, '_digiplanet_license_type', true) ?: 'single';
                        $expiry_date = $license->expires_at ? date_i18n(get_option('date_format'), strtotime($license->expires_at)) : __('Never', 'digiplanet-digital-products');
                        $days_left = $license->expires_at ? ceil((strtotime($license->expires_at) - time()) / DAY_IN_SECONDS) : null;
                        ?>
                        <tr>
                            <td>
                                <div class="digiplanet-license-product">
                                    <?php if (has_post_thumbnail($license->product_id)): ?>
                                        <div class="digiplanet-license-product-image">
                                            <?php echo get_the_post_thumbnail($license->product_id, 'thumbnail'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="digiplanet-license-product-info">
                                        <h4><?php echo esc_html($product->post_title); ?></h4>
                                        <?php if ($license->version): ?>
                                            <span class="digiplanet-license-version">v<?php echo esc_html($license->version); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="digiplanet-license-key-display">
                                    <code class="digiplanet-license-key" id="license-key-<?php echo $license->id; ?>">
                                        <?php echo esc_html($license->license_key); ?>
                                    </code>
                                    <div class="digiplanet-license-actions">
                                        <button type="button" class="digiplanet-btn-copy" data-clipboard-target="#license-key-<?php echo $license->id; ?>">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                        <button type="button" class="digiplanet-btn-view" onclick="toggleLicenseKey(<?php echo $license->id; ?>)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="digiplanet-status-badge digiplanet-status-<?php echo $license->status; ?>">
                                    <?php echo ucfirst($license->status); ?>
                                </span>
                            </td>
                            <td>
                                <span class="digiplanet-license-type"><?php echo ucfirst($license_type); ?></span>
                            </td>
                            <td>
                                <div class="digiplanet-activations-info">
                                    <span class="digiplanet-activations-count"><?php echo $license->activation_count; ?> / <?php echo $license->max_activations; ?></span>
                                    <?php if ($license->activation_count > 0): ?>
                                        <button type="button" class="digiplanet-btn-view-activations" onclick="viewActivations(<?php echo $license->id; ?>)">
                                            <i class="fas fa-list"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <?php echo date_i18n(get_option('date_format'), strtotime($license->created_at)); ?>
                            </td>
                            <td>
                                <?php if ($license->expires_at): ?>
                                    <div class="digiplanet-expiry-info">
                                        <span><?php echo $expiry_date; ?></span>
                                        <?php if ($days_left !== null && $days_left > 0): ?>
                                            <span class="digiplanet-days-left">(<?php printf(__('%d days left', 'digiplanet-digital-products'), $days_left); ?>)</span>
                                        <?php elseif ($days_left !== null && $days_left <= 0): ?>
                                            <span class="digiplanet-expired"><?php _e('Expired', 'digiplanet-digital-products'); ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <?php _e('Never', 'digiplanet-digital-products'); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="digiplanet-license-table-actions">
                                    <button type="button" class="digiplanet-btn digiplanet-btn-sm" onclick="activateLicense(<?php echo $license->id; ?>)">
                                        <i class="fas fa-plug"></i>
                                        <?php _e('Activate', 'digiplanet-digital-products'); ?>
                                    </button>
                                    <?php if ($license->activation_count > 0): ?>
                                        <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-btn-secondary" onclick="deactivateLicense(<?php echo $license->id; ?>)">
                                            <i class="fas fa-unplug"></i>
                                            <?php _e('Deactivate', 'digiplanet-digital-products'); ?>
                                        </button>
                                    <?php endif; ?>
                                    <button type="button" class="digiplanet-btn digiplanet-btn-sm digiplanet-btn-outline" onclick="downloadLicenseFile(<?php echo $license->id; ?>)">
                                        <i class="fas fa-download"></i>
                                        <?php _e('Download', 'digiplanet-digital-products'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        
                        <!-- Activations Modal (hidden by default) -->
                        <div id="activations-modal-<?php echo $license->id; ?>" class="digiplanet-modal" style="display: none;">
                            <div class="digiplanet-modal-content">
                                <div class="digiplanet-modal-header">
                                    <h3><?php _e('License Activations', 'digiplanet-digital-products'); ?></h3>
                                    <button type="button" class="digiplanet-modal-close" onclick="closeModal('activations-modal-<?php echo $license->id; ?>')">&times;</button>
                                </div>
                                <div class="digiplanet-modal-body">
                                    <div class="digiplanet-activations-list" id="activations-list-<?php echo $license->id; ?>">
                                        <!-- Activations will be loaded here via AJAX -->
                                        <div class="digiplanet-loading">
                                            <i class="fas fa-spinner fa-spin"></i>
                                            <?php _e('Loading activations...', 'digiplanet-digital-products'); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- License Filters -->
        <div class="digiplanet-license-filters">
            <h3><?php _e('Filter Licenses', 'digiplanet-digital-products'); ?></h3>
            <div class="digiplanet-filter-controls">
                <select id="license-status-filter" class="digiplanet-filter-select">
                    <option value=""><?php _e('All Statuses', 'digiplanet-digital-products'); ?></option>
                    <option value="active"><?php _e('Active', 'digiplanet-digital-products'); ?></option>
                    <option value="inactive"><?php _e('Inactive', 'digiplanet-digital-products'); ?></option>
                    <option value="expired"><?php _e('Expired', 'digiplanet-digital-products'); ?></option>
                </select>
                
                <select id="license-type-filter" class="digiplanet-filter-select">
                    <option value=""><?php _e('All Types', 'digiplanet-digital-products'); ?></option>
                    <option value="single"><?php _e('Single', 'digiplanet-digital-products'); ?></option>
                    <option value="multi"><?php _e('Multi-site', 'digiplanet-digital-products'); ?></option>
                    <option value="developer"><?php _e('Developer', 'digiplanet-digital-products'); ?></option>
                    <option value="lifetime"><?php _e('Lifetime', 'digiplanet-digital-products'); ?></option>
                </select>
                
                <input type="text" id="license-search" class="digiplanet-filter-input" placeholder="<?php _e('Search by product or license key...', 'digiplanet-digital-products'); ?>">
                
                <button type="button" class="digiplanet-btn digiplanet-btn-primary" onclick="filterLicenses()">
                    <i class="fas fa-filter"></i>
                    <?php _e('Filter', 'digiplanet-digital-products'); ?>
                </button>
                
                <button type="button" class="digiplanet-btn digiplanet-btn-secondary" onclick="resetFilters()">
                    <i class="fas fa-redo"></i>
                    <?php _e('Reset', 'digiplanet-digital-products'); ?>
                </button>
            </div>
        </div>
        
        <!-- Export Options -->
        <div class="digiplanet-export-options">
            <h3><?php _e('Export Licenses', 'digiplanet-digital-products'); ?></h3>
            <div class="digiplanet-export-buttons">
                <button type="button" class="digiplanet-btn digiplanet-btn-outline" onclick="exportLicenses('csv')">
                    <i class="fas fa-file-csv"></i>
                    <?php _e('Export as CSV', 'digiplanet-digital-products'); ?>
                </button>
                <button type="button" class="digiplanet-btn digiplanet-btn-outline" onclick="exportLicenses('pdf')">
                    <i class="fas fa-file-pdf"></i>
                    <?php _e('Export as PDF', 'digiplanet-digital-products'); ?>
                </button>
                <button type="button" class="digiplanet-btn digiplanet-btn-outline" onclick="printLicenses()">
                    <i class="fas fa-print"></i>
                    <?php _e('Print Licenses', 'digiplanet-digital-products'); ?>
                </button>
            </div>
        </div>
        
        <!-- Bulk Actions -->
        <div class="digiplanet-bulk-actions">
            <h3><?php _e('Bulk Actions', 'digiplanet-digital-products'); ?></h3>
            <div class="digiplanet-bulk-controls">
                <select id="bulk-action-select" class="digiplanet-bulk-select">
                    <option value=""><?php _e('Select Action', 'digiplanet-digital-products'); ?></option>
                    <option value="export"><?php _e('Export Selected', 'digiplanet-digital-products'); ?></option>
                    <option value="deactivate"><?php _e('Deactivate All', 'digiplanet-digital-products'); ?></option>
                    <option value="refresh"><?php _e('Refresh Licenses', 'digiplanet-digital-products'); ?></option>
                </select>
                
                <button type="button" class="digiplanet-btn digiplanet-btn-primary" onclick="performBulkAction()">
                    <i class="fas fa-play"></i>
                    <?php _e('Apply', 'digiplanet-digital-products'); ?>
                </button>
                
                <label class="digiplanet-checkbox-label">
                    <input type="checkbox" id="select-all-licenses">
                    <?php _e('Select All', 'digiplanet-digital-products'); ?>
                </label>
            </div>
        </div>
    <?php else: ?>
        <div class="digiplanet-no-licenses">
            <div class="digiplanet-no-licenses-icon">
                <i class="fas fa-key"></i>
            </div>
            <h3><?php _e('No Licenses Found', 'digiplanet-digital-products'); ?></h3>
            <p><?php _e('You don\'t have any software licenses yet. Purchase a digital product to get your first license.', 'digiplanet-digital-products'); ?></p>
            <div class="digiplanet-no-licenses-actions">
                <a href="<?php echo get_post_type_archive_link('digiplanet_product'); ?>" class="digiplanet-btn digiplanet-btn-primary">
                    <i class="fas fa-shopping-cart"></i>
                    <?php _e('Browse Products', 'digiplanet-digital-products'); ?>
                </a>
                <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('products'); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                    <i class="fas fa-box-open"></i>
                    <?php _e('View My Products', 'digiplanet-digital-products'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Activation Form Modal -->
<div id="activation-modal" class="digiplanet-modal" style="display: none;">
    <div class="digiplanet-modal-content">
        <div class="digiplanet-modal-header">
            <h3><?php _e('Activate License', 'digiplanet-digital-products'); ?></h3>
            <button type="button" class="digiplanet-modal-close" onclick="closeModal('activation-modal')">&times;</button>
        </div>
        <div class="digiplanet-modal-body">
            <form id="activation-form" method="post">
                <input type="hidden" id="activation-license-id" name="license_id">
                
                <div class="digiplanet-form-group">
                    <label for="activation-domain"><?php _e('Domain/Website URL', 'digiplanet-digital-products'); ?> *</label>
                    <input type="url" id="activation-domain" name="domain" required placeholder="https://example.com">
                    <p class="digiplanet-form-help"><?php _e('Enter the domain where you want to activate this license.', 'digiplanet-digital-products'); ?></p>
                </div>
                
                <div class="digiplanet-form-group">
                    <label for="activation-ip"><?php _e('IP Address', 'digiplanet-digital-products'); ?></label>
                    <input type="text" id="activation-ip" name="ip_address" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>" readonly>
                </div>
                
                <div class="digiplanet-form-group">
                    <label for="activation-notes"><?php _e('Notes (Optional)', 'digiplanet-digital-products'); ?></label>
                    <textarea id="activation-notes" name="notes" rows="3" placeholder="<?php _e('Any additional notes about this activation...', 'digiplanet-digital-products'); ?>"></textarea>
                </div>
                
                <div class="digiplanet-form-actions">
                    <button type="submit" class="digiplanet-btn digiplanet-btn-primary">
                        <i class="fas fa-plug"></i>
                        <?php _e('Activate License', 'digiplanet-digital-products'); ?>
                    </button>
                    <button type="button" class="digiplanet-btn digiplanet-btn-secondary" onclick="closeModal('activation-modal')">
                        <?php _e('Cancel', 'digiplanet-digital-products'); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Initialize clipboard
    new ClipboardJS('.digiplanet-btn-copy');
    
    // Show copy confirmation
    $('.digiplanet-btn-copy').on('click', function() {
        var $button = $(this);
        var originalHTML = $button.html();
        
        $button.html('<i class="fas fa-check"></i>');
        $button.addClass('copied');
        
        setTimeout(function() {
            $button.html(originalHTML);
            $button.removeClass('copied');
        }, 2000);
    });
    
    // Handle select all checkbox
    $('#select-all-licenses').on('change', function() {
        var isChecked = $(this).is(':checked');
        $('.license-checkbox').prop('checked', isChecked);
    });
    
    // Handle activation form submission
    $('#activation-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serialize();
        
        $.ajax({
            url: digiplanet_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'digiplanet_activate_license',
                data: formData,
                nonce: digiplanet_ajax.nonce
            },
            beforeSend: function() {
                $('#activation-form button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Activating...');
            },
            success: function(response) {
                if (response.success) {
                    showNotification(response.data.message, 'success');
                    setTimeout(function() {
                        location.reload();
                    }, 2000);
                } else {
                    showNotification(response.data.message, 'error');
                    $('#activation-form button[type="submit"]').prop('disabled', false).html('<i class="fas fa-plug"></i> Activate License');
                }
            },
            error: function() {
                showNotification('An error occurred. Please try again.', 'error');
                $('#activation-form button[type="submit"]').prop('disabled', false).html('<i class="fas fa-plug"></i> Activate License');
            }
        });
    });
});

function toggleLicenseKey(licenseId) {
    var $key = $('#license-key-' + licenseId);
    var $button = $key.siblings('.digiplanet-license-actions').find('.digiplanet-btn-view');
    
    if ($key.hasClass('masked')) {
        $key.removeClass('masked');
        $button.html('<i class="fas fa-eye-slash"></i>');
    } else {
        $key.addClass('masked');
        $button.html('<i class="fas fa-eye"></i>');
    }
}

function viewActivations(licenseId) {
    $('#activations-modal-' + licenseId).show();
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_get_license_activations',
            license_id: licenseId,
            nonce: digiplanet_ajax.nonce
        },
        success: function(response) {
            if (response.success) {
                $('#activations-list-' + licenseId).html(response.data.html);
            } else {
                $('#activations-list-' + licenseId).html('<p class="digiplanet-error">' + response.data.message + '</p>');
            }
        }
    });
}

function activateLicense(licenseId) {
    $('#activation-license-id').val(licenseId);
    $('#activation-modal').show();
}

function deactivateLicense(licenseId) {
    if (!confirm('Are you sure you want to deactivate this license? This will remove all activations.')) {
        return;
    }
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_deactivate_license',
            license_id: licenseId,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            showNotification('Deactivating license...', 'info');
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showNotification(response.data.message, 'error');
            }
        }
    });
}

function filterLicenses() {
    var status = $('#license-status-filter').val();
    var type = $('#license-type-filter').val();
    var search = $('#license-search').val();
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_filter_licenses',
            status: status,
            type: type,
            search: search,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            $('.digiplanet-licenses-table-wrapper').addClass('loading');
        },
        success: function(response) {
            if (response.success) {
                $('.digiplanet-licenses-table-wrapper').html(response.data.html);
            } else {
                showNotification(response.data.message, 'error');
            }
        },
        complete: function() {
            $('.digiplanet-licenses-table-wrapper').removeClass('loading');
        }
    });
}

function resetFilters() {
    $('#license-status-filter').val('');
    $('#license-type-filter').val('');
    $('#license-search').val('');
    filterLicenses();
}

function exportLicenses(format) {
    var selectedLicenses = [];
    $('.license-checkbox:checked').each(function() {
        selectedLicenses.push($(this).val());
    });
    
    if (selectedLicenses.length === 0) {
        selectedLicenses = 'all';
    }
    
    window.location.href = digiplanet_ajax.ajax_url + '?action=digiplanet_export_licenses&format=' + format + '&licenses=' + selectedLicenses + '&nonce=' + digiplanet_ajax.nonce;
}

function printLicenses() {
    var originalContent = $('body').html();
    var printContent = $('.digiplanet-my-licenses').html();
    
    $('body').html('<div class="digiplanet-print-content">' + printContent + '</div>');
    window.print();
    $('body').html(originalContent);
}

function performBulkAction() {
    var action = $('#bulk-action-select').val();
    
    if (!action) {
        showNotification('Please select an action.', 'warning');
        return;
    }
    
    switch (action) {
        case 'export':
            exportLicenses('csv');
            break;
        case 'deactivate':
            deactivateSelectedLicenses();
            break;
        case 'refresh':
            refreshLicenses();
            break;
    }
}

function deactivateSelectedLicenses() {
    var selectedLicenses = [];
    $('.license-checkbox:checked').each(function() {
        selectedLicenses.push($(this).val());
    });
    
    if (selectedLicenses.length === 0) {
        showNotification('Please select at least one license.', 'warning');
        return;
    }
    
    if (!confirm('Are you sure you want to deactivate ' + selectedLicenses.length + ' license(s)?')) {
        return;
    }
    
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_bulk_deactivate_licenses',
            licenses: selectedLicenses,
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            showNotification('Deactivating licenses...', 'info');
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                showNotification(response.data.message, 'error');
            }
        }
    });
}

function refreshLicenses() {
    $.ajax({
        url: digiplanet_ajax.ajax_url,
        type: 'POST',
        data: {
            action: 'digiplanet_refresh_licenses',
            nonce: digiplanet_ajax.nonce
        },
        beforeSend: function() {
            showNotification('Refreshing licenses...', 'info');
        },
        success: function(response) {
            if (response.success) {
                showNotification(response.data.message, 'success');
                setTimeout(function() {
                    location.reload();
                }, 1000);
            } else {
                showNotification(response.data.message, 'error');
            }
        }
    });
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}

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
.digiplanet-my-licenses {
    padding: 30px;
}

.digiplanet-license-key {
    font-family: 'Courier New', monospace;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    font-size: 14px;
}

.digiplanet-license-key.masked {
    filter: blur(4px);
    user-select: none;
}

.digiplanet-license-actions {
    display: flex;
    gap: 5px;
    margin-top: 5px;
}

.digiplanet-btn-copy,
.digiplanet-btn-view {
    padding: 5px 10px;
    font-size: 12px;
    border: none;
    background: #e9ecef;
    border-radius: 4px;
    cursor: pointer;
}

.digiplanet-btn-copy:hover,
.digiplanet-btn-view:hover {
    background: #dee2e6;
}

.digiplanet-btn-copy.copied {
    background: #28a745;
    color: white;
}

.digiplanet-activations-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.digiplanet-days-left {
    font-size: 12px;
    color: #6c757d;
}

.digiplanet-expired {
    color: #dc3545;
    font-weight: bold;
}

.digiplanet-license-filters,
.digiplanet-export-options,
.digiplanet-bulk-actions {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.digiplanet-filter-controls,
.digiplanet-export-buttons,
.digiplanet-bulk-controls {
    display: flex;
    gap: 15px;
    align-items: center;
    flex-wrap: wrap;
}

.digiplanet-no-licenses {
    text-align: center;
    padding: 60px 30px;
}

.digiplanet-no-licenses-icon {
    font-size: 64px;
    color: #6c757d;
    margin-bottom: 20px;
}

.digiplanet-no-licenses-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 30px;
}
</style>