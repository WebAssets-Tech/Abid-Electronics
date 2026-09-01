<?php
/**
 * WebAssets AI Assistant — Logs Viewer
 */

if (!defined('ABSPATH')) exit;

class WebAssetsAI_Logs_Page {

    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu'], 20);
        add_action('admin_init', [$this, 'handle_actions']);
    }

    public function register_menu() {
        add_submenu_page(
            'webassets-ai',
            'System Logs',
            'System Logs',
            'manage_options',
            'webassets-ai-logs',
            [$this, 'render_page']
        );
    }

    public function handle_actions() {
        if (!isset($_GET['page']) || $_GET['page'] !== 'webassets-ai-logs') return;

        if (isset($_POST['waai_clear_logs']) && check_admin_referer('waai_clear_logs_action')) {
            global $wpdb;
            if (class_exists('WebAssetsAI_Logger')) {
                $table = WebAssetsAI_Logger::get_table_name();
                // Avoid TRUNCATE permissions issues by using DELETE
                $wpdb->query("DELETE FROM $table");
                add_settings_error('waai_logs', 'waai_logs_cleared', 'System logs cleared successfully.', 'success');
            }
        }
    }

    public function render_page() {
        if (!current_user_can('manage_options')) return;
        
        // Ensure table exists
        if (class_exists('WebAssetsAI_Logger')) {
            WebAssetsAI_Logger::create_table();
        }

        global $wpdb;
        $table = class_exists('WebAssetsAI_Logger') ? WebAssetsAI_Logger::get_table_name() : '';
        
        $logs = [];
        if ($table && $wpdb->get_var("SHOW TABLES LIKE '{$table}'") === $table) {
            $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY created_at DESC LIMIT 500");
        }
        
        ?>
        <div class="wrap">
            <h1>WebAssets AI System Logs</h1>
            <?php settings_errors('waai_logs'); ?>
            
            <form method="post" action="" style="margin-bottom: 15px;">
                <?php wp_nonce_field('waai_clear_logs_action'); ?>
                <input type="submit" name="waai_clear_logs" class="button button-secondary" value="Clear All Logs" onclick="return confirm('Are you sure you want to delete all logs?');">
            </form>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 15%;">Time</th>
                        <th style="width: 10%;">Level</th>
                        <th style="width: 25%;">Event</th>
                        <th style="width: 50%;">Context</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="4">No logs found.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $level_color = '#333';
                            if ($log->level === 'ERROR') $level_color = '#d63638';
                            if ($log->level === 'WARNING') $level_color = '#f0b849';
                            if ($log->level === 'INFO') $level_color = '#2271b1';
                            if ($log->level === 'DEBUG') $level_color = '#72777c';
                        ?>
                            <tr>
                                <td><?php echo esc_html($log->created_at); ?></td>
                                <td><span style="color: <?php echo $level_color; ?>; font-weight: bold;"><?php echo esc_html($log->level); ?></span></td>
                                <td><strong><?php echo esc_html($log->event); ?></strong></td>
                                <td>
                                    <pre style="margin:0; white-space: pre-wrap; font-size: 11px; background: #f6f7f7; padding: 5px; border: 1px solid #c3c4c7;"><?php 
                                        $ctx = json_decode($log->context, true);
                                        if (json_last_error() === JSON_ERROR_NONE) {
                                            // Make it readable
                                            echo esc_html(print_r($ctx, true));
                                        } else {
                                            echo esc_html($log->context);
                                        }
                                    ?></pre>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}

new WebAssetsAI_Logs_Page();
