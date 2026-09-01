<?php
/**
 * The header for our theme
 *
 * @package WebAssets
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Favicon -->
    <?php
    $site_icon_url = get_site_icon_url();
    if ($site_icon_url): ?>
        <link rel="shortcut icon" type="image/png" href="<?php echo esc_url($site_icon_url); ?>">
    <?php else: ?>
        <link rel="shortcut icon" type="image/png"
            href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/favicon.png">
    <?php endif; ?>

    <!-- Schema Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "LocalBusiness",
      "name": "Abid Electronics Service Hub",
      "image": "<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/logo.svg",
      "@id": "<?php echo esc_url(home_url('/')); ?>",
      "url": "<?php echo esc_url(home_url('/')); ?>",
      "telephone": "+919622917697",
      "priceRange": "₹₹",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "Bemina Crossing, Chattabal",
        "addressLocality": "Srinagar",
        "postalCode": "190001",
        "addressRegion": "Jammu and Kashmir",
        "addressCountry": "IN"
      },
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 34.0839326,
        "longitude": 74.7875588
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday",
          "Tuesday",
          "Wednesday",
          "Thursday",
          "Friday",
          "Saturday",
          "Sunday"
        ],
        "opens": "09:00",
        "closes": "20:00"
      },
      "sameAs": [
        "https://www.facebook.com",
        "https://www.instagram.com"
      ],
      "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.9",
        "reviewCount": "337"
      }
    }
    </script>

    <!-- Theme CSS Dependencies -->
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/themify-icons.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/flaticon_fixaroo.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/animate.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/owl.carousel.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/owl.theme.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/slick.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/slick-theme.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/swiper.min.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/owl.transitions.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/jquery.fancybox.css" rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/css/odometer-theme-default.css"
        rel="stylesheet">
    <link href="<?php echo esc_url(get_template_directory_uri()); ?>/assets/sass/style.css" rel="stylesheet">

    <?php wp_head(); ?>
    <link href="<?php echo esc_url(get_stylesheet_uri()); ?>" rel="stylesheet">
</head>

<body <?php body_class(); ?>>
    <?php wp_body_open(); ?>

    <!-- start page-wrapper -->
    <div class="page-wrapper">
        <!-- start preloader -->
        <div class="preloader">
            <div class="vertical-centered-box">
                <div class="content">
                    <div class="loader-circle"></div>
                    <div class="loader-line-mask">
                        <div class="loader-line"></div>
                    </div>
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/preloader.png" alt="">
                </div>
            </div>
        </div>
        <!-- end preloader -->

        <!-- Start header -->
        <header id="header">
            <div class="wpo-site-header">
                <nav class="navigation navbar navbar-expand-lg navbar-light">
                    <div class="container-fluid px-3 px-xl-5">
                        <!-- Mobile Menu Offcanvas Container (Mobile Only) -->
                        <div id="navbar" class="collapse navbar-collapse navigation-holder d-lg-none">
                            <button class="menu-close"><i class="ti-close"></i></button>
                            <?php
                            wp_nav_menu([
                                'theme_location' => 'header-menu',
                                'container' => false,
                                'menu_class' => 'nav navbar-nav mb-2 mb-lg-0',
                                'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
                                'walker' => new wp_bootstrap_navwalker(),
                            ]);
                            ?>

                            <!-- Mobile Menu Bottom Actions (White Card with Theme Colors) -->
                            <div class="mobile-menu-actions-wrap p-3">
                                <div class="mobile-menu-actions bg-white p-3 rounded-3 shadow-sm text-center">
                                    <!-- Subtle Section Label -->
                                    <div class="mobile-actions-header mb-2 text-center">
                                        <span class="text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 1px; color: #6b7280;">Quick Support &amp; Booking</span>
                                    </div>

                                    <!-- Book Appointment Button (Theme Royal Blue) -->
                                    <div class="mobile-menu-cta mb-2">
                                        <?php $appoint_link = get_theme_mod('hero_btn1_link', home_url('/appointment/')); ?>
                                        <a href="<?php echo esc_url($appoint_link); ?>" class="theme-btn-s2 w-100 text-center py-2 px-3 d-flex align-items-center justify-content-center gap-2 text-decoration-none" style="font-size: 14px; font-weight: 600; border-radius: 6px; letter-spacing: 0.3px; background: #3860D2; border-color: #3860D2; color: #ffffff;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="bi bi-calendar2-check" viewBox="0 0 16 16">
                                                <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
                                            <span><?php echo esc_html(get_theme_mod('hero_btn1_text', 'Book Appointment')); ?></span>
                                        </a>
                                    </div>

                                    <!-- Small Call and WhatsApp Buttons (Theme Ice Blue & WhatsApp Green) -->
                                    <?php 
                                    $phone_num = get_theme_mod('contact_phone', '+91 9622917697');
                                    $clean_phone = preg_replace('/[^0-9+]/', '', $phone_num);
                                    $wa_link = get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697');
                                    ?>
                                    <div class="mobile-contact-btns d-flex gap-2 mb-3">
                                        <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="btn flex-fill d-inline-flex align-items-center justify-content-center gap-2 py-2 px-2 text-decoration-none" style="font-size: 13px; font-weight: 600; border-radius: 6px; background-color: #eef2fd; color: #3860D2; border: 1px solid #d0defe;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                            </svg>
                                            <span>Call Now</span>
                                        </a>
                                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn flex-fill d-inline-flex align-items-center justify-content-center gap-2 py-2 px-2 text-decoration-none" style="font-size: 13px; font-weight: 600; border-radius: 6px; background-color: #25D366; color: #ffffff; border: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.707 1.916.807 2.05c.099.133 1.39 2.123 3.37 2.977.47.203.837.324 1.123.415.474.152.905.13 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    </div>

                                    <!-- Social Media Icons (Theme Ice Blue Buttons with Royal Blue Icons) -->
                                    <div class="mobile-menu-socials d-flex align-items-center justify-content-center gap-2 pt-1">
                                        <?php 
                                        $ig = get_theme_mod('social_instagram', '#');
                                        $fb = get_theme_mod('social_facebook', '#');
                                        $tw = get_theme_mod('social_twitter', '#');
                                        ?>
                                        <?php if ($ig) : ?>
                                            <a href="<?php echo esc_url($ig); ?>" target="_blank" class="mobile-social-icon" title="Instagram">
                                                <i class="ti-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($fb) : ?>
                                            <a href="<?php echo esc_url($fb); ?>" target="_blank" class="mobile-social-icon" title="Facebook">
                                                <i class="ti-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tw) : ?>
                                            <a href="<?php echo esc_url($tw); ?>" target="_blank" class="mobile-social-icon" title="Twitter">
                                                <i class="ti-twitter-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="mobile-social-icon" title="WhatsApp">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.707 1.916.807 2.05c.099.133 1.39 2.123 3.37 2.977.47.203.837.324 1.123.415.474.152.905.13 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Topbar (Visible on < 992px) -->
                        <div class="d-flex d-lg-none align-items-center justify-content-between w-100 py-2">
                            <div class="mobail-menu">
                                <button type="button" class="navbar-toggler open-btn">
                                    <span class="sr-only">Toggle navigation</span>
                                    <span class="icon-bar first-angle"></span>
                                    <span class="icon-bar middle-angle"></span>
                                    <span class="icon-bar last-angle"></span>
                                </button>
                            </div>
                            <div class="navbar-header text-center">
                                <a class="navbar-brand m-0" href="<?php echo esc_url(home_url('/')); ?>">
                                    <?php
                                    if (has_custom_logo()) {
                                        the_custom_logo();
                                    } else {
                                        echo '<img src="' . esc_url(get_template_directory_uri()) . '/assets/images/logo.svg" alt="Fixaroo" style="max-height: 40px;">';
                                    }
                                    ?>
                                </a>
                            </div>
                            <div class="mobile-call-btn">
                                <?php $contact_phone = get_theme_mod('contact_phone', '9622917697'); ?>
                                <a href="tel:<?php echo esc_attr(str_replace(' ', '', $contact_phone)); ?>"
                                    class="btn btn-primary rounded-circle p-2"
                                    style="width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; background-color: #3860D2; border-color: #3860D2;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-telephone-fill text-white" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <!-- Desktop Header Navigation (Visible on >= 992px) -->
                        <div
                            class="header-desktop-wrap d-none d-lg-flex align-items-center justify-content-between w-100">
                            <!-- Left Menu -->
                            <div class="header-nav-left d-flex align-items-center justify-content-end flex-grow-1 pe-4">
                                <?php webassets_render_header_menu('left'); ?>
                            </div>

                            <!-- Center Logo -->
                            <div class="navbar-header flex-shrink-0 text-center px-3">
                                <a class="navbar-brand m-0" href="<?php echo esc_url(home_url('/')); ?>">
                                    <?php
                                    if (has_custom_logo()) {
                                        the_custom_logo();
                                    } else {
                                        echo '<img src="' . esc_url(get_template_directory_uri()) . '/assets/images/logo.svg" alt="Fixaroo">';
                                    }
                                    ?>
                                </a>
                            </div>

                            <!-- Right Menu + Search + Call Button -->
                            <div
                                class="header-nav-right-wrap d-flex align-items-center justify-content-between flex-grow-1 ps-4">
                                <div class="header-nav-right d-flex align-items-center">
                                    <?php webassets_render_header_menu('right'); ?>
                                </div>
                                <div class="header-actions d-flex align-items-center gap-3 ms-auto">

                                    <div class="close-form">
                                        <?php $contact_phone = get_theme_mod('contact_phone', '9622917697'); ?>
                                        <a class="theme-btn-s2 call-btn-header"
                                            href="tel:<?php echo esc_attr(str_replace(' ', '', $contact_phone)); ?>">
                                            <i>
                                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15"
                                                    fill="currentColor" class="bi bi-telephone" viewBox="0 0 16 16">
                                                    <path
                                                        d="M3.654 1.328a.678.678 0 0 0-1.015-.063L1.605 2.3c-.483.484-.661 1.169-.45 1.77a17.568 17.568 0 0 0 4.168 6.608 17.569 17.569 0 0 0 6.608 4.168c.601.211 1.286.033 1.77-.45l1.034-1.034a.678.678 0 0 0-.063-1.015l-2.307-1.794a.678.678 0 0 0-.58-.122l-2.19.547a1.745 1.745 0 0 1-1.657-.459L5.482 8.062a1.745 1.745 0 0 1-.46-1.657l.548-2.19a.678.678 0 0 0-.122-.58L3.654 1.328zM1.884.511a1.745 1.745 0 0 1 2.612.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.678.678 0 0 0 .178.643l2.457 2.457a.678.678 0 0 0 .644.178l2.189-.547a1.745 1.745 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.634 18.634 0 0 1-7.01-4.42 18.634 18.634 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877L1.885.511z" />
                                                </svg>
                                            </i>
                                            <span><?php echo esc_html($contact_phone); ?></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div><!-- end of container -->
                </nav>
            </div>
        </header>
        <!-- end of header -->