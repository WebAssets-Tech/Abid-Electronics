<?php
/**
 * The template for displaying single Service details (High Converting Landing Page)
 *
 * @package WebAssets
 */

get_header(); 
$current_service_id = get_the_ID();
$phone_num = get_theme_mod('contact_phone', '+91 9622917697');
$clean_phone = preg_replace('/[^0-9+]/', '', $phone_num);
$wa_link = get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697');
$book_link = get_theme_mod('hero_btn1_link', home_url('/book-appointment-and-service/'));
?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2><?php the_title(); ?></h2>
                    <h3>Doorstep Appliance Repair in Srinagar</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!--start of service-single-page -->
<section class="service-single-page section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-12 order-lg-2">
                <div class="service-single-wrap entry-content">
                    
                    <!-- Featured Image / Thumbnail -->
                    <div class="title-image mb-4">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('full', ['class' => 'img-fluid w-100 rounded-3 shadow-sm']); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-single/img-1.jpg" alt="<?php the_title_attribute(); ?>" class="img-fluid w-100 rounded-3 shadow-sm">
                        <?php endif; ?>
                    </div>

                    <!-- Value Proposition Badges -->
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="badge bg-success fs-6 px-3 py-2"><i class="ti-time me-1"></i> Same-Day Doorstep Visit (2-4 Hrs)</span>
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="ti-shield me-1"></i> 100% Genuine Parts &amp; Warranty</span>
                    </div>

                    <h2><?php the_title(); ?> in Srinagar</h2>

                    <!-- Immediate Conversion Action Bar -->
                    <div class="d-flex flex-wrap gap-3 my-4">
                        <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="btn d-inline-flex align-items-center justify-content-center gap-2 px-4 py-3 shadow-sm text-decoration-none" style="background-color: #0f172a; color: #ffffff !important; font-weight: 700; border-radius: 50px; font-size: 15px;">
                            <i class="ti-headphone-alt"></i> <span>Call: <?php echo esc_html($phone_num); ?></span>
                        </a>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn d-inline-flex align-items-center justify-content-center gap-2 px-4 py-3 shadow-sm text-decoration-none" style="background-color: #25D366; color: #ffffff !important; font-weight: 700; border-radius: 50px; font-size: 15px;">
                            <i class="ti-comments"></i> <span>WhatsApp Us</span>
                        </a>
                        <a href="<?php echo esc_url($book_link); ?>" class="btn d-inline-flex align-items-center justify-content-center gap-2 px-4 py-3 shadow-sm text-decoration-none" style="background-color: #3860D2; color: #ffffff !important; font-weight: 700; border-radius: 50px; font-size: 15px;">
                            <i class="ti-calendar"></i> <span>Book Appointment</span>
                        </a>
                    </div>

                    <!-- Service Description Body -->
                    <div class="service-details-body">
                        <?php
                        while (have_posts()) :
                            the_post();
                            $content = get_the_content();
                            if (!empty(trim($content))) {
                                the_content();
                            } else {
                                ?>
                                <p class="lead">At Abid Electronics, we provide certified, fast, and dependable doorstep <strong><?php the_title(); ?></strong> across Srinagar with experienced technicians and 100% genuine replacement parts.</p>
                                <p>Whether you're experiencing sudden breakdowns, electrical faults, heating or cooling failures, strange noises, or error codes, our specialist technician arrives directly at your home or business equipped with advanced diagnostic tools to resolve the issue on the spot. We proudly serve residential and commercial clients across Chattabal, Bemina, Tengpora, Batamaloo, Rajbagh, Lal Chowk, and all areas of Srinagar.</p>
                                
                                <h3>Why Choose Abid Electronics for <?php the_title(); ?>?</h3>
                                <ul>
                                    <li><strong>Same-Day Doorstep Service:</strong> Technician arrives within 2 to 4 hours of your booking anywhere in Srinagar.</li>
                                    <li><strong>Certified Technicians:</strong> Highly skilled professionals trained across all multi-brand appliance models.</li>
                                    <li><strong>100% Genuine Spare Parts:</strong> Brand-approved, authentic replacement parts for lasting reliability.</li>
                                    <li><strong>Transparent Pricing:</strong> Upfront estimate provided before any repair or part replacement begins.</li>
                                    <li><strong>Service Warranty:</strong> Complete peace of mind with our dedicated post-repair guarantee on parts and labor.</li>
                                </ul>

                                <h3>Multi-Brand Repair Mastery</h3>
                                <p>We service and repair all leading international and Indian brands including <strong>LG, Samsung, Whirlpool, Godrej, Haier, Panasonic, IFB, Bosch, Voltas, Carrier, Daikin, and Blue Star</strong>.</p>
                                <?php
                            }
                        endwhile;
                        ?>
                    </div>

                    <!-- Static Work Process from project-single.html -->
                    <div class="wpo-p-details-section mt-4">
                        <div class="process-wrap">
                            <h5>Our 3-Step Repair Process</h5>
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-handshake"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>1. Easy Booking</h3>
                                            <p>Call, WhatsApp, or book online in under 30 seconds for same-day service.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-medal"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>2. Doorstep Diagnosis</h3>
                                            <p>Our certified technician inspects the appliance and gives a clear upfront estimate.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-gift-box"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>3. Genuine Repair &amp; Warranty</h3>
                                            <p>Quick on-site fix with genuine replacement parts and warranty coverage.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic FAQ Accordion for Service Single -->
                    <div class="service-faq-section mt-5">
                        <h3 class="mb-4" style="font-size: 24px; font-weight: 700;">Frequently Asked Questions</h3>
                        <div class="accordion" id="serviceFaqAccordion">
                            <?php
                            $faq_query = new WP_Query([
                                'post_type'      => 'faq',
                                'posts_per_page' => 5,
                                'order'          => 'ASC'
                            ]);

                            $faq_idx = 0;
                            if ($faq_query->have_posts()) :
                                while ($faq_query->have_posts()) : $faq_query->the_post();
                                    $faq_idx++;
                                    $is_first = ($faq_idx === 1);
                                    $collapse_id = 'serviceFaq' . $faq_idx;
                                    $heading_id  = 'serviceFaqHead' . $faq_idx;
                                    ?>
                                    <div class="accordion-item mb-3">
                                        <h4 class="accordion-header" id="<?php echo esc_attr($heading_id); ?>" style="font-size: 16px; margin: 0;">
                                            <button class="accordion-button <?php echo $is_first ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                                                aria-controls="<?php echo esc_attr($collapse_id); ?>">
                                                <?php the_title(); ?>
                                            </button>
                                        </h4>
                                        <div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse <?php echo $is_first ? 'show' : ''; ?>"
                                            aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#serviceFaqAccordion">
                                            <div class="accordion-body">
                                                <p class="mb-0"><?php echo wp_strip_all_tags(get_the_content()); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                                ?>
                                <div class="accordion-item mb-3">
                                    <h4 class="accordion-header" id="serviceFaqHead1" style="font-size: 16px; margin: 0;">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#serviceFaq1" aria-expanded="true" aria-controls="serviceFaq1">
                                            How quickly can a technician visit my location in Srinagar?
                                        </button>
                                    </h4>
                                    <div id="serviceFaq1" class="accordion-collapse collapse show" aria-labelledby="serviceFaqHead1" data-bs-parent="#serviceFaqAccordion">
                                        <div class="accordion-body">
                                            <p class="mb-0">We offer same-day doorstep service across Srinagar, typically arriving within 2 to 4 hours of booking confirmation.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item mb-3">
                                    <h4 class="accordion-header" id="serviceFaqHead2" style="font-size: 16px; margin: 0;">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serviceFaq2" aria-expanded="false" aria-controls="serviceFaq2">
                                            Do you use genuine manufacturer replacement parts?
                                        </button>
                                    </h4>
                                    <div id="serviceFaq2" class="accordion-collapse collapse" aria-labelledby="serviceFaqHead2" data-bs-parent="#serviceFaqAccordion">
                                        <div class="accordion-body">
                                            <p class="mb-0">Yes, 100% genuine and brand-authorized spare parts are used for all repairs with warranty coverage.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-item mb-3">
                                    <h4 class="accordion-header" id="serviceFaqHead3" style="font-size: 16px; margin: 0;">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#serviceFaq3" aria-expanded="false" aria-controls="serviceFaq3">
                                            What is your service warranty policy?
                                        </button>
                                    </h4>
                                    <div id="serviceFaq3" class="accordion-collapse collapse" aria-labelledby="serviceFaqHead3" data-bs-parent="#serviceFaqAccordion">
                                        <div class="accordion-body">
                                            <p class="mb-0">We provide a comprehensive warranty on replaced spare parts and workmanship so you have total peace of mind.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- In-Content High Converting CTA Box -->
                    <div class="service-booking-cta p-4 p-md-5 my-5 rounded-4 bg-white border shadow-sm" style="border-left: 5px solid #3860D2 !important;">
                        <div class="row align-items-center g-3">
                            <div class="col-lg-7 col-12 text-start">
                                <h3 class="mb-2" style="font-size: 22px; font-weight: 800; color: #0f172a;">Need Fast <?php the_title(); ?>?</h3>
                                <p class="mb-0 text-muted" style="font-size: 15px;">Get your appliance inspected and repaired today by Srinagar's top-rated specialists.</p>
                            </div>
                            <div class="col-lg-5 col-12">
                                <div class="d-flex flex-wrap gap-3 justify-content-lg-end align-items-center">
                                    <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="btn d-inline-flex align-items-center justify-content-center gap-2 px-4 py-3 shadow-sm text-decoration-none" style="background-color: #0f172a; color: #ffffff !important; font-weight: 700; border-radius: 50px; font-size: 14px;">
                                        <i class="ti-headphone-alt"></i> <span>Call Now</span>
                                    </a>
                                    <a href="<?php echo esc_url($book_link); ?>" class="btn d-inline-flex align-items-center justify-content-center gap-2 px-4 py-3 shadow-sm text-decoration-none" style="background-color: #3860D2; color: #ffffff !important; font-weight: 700; border-radius: 50px; font-size: 14px;">
                                        <span>Book Online</span> <i class="ti-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Dynamic Services Sidebar (Hidden on Mobile) -->
            <div class="col-lg-4 col-12 order-lg-1 d-none d-lg-block">
                <div class="service-sidebar">
                    
                    <!-- All Services Navigation -->
                    <div class="service-catagory">
                        <ul>
                            <?php
                            $all_services = new WP_Query([
                                'post_type'      => 'services',
                                'posts_per_page' => -1,
                                'order'          => 'ASC'
                            ]);

                            if ($all_services->have_posts()) :
                                while ($all_services->have_posts()) : $all_services->the_post();
                                    $is_active = (get_the_ID() === $current_service_id);
                                    ?>
                                    <li>
                                        <a href="<?php the_permalink(); ?>" class="<?php echo $is_active ? 'active' : ''; ?>">
                                            <?php the_title(); ?>
                                        </a>
                                    </li>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                                ?>
                                <li><a href="#" class="active"><?php the_title(); ?></a></li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <!-- Call Box -->
                    <div class="service-info mb-4">
                        <div class="icon">
                            <i class="fi flaticon-phone-call"></i>
                        </div>
                        <h2>Need Immediate Appliance Repair?</h2>
                        <span>Call Technician Anytime</span>
                        <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="num">
                            <span><?php echo esc_html($phone_num); ?></span>
                        </a>
                    </div>

                    <!-- Fast Online Booking Card in Sidebar -->
                    <div class="bg-white p-4 rounded-3 border shadow-sm text-center mb-4">
                        <div class="mb-3">
                            <i class="ti-calendar text-primary" style="font-size: 32px;"></i>
                        </div>
                        <h4 style="font-weight: 700; font-size: 18px; margin-bottom: 8px;">Book Online in 30 Seconds</h4>
                        <p class="text-muted" style="font-size: 13px; margin-bottom: 16px;">Schedule a certified technician visit at your preferred time slot across Srinagar.</p>
                        <a href="<?php echo esc_url($book_link); ?>" class="theme-btn-s2 w-100 py-2 d-block text-center mb-2">Book Service Online</a>
                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="theme-btn w-100 py-2 d-block text-center" style="background-color: #25D366; border-color: #25D366;">
                            <i class="ti-comments me-1"></i> WhatsApp Booking
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
<!--end of service-single-page -->

<!--start project section (Work Gallery) -->
<section class="wpo-project-section" style="background: #f8fafc; padding-top: 80px; padding-bottom: 80px;">
    <div class="project-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-12">
                    <div class="wpo-section-title-s2">
                        <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Work Gallery</span>
                        <h2 class="poort-text poort-in-right">Recent Appliance Repairs in Srinagar</h2>
                        <p>Browse through our on-site doorstep repair work across Srinagar neighborhoods including Chattabal, Bemina, and Tengpora.</p>
                    </div>
                </div>
                <div class="col-lg-5 col-12">
                    <div class="title-btn-right wow fadeInRightSlow" data-wow-duration="1000ms">
                        <a href="<?php echo esc_url($book_link); ?>" class="theme-btn-s2">Book An Appointment</a>
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
                    $group_attr = $is_video ? '' : 'data-fancybox-group="service-gallery"';
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

<!-- start testimonial-section -->
<section class="wpo-testimonial-section section-padding">
    <div class="container">
        <div class="wpo-section-title text-center mb-5">
            <span>Client Reviews</span>
            <h2 class="poort-text">What Our Customers Say</h2>
            <p class="text-muted">Over 337+ 5-Star verified reviews from satisfied homeowners and business owners across Srinagar.</p>
        </div>
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
            <?php endwhile; wp_reset_postdata(); endif; ?>
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
                <?php endwhile; wp_reset_postdata(); endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end testimonial-section-->

<?php get_footer(); ?>
