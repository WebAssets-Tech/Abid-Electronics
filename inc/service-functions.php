<?php
// Register Services CPT
function webassets_service_post_type() {
    register_post_type('services', [
        'labels'      => [
            'name'          => __('Services', 'webassets'),
            'singular_name' => __('Service', 'webassets'),
            'add_new'       => __('Add New Service', 'webassets'),
            'edit_item'     => __('Edit Service', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive' => true,
        'taxonomies'  => ['service_category'],
        'menu_icon'   => 'dashicons-admin-tools',
        'rewrite'     => ['slug' => 'service'],
    ]);
}
add_action('init', 'webassets_service_post_type');

// Register Service Taxonomy
function webassets_service_taxonomy() {
    register_taxonomy('service_category', ['services'], [
        'labels' => [
            'name' => __('Service Categories', 'webassets'),
            'singular_name' => __('Service Category', 'webassets'),
        ],
        'public' => true,
        'hierarchical' => true,
        'show_admin_column' => true,
    ]);
}
add_action('init', 'webassets_service_taxonomy');

// Create Meta Boxes
function webassets_service_meta_boxes() {
    add_meta_box('service_details', __('Service Details', 'webassets'), 'webassets_service_meta_callback', 'services', 'normal', 'high');
}
add_action('add_meta_boxes', 'webassets_service_meta_boxes');

function webassets_service_meta_callback($post) {
    wp_nonce_field('save_service_meta', 'service_meta_nonce');
    $service_icon = get_post_meta($post->ID, '_service_icon', true);
    $service_price = get_post_meta($post->ID, '_service_price', true);
    ?>
    <p>
        <label for="service_icon"><?php _e('Service Icon URL', 'webassets'); ?></label><br>
        <input type="text" id="service_icon" name="service_icon" value="<?php echo esc_attr($service_icon); ?>" style="width:100%;" />
    </p>
    <p>
        <label for="service_price"><?php _e('Starting Price', 'webassets'); ?></label><br>
        <input type="text" id="service_price" name="service_price" value="<?php echo esc_attr($service_price); ?>" style="width:100%;" />
    </p>
    <?php
}

function webassets_save_service_meta($post_id) {
    if (!isset($_POST['service_meta_nonce']) || !wp_verify_nonce($_POST['service_meta_nonce'], 'save_service_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['service_icon'])) {
        update_post_meta($post_id, '_service_icon', sanitize_text_field($_POST['service_icon']));
    }
    if (isset($_POST['service_price'])) {
        update_post_meta($post_id, '_service_price', sanitize_text_field($_POST['service_price']));
    }
}
add_action('save_post', 'webassets_save_service_meta');
