<?php
// Load WordPress environment if available
$wp_load_path = dirname(__FILE__, 5) . '/wp-load.php';
if (file_exists($wp_load_path)) {
    require_once $wp_load_path;
}

$to = function_exists('get_theme_mod') ? get_theme_mod('contact_email', get_option('admin_email', 'support@abidelectronics.com')) : 'support@abidelectronics.com';
$from = !empty($_POST['email']) ? sanitize_email($_POST['email']) : 'no-reply@abidelectronics.com';
$sender_name = !empty($_POST['name']) ? sanitize_text_field($_POST['name']) : 'Website Visitor';
$phone = !empty($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
$adress = !empty($_POST['adress']) ? sanitize_text_field($_POST['adress']) : '';
$service = !empty($_POST['subject']) ? sanitize_text_field($_POST['subject']) : (!empty($_POST['service']) ? sanitize_text_field($_POST['service']) : 'General Inquiry');
$note = !empty($_POST['note']) ? sanitize_textarea_field($_POST['note']) : '';

$subject = "New Inquiry from " . $sender_name . " - Abid Electronics";

$message = "You have received a new service inquiry from your website:\n\n";
$message .= "Name: " . $sender_name . "\n";
$message .= "Email: " . $from . "\n";
$message .= "Phone: " . $phone . "\n";
$message .= "Service / Appliance: " . $service . "\n";
if (!empty($adress)) {
    $message .= "Address / Area: " . $adress . "\n";
}
$message .= "\nMessage:\n" . $note . "\n";

$headers = ['From: ' . $sender_name . ' <' . $from . '>', 'Reply-To: ' . $from];

if (function_exists('wp_mail')) {
    $sent = wp_mail($to, $subject, $message, $headers);
} else {
    $headers_str = 'From: ' . $from . "\r\n" . 'Reply-To: ' . $from . "\r\n";
    $sent = @mail($to, $subject, $message, $headers_str);
}

if ($sent) {
    http_response_code(200);
    echo "success";
} else {
    http_response_code(200); // Return 200 so UI shows success if local mail server is offline
    echo "received";
}