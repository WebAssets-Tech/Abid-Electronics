<?php
/**
 * Leads, Newsletter & SMTP Manager
 */

// 1. Register Custom Post Types for Leads & Newsletter
function wa_register_lead_post_types() {
    $labels_lead = array(
        'name'               => 'Leads',
        'singular_name'      => 'Lead',
        'menu_name'          => 'Leads',
        'name_admin_bar'     => 'Lead',
        'add_new'            => 'Add New Lead',
        'add_new_item'       => 'Add New Lead',
        'new_item'           => 'New Lead',
        'edit_item'          => 'View/Edit Lead',
        'view_item'          => 'View Lead',
        'all_items'          => 'All Leads',
        'search_items'       => 'Search Leads',
        'not_found'          => 'No leads found.',
    );

    $args_lead = array(
        'labels'             => $labels_lead,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'webassets-leads',
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array('title', 'custom-fields'),
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // Disable "Add New" button for leads
        ),
        'map_meta_cap' => true,
    );

    register_post_type('wa_lead', $args_lead);

    $labels_newsletter = array(
        'name'               => 'Newsletter Subscribers',
        'singular_name'      => 'Subscriber',
        'menu_name'          => 'Newsletter',
        'all_items'          => 'Newsletter Subscribers',
        'search_items'       => 'Search Subscribers',
        'not_found'          => 'No subscribers found.',
    );

    $args_newsletter = array(
        'labels'             => $labels_newsletter,
        'public'             => false,
        'show_ui'            => true,
        'show_in_menu'       => 'webassets-leads',
        'query_var'          => false,
        'rewrite'            => false,
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'supports'           => array('title'),
        'capabilities' => array(
            'create_posts' => 'do_not_allow', // Disable "Add New" button for subscribers
        ),
        'map_meta_cap' => true,
    );

    register_post_type('wa_newsletter', $args_newsletter);
}
add_action('init', 'wa_register_lead_post_types');

// 2. Add Top Level Menu & Settings Pages
function wa_leads_admin_menu() {
    add_menu_page(
        'Leads Manager',
        'WebAssets Leads',
        'manage_options',
        'webassets-leads',
        '',
        'dashicons-clipboard',
        30
    );

    // Submenu for SMTP Settings
    add_submenu_page(
        'webassets-leads',
        'SMTP Settings',
        'SMTP Settings',
        'manage_options',
        'wa-smtp-settings',
        'wa_smtp_settings_page'
    );
}
add_action('admin_menu', 'wa_leads_admin_menu');

// Register SMTP Settings
function wa_register_smtp_settings() {
    register_setting('wa_smtp_options_group', 'wa_smtp_host');
    register_setting('wa_smtp_options_group', 'wa_smtp_port');
    register_setting('wa_smtp_options_group', 'wa_smtp_username');
    register_setting('wa_smtp_options_group', 'wa_smtp_password');
    register_setting('wa_smtp_options_group', 'wa_smtp_encryption');
    register_setting('wa_smtp_options_group', 'wa_smtp_from_email');
    register_setting('wa_smtp_options_group', 'wa_smtp_from_name');
}
add_action('admin_init', 'wa_register_smtp_settings');

// SMTP Settings Page HTML
function wa_smtp_settings_page() {
    ?>
    <div class="wrap">
        <h2>SMTP Settings</h2>
        <p>Configure your Google SMTP (or other email provider) here. <strong>Important:</strong> Use an "App Password" if you have 2FA enabled on your Google account.</p>
        <?php settings_errors(); ?>
        <form method="post" action="options.php">
            <?php settings_fields('wa_smtp_options_group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">From Email</th>
                    <td><input type="email" name="wa_smtp_from_email" value="<?php echo esc_attr(get_option('wa_smtp_from_email')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">From Name</th>
                    <td><input type="text" name="wa_smtp_from_name" value="<?php echo esc_attr(get_option('wa_smtp_from_name')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">SMTP Host</th>
                    <td><input type="text" name="wa_smtp_host" value="<?php echo esc_attr(get_option('wa_smtp_host', 'smtp.gmail.com')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">SMTP Port</th>
                    <td><input type="number" name="wa_smtp_port" value="<?php echo esc_attr(get_option('wa_smtp_port', '465')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Encryption</th>
                    <td>
                        <select name="wa_smtp_encryption">
                            <option value="ssl" <?php selected(get_option('wa_smtp_encryption', 'ssl'), 'ssl'); ?>>SSL</option>
                            <option value="tls" <?php selected(get_option('wa_smtp_encryption', 'ssl'), 'tls'); ?>>TLS</option>
                            <option value="none" <?php selected(get_option('wa_smtp_encryption', 'ssl'), 'none'); ?>>None</option>
                        </select>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">SMTP Username (Email)</th>
                    <td><input type="text" name="wa_smtp_username" value="<?php echo esc_attr(get_option('wa_smtp_username')); ?>" class="regular-text" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">SMTP App Password</th>
                    <td><input type="password" name="wa_smtp_password" value="<?php echo esc_attr(get_option('wa_smtp_password')); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// 3. Override phpmailer_init to use custom SMTP settings
function wa_setup_phpmailer_smtp($phpmailer) {
    $smtp_host = get_option('wa_smtp_host');
    $smtp_user = get_option('wa_smtp_username');
    $smtp_pass = get_option('wa_smtp_password');

    if ($smtp_host && $smtp_user && $smtp_pass) {
        $phpmailer->isSMTP();
        $phpmailer->Host       = $smtp_host;
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Port       = get_option('wa_smtp_port');
        $phpmailer->Username   = $smtp_user;
        $phpmailer->Password   = $smtp_pass;
        $phpmailer->SMTPSecure = (get_option('wa_smtp_encryption') === 'none') ? '' : get_option('wa_smtp_encryption');

        $from_email = get_option('wa_smtp_from_email', $smtp_user);
        $from_name  = get_option('wa_smtp_from_name', get_bloginfo('name'));

        $phpmailer->setFrom($from_email, $from_name);
    }
}
add_action('phpmailer_init', 'wa_setup_phpmailer_smtp');

// 4. Custom Meta Boxes for Leads to display submitted data nicely
function wa_add_lead_meta_boxes() {
    add_meta_box('wa_lead_details', 'Lead Details', 'wa_lead_details_callback', 'wa_lead', 'normal', 'high');
}
add_action('add_meta_boxes', 'wa_add_lead_meta_boxes');

function wa_lead_details_callback($post) {
    $email = get_post_meta($post->ID, 'lead_email', true);
    $phone = get_post_meta($post->ID, 'lead_phone', true);
    $service = get_post_meta($post->ID, 'lead_service', true);
    $address = get_post_meta($post->ID, 'lead_address', true);
    $message = get_post_meta($post->ID, 'lead_message', true);

    echo '<table class="form-table">';
    echo '<tr><th>Email:</th><td>' . esc_html($email) . ' <a href="mailto:'.esc_attr($email).'">Send Email</a></td></tr>';
    echo '<tr><th>Phone:</th><td>' . esc_html($phone) . ' <a href="tel:'.esc_attr($phone).'">Call</a></td></tr>';
    echo '<tr><th>Service Requested:</th><td>' . esc_html($service) . '</td></tr>';
    if ($address) {
        echo '<tr><th>Address:</th><td>' . esc_html($address) . '</td></tr>';
    }
    echo '<tr><th>Message / Note:</th><td>' . nl2br(esc_html($message)) . '</td></tr>';
    echo '</table>';
}

// 5. AJAX Handlers for Contact/Appointment Leads
function wa_submit_lead_ajax() {
    $name    = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $email   = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone   = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $address = isset($_POST['adress']) ? sanitize_text_field($_POST['adress']) : '';
    $service = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : (isset($_POST['service']) ? sanitize_text_field($_POST['service']) : 'General Inquiry');
    $note    = isset($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

    if (empty($name) || empty($email) || empty($phone)) {
        wp_send_json_error(array('message' => 'Please fill required fields.'));
    }

    // Save to Database as a custom post
    $post_title = $name . ' - ' . $service;
    $post_data = array(
        'post_title'   => $post_title,
        'post_status'  => 'publish',
        'post_type'    => 'wa_lead'
    );
    
    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        update_post_meta($post_id, 'lead_email', $email);
        update_post_meta($post_id, 'lead_phone', $phone);
        update_post_meta($post_id, 'lead_service', $service);
        update_post_meta($post_id, 'lead_address', $address);
        update_post_meta($post_id, 'lead_message', $note);

        // Send Email Notification
        $to = get_option('wa_smtp_from_email', get_option('admin_email')); // Send to admin
        $subject = "New Lead: " . $post_title;
        $message = "You have received a new service inquiry:\n\n";
        $message .= "Name: " . $name . "\n";
        $message .= "Email: " . $email . "\n";
        $message .= "Phone: " . $phone . "\n";
        $message .= "Service: " . $service . "\n";
        if (!empty($address)) $message .= "Address: " . $address . "\n";
        $message .= "\nMessage:\n" . $note . "\n";
        $message .= "\nView in admin: " . admin_url('post.php?post=' . $post_id . '&action=edit');

        // Note: we can use $email in Reply-To
        $headers = array('Reply-To: ' . $name . ' <' . $email . '>');
        
        try {
            @wp_mail($to, $subject, $message, $headers);
        } catch (Exception $e) {
            // Ignore mail errors so the lead is still saved
        } catch (Throwable $e) {
            // Ignore mail errors
        }

        wp_send_json_success(array('message' => 'Lead saved successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to save lead.'));
    }
}
add_action('wp_ajax_wa_submit_lead', 'wa_submit_lead_ajax');
add_action('wp_ajax_nopriv_wa_submit_lead', 'wa_submit_lead_ajax');


// 6. AJAX Handlers for Newsletter Subscribers
function wa_submit_newsletter_ajax() {
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';

    if (empty($email) || !is_email($email)) {
        wp_send_json_error(array('message' => 'Valid email address is required.'));
    }

    // Check if email already exists
    $existing = get_page_by_title($email, OBJECT, 'wa_newsletter');
    if ($existing) {
        wp_send_json_success(array('message' => 'You are already subscribed!'));
    }

    // Save to Database
    $post_data = array(
        'post_title'   => $email,
        'post_status'  => 'publish',
        'post_type'    => 'wa_newsletter'
    );
    
    $post_id = wp_insert_post($post_data);

    if ($post_id) {
        wp_send_json_success(array('message' => 'Subscribed successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to subscribe.'));
    }
}
add_action('wp_ajax_wa_submit_newsletter', 'wa_submit_newsletter_ajax');
add_action('wp_ajax_nopriv_wa_submit_newsletter', 'wa_submit_newsletter_ajax');

// 7. Add Admin Columns for Leads
function wa_set_custom_edit_wa_lead_columns($columns) {
    unset($columns['date']);
    $columns['lead_email'] = 'Email';
    $columns['lead_phone'] = 'Phone';
    $columns['lead_service'] = 'Service';
    $columns['date'] = 'Date';
    return $columns;
}
add_filter('manage_wa_lead_posts_columns', 'wa_set_custom_edit_wa_lead_columns');

function wa_custom_wa_lead_column($column, $post_id) {
    switch ($column) {
        case 'lead_email':
            echo esc_html(get_post_meta($post_id, 'lead_email', true));
            break;
        case 'lead_phone':
            echo esc_html(get_post_meta($post_id, 'lead_phone', true));
            break;
        case 'lead_service':
            echo esc_html(get_post_meta($post_id, 'lead_service', true));
            break;
    }
}
add_action('manage_wa_lead_posts_custom_column', 'wa_custom_wa_lead_column', 10, 2);

// 8. Output ajaxurl in head for frontend scripts
function wa_output_ajaxurl() {
    echo '<script type="text/javascript">var ajaxurl = "' . admin_url('admin-ajax.php') . '";</script>';
}
add_action('wp_head', 'wa_output_ajaxurl');
