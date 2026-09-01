<?php
function faq_post_type() {
    register_post_type('faq', [
        'labels'      => [
            'name'          => __('FAQs', 'webassets'),
            'singular_name' => __('FAQ', 'webassets'),
            'add_new'       => __('Add New FAQ', 'webassets'),
            'edit_item'     => __('Edit FAQ', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor'],
        'has_archive' => false,
        'menu_icon'   => 'dashicons-editor-help',
        'rewrite'     => ['slug' => 'faq'],
    ]);
}
add_action('init', 'faq_post_type');
