<?php
// Add Nav Walker for Bootstrap Menu Support
function register_navwalker() {
    require_once get_template_directory() . '/wp_bootstrap_navwalker.php';
}
add_action('after_setup_theme', 'register_navwalker');

// Registering standard menus
register_nav_menus([
    "header-menu"        => "Header Menu (Full / Mobile)",
    "header-menu-left"   => "Header Menu Left (Before Logo)",
    "header-menu-right"  => "Header Menu Right (After Logo)",
    "footer-menu"        => "Footer Menu (Quick Links)",
    "footer-services"    => "Footer Services Menu",
    "footer-areas"       => "Footer Areas Menu",
    "company-links"      => "Company Links",
    "footer-strip-links" => "Footer Strip Links",
]);

/**
 * Render Header Navigation (Left or Right of centered Logo)
 * Supports explicit menu locations (header-menu-left / header-menu-right)
 * Or automatically splits the items of 'header-menu' 50/50 around the logo.
 */
function webassets_render_header_menu($position = 'left') {
    if ($position === 'left' && has_nav_menu('header-menu-left')) {
        wp_nav_menu([
            'theme_location' => 'header-menu-left',
            'container'      => false,
            'menu_class'     => 'nav navbar-nav mb-0',
            'fallback_cb'    => 'wp_bootstrap_navwalker::fallback',
            'walker'         => new wp_bootstrap_navwalker(),
        ]);
        return;
    }
    if ($position === 'right' && has_nav_menu('header-menu-right')) {
        wp_nav_menu([
            'theme_location' => 'header-menu-right',
            'container'      => false,
            'menu_class'     => 'nav navbar-nav mb-0',
            'fallback_cb'    => 'wp_bootstrap_navwalker::fallback',
            'walker'         => new wp_bootstrap_navwalker(),
        ]);
        return;
    }

    // Auto-split items from 'header-menu'
    $locations = get_nav_menu_locations();
    if (isset($locations['header-menu'])) {
        $menu = wp_get_nav_menu_object($locations['header-menu']);
        if ($menu) {
            $items = wp_get_nav_menu_items($menu->term_id);
            if (!empty($items)) {
                // Determine top-level items
                $top_level = array_values(array_filter($items, function($item) {
                    return empty($item->menu_item_parent);
                }));
                $total_top = count($top_level);
                $split_index = (int) ceil($total_top / 2);

                $target_top_ids = [];
                for ($i = 0; $i < $total_top; $i++) {
                    if ($position === 'left' && $i < $split_index) {
                        $target_top_ids[] = (int) $top_level[$i]->ID;
                    } elseif ($position === 'right' && $i >= $split_index) {
                        $target_top_ids[] = (int) $top_level[$i]->ID;
                    }
                }

                echo '<ul class="nav navbar-nav mb-0">';
                foreach ($items as $item) {
                    $is_top = empty($item->menu_item_parent);
                    if ($is_top && in_array((int)$item->ID, $target_top_ids, true)) {
                        // Check if item has children
                        $children = array_filter($items, function($child) use ($item) {
                            return (int)$child->menu_item_parent === (int)$item->ID;
                        });
                        $has_children = !empty($children);
                        $item_classes = is_array($item->classes) ? $item->classes : [];
                        if ($has_children) {
                            $item_classes[] = 'menu-item-has-children';
                        }
                        if ($item->current) {
                            $item_classes[] = 'active';
                        }
                        $classes_str = implode(' ', array_filter(array_unique($item_classes)));

                        echo '<li class="' . esc_attr($classes_str) . '">';
                        echo '<a href="' . esc_url($item->url) . '">' . esc_html($item->title) . '</a>';
                        if ($has_children) {
                            echo '<ul class="sub-menu">';
                            foreach ($children as $child) {
                                echo '<li><a href="' . esc_url($child->url) . '">' . esc_html($child->title) . '</a></li>';
                            }
                            echo '</ul>';
                        }
                        echo '</li>';
                    }
                }
                echo '</ul>';
                return;
            }
        }
    }

    // Default static fallback if no menu assigned yet
    if ($position === 'left') {
        ?>
        <ul class="nav navbar-nav mb-0">
            <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
            <li><a href="<?php echo esc_url(home_url('/services/')); ?>">Services</a></li>
            <li><a href="<?php echo esc_url(home_url('/gallery/')); ?>">Gallery</a></li>
        </ul>
        <?php
    } else {
        ?>
        <ul class="nav navbar-nav mb-0">
            <li><a href="<?php echo esc_url(home_url('/about-us/')); ?>">About Us</a></li>
            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a></li>
            <li><a href="<?php echo esc_url(home_url('/appointment/')); ?>">Book Appointment</a></li>
        </ul>
        <?php
    }
}

function theme_customization() {
    // Support for site logo
    add_theme_support('custom-logo', [
        "height"      => 100,
        "width"       => 100,
        "flex-height" => true,
        "flex-width"  => true,
        "header-text" => ["site-title", "site-description"],
    ]);

    // Core Supports
    add_theme_support('menus');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');

    // Add Support for Custom Backgrounds
    add_theme_support('custom-background', [
        'default-color' => 'FFF',
        'default-image' => get_template_directory_uri() . '/assets/images/bg.jpg',
    ]);
}
add_action('after_setup_theme', 'theme_customization');

// ✅ Add Custom Footer Logo Support Using Customizer API
function webassets_customize_register($wp_customize)
{
    // 1. Footer Logo Section
    $wp_customize->add_section('footer_logo_section', [
        'title'    => __('Footer Logo', 'webassets'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('footer_logo', [
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'absint', // Ensures the input is an ID
        'type'              => 'theme_mod',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'footer_logo', [
        'label'     => __('Upload Footer Logo', 'webassets'),
        'section'   => 'footer_logo_section',
        'settings'  => 'footer_logo',
        'mime_type' => 'image',
    ]));

    // 2. Contact Email
    $wp_customize->add_setting('contact_email', [
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('contact_email', [
        'label'   => __('Contact Email', 'webassets'),
        'section' => 'title_tagline', // Placed in Site Identity section
        'type'    => 'email',
    ]);

    // 3. Contact Phone
    $wp_customize->add_setting('contact_phone', [
        'default'           => '123-456-7890',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_phone', [
        'label'   => __('Contact Phone', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 4. Secondary Contact Phone
    $wp_customize->add_setting('contact_phone_sec', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_phone_sec', [
        'label'   => __('Secondary Contact Phone', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 5. Contact Address
    $wp_customize->add_setting('contact_address', [
        'default'           => '123 Main St, Anytown, USA',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_address', [
        'label'   => __('Contact Address', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 6. Google Map Link
    $wp_customize->add_setting('google_map_link', [
        'default'           => 'https://g.co/kgs/ANjbN5N',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('google_map_link', [
        'label'   => __('Google Map Link', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'url',
    ]);

    // 7. Another Email (Support)
    $wp_customize->add_setting('contact_email_2', [
        'default'           => 'support@example.com',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('contact_email_2', [
        'label'   => __('Another Email', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'email',
    ]);

    // 8. Social Media Links
    $socials = [
        'instagram' => 'Instagram URL',
        'twitter'   => 'Twitter URL',
        'whatsapp'  => 'WhatsApp URL',
        'facebook'  => 'Facebook URL'
    ];

    foreach ($socials as $key => $label) {
        $wp_customize->add_setting('social_' . $key, [
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control('social_' . $key, [
            'label'   => __($label, 'webassets'),
            'section' => 'title_tagline',
            'type'    => 'url',
        ]);
    }
}
add_action('customize_register', 'webassets_customize_register');

// ✅ Helper Function to Display Footer Logo on the frontend
function get_footer_logo()
{
    $footer_logo_id = get_theme_mod('footer_logo');
    if ($footer_logo_id) {
        return wp_get_attachment_image_url($footer_logo_id, 'full');
    }
    return false;
}

function webassets_sidebar_registration() {
    register_sidebar([
        "name"          => "Main Sidebar",
        "id"            => "sidebar",
        "before_widget" => "<div class='widget'>",
        "after_widget"  => "</div>",
        "before_title"  => "<h2 class='widget-title'>",
        "after_title"   => "</h2>",
    ]);

    // Register Footer Widget Areas (1 through 4)
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => __('Footer ' . $i, 'webassets'),
            'id'            => 'footer-' . $i,
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3>',
            'after_title'   => '</h3>',
        ]);
    }
}
add_action("widgets_init", "webassets_sidebar_registration");

// Add Google ReCAPTCHA to Comment Form
add_filter('comment_form_submit_field', 'webassets_add_recaptcha_to_submit_field', 10, 2);
function webassets_add_recaptcha_to_submit_field($submit_field, $args) {
    $recaptcha_html = '<div class="form-input" style="margin-bottom: 20px;">
        <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
    </div>';
    return $recaptcha_html . $submit_field;
}

// Disable REST API anonymous comments
add_filter('rest_allow_anonymous_comments', '__return_false');

// Disable XML-RPC pingbacks
add_filter('xmlrpc_methods', function($methods) {
    unset($methods['pingback.ping']);
    return $methods;
});

// Including Proprietary Modules
if (file_exists(get_template_directory() . '/inc/licensing/init.php')) {
    require_once get_template_directory() . '/inc/licensing/init.php';
}
if (file_exists(get_template_directory() . '/inc/website-planner/wp-functions.php')) {
    require_once get_template_directory() . '/inc/website-planner/wp-functions.php';
}
if (file_exists(get_template_directory() . '/webassets-ai-assistant/wordpress-integration.php')) {
    require_once get_template_directory() . '/webassets-ai-assistant/wordpress-integration.php';
}

// Including standard CPTs & SEO Engine
$inc_files = [
    '/inc/service-functions.php',
    '/inc/location-functions.php',
    '/inc/testimonial-functions.php',
    '/inc/work-gallery-functions.php',
    '/inc/faq-functions.php',
    '/inc/brand-functions.php',
    '/inc/homepage-customizer.php',
    '/inc/team-functions.php',
    '/inc/seo-functions.php'
];

foreach ($inc_files as $file) {
    if (file_exists(get_template_directory() . $file)) {
        require_once get_template_directory() . $file;
    }
}
