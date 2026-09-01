<?php
function partner_brand_post_type() {
    register_post_type('partner_brand', [
        'labels'      => [
            'name'          => __('Partner Brands', 'webassets'),
            'singular_name' => __('Brand', 'webassets'),
            'add_new'       => __('Add New Brand', 'webassets'),
            'edit_item'     => __('Edit Brand', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'thumbnail'],
        'has_archive' => false,
        'menu_icon'   => 'dashicons-awards',
        'rewrite'     => ['slug' => 'partner-brand'],
    ]);
}
add_action('init', 'partner_brand_post_type');
