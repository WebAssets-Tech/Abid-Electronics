<?php
function homepage_customize_register($wp_customize) {
    // HERO SECTION
    $wp_customize->add_section('hero_section', [
        'title'    => __('Hero Section', 'webassets'),
        'priority' => 31,
    ]);
    
    $wp_customize->add_setting('hero_topbar_phone', ['default' => '+91-9622917697', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_topbar_phone', ['label' => 'Topbar Phone', 'section' => 'hero_section', 'type' => 'text']);
    $wp_customize->add_setting('hero_topbar_email', ['default' => 'support@abidelectronics.com', 'sanitize_callback' => 'sanitize_email']);
    $wp_customize->add_control('hero_topbar_email', ['label' => 'Topbar Email', 'section' => 'hero_section', 'type' => 'text']);

    $wp_customize->add_setting('hero_headline', ['default' => 'Srinagar\'s Most Trusted Multi-Brand Appliance Repair Hub.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_headline', ['label' => 'Headline', 'section' => 'hero_section', 'type' => 'text']);
    
    $wp_customize->add_setting('hero_subheadline', ['default' => '5-Star Rated, Same-Day Doorstep Service, All Brands & Appliances', 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('hero_subheadline', ['label' => 'Subheadline (HTML allowed)', 'section' => 'hero_section', 'type' => 'textarea']);
    
    $wp_customize->add_setting('hero_btn1_text', ['default' => 'Book An Appointment', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_btn1_text', ['label' => 'Button 1 Text', 'section' => 'hero_section', 'type' => 'text']);
    
    $wp_customize->add_setting('hero_btn1_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('hero_btn1_link', ['label' => 'Button 1 Link', 'section' => 'hero_section', 'type' => 'url']);
    
    $wp_customize->add_setting('hero_btn2_text', ['default' => 'Call 9622917697', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('hero_btn2_text', ['label' => 'Button 2 Text', 'section' => 'hero_section', 'type' => 'text']);
    
    $wp_customize->add_setting('hero_btn2_link', ['default' => 'tel:+919622917697', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('hero_btn2_link', ['label' => 'Button 2 Link', 'section' => 'hero_section', 'type' => 'url']);

    $wp_customize->add_setting('hero_bg_image', [
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'hero_bg_image', [
        'label'    => __('Hero Background Image', 'webassets'),
        'section'  => 'hero_section',
        'settings' => 'hero_bg_image',
    ]));

    // ABOUT SECTION
    $wp_customize->add_section('about_section', [
        'title'    => __('About Section', 'webassets'),
        'priority' => 32,
    ]);
    $wp_customize->add_setting('about_left_title', ['default' => 'about us', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('about_left_title', ['label' => 'Left Title', 'section' => 'about_section', 'type' => 'text']);

    $wp_customize->add_setting('about_right_title', ['default' => 'At Abid Electronics, we are Srinagar\'s top-rated appliance repair experts.', 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('about_right_title', ['label' => 'Right Title (HTML allowed)', 'section' => 'about_section', 'type' => 'textarea']);

    $wp_customize->add_setting('about_card1_title', ['default' => 'expertise area', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('about_card1_title', ['label' => 'Card 1 Title', 'section' => 'about_section', 'type' => 'text']);

    $wp_customize->add_setting('about_card1_list', ['default' => "Same-Day Doorstep Service\nAll Major Brands Serviced\nGenuine Spare Parts\n5-Star Rated Service", 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('about_card1_list', ['label' => 'Card 1 List (One per line)', 'section' => 'about_section', 'type' => 'textarea']);

    $wp_customize->add_setting('about_card2_title', ['default' => 'Our Mission', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('about_card2_title', ['label' => 'Card 2 Title', 'section' => 'about_section', 'type' => 'text']);

    $wp_customize->add_setting('about_card2_list', ['default' => "Certified Technicians\nAffordable & Transparent Pricing\nOver 1000+ Appliances Repaired\nSatisfaction Guarantee", 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('about_card2_list', ['label' => 'Card 2 List (One per line)', 'section' => 'about_section', 'type' => 'textarea']);

    $wp_customize->add_setting('about_card3_image', ['capability' => 'edit_theme_options', 'sanitize_callback' => 'absint', 'type' => 'theme_mod']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'about_card3_image', [
        'label' => 'Card 3 Image', 'section' => 'about_section', 'settings' => 'about_card3_image', 'mime_type' => 'image',
    ]));

    // MARQUEE
    $wp_customize->add_section('marquee_section', [
        'title'    => __('Marquee Strips', 'webassets'),
        'priority' => 33,
    ]);
    $wp_customize->add_setting('marquee_item_1', ['default' => 'Same-Day Doorstep Service Across Srinagar', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('marquee_item_1', ['label' => 'Marquee Item 1', 'section' => 'marquee_section', 'type' => 'text']);
    $wp_customize->add_setting('marquee_item_2', ['default' => 'Call 9622917697', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('marquee_item_2', ['label' => 'Marquee Item 2', 'section' => 'marquee_section', 'type' => 'text']);
    $wp_customize->add_setting('marquee_item_3', ['default' => "Srinagar's 5-Star Rated Appliance Repair Hub", 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('marquee_item_3', ['label' => 'Marquee Item 3', 'section' => 'marquee_section', 'type' => 'text']);
    $wp_customize->add_setting('bottom_marquee_text', ['default' => 'Schedule a Free Electrical System Evaluation. Plan Your Free Energy Efficiency Consultation', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('bottom_marquee_text', ['label' => 'Bottom Marquee Text', 'section' => 'marquee_section', 'type' => 'text']);
    $wp_customize->add_setting('bottom_marquee_btn_text', ['default' => 'Book Now', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('bottom_marquee_btn_text', ['label' => 'Bottom Marquee Button Text', 'section' => 'marquee_section', 'type' => 'text']);
    $wp_customize->add_setting('bottom_marquee_btn_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('bottom_marquee_btn_link', ['label' => 'Bottom Marquee Button Link', 'section' => 'marquee_section', 'type' => 'url']);

    // SERVICES SECTION
    $wp_customize->add_section('services_section_settings', [
        'title'    => __('Services Section', 'webassets'),
        'priority' => 33.5,
    ]);
    $wp_customize->add_setting('services_label', ['default' => 'Our services', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('services_label', ['label' => 'Section Label', 'section' => 'services_section_settings', 'type' => 'text']);
    $wp_customize->add_setting('services_title', ['default' => 'Multi-Brand Appliance Repair Tailored for You', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('services_title', ['label' => 'Section Title', 'section' => 'services_section_settings', 'type' => 'text']);
    $wp_customize->add_setting('services_desc', ['default' => 'We provide comprehensive repair and maintenance for all your home and commercial appliances, with genuine parts and same-day service across Srinagar.', 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('services_desc', ['label' => 'Description', 'section' => 'services_section_settings', 'type' => 'textarea']);
    $wp_customize->add_setting('services_btn_text', ['default' => 'Book An Appointment', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('services_btn_text', ['label' => 'Bottom Button Text', 'section' => 'services_section_settings', 'type' => 'text']);
    $wp_customize->add_setting('services_btn_link', ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('services_btn_link', ['label' => 'Bottom Button Link', 'section' => 'services_section_settings', 'type' => 'url']);

    // WHY CHOOSE US / FEATURES
    $wp_customize->add_section('features_section', [
        'title'    => __('Why Choose Us', 'webassets'),
        'priority' => 34,
    ]);
    $wp_customize->add_setting('features_title', ['default' => 'Why Choose Us?', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('features_title', ['label' => 'Title', 'section' => 'features_section', 'type' => 'text']);
    $wp_customize->add_setting('features_subtitle', ['default' => "Srinagar's Most Trusted Appliance Repair Hub.<br>We repair all major brands...", 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('features_subtitle', ['label' => 'Subtitle (HTML)', 'section' => 'features_section', 'type' => 'textarea']);

    $default_features = [
        1 => '337+ Justdial Reviews (4.9★)',
        2 => 'magicpin Verified',
        3 => 'IndiaMART Listed',
        4 => 'Same-Day Doorstep Service',
        5 => '10+ Years of Experience',
        6 => 'Warranty on Work'
    ];
    for ($i=1; $i<=6; $i++) {
        $wp_customize->add_setting("feature_image_$i", ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
        $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, "feature_image_$i", [
            'label' => "Feature $i Image",
            'section' => 'features_section',
            'settings' => "feature_image_$i"
        ]));
        $wp_customize->add_setting("feature_text_$i", ['default' => $default_features[$i], 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control("feature_text_$i", ['label' => "Feature $i Text", 'section' => 'features_section', 'type' => 'text']);
        $wp_customize->add_setting("feature_link_$i", ['default' => '', 'sanitize_callback' => 'esc_url_raw']);
        $wp_customize->add_control("feature_link_$i", ['label' => "Feature $i Link", 'section' => 'features_section', 'type' => 'url']);
    }

    // WORK GALLERY
    $wp_customize->add_section('gallery_section', ['title' => __('Work Gallery', 'webassets'), 'priority' => 34.5]);
    for ($i=1; $i<=6; $i++) {
        $wp_customize->add_setting("gallery_img_$i", ['sanitize_callback' => 'absint']);
        $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, "gallery_img_$i", ['label' => "Gallery Image $i", 'section' => 'gallery_section']));
    }

    // HISTORY/PROCESS
    $wp_customize->add_section('process_section', ['title' => __('Process/History', 'webassets'), 'priority' => 34.6]);
    $wp_customize->add_setting('process_steps', ['default' => "Step 1: Contact\nStep 2: Diagnosis\nStep 3: Repair", 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('process_steps', ['label' => 'Steps (New line separated)', 'section' => 'process_section', 'type' => 'textarea']);

    // VIDEO SECTION
    $wp_customize->add_section('video_section', [
        'title'    => __('Video Section', 'webassets'),
        'priority' => 35,
    ]);
    $wp_customize->add_setting('video_title', ['default' => 'watch video', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('video_title', ['label' => 'Video Title', 'section' => 'video_section', 'type' => 'text']);
    $wp_customize->add_setting('video_url', ['default' => 'https://www.youtube.com/embed/mh7w_zY2PZ4?si=7qlsk0vtkrtICSct', 'sanitize_callback' => 'esc_url_raw']);
    $wp_customize->add_control('video_url', ['label' => 'YouTube Embed URL', 'section' => 'video_section', 'type' => 'url']);
    $wp_customize->add_setting('video_poster', ['sanitize_callback' => 'absint']);
    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'video_poster', ['label' => 'Video Poster Image', 'section' => 'video_section']));
    
    // ODOMETER STATS
    $wp_customize->add_section('stats_section', [
        'title'    => __('Stats (Odometer)', 'webassets'),
        'priority' => 36,
    ]);
    $wp_customize->add_setting('stats_title', ['default' => 'Trusted by thousands across Srinagar for all major appliance brands', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('stats_title', ['label' => 'Main Title', 'section' => 'stats_section', 'type' => 'text']);
    
    $default_stats = [
        1 => ['number' => '10', 'suffix' => '+', 'label' => 'Years of Experience'],
        2 => ['number' => '337', 'suffix' => '+', 'label' => '5-Star Reviews'],
        3 => ['number' => '4', 'suffix' => '.9', 'label' => 'Average Rating'],
        4 => ['number' => '1000', 'suffix' => '+', 'label' => 'Appliances Fixed'],
    ];
    for ($i=1; $i<=4; $i++) {
        $wp_customize->add_setting("stat_number_$i", ['default' => $default_stats[$i]['number'], 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control("stat_number_$i", ['label' => "Stat $i Number", 'section' => 'stats_section', 'type' => 'text']);
        $wp_customize->add_setting("stat_suffix_$i", ['default' => $default_stats[$i]['suffix'], 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control("stat_suffix_$i", ['label' => "Stat $i Suffix", 'section' => 'stats_section', 'type' => 'text']);
        $wp_customize->add_setting("stat_label_$i", ['default' => $default_stats[$i]['label'], 'sanitize_callback' => 'sanitize_text_field']);
        $wp_customize->add_control("stat_label_$i", ['label' => "Stat $i Label", 'section' => 'stats_section', 'type' => 'text']);
    }

    // FAQ & BRANDS
    $wp_customize->add_section('faq_section', ['title' => __('FAQ Section', 'webassets'), 'priority' => 36.1]);
    $wp_customize->add_setting('faq_content', ['default' => "Q: How to book?\nA: Call us.", 'sanitize_callback' => 'sanitize_textarea_field']);
    $wp_customize->add_control('faq_content', ['label' => 'FAQ Content', 'section' => 'faq_section', 'type' => 'textarea']);
    $wp_customize->add_section('brands_section', ['title' => __('Brands Section', 'webassets'), 'priority' => 36.2]);
    $wp_customize->add_setting('brand_logos', ['sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('brand_logos', ['label' => 'Brand Logo IDs (comma separated)', 'section' => 'brands_section', 'type' => 'text']);

    // BLOG SECTION
    $wp_customize->add_section('blog_section', ['title' => __('Blog/News', 'webassets'), 'priority' => 36.5]);
    $wp_customize->add_setting('blog_enable', ['default' => 1, 'sanitize_callback' => 'absint']);
    $wp_customize->add_control('blog_enable', ['label' => 'Enable Blog Section', 'section' => 'blog_section', 'type' => 'checkbox']);

    // CTA SECTION
    $wp_customize->add_section('cta_section', [
        'title'    => __('Bottom CTA', 'webassets'),
        'priority' => 37,
    ]);
    $wp_customize->add_setting('cta_title', ['default' => 'Trusted, Fast & Affordable Service at Your Doorstep.', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('cta_title', ['label' => 'Title', 'section' => 'cta_section', 'type' => 'text']);
    $wp_customize->add_setting('cta_subtitle', ['default' => 'Powering Your World Safely Efficiently & Reliably', 'sanitize_callback' => 'wp_kses_post']);
    $wp_customize->add_control('cta_subtitle', ['label' => 'Subtitle (HTML)', 'section' => 'cta_section', 'type' => 'textarea']);
    $wp_customize->add_setting('cta_btn1_text', ['default' => 'Contact Us', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('cta_btn1_text', ['label' => 'CTA Button 1 Text', 'section' => 'cta_section', 'type' => 'text']);
    // FOOTER SECTION
    $wp_customize->add_section('footer_section', [
        'title'    => __('Footer Column Titles', 'webassets'),
        'priority' => 39,
    ]);
    $wp_customize->add_setting('footer_col2_title', ['default' => 'Our Services', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col2_title', ['label' => 'Column 2 Title (Services)', 'section' => 'footer_section', 'type' => 'text']);
    $wp_customize->add_setting('footer_col3_title', ['default' => 'Quick Links', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col3_title', ['label' => 'Column 3 Title (Quick Links)', 'section' => 'footer_section', 'type' => 'text']);
    $wp_customize->add_setting('footer_col4_title', ['default' => 'Areas We Serve', 'sanitize_callback' => 'sanitize_text_field']);
    $wp_customize->add_control('footer_col4_title', ['label' => 'Column 4 Title (Areas)', 'section' => 'footer_section', 'type' => 'text']);
}
add_action('customize_register', 'homepage_customize_register');
