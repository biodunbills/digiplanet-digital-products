<?php
/**
 * Software Client Dashboard Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user = wp_get_current_user();
?>

<div class="digiplanet-software-client-dashboard">
    <!-- Welcome Section -->
    <div class="digiplanet-welcome-section">
        <h1><?php printf(__('Welcome, %s!', 'digiplanet-digital-products'), esc_html($user->display_name)); ?></h1>
        <p><?php _e('Your dedicated software client portal for project management and support.', 'digiplanet-digital-products'); ?></p>
    </div>
    
    <!-- Client Overview -->
    <div class="digiplanet-client-overview">
        <div class="digiplanet-client-info">
            <div class="digiplanet-client-avatar">
                <?php echo get_avatar($user->ID, 100); ?>
            </div>
            <div class="digiplanet-client-details">
                <h2><?php echo esc_html($user->display_name); ?></h2>
                <p class="digiplanet-client-email"><?php echo esc_html($user->user_email); ?></p>
                <p class="digiplanet-client-role">
                    <span class="dashicons dashicons-businessperson"></span>
                    <?php _e('Software Client', 'digiplanet-digital-products'); ?>
                </p>
                <p class="digiplanet-client-since">
                    <span class="dashicons dashicons-calendar"></span>
                    <?php 
                    printf(
                        __('Client since %s', 'digiplanet-digital-products'),
                        date_i18n(get_option('date_format'), strtotime($user->user_registered))
                    );
                    ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="digiplanet-quick-actions">
        <h2><?php _e('Quick Actions', 'digiplanet-digital-products'); ?></h2>
        <div class="digiplanet-actions-grid">
            <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('projects'); ?>" class="digiplanet-action-card">
                <div class="digiplanet-action-icon">
                    <span class="dashicons dashicons-portfolio"></span>
                </div>
                <h3><?php _e('View Projects', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Check the status of your software projects', 'digiplanet-digital-products'); ?></p>
            </a>
            
            <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('support'); ?>" class="digiplanet-action-card">
                <div class="digiplanet-action-icon">
                    <span class="dashicons dashicons-sos"></span>
                </div>
                <h3><?php _e('Support Tickets', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Get help with your software or report issues', 'digiplanet-digital-products'); ?></p>
            </a>
            
            <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('documents'); ?>" class="digiplanet-action-card">
                <div class="digiplanet-action-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <h3><?php _e('Documents', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Access documentation, guides, and resources', 'digiplanet-digital-products'); ?></p>
            </a>
            
            <a href="<?php echo Digiplanet_User_Portal::get_instance()->get_account_url('settings'); ?>" class="digiplanet-action-card">
                <div class="digiplanet-action-icon">
                    <span class="dashicons dashicons-admin-generic"></span>
                </div>
                <h3><?php _e('Account Settings', 'digiplanet-digital-products'); ?></h3>
                <p><?php _e('Update your profile and preferences', 'digiplanet-digital-products'); ?></p>
            </a>
        </div>
    </div>
    
    <!-- Client Status -->
    <div class="digiplanet-client-status">
        <h2><?php _e('Your Client Status', 'digiplanet-digital-products'); ?></h2>
        <div class="digiplanet-status-grid">
            <div class="digiplanet-status-item">
                <div class="digiplanet-status-header">
                    <span class="digiplanet-status-title"><?php _e('Active Projects', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-status-value"><?php echo esc_html($this->get_active_project_count($user->ID)); ?></span>
                </div>
                <div class="digiplanet-status-progress">
                    <div class="digiplanet-progress-bar">
                        <div class="digiplanet-progress-fill" style="width: <?php echo $this->get_project_progress($user->ID); ?>%"></div>
                    </div>
                    <span class="digiplanet-progress-text">
                        <?php echo $this->get_project_progress($user->ID); ?>% <?php _e('Overall Progress', 'digiplanet-digital-products'); ?>
                    </span>
                </div>
            </div>
            
            <div class="digiplanet-status-item">
                <div class="digiplanet-status-header">
                    <span class="digiplanet-status-title"><?php _e('Open Support Tickets', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-status-value"><?php echo esc_html($this->get_open_ticket_count($user->ID)); ?></span>
                </div>
                <div class="digiplanet-status-meta">
                    <?php 
                    $avg_response_time = $this->get_avg_response_time($user->ID);
                    if ($avg_response_time): ?>
                        <span class="digiplanet-response-time">
                            <span class="dashicons dashicons-clock"></span>
                            <?php printf(__('Avg response: %s hours', 'digiplanet-digital-products'), $avg_response_time); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="digiplanet-status-item">
                <div class="digiplanet-status-header">
                    <span class="digiplanet-status-title"><?php _e('Upcoming Deliverables', 'digiplanet-digital-products'); ?></span>
                    <span class="digiplanet-status-value"><?php echo esc_html($this->get_upcoming_deliverables_count($user->ID)); ?></span>
                </div>
                <div class="digiplanet-status-meta">
                    <?php
                    $next_delivery = $this->get_next_delivery_date($user->ID);
                    if ($next_delivery): ?>
                        <span class="digiplanet-next-delivery">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php printf(__('Next: %s', 'digiplanet-digital-products'), date_i18n(get_option('date_format'), strtotime($next_delivery))); ?>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="digiplanet-recent-activity">
        <h2><?php _e('Recent Activity', 'digiplanet-digital-products'); ?></h2>
        <div class="digiplanet-activity-timeline">
            <?php
            $recent_activities = $this->get_recent_activities($user->ID, 5);
            if (!empty($recent_activities)):
                foreach ($recent_activities as $activity): ?>
                    <div class="digiplanet-activity-item">
                        <div class="digiplanet-activity-icon">
                            <span class="dashicons <?php echo esc_attr($activity['icon']); ?>"></span>
                        </div>
                        <div class="digiplanet-activity-content">
                            <p><?php echo wp_kses_post($activity['description']); ?></p>
                            <span class="digiplanet-activity-time">
                                <?php echo human_time_diff(strtotime($activity['time']), current_time('timestamp')); ?> <?php _e('ago', 'digiplanet-digital-products'); ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach;
            else: ?>
                <div class="digiplanet-no-activity">
                    <p><?php _e('No recent activity found.', 'digiplanet-digital-products'); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Client Announcements -->
    <?php $announcements = $this->get_client_announcements($user->ID); ?>
    <?php if (!empty($announcements)): ?>
        <div class="digiplanet-client-announcements">
            <h2><?php _e('Announcements', 'digiplanet-digital-products'); ?></h2>
            <div class="digiplanet-announcements-grid">
                <?php foreach ($announcements as $announcement): ?>
                    <div class="digiplanet-announcement-card">
                        <div class="digiplanet-announcement-header">
                            <span class="digiplanet-announcement-badge"><?php echo esc_html($announcement['type']); ?></span>
                            <span class="digiplanet-announcement-date"><?php echo date_i18n(get_option('date_format'), strtotime($announcement['date'])); ?></span>
                        </div>
                        <h3><?php echo esc_html($announcement['title']); ?></h3>
                        <p><?php echo wp_kses_post($announcement['content']); ?></p>
                        <?php if (!empty($announcement['action_url'])): ?>
                            <a href="<?php echo esc_url($announcement['action_url']); ?>" class="digiplanet-btn digiplanet-btn-secondary">
                                <?php echo esc_html($announcement['action_text']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.digiplanet-software-client-dashboard {
    padding: 20px;
}

.digiplanet-welcome-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.digiplanet-welcome-section h1 {
    margin: 0 0 10px 0;
    color: #1d2327;
    font-size: 32px;
}

.digiplanet-welcome-section p {
    margin: 0;
    color: #646970;
    font-size: 18px;
}

.digiplanet-client-overview {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    color: white;
}

.digiplanet-client-info {
    display: flex;
    align-items: center;
    gap: 30px;
}

.digiplanet-client-avatar {
    flex-shrink: 0;
}

.digiplanet-client-avatar img {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    border: 3px solid rgba(255,255,255,0.3);
}

.digiplanet-client-details h2 {
    margin: 0 0 10px 0;
    font-size: 28px;
    color: white;
}

.digiplanet-client-email {
    margin: 0 0 10px 0;
    opacity: 0.9;
    font-size: 16px;
}

.digiplanet-client-role,
.digiplanet-client-since {
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
    opacity: 0.9;
}

.digiplanet-client-role .dashicons,
.digiplanet-client-since .dashicons {
    font-size: 16px;
}

.digiplanet-quick-actions {
    margin-bottom: 40px;
}

.digiplanet-quick-actions h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-actions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.digiplanet-action-card {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 12px;
    padding: 25px;
    text-decoration: none;
    color: inherit;
    display: block;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.digiplanet-action-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #2271b1;
}

.digiplanet-action-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.3s;
}

.digiplanet-action-card:hover::before {
    opacity: 1;
}

.digiplanet-action-icon {
    margin-bottom: 15px;
}

.digiplanet-action-icon .dashicons {
    font-size: 32px;
    width: 32px;
    height: 32px;
    color: #2271b1;
}

.digiplanet-action-card h3 {
    margin: 0 0 10px 0;
    font-size: 18px;
    color: #1d2327;
}

.digiplanet-action-card p {
    margin: 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.5;
}

.digiplanet-client-status {
    margin-bottom: 40px;
}

.digiplanet-client-status h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.digiplanet-status-item {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 12px;
    padding: 20px;
}

.digiplanet-status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.digiplanet-status-title {
    font-weight: 500;
    color: #646970;
    font-size: 14px;
}

.digiplanet-status-value {
    font-size: 24px;
    font-weight: 600;
    color: #1d2327;
}

.digiplanet-status-progress {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.digiplanet-progress-bar {
    height: 8px;
    background: #f0f0f1;
    border-radius: 4px;
    overflow: hidden;
}

.digiplanet-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #46b450 0%, #7ad03a 100%);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.digiplanet-progress-text {
    font-size: 12px;
    color: #646970;
    text-align: right;
}

.digiplanet-status-meta {
    display: flex;
    flex-direction: column;
    gap: 5px;
    margin-top: 10px;
}

.digiplanet-response-time,
.digiplanet-next-delivery {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    color: #646970;
}

.digiplanet-response-time .dashicons,
.digiplanet-next-delivery .dashicons {
    font-size: 14px;
}

.digiplanet-recent-activity {
    margin-bottom: 40px;
}

.digiplanet-recent-activity h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-activity-timeline {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 12px;
    padding: 20px;
}

.digiplanet-activity-item {
    display: flex;
    gap: 15px;
    padding: 15px;
    border-bottom: 1px solid #f0f0f1;
}

.digiplanet-activity-item:last-child {
    border-bottom: none;
}

.digiplanet-activity-icon {
    flex-shrink: 0;
}

.digiplanet-activity-icon .dashicons {
    font-size: 20px;
    width: 20px;
    height: 20px;
    color: #2271b1;
}

.digiplanet-activity-content {
    flex: 1;
}

.digiplanet-activity-content p {
    margin: 0 0 5px 0;
    color: #1d2327;
    font-size: 14px;
}

.digiplanet-activity-time {
    font-size: 12px;
    color: #646970;
}

.digiplanet-no-activity {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.digiplanet-client-announcements h2 {
    margin: 0 0 20px 0;
    font-size: 24px;
    color: #1d2327;
}

.digiplanet-announcements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.digiplanet-announcement-card {
    background: white;
    border: 1px solid #dcdcde;
    border-radius: 12px;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

.digiplanet-announcement-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: #2271b1;
}

.digiplanet-announcement-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.digiplanet-announcement-badge {
    padding: 4px 8px;
    background: #f0f6fc;
    color: #2271b1;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    text-transform: uppercase;
}

.digiplanet-announcement-date {
    font-size: 12px;
    color: #646970;
}

.digiplanet-announcement-card h3 {
    margin: 0 0 10px 0;
    font-size: 16px;
    color: #1d2327;
}

.digiplanet-announcement-card p {
    margin: 0 0 15px 0;
    color: #646970;
    font-size: 14px;
    line-height: 1.5;
}

.digiplanet-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    text-decoration: none;
    border-radius: 4px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.2s;
}

.digiplanet-btn-primary {
    background: #2271b1;
    color: white;
    border-color: #2271b1;
}

.digiplanet-btn-primary:hover {
    background: #135e96;
    border-color: #135e96;
    color: white;
}

.digiplanet-btn-secondary {
    background: transparent;
    color: #2271b1;
    border-color: #2271b1;
}

.digiplanet-btn-secondary:hover {
    background: #f0f6fc;
}
</style>