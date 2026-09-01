<?php
/**
 * Advanced SEO Engine for Abid Electronics Service Hub
 * Designed according to Abid Electronics Local Domination Strategy Blueprint.
 * 
 * Handles:
 * - Dynamic Title Tags (High-CTR Keyword-Optimized)
 * - Meta Descriptions & Focus Keywords
 * - OpenGraph & Twitter Social Cards
 * - Canonical & Multi-Language Alternate Tags
 * - Geo SEO Tags (Srinagar, Jammu & Kashmir)
 * - Comprehensive JSON-LD Schemas:
 *   1. LocalBusiness / HomeAndConstructionBusiness (NAP + AggregateRating + Offers)
 *   2. Service Schema (Per Service Page)
 *   3. FAQPage Schema (Rich Snippets from FAQ CPT)
 *   4. BreadcrumbList Schema (Hierarchy)
 *   5. VideoObject Schema (Video Snippets)
 *   6. WebSite Schema (Sitelinks Searchbox)
 *   7. SiteNavigationElement Schema
 *
 * @package WebAssets
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Filter Document Title for SEO Optimization
 */
function abidelectronics_seo_title_filter($title) {
    if (is_front_page() || is_home()) {
        return 'Abid Electronics Service Hub — Appliance Repair Srinagar';
    }

    $page_slug = get_post_field('post_name', get_queried_object_id());

    // Service Pages
    if (is_page('refrigerator-repair-srinagar') || $page_slug === 'refrigerator-repair-srinagar' || strpos($page_slug, 'fridge') !== false || strpos($page_slug, 'refrigerator') !== false) {
        return 'Refrigerator & Fridge Repair in Srinagar | Abid Electronics';
    }
    if (is_page('washing-machine-repair-srinagar') || $page_slug === 'washing-machine-repair-srinagar' || strpos($page_slug, 'washing-machine') !== false) {
        return 'Washing Machine Repair in Srinagar | Abid Electronics';
    }
    if (is_page('ac-repair-service-srinagar') || is_page('ac-repair-srinagar') || $page_slug === 'ac-repair-service-srinagar' || $page_slug === 'ac-repair-srinagar' || strpos($page_slug, 'ac-repair') !== false) {
        return 'AC Repair & Service in Srinagar | Abid Electronics';
    }
    if (is_page('geyser-repair-srinagar') || $page_slug === 'geyser-repair-srinagar' || strpos($page_slug, 'geyser') !== false || strpos($page_slug, 'water-heater') !== false) {
        return 'Geyser Repair in Srinagar — Water Heater Service | Abid Electronics';
    }
    if (is_page('microwave-oven-repair-srinagar') || $page_slug === 'microwave-oven-repair-srinagar' || strpos($page_slug, 'microwave') !== false) {
        return 'Microwave & Oven Repair in Srinagar | Abid Electronics';
    }
    if (is_page('commercial-refrigeration-repair') || $page_slug === 'commercial-refrigeration-repair' || strpos($page_slug, 'commercial') !== false) {
        return 'Commercial Fridge & Bakery Counter Repair Srinagar | Abid Electronics';
    }

    // Location Landing Pages
    if (is_page('chattabal') || $page_slug === 'chattabal') {
        return 'Appliance Repair in Chattabal, Srinagar | Abid Electronics';
    }
    if (is_page('bemina') || $page_slug === 'bemina') {
        return 'Appliance Repair Near Bemina Crossing, Srinagar | Abid Electronics';
    }
    if (is_page('tengpora') || $page_slug === 'tengpora') {
        return 'Appliance Repair in Tengpora, Srinagar | Abid Electronics';
    }

    // Standard Info Pages
    if (is_page('about-us') || is_page('about') || in_array($page_slug, ['about-us', 'about'])) {
        return 'About Abid Electronics — Trusted Appliance Repair in Srinagar';
    }
    if (is_page('contact') || is_page('contact-us') || is_page('book-appointment-and-service') || is_page('appointment') || in_array($page_slug, ['contact', 'contact-us', 'book-appointment-and-service', 'appointment'])) {
        return 'Contact Abid Electronics — Book a Repair in Srinagar';
    }
    if (is_page('gallery') || is_page('work-gallery') || in_array($page_slug, ['gallery', 'work-gallery'])) {
        return 'Work Gallery — Recent Appliance Repairs in Srinagar | Abid Electronics';
    }
    if (is_page('faq') || $page_slug === 'faq') {
        return 'Frequently Asked Questions — Appliance Repair | Abid Electronics';
    }
    if (is_page('services') || is_page('our-services') || in_array($page_slug, ['services', 'our-services'])) {
        return 'Doorstep Appliance Repair Services in Srinagar | Abid Electronics';
    }

    // Custom Post Types
    if (is_singular('services')) {
        return get_the_title() . ' in Srinagar | Abid Electronics';
    }
    if (is_singular('location')) {
        return 'Appliance Repair in ' . get_the_title() . ', Srinagar | Abid Electronics';
    }
    if (is_singular('post')) {
        return get_the_title() . ' | Abid Electronics Blog';
    }

    return $title;
}
add_filter('pre_get_document_title', 'abidelectronics_seo_title_filter', 20);

/**
 * Output Technical SEO Meta Tags in <head>
 */
function webassets_seo_meta_tags() {
    if (did_action('webassets_seo_meta_tags_done')) {
        return;
    }

    $site_name   = 'Abid Electronics Service Hub';
    $phone_num   = '+919622917697';
    $url         = get_permalink();
    $title       = wp_get_document_title();
    $page_slug   = get_post_field('post_name', get_queried_object_id());
    $description = "Srinagar's 5-star rated appliance repair hub. Fridge, washing machine, AC, geyser & commercial repair. Same-day doorstep service across Srinagar. Call 9622917697.";
    $image       = esc_url(get_template_directory_uri()) . '/assets/images/hero/hero-mobile-appliances.png';

    if (is_front_page() || is_home()) {
        $url         = home_url('/');
        $title       = 'Abid Electronics Service Hub — Appliance Repair Srinagar';
        $description = "Srinagar's 5-star rated appliance repair hub. Fridge, washing machine, AC, geyser & commercial repair. Same-day doorstep service. Call 9622917697.";
    } elseif (is_page('refrigerator-repair-srinagar') || $page_slug === 'refrigerator-repair-srinagar' || strpos($page_slug, 'fridge') !== false || strpos($page_slug, 'refrigerator') !== false) {
        $title       = 'Refrigerator & Fridge Repair in Srinagar | Abid Electronics';
        $description = 'Same-day fridge repair in Srinagar & Chattabal. Cooling issues, gas filling, compressor repair — all brands. Certified technicians. Call now.';
    } elseif (is_page('washing-machine-repair-srinagar') || $page_slug === 'washing-machine-repair-srinagar' || strpos($page_slug, 'washing-machine') !== false) {
        $title       = 'Washing Machine Repair in Srinagar | Abid Electronics';
        $description = 'Fast washing machine repair near you in Srinagar. Front-load, top-load & semi-automatic. Genuine parts, same-day doorstep visits. Call 9622917697.';
    } elseif (is_page('ac-repair-service-srinagar') || is_page('ac-repair-srinagar') || $page_slug === 'ac-repair-service-srinagar' || $page_slug === 'ac-repair-srinagar' || strpos($page_slug, 'ac-repair') !== false) {
        $title       = 'AC Repair & Service in Srinagar | Abid Electronics';
        $description = 'AC not cooling? Get expert gas filling, servicing & repair in Srinagar. Same-day doorstep visits, all brands. Book your AC service today.';
    } elseif (is_page('geyser-repair-srinagar') || $page_slug === 'geyser-repair-srinagar' || strpos($page_slug, 'geyser') !== false || strpos($page_slug, 'water-heater') !== false) {
        $title       = 'Geyser Repair in Srinagar — Water Heater Service | Abid Electronics';
        $description = 'Geyser not heating? Fast electric & gas geyser repair across Srinagar & Bemina. Certified technicians, genuine parts. Call 9622917697.';
    } elseif (is_page('microwave-oven-repair-srinagar') || $page_slug === 'microwave-oven-repair-srinagar' || strpos($page_slug, 'microwave') !== false) {
        $title       = 'Microwave & Oven Repair in Srinagar | Abid Electronics';
        $description = 'Microwave, oven & induction cooktop repair in Srinagar. Quick diagnosis, doorstep service, all major brands. Call Abid Electronics today.';
    } elseif (is_page('commercial-refrigeration-repair') || $page_slug === 'commercial-refrigeration-repair' || strpos($page_slug, 'commercial') !== false) {
        $title       = 'Commercial Fridge & Bakery Counter Repair Srinagar | Abid Electronics';
        $description = 'Deep freezer, bakery display counter & commercial refrigeration repair in Srinagar. Trusted by local bakeries & shops. Call 9622917697.';
    } elseif (is_page('chattabal') || $page_slug === 'chattabal') {
        $title       = 'Appliance Repair in Chattabal, Srinagar | Abid Electronics';
        $description = 'Local appliance repair shop in Chattabal, Srinagar. Fridge, washing machine, AC & geyser repair — same-day doorstep visits. Call now.';
    } elseif (is_page('bemina') || $page_slug === 'bemina') {
        $title       = 'Appliance Repair Near Bemina Crossing, Srinagar | Abid Electronics';
        $description = 'Fast appliance repair near Bemina Crossing. Fridge, washing machine, geyser & AC service in Srinagar. Certified local technicians.';
    } elseif (is_page('tengpora') || $page_slug === 'tengpora') {
        $title       = 'Appliance Repair in Tengpora, Srinagar | Abid Electronics';
        $description = 'Doorstep appliance repair service in Tengpora, Srinagar. Same-day visits for all home & commercial appliance brands. Call 9622917697.';
    } elseif (is_page('about-us') || is_page('about') || in_array($page_slug, ['about-us', 'about'])) {
        $title       = 'About Abid Electronics — Trusted Appliance Repair in Srinagar';
        $description = 'Meet the team behind Srinagar\'s 5-star rated appliance repair hub. Certified technicians, genuine parts, years of trusted local service.';
    } elseif (is_page('contact') || is_page('contact-us') || is_page('book-appointment-and-service') || in_array($page_slug, ['contact', 'contact-us', 'book-appointment-and-service', 'appointment'])) {
        $title       = 'Contact Abid Electronics — Book a Repair in Srinagar';
        $description = 'Call, WhatsApp or visit Abid Electronics in Chattabal, Srinagar. Same-day appliance repair booking. Phone: 9622917697.';
    } elseif (is_page('gallery') || is_page('work-gallery') || in_array($page_slug, ['gallery', 'work-gallery'])) {
        $title       = 'Work Gallery — Recent Appliance Repairs in Srinagar | Abid Electronics';
        $description = 'Explore our recent refrigerator, washing machine, AC, and commercial refrigeration repair projects completed across Srinagar.';
    } elseif (is_page('faq') || $page_slug === 'faq') {
        $title       = 'Frequently Asked Questions — Appliance Repair | Abid Electronics';
        $description = 'Got questions about pricing, warranty, or same-day repair visits in Srinagar? Find answers to the most common queries here.';
    } elseif (is_singular('services')) {
        $post = get_post();
        $title = get_the_title() . ' in Srinagar | ' . $site_name;
        $description = wp_trim_words(strip_shortcodes($post->post_content), 28) ?: "Expert " . get_the_title() . " in Srinagar by Abid Electronics. Same-day doorstep service, certified technicians, genuine parts.";
        if (has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url($post->ID, 'full');
        }
    } elseif (is_singular('location')) {
        $post = get_post();
        $title = 'Appliance Repair in ' . get_the_title() . ', Srinagar | ' . $site_name;
        $description = "Fast, doorstep appliance repair service in " . get_the_title() . ", Srinagar. Refrigerator, washing machine, AC & geyser repair. Call 9622917697.";
        if (has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url($post->ID, 'full');
        }
    } elseif (is_singular('post')) {
        $post = get_post();
        $title = get_the_title() . ' | Abid Electronics Blog';
        $description = wp_trim_words(strip_shortcodes($post->post_content), 25);
        if (has_post_thumbnail()) {
            $image = get_the_post_thumbnail_url($post->ID, 'full');
        }
    }

    $keywords = "abid electronics, abid electronics srinagar, appliance repair srinagar, appliance repair near me, fridge repair near me, refrigerator repair srinagar, fridge gas filling srinagar, washing machine repair srinagar, washing machine technician bemina, ac repair and service srinagar, ac gas filling srinagar, geyser repair srinagar, geyser repair chattabal, microwave oven repair srinagar, commercial fridge repair srinagar, bakery display counter repair, appliance repair chattabal, appliance repair bemina, appliance repair tengpora";
    ?>
    <!-- Technical SEO by Abid Electronics Engine -->
    <meta name="description" content="<?php echo esc_attr($description); ?>">
    <meta name="keywords" content="<?php echo esc_attr($keywords); ?>">
    <link rel="canonical" href="<?php echo esc_url($url); ?>">
    <link rel="alternate" hreflang="en-in" href="<?php echo esc_url(home_url('/')); ?>">
    <link rel="alternate" hreflang="x-default" href="<?php echo esc_url(home_url('/')); ?>">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="<?php echo (is_front_page() || is_home()) ? 'website' : (is_singular() ? 'article' : 'website'); ?>">
    <meta property="og:url" content="<?php echo esc_url($url); ?>">
    <meta property="og:site_name" content="<?php echo esc_attr($site_name); ?>">
    <meta property="og:locale" content="en_IN">
    <meta property="og:title" content="<?php echo esc_attr($title); ?>">
    <meta property="og:description" content="<?php echo esc_attr($description); ?>">
    <?php if ($image): ?>
        <meta property="og:image" content="<?php echo esc_url($image); ?>">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    <?php endif; ?>

    <!-- Twitter Card -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?php echo esc_url($url); ?>">
    <meta property="twitter:title" content="<?php echo esc_attr($title); ?>">
    <meta property="twitter:description" content="<?php echo esc_attr($description); ?>">
    <?php if ($image): ?>
        <meta property="twitter:image" content="<?php echo esc_url($image); ?>">
    <?php endif; ?>

    <!-- Robots & Crawlers -->
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1" />
    <?php

    // Structured Data Engine
    webassets_seo_structured_data($title, $description, $url, $image);

    do_action('webassets_seo_meta_tags_done');
}
add_action('wp_head', 'webassets_seo_meta_tags', 1);

/**
 * Geo SEO Meta Tags for Local Srinagar Ranking
 */
function webassets_seo_geo_tags() {
    ?>
    <!-- Geo Location SEO (Srinagar, Jammu & Kashmir) -->
    <meta name="geo.region" content="IN-JK">
    <meta name="geo.placename" content="Srinagar">
    <meta name="geo.position" content="34.0886;74.7890">
    <meta name="ICBM" content="34.0886, 74.7890">
    <?php
}
add_action('wp_head', 'webassets_seo_geo_tags', 2);

/**
 * Output Rich JSON-LD Structured Data
 */
function webassets_seo_structured_data($title, $description, $url, $image) {
    $schemas = [];

    // 1. Breadcrumb Schema (All Pages except Front Page)
    if (!is_front_page()) {
        $breadcrumb_list = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Home",
                    "item" => home_url('/')
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => get_the_title() ?: $title,
                    "item" => $url
                ]
            ]
        ];
        $schemas[] = $breadcrumb_list;
    }

    // 2. BlogPosting Schema (For Blog Single Posts)
    if (is_singular('post')) {
        $post = get_post();
        $schemas[] = [
            "@context" => "https://schema.org",
            "@type" => "BlogPosting",
            "headline" => $title,
            "description" => $description,
            "image" => $image,
            "author" => [
                "@type" => "Person",
                "name" => get_the_author_meta('display_name', $post->post_author) ?: 'Abid Electronics Team'
            ],
            "datePublished" => get_the_date('c', $post->ID),
            "dateModified" => get_the_modified_date('c', $post->ID),
            "mainEntityOfPage" => [
                "@type" => "WebPage",
                "@id" => $url
            ],
            "publisher" => [
                "@type" => "Organization",
                "name" => "Abid Electronics Service Hub",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => esc_url(get_template_directory_uri()) . '/assets/images/logo.svg'
                ]
            ]
        ];
    }

    // 3. LocalBusiness & Electrician Schema (Sitewide Foundation)
    $local_business_schema = [
        "@context" => "https://schema.org",
        "@type" => "HomeAndConstructionBusiness",
        "@id" => home_url('/#organization'),
        "name" => "Abid Electronics Service Hub",
        "alternateName" => ["Abid Electronics", "Abid Electronics Srinagar", "Abid Electronics Appliance Repair"],
        "url" => home_url('/'),
        "logo" => esc_url(get_template_directory_uri()) . '/assets/images/logo.svg',
        "image" => esc_url(get_template_directory_uri()) . '/assets/images/hero/hero-mobile-appliances.png',
        "description" => "Srinagar's top-rated multi-brand home and commercial appliance repair service hub. Certified technicians for Refrigerator, Washing Machine, AC, Geyser, Microwave, and Bakery Display Counters.",
        "telephone" => "+919622917697",
        "email" => "support@abidelectronics.in",
        "priceRange" => "₹₹",
        "paymentAccepted" => "Cash, UPI, Google Pay, PhonePe, Paytm, Bank Transfer",
        "currenciesAccepted" => "INR",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "Bemina Crossing, Chattabal",
            "addressLocality" => "Srinagar",
            "addressRegion" => "Jammu and Kashmir",
            "postalCode" => "190001",
            "addressCountry" => "IN"
        ],
        "geo" => [
            "@type" => "GeoCoordinates",
            "latitude" => 34.0886,
            "longitude" => 74.7890
        ],
        "openingHoursSpecification" => [
            [
                "@type" => "OpeningHoursSpecification",
                "dayOfWeek" => [
                    "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"
                ],
                "opens" => "09:00",
                "closes" => "20:00"
            ]
        ],
        "areaServed" => [
            ["@type" => "City", "name" => "Srinagar"],
            ["@type" => "AdministrativeArea", "name" => "Chattabal"],
            ["@type" => "AdministrativeArea", "name" => "Bemina"],
            ["@type" => "AdministrativeArea", "name" => "Tengpora"],
            ["@type" => "AdministrativeArea", "name" => "Batamaloo"],
            ["@type" => "AdministrativeArea", "name" => "Rajbagh"],
            ["@type" => "AdministrativeArea", "name" => "Lal Chowk"],
            ["@type" => "State", "name" => "Jammu and Kashmir"]
        ],
        "knowsAbout" => [
            "Refrigerator Repair",
            "Fridge Gas Filling",
            "Washing Machine Repair",
            "Front Load Washing Machine Service",
            "Air Conditioner Repair & Servicing",
            "AC Gas Filling",
            "Geyser & Water Heater Repair",
            "Microwave & Oven Repair",
            "Commercial Refrigeration",
            "Bakery Display Counter Repair",
            "Deep Freezer Maintenance"
        ],
        "aggregateRating" => [
            "@type" => "AggregateRating",
            "ratingValue" => "4.9",
            "bestRating" => "5",
            "worstRating" => "1",
            "ratingCount" => "337",
            "reviewCount" => "337"
        ],
        "sameAs" => array_values(array_filter([
            "https://www.justdial.com/Srinagar/Abid-Electronics-Service-Hub-Near-Bemina-Crossing-Chattabal/",
            "https://www.indiamart.com/abidelectronicsservicehub/",
            "https://magicpin.in/Srinagar/Tengpora/Services/Abid-Electronics/",
            get_theme_mod('social_facebook', 'https://www.facebook.com/abidelectronicssrinagar'),
            get_theme_mod('social_instagram', 'https://www.instagram.com/abidelectronics'),
            get_theme_mod('social_twitter')
        ])),
        "hasOfferCatalog" => [
            "@type" => "OfferCatalog",
            "name" => "Appliance Repair Services",
            "itemListElement" => [
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Refrigerator & Fridge Repair",
                        "description" => "Same-day doorstep refrigerator repair, cooling diagnosis, gas filling, and compressor maintenance in Srinagar."
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Washing Machine Repair",
                        "description" => "Certified repair for front-load, top-load, and semi-automatic washing machines across Srinagar."
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "AC Repair & Deep Servicing",
                        "description" => "Split & Window AC repair, gas refilling, filter cleaning, and coil servicing."
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Geyser & Water Heater Repair",
                        "description" => "Fast heating element replacement, thermostat repair, and electric/gas geyser service in Srinagar."
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Microwave & Oven Repair",
                        "description" => "Doorstep electronic repair for convection, grill, and solo microwave ovens."
                    ]
                ],
                [
                    "@type" => "Offer",
                    "itemOffered" => [
                        "@type" => "Service",
                        "name" => "Commercial Refrigeration & Bakery Counters",
                        "description" => "Deep freezer, cake display counter, and commercial refrigeration repair for bakeries and shops."
                    ]
                ]
            ]
        ]
    ];
    $schemas[] = $local_business_schema;

    // 4. Dedicated Service Schema (For Service Pages / Single Services)
    if (is_singular('services') || is_page(['services', 'refrigerator-repair-srinagar', 'washing-machine-repair-srinagar', 'ac-repair-service-srinagar', 'geyser-repair-srinagar', 'microwave-oven-repair-srinagar', 'commercial-refrigeration-repair'])) {
        $service_name = get_the_title() ?: 'Appliance Repair Service';
        $service_schema = [
            "@context" => "https://schema.org",
            "@type" => "Service",
            "serviceType" => $service_name,
            "provider" => [
                "@type" => "LocalBusiness",
                "name" => "Abid Electronics Service Hub",
                "telephone" => "+919622917697",
                "address" => [
                    "@type" => "PostalAddress",
                    "streetAddress" => "Bemina Crossing, Chattabal",
                    "addressLocality" => "Srinagar",
                    "addressRegion" => "Jammu and Kashmir",
                    "postalCode" => "190001",
                    "addressCountry" => "IN"
                ]
            ],
            "areaServed" => "Srinagar, Jammu and Kashmir",
            "description" => $description
        ];
        $schemas[] = $service_schema;
    }

    // 5. FAQPage Schema (For Home & FAQ Pages)
    if (is_front_page() || is_page('faq')) {
        $faq_query = new WP_Query([
            'post_type'      => 'faq',
            'posts_per_page' => -1,
            'post_status'    => 'publish'
        ]);

        $faq_items = [];
        if ($faq_query->have_posts()) {
            while ($faq_query->have_posts()) {
                $faq_query->the_post();
                $faq_items[] = [
                    "@type" => "Question",
                    "name" => get_the_title(),
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => wp_strip_all_tags(get_the_content())
                    ]
                ];
            }
            wp_reset_postdata();
        }

        // Fallback default FAQs if CPT empty
        if (empty($faq_items)) {
            $faq_items = [
                [
                    "@type" => "Question",
                    "name" => "How fast can you repair an appliance in Srinagar?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Most repairs are completed same-day with doorstep visits across Srinagar, Chattabal, Bemina, and Tengpora."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "Do you repair all refrigerator and washing machine brands?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Yes — we service LG, Samsung, Whirlpool, Godrej, Haier, Panasonic, IFB, Bosch, Voltas, and all major brands."
                    ]
                ],
                [
                    "@type" => "Question",
                    "name" => "Do you provide genuine spare parts with warranty?",
                    "acceptedAnswer" => [
                        "@type" => "Answer",
                        "text" => "Yes, all repairs use 100% genuine replacement parts with guaranteed service warranty."
                    ]
                ]
            ];
        }

        $schemas[] = [
            "@context" => "https://schema.org",
            "@type" => "FAQPage",
            "mainEntity" => $faq_items
        ];
    }

    // 6. VideoObject Schema (Rich Video Snippet)
    if (is_front_page()) {
        $schemas[] = [
            "@context" => "https://schema.org",
            "@type" => "VideoObject",
            "name" => "Abid Electronics Service Hub — Same-Day Appliance Repair Srinagar",
            "description" => "Doorstep repair for Refrigerator, Washing Machine, AC, Geyser, and Commercial Refrigeration across Srinagar.",
            "thumbnailUrl" => [
                esc_url(get_template_directory_uri()) . '/assets/images/hero/hero-mobile-appliances.png'
            ],
            "uploadDate" => "2024-01-01T09:00:00+05:30",
            "contentUrl" => home_url('/'),
            "embedUrl" => "https://www.youtube.com/embed/mh7w_zY2PZ4",
            "publisher" => [
                "@type" => "Organization",
                "name" => "Abid Electronics Service Hub",
                "logo" => [
                    "@type" => "ImageObject",
                    "url" => esc_url(get_template_directory_uri()) . '/assets/images/logo.svg'
                ]
            ]
        ];
    }

    // 7. WebSite Schema with Sitelinks Searchbox
    $schemas[] = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => "Abid Electronics Service Hub",
        "url" => home_url('/'),
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => [
                "@type" => "EntryPoint",
                "urlTemplate" => home_url('/?s={search_term_string}')
            ],
            "query-input" => "required name=search_term_string"
        ]
    ];

    echo "\n" . '<!-- Abid Electronics Structured Data Engine (JSON-LD) -->' . "\n";
    echo '<script type="application/ld+json">' . wp_json_encode($schemas, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . '</script>' . "\n";
}
