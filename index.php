<?php
/* Template Name: Home Page */
get_header(); 
$hero_bg = get_theme_mod('hero_bg_image');
$hero_style = $hero_bg ? "style=\"background-image: url('" . esc_url($hero_bg) . "');\"" : "";
?>
<!-- start hero section -->
        <section class="wpo-hero-section" <?php echo $hero_style; ?>>
            <div class="container-fluid">
                <div class="hero-wapper">
                    <div class="hero-side-left">
                        <div class="hero-side-left-items">
                            <ul>
                                <li><a href="<?php echo esc_url(get_theme_mod('hero_phone_link', 'tel:+919622917697')); ?>"><i class="flaticon-phone"></i><?php echo esc_html(get_theme_mod('hero_phone', '+91 9622917697')); ?></a></li>
                                <li><a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>"><i class="flaticon-phone"></i>WhatsApp Us</a>
                                </li>
                                <li><a href="<?php echo esc_url(get_theme_mod('hero_bookonline_link', home_url('/book-appointment-and-service/'))); ?>"><i class="flaticon-bag"></i>Book online</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="hero-content">
                        <div class="hero-title wow fadeInUp" data-wow-delay="0.0s">
                            <span><?php echo esc_html(get_theme_mod('hero_headline', 'Srinagar\'s Most Trusted Multi-Brand Appliance Repair Hub.')); ?></span>
                        </div>
                        <div class="hero-sub-title wow fadeInUp" data-wow-delay="0.3s">
                            <h2><?php echo wp_kses_post(get_theme_mod("hero_subheadline", "5-Star Rated, <br><span>Same-Day</span> Doorstep Service, <br>All Brands & <span>Appliances</span>")); ?></h2>
                        </div>
                        <div class="hero-btns wow fadeInUp" data-wow-delay="0.6s">
                            <a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('hero_btn1_text', 'Book An Appointment')); ?></a>
                            <a href="<?php echo esc_url(get_theme_mod('hero_btn2_link', 'tel:+919622917697')); ?>" class="theme-btn-s3"><span class="rolling-text"><?php echo esc_html(get_theme_mod('hero_btn2_text', 'Call 9622917697')); ?></span></a>
                        </div>

                        <!-- Mobile Hero Showcase Illustration (Integrated directly below buttons on mobile) -->
                        <div class="hero-mobile-illustration d-block d-lg-none wow fadeInUp" data-wow-delay="0.4s">
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/hero/hero-mobile-appliances.png" alt="Abid Electronics Multi-Brand Appliance Repair Hub" class="img-fluid">
                        </div>

                        <div class="hero-content-shape wow fadeInLeftSlow" data-wow-duration="1200ms">
                            <svg xmlns="http://www.w3.org/2000/svg" width="874" height="496" viewBox="0 0 874 496"
                                fill="none">
                                <foreignObject x="-40" y="-40" width="954" height="576">
                                    <div xmlns="http://www.w3.org/1999/xhtml"
                                        style="backdrop-filter:blur(20px);clip-path:url(#bgblur_0_4454_3012_clip_path);height:100%;width:100%">
                                    </div>
                                </foreignObject>
                                <g data-figma-bg-blur-radius="40">
                                    <mask id="path-1-inside-1_4454_3012" fill="white">
                                        <path
                                            d="M663 155C663 173.225 677.775 188 696 188H841C859.225 188 874 202.775 874 221V463C874 481.225 859.225 496 841 496L33 496C14.7746 496 0 481.225 0 463L0 33C0 14.7746 14.7746 0 33 0L630 0C648.225 0 663 14.7746 663 33V155Z" />
                                    </mask>
                                    <path
                                        d="M663 155C663 173.225 677.775 188 696 188H841C859.225 188 874 202.775 874 221V463C874 481.225 859.225 496 841 496L33 496C14.7746 496 0 481.225 0 463L0 33C0 14.7746 14.7746 0 33 0L630 0C648.225 0 663 14.7746 663 33V155Z"
                                        fill="white" />
                                    <path
                                        d="M696 188V189H841V188V187H696V188ZM874 221H873V463H874H875V221H874ZM841 496V495L33 495V496V497L841 497V496ZM0 463H1L1 33H0H-1L-1 463H0ZM33 0V1L630 1V0V-1L33 -1V0ZM663 33H662V155H663H664V33H663ZM630 0V1C647.673 1 662 15.3269 662 33H663H664C664 14.2223 648.778 -1 630 -1V0ZM0 33H1C1 15.3269 15.3269 1 33 1V0V-1C14.2223 -1 -1 14.2223 -1 33H0ZM33 496V495C15.3269 495 1 480.673 1 463H0H-1C-1 481.778 14.2223 497 33 497V496ZM874 463H873C873 480.673 858.673 495 841 495V496V497C859.778 497 875 481.778 875 463H874ZM841 188V189C858.673 189 873 203.327 873 221H874H875C875 202.222 859.778 187 841 187V188ZM696 188V187C678.327 187 664 172.673 664 155H663H662C662 173.778 677.222 189 696 189V188Z"
                                        fill="black" fill-opacity="0.09" mask="url(#path-1-inside-1_4454_3012)" />
                                </g>
                                <defs>
                                    <clipPath id="bgblur_0_4454_3012_clip_path" transform="translate(40 40)">
                                        <path
                                            d="M663 155C663 173.225 677.775 188 696 188H841C859.225 188 874 202.775 874 221V463C874 481.225 859.225 496 841 496L33 496C14.7746 496 0 481.225 0 463L0 33C0 14.7746 14.7746 0 33 0L630 0C648.225 0 663 14.7746 663 33V155Z" />
                                    </clipPath>
                                </defs>
                            </svg>
                        </div>
                    </div>
                    <div class="shape-2">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/hero/shape-2.png" alt="shape">
                    </div>
                </div>
            </div>
        </section>
        <!-- end hero section -->

        <!-- start about section -->
        <section class="wpo-about-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 col-md-12 col-12">
                        <div class="about-title-left wow fadeInLeftSlow" data-wow-duration="1200ms">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('about_left_title', 'about us')); ?></span>
                            <!-- <span>about us</span> -->
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-12 col-12">
                        <div class="about-title-right">
                            <h2 class="poort-text poort-in-right"><?php echo wp_kses_post(get_theme_mod('about_right_title', 'At <span>Abid Electronics,</span> we are Srinagar\'s <span>top-rated appliance repair</span> experts. With over a decade of hands- <span>on experience</span>,')); ?></h2>
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
                                <ul><?php $c1_lines = explode("\n", get_theme_mod("about_card1_list", "Same-Day Doorstep Service\nAll Major Brands Serviced\nGenuine Spare Parts\n5-Star Rated Service"));foreach ($c1_lines as $c1) {if (trim($c1)) {
                                        echo "<li><span>" . esc_html($c1) . "</span></li>";
                                    }}?></ul>
                            </div>
                        </div>
                        <div class="about-items">
                            <div class="icon">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/icon-2.svg" alt="icon">
                            </div>
                            <div class="about-text">
                                <h3><?php echo esc_html(get_theme_mod('about_card2_title', 'Our Mission')); ?></h3>
                                <ul><?php $c2_lines = explode("\n", get_theme_mod("about_card2_list", "Certified Technicians\nAffordable & Transparent Pricing\nOver 1000+ Appliances Repaired\nSatisfaction Guarantee"));foreach ($c2_lines as $c2) {if (trim($c2)) {
                                        echo "<li" . (trim($c2) == "Affordable & Transparent Pricing" ? " class='active'" : "") . "><span>" . esc_html($c2) . "</span></li>";
                                    }}?></ul>
                            </div>
                        </div>
                        <div class="about-items">
                            <div class="items-image">
                                <?php $about_img = get_theme_mod("about_card3_image");
                                    if ($about_img) {
                                    echo wp_get_attachment_image($about_img, "full");
                                } else {?><img
                                        src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/1.jpg" alt="image"><?php }?>
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
                                  <img src="<?php echo esc_url($icon_url); ?>" alt="image">
                                  <h3><?php the_title(); ?></h3>
                              </div>
                          </div>
                          <div class="service-expanded">
                              <?php if (has_post_thumbnail()) {
                                              the_post_thumbnail('full', ['class' => 'service-image']);
                                      } else {?>
                                  <img class="service-image" src="<?php echo esc_url($fallback_img_url); ?>" alt="<?php the_title_attribute(); ?>">
                              <?php }?>
                              <div class="service-content">
                                  <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                  <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>

                                  <a class="arrow" href="<?php the_permalink(); ?>"><i class="ti-arrow-top-right"></i></a>
                              </div>
                          </div>
                      </div>
<?php endwhile;
    wp_reset_postdata();endif; ?>
                  </div>
                  <div class="shape">
                      <svg version="1.1" xmlns="http://www.w3.org/2000/svg">
                          <path
                              d="M0 0 C0.95492315 -0.00226556 1.90984629 -0.00453111 2.8937065 -0.00686532 C6.06608773 -0.01315201 9.23842565 -0.01237941 12.41081238 -0.0115509 C14.69658462 -0.0145457 16.98235636 -0.01794897 19.26812744 -0.02172852 C25.45829608 -0.03053948 31.64845089 -0.03290539 37.83862519 -0.0335443 C41.71060151 -0.03425638 45.58257434 -0.03639369 49.45454979 -0.03904152 C62.27859919 -0.0478085 75.10263746 -0.05246243 87.92668942 -0.05171105 C88.61657695 -0.05167109 89.30646448 -0.05163113 90.01725769 -0.05158997 C91.0533659 -0.05152864 91.0533659 -0.05152864 92.11040559 -0.05146609 C103.30102578 -0.05105976 114.4916123 -0.06062867 125.68222276 -0.07472833 C137.19856219 -0.08912286 148.71488088 -0.09581408 160.23122996 -0.09513456 C166.68640349 -0.09488015 173.14153888 -0.09769977 179.59670448 -0.10831261 C185.66982179 -0.11802541 191.74286473 -0.11805097 197.81598473 -0.11079597 C200.03747542 -0.10975413 202.25897083 -0.1120891 204.48045349 -0.11815643 C225.32569193 -0.17137614 242.7304994 0.45405382 259.00138855 15.13371277 C268.36797554 25.735957 272.67994512 36.90841451 273.37638855 50.88371277 C274.30775193 65.5329537 279.77131193 77.98893253 290.4662323 88.09465027 C303.07175297 98.23345118 318.77397042 99.5431656 334.37638855 99.82121277 C335.70975249 99.85451438 337.04308703 99.88901496 338.37638855 99.92472839 C341.58463668 100.00609701 344.79264961 100.07559973 348.00138855 100.13371277 C348.00138855 103.43371277 348.00138855 106.73371277 348.00138855 110.13371277 C189.27138855 110.13371277 30.54138855 110.13371277 -132.99861145 110.13371277 C-132.99861145 106.83371277 -132.99861145 103.53371277 -132.99861145 100.13371277 C-131.83627991 100.11183899 -131.83627991 100.11183899 -130.65046692 100.08952332 C-99.35072643 99.80488703 -99.35072643 99.80488703 -72.99861145 84.32511902 C-69.60803976 80.61005902 -67.21163838 76.63421096 -64.99861145 72.13371277 C-64.60802551 71.34867371 -64.21743958 70.56363464 -63.8150177 69.75480652 C-61.20940495 63.65595469 -60.54579085 57.73394856 -59.99861145 51.19621277 C-58.74060685 36.26412527 -54.19292424 23.17165318 -42.9322052 12.68840027 C-29.95390178 2.25331994 -16.15744064 -0.02867834 0 0 Z "
                              fill="#fff" transform="translate(132.9986114501953,10.866287231445313)" />
                      </svg>
                  </div>
                  <div class="service-btn">
                      <a href="<?php echo esc_url(get_theme_mod('services_btn_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('services_btn_text', 'Book An Appointment')); ?></a>
                  </div>
                  <div class="box"></div>
                  <div class="box-2"></div>
              </div>
          </section>
        <!--End  service-->

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
                <img src="<?php echo $vid_img ? esc_url($vid_img) : esc_url(get_template_directory_uri()) . '/assets/images/video.jpg'; ?>" alt="image">
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
                                <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('gallery_section_label', 'Work gallery')); ?></span>
                                <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('gallery_section_title', "we've powered over 200+ successful projects.")); ?></h2>
                                <p><?php echo esc_html(get_theme_mod('gallery_section_desc', 'From refrigerator repairs to complete AC servicing, see our quality work across Srinagar.')); ?></p>
                            </div>
                        </div>
                        <div class="col-lg-5 col-12">
                            <div class="title-btn-right wow fadeInRightSlow" data-wow-duration="1000ms">
                                <a href="<?php echo esc_url(get_theme_mod('gallery_btn_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('gallery_btn_text', 'Book An Appointment')); ?></a>
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
        $group_attr = $is_video ? '' : 'data-fancybox-group="home-gallery"';
?>
                          <div class="project-card">
                              <div class="image">
                                  <a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?> d-block" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>">
                                      <?php if ($is_video) : ?>
                                          <span class="gallery-video-badge"><i class="ti-video-clapper"></i> Video</span>
                                          <div class="gallery-play-btn"><i class="ti-control-play"></i></div>
                                      <?php endif; ?>
                                      <?php if (has_post_thumbnail()) { the_post_thumbnail('full'); } else { ?>
                                      <img src="<?php echo esc_url($full_work_img); ?>" alt="<?php the_title_attribute(); ?>">
                                      <?php } ?>
                                  </a>
                                  <div class="content">
                                      <h2><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h2>
                                      <div class="icon"><a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>"><img
                                                  src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-top-hover.png" alt="icon"></a>
                                      </div>
                                  </div>
                              </div>
                          </div>
<?php endwhile;
    wp_reset_postdata();endif; ?>
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
                            <span><?php echo esc_html(get_theme_mod('process_label', 'How It Works')); ?></span>
                            <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('process_title', 'Our Simple 3-Step Repair Process')); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="row">
<?php
    $process_defaults = ['Book a Service', 'Get a Free Estimate', 'Professional Work Execution'];
    $durations        = ['1000ms', '1200ms', '1400ms'];
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
                                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('faq_label', 'faq')); ?></span>
                                    <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('faq_title', 'Frequently Asked Questions')); ?></h2>
                                    <p><?php echo esc_html(get_theme_mod('faq_desc', 'Got questions about our appliance repair services? Find answers to the most common queries below.')); ?></p>
                                </div>
                                <a href="<?php echo esc_url(get_theme_mod('faq_btn_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('faq_btn_text', 'Book An Appointment')); ?></a>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="wpo-faq-items wow fadeInRightSlow" data-wow-duration="1000ms">
                                <div class="accordion" id="accordionExample">
<?php
    $faq_query                                = new WP_Query(['post_type' => 'faq', 'posts_per_page' => -1]);
    if ($faq_query->have_posts()): $faq_count = 0;while ($faq_query->have_posts()): $faq_query->the_post();
        $faq_count++;
?>
                                    <div class="accordion-item">
                                        <h3 class="accordion-header" id="heading<?php echo $faq_count; ?>">
                                            <button class="accordion-button <?php echo $faq_count == 1 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#collapse<?php echo $faq_count; ?>" aria-expanded="<?php echo $faq_count == 1 ? 'true' : 'false'; ?>"
                                                aria-controls="collapse<?php echo $faq_count; ?>">
                                                <?php the_title(); ?>
                                            </button>
                                        </h3>
                                        <div id="collapse<?php echo $faq_count; ?>" class="accordion-collapse collapse <?php echo $faq_count == 1 ? 'show' : ''; ?>"
                                            aria-labelledby="heading<?php echo $faq_count; ?>" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <p><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                                            </div>
                                        </div>
                                    </div>
<?php endwhile;
    wp_reset_postdata();endif; ?>
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

        <!-- Start wpo-brand-partne-section -->
        <section class="wpo-brand-partner-section section-padding">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-12">
                        <div class="wpo-section-title-s2">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('brands_label', 'Business partner')); ?></span>
                            <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('brands_title', 'our brand partners')); ?></h2>
                            <p><?php echo esc_html(get_theme_mod('brands_desc', 'We are authorised to repair and service all major home appliance brands across Srinagar.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="client-wrap">
                    <div class="client-logo">
                        <ul>
<?php
    $brands_query = new WP_Query([
        'post_type'      => 'partner_brand',
        'posts_per_page' => -1,
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
        'post_status'    => 'publish',
    ]);
    if ($brands_query->have_posts()): while ($brands_query->have_posts()): $brands_query->the_post();
?>
                            <li><?php if (has_post_thumbnail()) {the_post_thumbnail('full', ['alt' => get_the_title()]); } else { ?><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/brand-logo/1.png" alt="<?php the_title_attribute(); ?>"><?php } ?></li>
<?php endwhile;
    wp_reset_postdata();endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
        <!-- end wpo-faq-section -->

        <!-- start text-marquee -->
        <section class="text-marquee moving-cursor-wrap">
            <div class="wraper">
                <div class="marquee_container">
                    <div>
                        <h2 class="marquee-s2">
                            <small><?php echo esc_html(get_theme_mod('bottom_marquee_text', 'Schedule a Free Repair Evaluation. Plan Your Same-Day Doorstep Service')); ?></small>
                        </h2>
                    </div>
                </div>
                <div class="booking-btn moving-cursor"><a class="btn-wrapper btn-move" href="<?php echo esc_url(get_theme_mod('bottom_marquee_btn_link', home_url('/book-appointment-and-service/'))); ?>"><small><?php echo esc_html(get_theme_mod('bottom_marquee_btn_text', 'Book Now')); ?></small></a></div>
            </div>
        </section>
        <!-- end text-marquee -->

        <!-- start testimonial-sectoin -->
        <Section class="wpo-testimonial-section">
            <div class="container">
                <div class="testimonial-wrap testimonial-slider">
                    <div class="image slider-for">
<?php
    $testi_query = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
    if ($testi_query->have_posts()): while ($testi_query->have_posts()): $testi_query->the_post();
?>
                        <div class="item">
                            <span class="feedback"><i class="flaticon-double-quotes"></i></span>
                            <?php if (has_post_thumbnail()) {the_post_thumbnail('full');}?>
                            <ul>
                                <li><i class="flaticon-star"></i></li>
                                <li><i class="flaticon-star"></i></li>
                                <li><i class="flaticon-star"></i></li>
                                <li><i class="flaticon-star"></i></li>
                                <li><i class="flaticon-star"></i></li>
                            </ul>
                        </div>
<?php endwhile;
    wp_reset_postdata();endif; ?>
                    </div>
                    <div class="content-wrap wow fadeInRightSlow" data-wow-duration="1000ms">
                        <div class="slider-nav">
<?php
    $testi_query2 = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
    if ($testi_query2->have_posts()): while ($testi_query2->have_posts()): $testi_query2->the_post();
?>
                            <div class="content">
                                <p><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                                <div class="client-name">
                                    <h4><?php the_title(); ?>/</h4>
                                    <span><?php echo esc_html(get_post_meta(get_the_ID(), '_testimonial_designation', true)); ?></span>
                                </div>
                            </div>
<?php endwhile;
    wp_reset_postdata();endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </Section>
        <!--end testimonial-section-->

        <!--start cta-section-->
        <?php
            $cta_bg    = get_theme_mod("cta_bg_image");
            $cta_style = $cta_bg ? "style=\"background: url('" . esc_url($cta_bg) . "') no-repeat right; background-size: cover;\"" : "";
        ?>
        <section class="wpo-cta-section moving-cursor-wrap" <?php echo $cta_style; ?>>
            <div class="container-fluid">
                <div class="cta-content">
                    <div class="cta-title wow fadeInUp" data-wow-delay="0.0s">
                        <span><?php echo esc_html(get_theme_mod("cta_title", "Trusted, Fast & Affordable Service at Your Doorstep.")); ?></span>
                    </div>
                    <div class="cta-sub-title wow fadeInUp" data-wow-delay="0.2s">
                        <h2><?php echo wp_kses_post(get_theme_mod("cta_subtitle", "Powering Your World Safely Efficiently & Reliably")); ?></h2>
                    </div>
                    <div class="cta-btns wow fadeInUp" data-wow-delay="0.4s">
                        <a href="<?php echo esc_url(get_theme_mod('cta_btn1_link', home_url('/book-appointment-and-service/'))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('cta_btn1_text', 'Book An Appointment')); ?></a>
                        <a href="<?php echo esc_url(get_theme_mod('cta_btn2_link', home_url('/about/'))); ?>" class="theme-btn-s3"><span class="rolling-text"><?php echo esc_html(get_theme_mod('cta_btn2_text', 'Learn More')); ?></span></a>
                    </div>
                    <div class="cta-content-shape wow fadeInLeftSlow" data-wow-duration="1200ms">
                        <svg xmlns="http://www.w3.org/2000/svg" width="755" height="450" viewBox="0 0 755 450"
                            fill="none">
                            <foreignObject x="-40" y="-40" width="835" height="530">
                                <div xmlns="http://www.w3.org/1999/xhtml"
                                    style="backdrop-filter:blur(20px);clip-path:url(#bgblur_0_4454_2791_clip_path);height:100%;width:100%">
                                </div>
                            </foreignObject>
                            <g data-figma-bg-blur-radius="40">
                                <mask id="path-1-inside-1_4454_2791" fill="white">
                                    <path
                                        d="M584 139C584 157.225 598.775 172 617 172L722 172C740.225 172 755 186.775 755 205V417C755 435.225 740.225 450 722 450L33 450C14.7746 450 0 435.225 0 417L0 33C0 14.7746 14.7746 0 33 0L551 0C569.225 0 584 14.7746 584 33V139Z" />
                                </mask>
                                <path
                                    d="M584 139C584 157.225 598.775 172 617 172L722 172C740.225 172 755 186.775 755 205V417C755 435.225 740.225 450 722 450L33 450C14.7746 450 0 435.225 0 417L0 33C0 14.7746 14.7746 0 33 0L551 0C569.225 0 584 14.7746 584 33V139Z"
                                    fill="white" />
                                <path
                                    d="M617 172V173L722 173V172V171L617 171V172ZM755 205H754V417H755H756V205H755ZM722 450V449L33 449V450V451L722 451V450ZM0 417H1L1 33H0H-1L-1 417H0ZM33 0V1L551 1V0V-1L33 -1V0ZM584 33H583V139H584H585V33H584ZM551 0V1C568.673 1 583 15.3269 583 33H584H585C585 14.2223 569.778 -1 551 -1V0ZM0 33H1C1 15.3269 15.3269 1 33 1V0V-1C14.2223 -1 -1 14.2223 -1 33H0ZM33 450V449C15.3269 449 1 434.673 1 417H0H-1C-1 435.778 14.2223 451 33 451V450ZM755 417H754C754 434.673 739.673 449 722 449V450V451C740.778 451 756 435.778 756 417H755ZM722 172V173C739.673 173 754 187.327 754 205H755H756C756 186.222 740.778 171 722 171V172ZM617 172V171C599.327 171 585 156.673 585 139H584H583C583 157.778 598.222 173 617 173V172Z"
                                    fill="black" fill-opacity="0.09" mask="url(#path-1-inside-1_4454_2791)" />
                            </g>
                            <defs>
                                <clipPath id="bgblur_0_4454_2791_clip_path" transform="translate(40 40)">
                                    <path
                                        d="M584 139C584 157.225 598.775 172 617 172L722 172C740.225 172 755 186.775 755 205V417C755 435.225 740.225 450 722 450L33 450C14.7746 450 0 435.225 0 417L0 33C0 14.7746 14.7746 0 33 0L551 0C569.225 0 584 14.7746 584 33V139Z" />
                                </clipPath>
                            </defs>
                        </svg>
                    </div>
                </div>
                <div class="booking-btn"><a class="btn-wrapper moving-cursor" href="<?php echo esc_url(get_theme_mod('cta_getintouch_link', home_url('/book-appointment-and-service/'))); ?>"><small><i><img
                                    src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/arrow-up.svg" alt="icon"></i>get in touch</small></a></div>
            </div>
        </section>
        <!--end cta-section -->

        <!-- start blog-section -->
        <section class="wpo-blog-section-s2 section-padding">
            <div class="container">
                <div class="row align-items-center mb-4">
                    <div class="col-lg-7 col-12">
                        <div class="wpo-section-title-s2">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('blog_label', 'Updated news & blogs')); ?></span>
                            <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('blog_title', 'Stay Updated with Our Latest Insights')); ?></h2>
                            <p><?php echo esc_html(get_theme_mod('blog_desc', 'Tips, guides, and news on appliance repair and maintenance for Srinagar homeowners.')); ?></p>
                        </div>
                    </div>
                    <div class="col-lg-5 col-12">
                        <div class="title-btn-right wow fadeInRightSlow" data-wow-duration="1000ms">
                            <a href="<?php echo esc_url(get_theme_mod('blog_btn_link', get_permalink(get_option('page_for_posts')))); ?>" class="theme-btn-s2"><?php echo esc_html(get_theme_mod('blog_btn_text', 'View All Posts')); ?></a>
                        </div>
                    </div>
                </div>
                <div class="row">
<?php
    $blog_query = new WP_Query(['post_type' => 'post', 'posts_per_page' => 3, 'post_status' => 'publish']);
    $blog_durations = ['1000ms', '1200ms', '1400ms'];
    $blog_fallback_imgs = [3, 8, 7];
    $blog_idx = 0;

    if ($blog_query->have_posts()): 
        while ($blog_query->have_posts()): $blog_query->the_post();
            $duration = $blog_durations[$blog_idx] ?? '1000ms';
            $fallback_img_num = $blog_fallback_imgs[$blog_idx] ?? ($blog_idx + 1);
            $categories = get_the_category();
            $cat_name = !empty($categories) ? $categories[0]->name : 'Appliance Repair';
?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="blog-card wow fadeInUp" data-wow-duration="<?php echo esc_attr($duration); ?>">
                            <div class="image">
                                <?php if (has_post_thumbnail()) {
                                    the_post_thumbnail('medium_large');
                                } else { ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/blog/img-<?php echo esc_attr($fallback_img_num); ?>.jpg" alt="<?php the_title_attribute(); ?>">
                                <?php } ?>
                            </div>
                            <div class="content">
                                <div class="date">
                                    <h3><?php echo get_the_date('d'); ?></h3>
                                    <span><?php echo get_the_date('M'); ?></span>
                                </div>
                                <div class="text">
                                    <span><?php echo esc_html($cat_name); ?></span>
                                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                </div>
                                <ul class="comment">
                                    <li><i class="flaticon-user"></i><span><?php the_author(); ?></span></li>
                                    <li><i class="fi ti-comment-alt"></i> <span><a href="<?php the_permalink(); ?>#comments">Comments(<?php echo get_comments_number(); ?>)</a></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
<?php 
            $blog_idx++;
        endwhile;
        wp_reset_postdata();
    else:
        // Static fallback cards matching template
        $fallbacks = [
            [
                'day' => '20', 'month' => 'Sep', 'cat' => 'Electrician',
                'title' => 'Smart Home Wiring Essential Insights for Today’s Electricians',
                'author' => 'Arlene McCoy', 'comments' => 5, 'img' => 3, 'dur' => '1000ms'
            ],
            [
                'day' => '28', 'month' => 'Sep', 'cat' => 'Electrician',
                'title' => 'Handling Electrical Emergencies What to Do When the Power Fails',
                'author' => 'Alen Folker', 'comments' => 5, 'img' => 8, 'dur' => '1200ms'
            ],
            [
                'day' => '30', 'month' => 'Sep', 'cat' => 'Electrician',
                'title' => 'Electrifying Innovation The Next Wave of Electrical Technology',
                'author' => 'Linda Johns', 'comments' => 5, 'img' => 7, 'dur' => '1400ms'
            ]
        ];
        foreach ($fallbacks as $fb):
?>
                    <div class="col-lg-4 col-md-6 col-12">
                        <div class="blog-card wow fadeInUp" data-wow-duration="<?php echo esc_attr($fb['dur']); ?>">
                            <div class="image">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/blog/img-<?php echo esc_attr($fb['img']); ?>.jpg" alt="image">
                            </div>
                            <div class="content">
                                <div class="date">
                                    <h3><?php echo esc_html($fb['day']); ?></h3>
                                    <span><?php echo esc_html($fb['month']); ?></span>
                                </div>
                                <div class="text">
                                    <span><?php echo esc_html($fb['cat']); ?></span>
                                    <h2><a href="<?php echo esc_url(home_url('/blog/')); ?>"><?php echo esc_html($fb['title']); ?></a></h2>
                                </div>
                                <ul class="comment">
                                    <li><i class="flaticon-user"></i><span><?php echo esc_html($fb['author']); ?></span></li>
                                    <li><i class="fi ti-comment-alt"></i> <span><a href="<?php echo esc_url(home_url('/blog/')); ?>">Comments(<?php echo esc_html($fb['comments']); ?>)</a></span></li>
                                </ul>
                            </div>
                        </div>
                    </div>
<?php 
        endforeach;
    endif; 
?>
                </div>
            </div>
        </section>
        <!-- end blog-section-->

        <?php get_footer(); ?>
