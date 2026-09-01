<?php
// Register Testimonials CPT
function webassets_testimonial_post_type() {
    register_post_type('testimonials', [
        'labels'      => [
            'name'          => __('Testimonials', 'webassets'),
            'singular_name' => __('Testimonial', 'webassets'),
            'add_new'       => __('Add New Testimonial', 'webassets'),
            'edit_item'     => __('Edit Testimonial', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive' => true,
        'menu_icon'   => 'dashicons-format-quote',
        'rewrite'     => ['slug' => 'testimonial'],
    ]);
}
add_action('init', 'webassets_testimonial_post_type');

// Create Meta Boxes for Rating
function webassets_testimonial_meta_boxes() {
    add_meta_box('testimonial_details', __('Testimonial Details', 'webassets'), 'webassets_testimonial_meta_callback', 'testimonials', 'normal', 'high');
}
add_action('add_meta_boxes', 'webassets_testimonial_meta_boxes');

function webassets_testimonial_meta_callback($post) {
    wp_nonce_field('save_testimonial_meta', 'testimonial_meta_nonce');
    $testimonial_rating = get_post_meta($post->ID, '_testimonial_rating', true);
    $testimonial_source = get_post_meta($post->ID, '_testimonial_source', true);
    ?>
    <p>
        <label for="testimonial_rating"><?php _e('Rating (out of 5)', 'webassets'); ?></label><br>
        <input type="number" id="testimonial_rating" name="testimonial_rating" value="<?php echo esc_attr($testimonial_rating); ?>" min="1" max="5" style="width:100%;" />
    </p>
    <p>
        <label for="testimonial_source"><?php _e('Source (e.g. Justdial, Google)', 'webassets'); ?></label><br>
        <input type="text" id="testimonial_source" name="testimonial_source" value="<?php echo esc_attr($testimonial_source); ?>" style="width:100%;" />
    </p>
    <?php
}

function webassets_save_testimonial_meta($post_id) {
    if (!isset($_POST['testimonial_meta_nonce']) || !wp_verify_nonce($_POST['testimonial_meta_nonce'], 'save_testimonial_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (isset($_POST['testimonial_rating'])) {
        update_post_meta($post_id, '_testimonial_rating', sanitize_text_field($_POST['testimonial_rating']));
    }
    if (isset($_POST['testimonial_source'])) {
        update_post_meta($post_id, '_testimonial_source', sanitize_text_field($_POST['testimonial_source']));
    }
}
add_action('save_post', 'webassets_save_testimonial_meta');
