<?php
/**
 * WebAssets AI Assistant — Logger — v2.4.0
 *
 * Handles logging of backend events into a custom database table.
 * v2.4.0: Added session_id + trace_id columns for client-side correlation.
 */

if (!defined('ABSPATH')) exit;

class WebAssetsAI_Logger {

    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . 'waai_logs';
    }

    public static function create_table() {
        global $wpdb;
        // Don't run outside WP
        if (!isset($wpdb)) return;
        
        $table_name = self::get_table_name();
        $charset_collate = $wpdb->get_charset_collate();

        // dbDelta safely adds new columns to existing tables without data loss.
        $sql = "CREATE TABLE $table_name (
            id         bigint(20)   NOT NULL AUTO_INCREMENT,
            created_at datetime     DEFAULT CURRENT_TIMESTAMP NOT NULL,
            level      varchar(20)  NOT NULL,
            event      varchar(255) NOT NULL,
            context    text         DEFAULT '' NOT NULL,
            session_id varchar(36)  DEFAULT '' NOT NULL,
            trace_id   varchar(36)  DEFAULT '' NOT NULL,
            PRIMARY KEY  (id),
            KEY idx_session (session_id),
            KEY idx_trace   (trace_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * @param string $level    DEBUG | INFO | WARNING | ERROR
     * @param string $event    Short event label
     * @param array  $context  Arbitrary key/value context data
     * @param string $session_id  Client session UUID (from X-WAAI-Session-ID header)
     * @param string $trace_id    Request trace UUID (from X-WAAI-Trace-ID header)
     */
    public static function log($level, $event, $context = [], $session_id = '', $trace_id = '') {
        global $wpdb;
        if (!isset($wpdb)) return;

        $table_name  = self::get_table_name();
        $context_str = is_array($context) || is_object($context) ? json_encode($context) : $context;

        // Suppress errors during insert in case table doesn't exist yet
        $wpdb->suppress_errors = true;
        $wpdb->insert(
            $table_name,
            [
                'level'      => strtoupper($level),
                'event'      => sanitize_text_field($event),
                'context'    => $context_str,
                'created_at' => current_time('mysql', 1),
                'session_id' => sanitize_text_field($session_id),
                'trace_id'   => sanitize_text_field($trace_id),
            ]
        );
        $wpdb->suppress_errors = false;
    }
    
    public static function cleanup_old_logs() {
        global $wpdb;
        if (!isset($wpdb)) return;
        $table_name = self::get_table_name();
        $wpdb->query("DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
    }
}

// Global helper function for easier logging
// Reads session/trace IDs from the global variables set by ai-proxy.php
if (!function_exists('waai_log')) {
    function waai_log($level, $event, $context = []) {
        if (!class_exists('WebAssetsAI_Logger')) return;
        $session_id = $GLOBALS['waai_session_id'] ?? '';
        $trace_id   = $GLOBALS['waai_trace_id']   ?? '';
        WebAssetsAI_Logger::log($level, $event, $context, $session_id, $trace_id);
    }
}

// Hook into WordPress daily cleanup
add_action('wp_scheduled_delete', ['WebAssetsAI_Logger', 'cleanup_old_logs']);
