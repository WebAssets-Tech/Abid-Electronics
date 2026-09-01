<?php
// Register Locations CPT
function webassets_location_post_type() {
    register_post_type('locations', [
        'labels'      => [
            'name'          => __('Locations', 'webassets'),
            'singular_name' => __('Location', 'webassets'),
            'add_new'       => __('Add New Location', 'webassets'),
            'edit_item'     => __('Edit Location', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'custom-fields'],
        'has_archive' => true,
        'menu_icon'   => 'dashicons-location-alt',
        'rewrite'     => ['slug' => 'location'],
    ]);
}
add_action('init', 'webassets_location_post_type');
