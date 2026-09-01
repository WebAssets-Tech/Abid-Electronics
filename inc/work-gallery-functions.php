<?php
// Register Work Gallery CPT
function work_gallery_post_type() {
    register_post_type('work_gallery', [
        'labels'      => [
            'name'          => __('Work Gallery', 'webassets'),
            'singular_name' => __('Gallery Item', 'webassets'),
            'add_new'       => __('Add New Item', 'webassets'),
            'add_new_item'  => __('Add New Gallery Item (Image or Video)', 'webassets'),
            'edit_item'     => __('Edit Gallery Item', 'webassets'),
        ],
        'public'              => true,
        'publicly_queryable'  => false,
        'exclude_from_search' => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'supports'            => ['title', 'thumbnail'],
        'has_archive'         => false,
        'menu_icon'           => 'dashicons-format-gallery',
        'rewrite'             => ['slug' => 'work-gallery'],
    ]);
}
add_action('init', 'work_gallery_post_type');

// Redirect any direct single work_gallery post URL to the Work Gallery page
add_action('template_redirect', 'webassets_redirect_single_work_gallery');
function webassets_redirect_single_work_gallery() {
    if (is_singular('work_gallery')) {
        $gallery_page = get_page_by_path('gallery');
        $target_url = $gallery_page ? get_permalink($gallery_page->ID) : home_url('/gallery/');
        wp_safe_redirect($target_url, 301);
        exit;
    }
}

// Add Meta Box for Gallery Item Media & Video
function webassets_gallery_meta_boxes() {
    add_meta_box(
        'gallery_media_details',
        __('Gallery Item Media Settings (Image / Video)', 'webassets'),
        'webassets_gallery_meta_callback',
        'work_gallery',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'webassets_gallery_meta_boxes');

function webassets_gallery_meta_callback($post) {
    wp_nonce_field('save_gallery_meta', 'gallery_meta_nonce');
    $media_type = get_post_meta($post->ID, '_gallery_media_type', true);
    if (!$media_type) {
        $media_type = 'image';
    }
    $video_url = get_post_meta($post->ID, '_gallery_video_url', true);
    ?>
    <div style="padding: 10px 0;">
        <p style="margin-bottom: 15px;">
            <label for="gallery_media_type" style="font-weight: 600;"><?php _e('Media Type:', 'webassets'); ?></label><br>
            <select id="gallery_media_type" name="gallery_media_type" style="width: 100%; max-width: 300px; margin-top: 5px;">
                <option value="image" <?php selected($media_type, 'image'); ?>><?php _e('Image (Standard Photo Lightbox)', 'webassets'); ?></option>
                <option value="video" <?php selected($media_type, 'video'); ?>><?php _e('Video (MP4 Upload or YouTube/Vimeo)', 'webassets'); ?></option>
            </select>
        </p>

        <div id="gallery_video_row" style="margin-bottom: 15px; <?php echo ($media_type === 'video') ? '' : 'display:none;'; ?>">
            <label for="gallery_video_url" style="font-weight: 600;"><?php _e('Video File or Stream URL:', 'webassets'); ?></label><br>
            <div style="display: flex; gap: 8px; margin-top: 5px; align-items: center;">
                <input type="text" id="gallery_video_url" name="gallery_video_url" value="<?php echo esc_attr($video_url); ?>" style="flex: 1;" placeholder="https://... or select MP4 from media library" />
                <button type="button" id="upload_gallery_video_btn" class="button button-secondary"><?php _e('Upload / Select Video (MP4)', 'webassets'); ?></button>
            </div>
            <p class="description" style="margin-top: 6px; color: #666;">
                <?php _e('Tip: You can upload an <strong>MP4</strong> video file from your computer or paste a <strong>YouTube</strong> / <strong>Vimeo</strong> link. Set the <strong>Featured Image</strong> on the right as the cover/poster thumbnail.', 'webassets'); ?>
            </p>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($) {
        // Toggle video input visibility
        $('#gallery_media_type').on('change', function() {
            if ($(this).val() === 'video') {
                $('#gallery_video_row').slideDown(200);
            } else {
                $('#gallery_video_row').slideUp(200);
            }
        });

        // WP Media Uploader for Video
        $('#upload_gallery_video_btn').on('click', function(e) {
            e.preventDefault();
            var videoFrame = wp.media({
                title: '<?php _e("Select or Upload Video", "webassets"); ?>',
                button: { text: '<?php _e("Use This Video", "webassets"); ?>' },
                library: { type: 'video' },
                multiple: false
            });

            videoFrame.on('select', function() {
                var attachment = videoFrame.state().get('selection').first().toJSON();
                $('#gallery_video_url').val(attachment.url);
            });

            videoFrame.open();
        });
    });
    </script>
    <?php
}

// Enqueue WP Media scripts on work_gallery edit screen
add_action('admin_enqueue_scripts', function($hook) {
    global $post_type;
    if ($post_type === 'work_gallery') {
        wp_enqueue_media();
    }
});

// Save Gallery Meta Box
function webassets_save_gallery_meta($post_id) {
    if (!isset($_POST['gallery_meta_nonce']) || !wp_verify_nonce($_POST['gallery_meta_nonce'], 'save_gallery_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['gallery_media_type'])) {
        update_post_meta($post_id, '_gallery_media_type', sanitize_text_field($_POST['gallery_media_type']));
    }
    if (isset($_POST['gallery_video_url'])) {
        update_post_meta($post_id, '_gallery_video_url', esc_url_raw($_POST['gallery_video_url']));
    }
}
add_action('save_post_work_gallery', 'webassets_save_gallery_meta');
