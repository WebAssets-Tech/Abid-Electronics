<?php
/* Template Name: About Us */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2>Abid Electronics Service Hub</h2>
                    <h3>About Us</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start about company main profile section (dynamic from page editor) -->
<section class="about-page-details">
    <div class="container">
        <!-- 1. Top Featured Banner (from page Featured Image or high-res theme banner) -->
        <div class="row">
            <div class="col-12">
                <div class="about-main-banner">
                    <?php if (has_post_thumbnail()) : ?>
                        <?php the_post_thumbnail('full'); ?>
                    <?php else : ?>
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/1.jpg" alt="Abid Electronics About Banner">
                    <?php endif; ?>
                </div>

                <!-- 2. Badges / Tag Pills Bar -->
                <div class="about-pills-bar">
                    <span class="about-pill-item">Abid Electronics — Multi-Brand Hub</span>
                    <span class="about-pill-item">Same-Day Doorstep Service</span>
                    <span class="about-pill-item">Certified Technicians</span>
                    <span class="about-pill-item">All Major Brands Serviced</span>
                    <span class="about-pill-item">Srinagar &amp; Kashmir</span>
                    <span class="about-pill-item">Genuine Spare Parts</span>
                    <span class="about-pill-item">Warranty on Work</span>
                    <span class="about-pill-item">5-Star Rated (4.9★)</span>
                </div>
            </div>
        </div>

        <!-- 3. Two Column Details (Page content on left, dynamic sidebar on right) -->
        <div class="row">
            <div class="col-lg-8 col-12">
                <div class="about-company-content entry-content">
                    <?php 
                    while (have_posts()) : the_post();
                        $content = get_the_content();
                        if (!empty(trim($content))) :
                            the_content();
                        else :
                            // Business default content if page content is not yet populated
                            ?>
                            <h2>Srinagar's Most Trusted Multi-Brand Home Appliance Repair Hub</h2>
                            <p class="lead">At Abid Electronics, we don't just repair appliances — we deliver prompt, reliable, and expert doorstep solutions that keep your home and business running smoothly across Srinagar and surrounding areas in Kashmir.</p>
                            <p>With over a decade of hands-on technical experience, our certified technicians specialize in comprehensive multi-brand repair and servicing for refrigerators, washing machines, air conditioners, geysers, and commercial cooling appliances. We understand that an appliance breakdown disrupts your daily routine, which is why we provide guaranteed same-day doorstep visits with 100% genuine replacement parts and transparent pricing.</p>
                            
                            <h3>Why Thousands of Households in Srinagar Trust Us:</h3>
                            <ul class="about-feature-bullets">
                                <li><strong>Certified &amp; Experienced Technicians:</strong> Highly trained experts equipped with modern diagnostic tools for all major brands including LG, Samsung, Whirlpool, Godrej, Panasonic, and Daikin.</li>
                                <li><strong>Same-Day Doorstep Service:</strong> Fast on-site visits across Chattabal, Bemina, Rajbagh, Lal Chowk, and all Srinagar localities.</li>
                                <li><strong>Transparent Pricing:</strong> Free upfront diagnostic estimates with no hidden fees or surprise charges.</li>
                                <li><strong>Post-Service Warranty:</strong> Complete peace of mind with a warranty on all spare parts and repair work.</li>
                            </ul>
                            <?php
                        endif;
                    endwhile;
                    ?>
                </div>
            </div>

            <!-- Right Column: Sidebar info matching reference image -->
            <div class="col-lg-4 col-12">
                <div class="about-company-sidebar">
                    <!-- Top Action Button -->
                    <a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', home_url('/appointment/'))); ?>" class="about-sidebar-btn theme-btn-s2 w-100 text-center mb-4">
                        Book Doorstep Repair
                    </a>

                    <!-- Sidebar Card -->
                    <div class="about-sidebar-card">
                        <!-- Address -->
                        <div class="about-sidebar-item">
                            <div class="icon"><i class="fi flaticon-home-address"></i></div>
                            <div class="info">
                                <h4>Our Address</h4>
                                <p><?php echo nl2br(esc_html(get_theme_mod('contact_address', "Bemina Crossing, Chattabal\nSrinagar, J&K - 190001"))); ?></p>
                            </div>
                        </div>

                        <!-- Contact Way -->
                        <div class="about-sidebar-item">
                            <div class="icon"><i class="fi flaticon-phone-call"></i></div>
                            <div class="info">
                                <h4>Contact Way</h4>
                                <p>
                                    <a href="mailto:<?php echo esc_attr(get_theme_mod('contact_email', 'support@abidelectronics.com')); ?>"><?php echo esc_html(get_theme_mod('contact_email', 'support@abidelectronics.com')); ?></a><br>
                                    <a href="tel:<?php echo esc_attr(get_theme_mod('contact_phone', '+919622917697')); ?>" class="phone-highlight"><?php echo esc_html(get_theme_mod('contact_phone', '+91 9622917697')); ?></a>
                                </p>
                            </div>
                        </div>

                        <!-- Open Hours -->
                        <div class="about-sidebar-item">
                            <div class="icon"><i class="ti-time"></i></div>
                            <div class="info">
                                <h4>Working Hours</h4>
                                <p>Monday – Sunday: 9:00 AM – 8:00 PM<br><span class="badge-status">Same-Day Emergency Service</span></p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="about-sidebar-social">
                            <a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>" target="_blank" title="WhatsApp"><i class="ti-mobile"></i></a>
                            <a href="<?php echo esc_url(get_theme_mod('social_instagram', '#')); ?>" target="_blank" title="Instagram"><i class="ti-instagram"></i></a>
                            <a href="<?php echo esc_url(get_theme_mod('social_facebook', '#')); ?>" target="_blank" title="Facebook"><i class="ti-facebook"></i></a>
                            <a href="<?php echo esc_url(get_theme_mod('social_twitter', '#')); ?>" target="_blank" title="Twitter"><i class="ti-twitter-alt"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end about company main profile section -->

<!-- start about section -->
<section class="wpo-about-section">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-12 col-12">
                <div class="about-title-left wow fadeInLeftSlow" data-wow-duration="1200ms">
                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('about_left_title', 'about us')); ?></span>
                </div>
            </div>
            <div class="col-lg-9 col-md-12 col-12">
                <div class="about-title-right">
                    <h2 class="poort-text poort-in-right"><?php echo wp_kses_post(get_theme_mod('about_right_title', "At <span>Abid Electronics,</span> we’re not just technicians we’re <span>problem solvers</span>, safety experts & service professionals. With over 10 years of hands-<span>on experience</span>.")); ?></h2>
                </div>
            </div>
        </div>
        <div class="about-max">
            <div class="about-wrap">
                <div class="about-items">
                    <div class="icon">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/icon-1.svg" alt="icon">
                    </div>
                    <div class="about-text">
                        <h3><?php echo esc_html(get_theme_mod('about_card1_title', 'expertise area')); ?></h3>
                        <ul>
                            <?php 
                            $c1_lines = explode("\n", get_theme_mod('about_card1_list', "Same-Day Doorstep Service\nAll Major Brands Serviced\nGenuine Spare Parts\n5-Star Rated Service"));
                            foreach ($c1_lines as $c1) {
                                if (trim($c1)) {
                                    echo '<li><span>' . esc_html(trim($c1)) . '</span></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="about-items">
                    <div class="icon">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/icon-2.svg" alt="icon">
                    </div>
                    <div class="about-text">
                        <h3><?php echo esc_html(get_theme_mod('about_card2_title', 'Our Mission')); ?></h3>
                        <ul>
                            <?php 
                            $c2_lines = explode("\n", get_theme_mod('about_card2_list', "Certified Technicians\nAffordable & Transparent Pricing\nOver 1000+ Appliances Repaired\nSatisfaction Guarantee"));
                            foreach ($c2_lines as $c2) {
                                if (trim($c2)) {
                                    $active_class = (trim($c2) === 'Affordable & Transparent Pricing') ? ' class="active"' : '';
                                    echo '<li' . $active_class . '><span>' . esc_html(trim($c2)) . '</span></li>';
                                }
                            }
                            ?>
                        </ul>
                    </div>
                </div>
                <div class="about-items">
                    <div class="items-image">
                        <?php 
                        $about_img = get_theme_mod('about_card3_image');
                        if ($about_img) {
                            echo wp_get_attachment_image($about_img, 'full');
                        } else {
                            echo '<img src="' . esc_url(get_template_directory_uri()) . '/assets/images/about/1.jpg" alt="About Abid Electronics">';
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end about section -->

<!-- Start marquee -->
<section class="marquee-section">
    <div class="marquee_container">
        <?php
            $marquee_items = [];
            for ($m = 1; $m <= 3; $m++) {
                $item = get_theme_mod("marquee_item_$m");
                if ($item) {
                    $marquee_items[] = $item;
                }
            }
            if (empty($marquee_items)) {
                $marquee_items = [
                    'Same-Day Doorstep Service Across Srinagar',
                    'Call 9622917697',
                    "Srinagar's 5-Star Rated Appliance Repair Hub",
                ];
            }
        ?>
        <div class="marquee">
            <?php foreach ($marquee_items as $m_item): ?>
                <h2><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marquee-shape.png" alt="icon"> <?php echo esc_html($m_item); ?></h2>
            <?php endforeach; ?>
        </div>
        <div class="marquee">
            <?php foreach ($marquee_items as $m_item): ?>
                <h2><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/marquee-shape.png" alt="icon"> <?php echo esc_html($m_item); ?></h2>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<!--End marquee-->

<!--start service-->
<section class="wpo-service-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-12">
                <div class="wpo-section-title">
                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('services_label', 'Our services')); ?></span>
                    <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('services_title', 'Multi-Brand Appliance Repair Tailored for You')); ?></h2>
                    <p><?php echo esc_html(get_theme_mod('services_desc', 'We provide comprehensive repair and maintenance for all your home and commercial appliances, with genuine parts and same-day service across Srinagar.')); ?></p>
                </div>
            </div>
        </div>
        <div class="service-wrap">
            <?php
            $services_query    = new WP_Query(['post_type' => 'services', 'posts_per_page' => -1, 'order' => 'ASC']);
            $service_icon_map  = [1 => 'icon-1.svg', 2 => 'icon-2.svg', 3 => 'icon-1.svg', 4 => 'icon-3.svg', 5 => 'icon-4.svg'];
            $service_img_map   = [1 => '2.jpg', 2 => '3.jpg', 3 => '1.jpg', 4 => '4.jpg', 5 => '5.jpg'];
            $service_durations = [1 => '1000ms', 2 => '1200ms', 3 => '1400ms', 4 => '1600ms', 5 => '1800ms'];
            $service_idx       = 0;

            if ($services_query->have_posts()): while ($services_query->have_posts()): $services_query->the_post();
                $service_idx++;
                $meta_icon = get_post_meta(get_the_ID(), '_service_icon', true);
                $icon_file = $service_icon_map[$service_idx] ?? 'icon-1.svg';
                $icon_url  = $meta_icon ? $meta_icon : (get_template_directory_uri() . '/assets/images/service/' . $icon_file);

                $fallback_img_file = $service_img_map[$service_idx] ?? '2.jpg';
                $fallback_img_url  = get_template_directory_uri() . '/assets/images/service/' . $fallback_img_file;
                $duration          = $service_durations[$service_idx] ?? '1000ms';
                $active_cls        = ($service_idx === 3) ? ' active' : '';
            ?>
                <div class="service-items<?php echo $active_cls; ?> wow fadeInUp" data-wow-duration="<?php echo esc_attr($duration); ?>">
                    <div class="service-default">
                        <div class="service-bg">
                            <img src="<?php echo esc_url($icon_url); ?>" alt="<?php the_title_attribute(); ?>">
                            <h3><?php the_title(); ?></h3>
                        </div>
                    </div>
                    <div class="service-expanded">
                        <?php if (has_post_thumbnail()) {
                            the_post_thumbnail('full', ['class' => 'service-image']);
                        } else { ?>
                            <img class="service-image" src="<?php echo esc_url($fallback_img_url); ?>" alt="<?php the_title_attribute(); ?>">
                        <?php } ?>
                        <div class="service-content">
                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                            <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
                            <a class="arrow" href="<?php the_permalink(); ?>"><i class="ti-arrow-top-right"></i></a>
                        </div>
                    </div>
                </div>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>
        <div class="shape">
            <svg version="1.1" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 0 C0.95492315 -0.00226556 1.90984629 -0.00453111 2.8937065 -0.00686532 C6.06608773 -0.01315201 9.23842565 -0.01237941 12.41081238 -0.0115509 C14.69658462 -0.0145457 16.98235636 -0.01794897 19.26812744 -0.02172852 C25.45829608 -0.03053948 31.64845089 -0.03290539 37.83862519 -0.0335443 C41.71060151 -0.03425638 45.58257434 -0.03639369 49.45454979 -0.03904152 C62.27859919 -0.0478085 75.10263746 -0.05246243 87.92668942 -0.05171105 C88.61657695 -0.05167109 89.30646448 -0.05163113 90.01725769 -0.05158997 C91.0533659 -0.05152864 91.0533659 -0.05152864 92.11040559 -0.05146609 C103.30102578 -0.05105976 114.4916123 -0.06062867 125.68222276 -0.07472833 C137.19856219 -0.08912286 148.71488088 -0.09581408 160.23122996 -0.09513456 C166.68640349 -0.09488015 173.14153888 -0.09769977 179.59670448 -0.10831261 C185.66982179 -0.11802541 191.74286473 -0.11805097 197.81598473 -0.11079597 C200.03747542 -0.10975413 202.25897083 -0.1120891 204.48045349 -0.11815643 C225.32569193 -0.17137614 242.7304994 0.45405382 259.00138855 15.13371277 C268.36797554 25.735957 272.67994512 36.90841451 273.37638855 50.88371277 C274.30775193 65.5329537 279.77131193 77.98893253 290.4662323 88.09465027 C303.07175297 98.23345118 318.77397042 99.5431656 334.37638855 99.82121277 C335.70975249 99.85451438 337.04308703 99.88901496 338.37638855 99.92472839 C341.58463668 100.00609701 344.79264961 100.07559973 348.00138855 100.13371277 C348.00138855 103.43371277 348.00138855 106.73371277 348.00138855 110.13371277 C189.27138855 110.13371277 30.54138855 110.13371277 -132.99861145 110.13371277 C-132.99861145 106.83371277 -132.99861145 103.53371277 -132.99861145 100.13371277 C-131.83627991 100.11183899 -131.83627991 100.11183899 -130.65046692 100.08952332 C-99.35072643 99.80488703 -99.35072643 99.80488703 -72.99861145 84.32511902 C-69.60803976 80.61005902 -67.21163838 76.63421096 -64.99861145 72.13371277 C-64.60802551 71.34867371 -64.21743958 70.56363464 -63.8150177 69.75480652 C-61.20940495 63.65595469 -60.54579085 57.73394856 -59.99861145 51.19621277 C-58.74060685 36.26412527 -54.19292424 23.17165318 -42.9322052 12.68840027 C-29.95390178 2.25331994 -16.15744064 -0.02867834 0 0 Z " fill="#fff" transform="translate(132.9986114501953,10.866287231445313)" />
            </svg>
        </div>
        <div class="service-btn">
            <a href="<?php echo esc_url(get_theme_mod('services_btn_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('services_btn_text', 'Book An Appointment')); ?></a>
        </div>
        <div class="box"></div>
        <div class="box-2"></div>
    </div>
</section>
<!--End service-->

<!--start feature section-->
<section class="wpo-feature-section section-padding">
    <div class="container-fluid">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-8 col-12">
                <div class="wpo-section-title">
                    <span><?php echo esc_html(get_theme_mod('features_title', 'Why Choose Us?')); ?></span>
                    <h2 class="poort-text poort-in-right"><?php echo wp_kses_post(get_theme_mod('features_subtitle', "Srinagar's Most Trusted Appliance Repair Hub.<br>We repair all major brands including LG, Samsung, Whirlpool, Godrej, Panasonic, and more.")); ?></h2>
                </div>
            </div>
        </div>
        <div class="feature-max">
            <div class="feature-wrap owl-carousel">
                <?php 
                $feature_defaults = [
                    '337+ Justdial Reviews (4.9★)',
                    'magicpin Verified',
                    'IndiaMART Listed',
                    'Same-Day Doorstep Service',
                    '10+ Years of Experience',
                    'Warranty on Work'
                ];
                for ($i = 1; $i <= 6; $i++):
                    $f_text = get_theme_mod("feature_text_$i", $feature_defaults[$i - 1]);
                    $f_link = get_theme_mod("feature_link_$i", home_url('/book-appointment-and-service/'));
                    $f_img  = get_theme_mod("feature_image_$i") ?: esc_url(get_template_directory_uri()) . "/assets/images/feature/$i.svg";
                    if (! $f_text) {
                        continue;
                    }
                ?>
                    <div class="feature-items">
                        <div class="icon">
                            <img src="<?php echo esc_url($f_img); ?>" alt="icon">
                        </div>
                        <div class="text">
                            <h3><a href="<?php echo esc_url($f_link); ?>"><?php echo esc_html($f_text); ?></a></h3>
                        </div>
                        <div class="arrow-icon">
                            <a href="<?php echo esc_url($f_link); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-top.png" alt="icon" class="icon-active">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-top-hover.png" alt="icon" class="icon-hover"></a>
                        </div>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </div>
</section>
<!--end feature section-->

<!--start video-section-->
<section class="wpo-video-section moving-cursor-wrap">
    <div class="video-image">
        <?php $vid_img = get_theme_mod('video_image'); ?>
        <img src="<?php echo $vid_img ? esc_url($vid_img) : esc_url(get_template_directory_uri()) . '/assets/images/video.jpg'; ?>" alt="video poster">
        <div class="booking-btn">
            <a class="btn-wrapper moving-cursor video-btn"
                href="<?php echo esc_url(get_theme_mod('video_url', 'https://www.youtube.com/embed/mh7w_zY2PZ4?si=7qlsk0vtkrtICSct')); ?>" data-type="iframe">
                <small>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 19 22" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M0.761719 0.591385L9.92688 5.89296L19.008 11.28L9.92688 16.5816L0.761719 21.9687L0.761719 11.28L0.761719 0.591385Z"
                            fill="white" />
                    </svg>
                    play
                </small>
            </a>
        </div>
    </div>
    <div class="video-text">
        <h2><?php echo esc_html(get_theme_mod('video_title', 'watch video')); ?></h2>
    </div>
</section>
<!--end video-section -->

<!--start odometer section-->
<section class="wpo-odometer-section">
    <div class="container">
        <div class="odometer-title wow fadeInUp" data-wow-duration="1200ms">
            <h2><?php echo esc_html(get_theme_mod('stats_title', 'Trusted by thousands across Srinagar for all major appliance brands')); ?></h2>
        </div>
        <div class="odometer-content">
            <?php 
            $stat_defaults = [
                ['num' => '10', 'suf' => '+', 'lbl' => 'Years of Experience'],
                ['num' => '337', 'suf' => '+', 'lbl' => '5-Star Reviews'],
                ['num' => '4', 'suf' => '.9', 'lbl' => 'Average Rating'],
                ['num' => '1000', 'suf' => '+', 'lbl' => 'Appliances Fixed']
            ];
            for ($i = 1; $i <= 4; $i++):
                $default = $stat_defaults[$i - 1];
                $s_num = get_theme_mod("stat_number_$i", $default['num']);
                if (! $s_num) {
                    continue;
                }
            ?>
                <div class="odometer-items wow fadeInUp" data-wow-delay="0.<?php echo $i - 1; ?>s">
                    <h2><span class="odometer" data-count="<?php echo esc_attr($s_num); ?>"><?php echo esc_html(str_repeat('0', strlen($s_num))); ?></span><span class="small"><?php echo esc_html(get_theme_mod("stat_suffix_$i", $default['suf'])); ?></span></h2>
                    <h3><?php echo esc_html(get_theme_mod("stat_label_$i", $default['lbl'])); ?></h3>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<!--end odometer section-->

<!--start project section-->
<section class="wpo-project-section">
    <div class="project-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-12">
                    <div class="wpo-section-title-s2">
                        <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Work gallery</span>
                        <h2 class="poort-text poort-in-right">We've completed over 1,000+ appliance repairs across Srinagar</h2>
                        <p>Specialized doorstep repair services for all major refrigerator, washing machine, and AC models.</p>
                    </div>
                </div>
                <div class="col-lg-5 col-12">
                    <div class="title-btn-right wow fadeInRightSlow" data-wow-duration="1000ms">
                        <a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2">Book An Appointment</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="container-fluid">
            <div class="project-slider owl-carousel">
                <?php
                $work_query = new WP_Query(['post_type' => 'work_gallery', 'posts_per_page' => -1]);
                $work_idx = 0;
                if ($work_query->have_posts()): while ($work_query->have_posts()): $work_query->the_post();
                    $work_idx++;
                    $media_type = get_post_meta(get_the_ID(), '_gallery_media_type', true);
                    $video_url  = get_post_meta(get_the_ID(), '_gallery_video_url', true);
                    $is_video   = ($media_type === 'video' && !empty($video_url));
                    $full_work_img = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : (get_template_directory_uri() . '/assets/images/project/' . (($work_idx % 4) + 1) . '.jpg');
                    $target_url = $is_video ? $video_url : $full_work_img;
                    $btn_class  = $is_video ? 'video-btn' : 'fancybox';
                    $group_attr = $is_video ? '' : 'data-fancybox-group="about-gallery"';
                ?>
                    <div class="project-card">
                        <div class="image">
                            <a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?> d-block" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>">
                                <?php if ($is_video) : ?>
                                    <span class="gallery-video-badge"><i class="ti-video-clapper"></i> Video</span>
                                    <div class="gallery-play-btn"><i class="ti-control-play"></i></div>
                                <?php endif; ?>
                                <?php if (has_post_thumbnail()) {
                                    the_post_thumbnail('full');
                                } else { ?>
                                    <img src="<?php echo esc_url($full_work_img); ?>" alt="<?php the_title_attribute(); ?>">
                                <?php } ?>
                            </a>
                            <div class="content">
                                <h2><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                                <div class="icon"><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-top-hover.png" alt="icon"></a></div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; wp_reset_postdata(); endif; ?>
            </div>
        </div>
    </div>
</section>
<!--end project section-->

<!--start history-section-->
<section class="wpo-history-section section-padding">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 col-12">
                <div class="wpo-section-title">
                    <span>How We Work</span>
                    <h2 class="poort-text poort-in-right">Simple, Transparent 3-Step Doorstep Repair Process</h2>
                </div>
            </div>
        </div>
        <div class="row">
            <?php
            $process_defaults = ['Book a Service', 'Get a Free Estimate', 'Professional Work Execution'];
            $durations = ['1000ms', '1200ms', '1400ms'];
            for ($i = 1; $i <= 3; $i++):
                $step = get_theme_mod("process_step_$i", $process_defaults[$i - 1]);
            ?>
                <div class="col col-lg-4 col-md-6 col-sm-6 col-12">
                    <div class="history-item wow fadeInUp" data-wow-duration="<?php echo $durations[$i - 1]; ?>">
                        <h2>0<?php echo $i; ?></h2>
                        <div class="text">
                            <h3><?php echo esc_html($step); ?></h3>
                        </div>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</section>
<!--end history-section-->

<!-- Start wpo-faq-section -->
<section class="wpo-faq-section">
    <div class="container">
        <div class="wpo-faq-wrap">
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-box wow fadeInLeftSlow" data-wow-duration="1000ms">
                        <div class="wpo-section-title-s2">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>faq</span>
                            <h2 class="poort-text poort-in-right">Frequently Asked Questions</h2>
                            <p>Got questions about pricing, warranty, or same-day repair visits? We've got answers.</p>
                        </div>
                        <a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2">Book An Appointment</a>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-items wow fadeInRightSlow" data-wow-duration="1000ms">
                        <div class="accordion" id="accordionExample">
                            <?php
                            $faq_query = new WP_Query(['post_type' => 'faq', 'posts_per_page' => -1]);
                            $faq_count = 0;
                            if ($faq_query->have_posts()): while ($faq_query->have_posts()): $faq_query->the_post();
                                $faq_count++;
                            ?>
                                <div class="accordion-item">
                                    <h3 class="accordion-header" id="heading<?php echo $faq_count; ?>">
                                        <button class="accordion-button <?php echo $faq_count > 1 ? 'collapsed' : ''; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq_count; ?>" aria-expanded="<?php echo $faq_count === 1 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $faq_count; ?>">
                                            <?php the_title(); ?>
                                        </button>
                                    </h3>
                                    <div id="collapse<?php echo $faq_count; ?>" class="accordion-collapse collapse <?php echo $faq_count === 1 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $faq_count; ?>" data-bs-parent="#accordionExample">
                                        <div class="accordion-body">
                                            <?php the_content(); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; wp_reset_postdata(); endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="shape-1"></div>
    <div class="shape-2"></div>
    <div class="shape-3"></div>
</section>
<!-- end wpo-faq-section -->

<!-- Start wpo-brand-partner-section -->
<section class="wpo-brand-partner-section section-padding">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 col-12">
                <div class="wpo-faq-box">
                    <div class="wpo-section-title-s2">
                        <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Brands We Service</span>
                        <h2 class="poort-text poort-in-right">All Major Appliance Brands</h2>
                        <p>Authorized expertise in servicing LG, Samsung, Whirlpool, Godrej, Panasonic, Daikin, IFB, and Bosch appliances with 100% genuine parts.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="client-wrap">
            <div class="client-logo">
                <ul>
                    <?php
                    $brands_query = new WP_Query(['post_type' => 'partner_brand', 'posts_per_page' => -1]);
                    $brand_idx = 0;
                    if ($brands_query->have_posts()): while ($brands_query->have_posts()): $brands_query->the_post();
                        $brand_idx++;
                        $fallback_brand_img = get_template_directory_uri() . '/assets/images/brand-logo/' . (($brand_idx % 12) + 1) . '.png';
                    ?>
                        <li>
                            <?php if (has_post_thumbnail()) {
                                the_post_thumbnail('full');
                            } else { ?>
                                <img src="<?php echo esc_url($fallback_brand_img); ?>" alt="<?php the_title_attribute(); ?>">
                            <?php } ?>
                        </li>
                    <?php endwhile; wp_reset_postdata(); endif; ?>
                </ul>
            </div>
        </div>
    </div>
</section>
<!-- end wpo-brand-partner-section -->

<!-- start text-marquee -->
<section class="text-marquee moving-cursor-wrap">
    <div class="wraper">
        <div class="marquee_container">
            <div>
                <h2 class="marquee-s2">
                    <small><?php echo esc_html(get_theme_mod('bottom_marquee_text', 'Schedule a Free Appliance Evaluation. Plan Your Maintenance Consultation')); ?></small>
                </h2>
            </div>
        </div>
        <div class="booking-btn moving-cursor">
            <a class="btn-wrapper btn-move" href="<?php echo esc_url(get_theme_mod('bottom_marquee_btn_link', home_url('/book-appointment-and-service/'))); ?>">
                <small><?php echo esc_html(get_theme_mod('bottom_marquee_btn_text', 'Book Now')); ?></small>
            </a>
        </div>
    </div>
</section>
<!-- end text-marquee -->

<?php get_footer(); ?>
