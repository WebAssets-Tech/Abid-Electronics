<?php
/**
 * WebAssets AI Assistant — Lead Management
 * Handles: custom DB table creation, admin leads table, search, CSV export.
 * 
 * Public API: WebAssetsAI_Leads::insert($data) — callable from ai-proxy.php
 */

if (!defined('ABSPATH')) exit;

class WebAssetsAI_Leads {

    const DB_VERSION     = '1.1';
    const DB_VERSION_KEY = 'waai_leads_db_version';

    public function __construct() {
        add_action('admin_init',  [$this, 'maybe_create_table']);
        add_action('admin_menu',  [$this, 'register_menu']);
        add_action('admin_post_waai_export_leads', [$this, 'handle_csv_export']);
    }

    /* -----------------------------------------------------------------------
       Database
    ----------------------------------------------------------------------- */

    public function maybe_create_table() {
        if (get_option(self::DB_VERSION_KEY) === self::DB_VERSION) return;
        $this->create_table();
        update_option(self::DB_VERSION_KEY, self::DB_VERSION);
    }

    public function create_table() {
        global $wpdb;
        $table   = $wpdb->prefix . 'waai_leads';
        $charset = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id           BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name         VARCHAR(255)    NOT NULL DEFAULT '',
            email        VARCHAR(255)    NOT NULL DEFAULT '',
            phone        VARCHAR(100)    NOT NULL DEFAULT '',
            query        TEXT            NOT NULL DEFAULT '',
            page_url     VARCHAR(500)    NOT NULL DEFAULT '',
            email_sent   TINYINT(1)      NOT NULL DEFAULT 0,
            sheets_saved TINYINT(1)      NOT NULL DEFAULT 0,
            created_at   DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Static insert — call from ai-proxy.php:
     *   WebAssetsAI_Leads::insert([ 'name'=>..., 'email'=>..., ... ])
     */
    public static function insert($data) {
        global $wpdb;
        $table = $wpdb->prefix . 'waai_leads';
        return $wpdb->insert($table, [
            'name'         => sanitize_text_field($data['name']        ?? ''),
            'email'        => sanitize_email(     $data['email']       ?? ''),
            'phone'        => sanitize_text_field($data['phone']       ?? ''),
            'query'        => sanitize_textarea_field($data['query']   ?? ''),
            'page_url'     => esc_url_raw(        $data['page_url']    ?? ''),
            'email_sent'   => intval(             $data['email_sent']  ?? 0),
            'sheets_saved' => intval(             $data['sheets_saved']?? 0),
        ], ['%s','%s','%s','%s','%s','%d','%d']);
    }

    /* -----------------------------------------------------------------------
       Admin Menu
    ----------------------------------------------------------------------- */

    public function register_menu() {
        add_submenu_page(
            'webassets-ai',
            'AI Leads',
            'Leads',
            'manage_options',
            'waai-leads',
            [$this, 'render_leads_page']
        );
    }

    /* -----------------------------------------------------------------------
       Admin Page
    ----------------------------------------------------------------------- */

    public function render_leads_page() {
        if (!current_user_can('manage_options')) return;

        global $wpdb;
        $table = $wpdb->prefix . 'waai_leads';

        /* ---- Handle single delete ---- */
        if (
            isset($_GET['action'], $_GET['id']) &&
            $_GET['action'] === 'delete' &&
            wp_verify_nonce($_GET['_wpnonce'] ?? '', 'waai_delete_' . intval($_GET['id']))
        ) {
            $wpdb->delete($table, ['id' => intval($_GET['id'])], ['%d']);
            echo '<div class="notice notice-success is-dismissible"><p>Lead deleted.</p></div>';
        }

        /* ---- Handle bulk delete ---- */
        if (
            isset($_POST['bulk_action'], $_POST['_wpnonce']) &&
            $_POST['bulk_action'] === 'delete' &&
            wp_verify_nonce($_POST['_wpnonce'], 'waai_bulk') &&
            !empty($_POST['lead_ids'])
        ) {
            $ids = implode(',', array_map('intval', (array)$_POST['lead_ids']));
            $wpdb->query("DELETE FROM {$table} WHERE id IN ({$ids})");
            echo '<div class="notice notice-success is-dismissible"><p>Selected leads deleted.</p></div>';
        }

        /* ---- Pagination & search ---- */
        $search   = sanitize_text_field($_GET['s'] ?? '');
        $page_num = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 20;
        $offset   = ($page_num - 1) * $per_page;

        $where = '';
        if ($search) {
            $like  = '%' . $wpdb->esc_like($search) . '%';
            $where = $wpdb->prepare("WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s", $like, $like, $like);
        }

        $total       = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table} {$where}");
        $leads       = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} {$where} ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        $total_pages = max(1, ceil($total / $per_page));

        $base_url    = admin_url('admin.php?page=waai-leads' . ($search ? '&s=' . urlencode($search) : ''));
        $export_url  = wp_nonce_url(admin_url('admin-post.php?action=waai_export_leads' . ($search ? '&s=' . urlencode($search) : '')), 'waai_export_leads');
        ?>

        <div class="wrap">
            <h1 class="wp-heading-inline" style="display:flex;align-items:center;gap:10px">
                🤖 WebAssets AI — Leads
            </h1>
            <a href="<?= esc_url($export_url) ?>" class="page-title-action">⬇ Export CSV</a>
            <hr class="wp-header-end">

            <!-- Search form -->
            <form method="get" style="margin-bottom:12px">
                <input type="hidden" name="page" value="waai-leads">
                <p class="search-box" style="display:flex;gap:8px;align-items:center">
                    <input type="search" name="s" value="<?= esc_attr($search) ?>" placeholder="Search name, email, phone..." style="padding:6px 12px;border-radius:6px;border:1px solid #ddd;font-size:13px;width:280px">
                    <button type="submit" class="button">Search</button>
                    <?php if ($search): ?><a href="?page=waai-leads" class="button">✕ Clear</a><?php endif; ?>
                </p>
            </form>

            <p style="color:#646970;margin:0 0 12px;font-size:13px">
                <strong><?= number_format($total) ?></strong> lead<?= $total !== 1 ? 's' : '' ?> total<?= $search ? ' (filtered)' : '' ?>
            </p>

            <form method="post" id="waai-leads-form">
                <?php wp_nonce_field('waai_bulk'); ?>
                <input type="hidden" name="bulk_action" value="delete">

                <!-- Top tablenav -->
                <div class="tablenav top" style="display:flex;justify-content:space-between;align-items:center">
                    <div>
                        <button type="submit" class="button" onclick="return confirm('Delete all selected leads?')">Delete Selected</button>
                    </div>
                    <?php if ($total_pages > 1): ?>
                    <div style="display:flex;align-items:center;gap:8px;font-size:13px">
                        <?php if ($page_num > 1): ?>
                            <a class="button" href="<?= esc_url($base_url . '&paged=' . ($page_num - 1)) ?>">← Prev</a>
                        <?php endif; ?>
                        <span style="color:#646970">Page <?= $page_num ?> / <?= $total_pages ?></span>
                        <?php if ($page_num < $total_pages): ?>
                            <a class="button" href="<?= esc_url($base_url . '&paged=' . ($page_num + 1)) ?>">Next →</a>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <table class="wp-list-table widefat fixed striped" style="margin-top:8px">
                    <thead>
                        <tr>
                            <th style="width:32px"><input type="checkbox" id="waai-select-all"></th>
                            <th style="width:130px">Name</th>
                            <th style="width:180px">Email</th>
                            <th style="width:130px">Phone</th>
                            <th>Query</th>
                            <th style="width:110px">Page</th>
                            <th style="width:120px">Saved To</th>
                            <th style="width:140px">Date &amp; Time</th>
                            <th style="width:80px">Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="9" style="text-align:center;padding:40px;color:#646970;font-size:14px">
                                📭 No leads yet. Leads appear here when visitors fill out the chat contact form.
                            </td>
                        </tr>
                    <?php else: foreach ($leads as $lead):
                        $del_url = wp_nonce_url(
                            admin_url('admin.php?page=waai-leads&action=delete&id=' . $lead->id),
                            'waai_delete_' . $lead->id
                        );
                    ?>
                        <tr>
                            <td><input type="checkbox" name="lead_ids[]" value="<?= $lead->id ?>"></td>
                            <td><strong><?= esc_html($lead->name) ?></strong></td>
                            <td>
                                <a href="mailto:<?= esc_attr($lead->email) ?>"><?= esc_html($lead->email) ?></a>
                            </td>
                            <td><?= esc_html($lead->phone) ?></td>
                            <td style="max-width:220px">
                                <span title="<?= esc_attr($lead->query) ?>" style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= esc_html($lead->query) ?></span>
                            </td>
                            <td>
                                <?php if ($lead->page_url): ?>
                                <a href="<?= esc_url($lead->page_url) ?>" target="_blank" title="<?= esc_attr($lead->page_url) ?>" style="font-size:12px">↗ View</a>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td style="font-size:12px;line-height:1.7">
                                <?php
                                $badges = [];
                                if ($lead->email_sent)   $badges[] = '<span style="color:#16a34a">✉ Email</span>';
                                else                     $badges[] = '<span style="color:#9ca3af">✗ Email</span>';
                                if ($lead->sheets_saved) $badges[] = '<span style="color:#16a34a">📊 Sheets</span>';
                                else                     $badges[] = '<span style="color:#9ca3af">✗ Sheets</span>';
                                echo implode('<br>', $badges);
                                ?>
                                <span style="color:#5f39ff">🗄 DB</span>
                            </td>
                            <td style="font-size:12px;color:#6b7280"><?= date('M d, Y', strtotime($lead->created_at)) ?><br><?= date('H:i', strtotime($lead->created_at)) ?></td>
                            <td>
                                <a href="<?= esc_url($del_url) ?>" class="button button-small" style="color:#dc2626;border-color:#dc2626" onclick="return confirm('Delete this lead?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </form>
        </div>

        <script>
        document.getElementById('waai-select-all').addEventListener('change', function() {
            document.querySelectorAll('#waai-leads-form input[name="lead_ids[]"]').forEach(function(c) {
                c.checked = this.checked;
            }, this);
        });
        </script>
        <?php
    }

    /* -----------------------------------------------------------------------
       CSV Export
    ----------------------------------------------------------------------- */

    public function handle_csv_export() {
        if (!current_user_can('manage_options')) wp_die('Unauthorized');
        check_admin_referer('waai_export_leads');

        global $wpdb;
        $table  = $wpdb->prefix . 'waai_leads';
        $search = sanitize_text_field($_GET['s'] ?? '');
        $where  = '';

        if ($search) {
            $like  = '%' . $wpdb->esc_like($search) . '%';
            $where = $wpdb->prepare("WHERE name LIKE %s OR email LIKE %s OR phone LIKE %s", $like, $like, $like);
        }

        $leads = $wpdb->get_results("SELECT * FROM {$table} {$where} ORDER BY created_at DESC", ARRAY_A);

        $filename = 'waai-leads-' . date('Y-m-d') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');

        $out = fopen('php://output', 'w');
        // BOM for Excel UTF-8 compatibility
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['ID','Name','Email','Phone','Query','Page URL','Email Sent','Sheets Saved','Date']);

        foreach ($leads as $row) {
            fputcsv($out, [
                $row['id'],
                $row['name'],
                $row['email'],
                $row['phone'],
                $row['query'],
                $row['page_url'],
                $row['email_sent']   ? 'Yes' : 'No',
                $row['sheets_saved'] ? 'Yes' : 'No',
                $row['created_at'],
            ]);
        }

        fclose($out);
        exit;
    }
}

new WebAssetsAI_Leads();
