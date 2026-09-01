<?php
/**
 * The template for displaying single Service details
 *
 * @package WebAssets
 */

get_header(); 
$current_service_id = get_the_ID();
?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2><?php the_title(); ?></h2>
                    <h3>Service Details</h3>
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
                    <div class="title-image mb-4">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('full', ['class' => 'img-fluid w-100 rounded-3']); ?>
                        <?php else : ?>
                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service-single/img-1.jpg" alt="<?php the_title_attribute(); ?>" class="img-fluid w-100 rounded-3">
                        <?php endif; ?>
                    </div>

                    <?php 
                    $price = get_post_meta(get_the_ID(), '_service_price', true);
                    if (!empty($price)) : 
                    ?>
                        <div class="service-pricing-tag mb-3">
                            <span class="badge bg-primary fs-6 px-3 py-2">Starting Diagnostic &amp; Visit: <?php echo esc_html($price); ?></span>
                        </div>
                    <?php endif; ?>

                    <h2><?php the_title(); ?></h2>

                    <div class="service-details-body">
                        <?php
                        while (have_posts()) :
                            the_post();
                            $content = get_the_content();
                            if (!empty(trim($content))) {
                                the_content();
                            } else {
                                ?>
                                <p class="lead">At Abid Electronics, we provide professional doorstep <?php the_title(); ?> across Srinagar with certified technicians and guaranteed genuine replacement parts.</p>
                                <p>Whether you're experiencing electrical faults, cooling failures, unusual noises, or motor issues, our expert team arrives fully equipped to diagnose and repair your appliance on the spot. We serve all residential and commercial customers throughout Chattabal, Bemina, Rajbagh, Lal Chowk, and all Srinagar neighborhoods.</p>
                                
                                <h3>Service Features &amp; Guarantees:</h3>
                                <ul>
                                    <li>Same-day emergency doorstep service within 2 to 4 hours of booking.</li>
                                    <li>100% genuine, manufacturer-approved replacement spare parts.</li>
                                    <li>Multi-brand capability covering LG, Samsung, Whirlpool, Godrej, Panasonic, and Daikin.</li>
                                    <li>Transparent, upfront pricing estimate before any repair work starts.</li>
                                    <li>Post-repair warranty on parts and workmanship for total peace of mind.</li>
                                </ul>
                                <?php
                            }
                        endwhile;
                        ?>
                    </div>

                    <!-- Static Work Process from project-single.html -->
                    <div class="wpo-p-details-section">
                        <div class="process-wrap mt-4">
                            <h5>Our Work Process</h5>
                            <div class="row">
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-handshake"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>Quality We Ensure</h3>
                                            <p>Thorough diagnostic inspection ensuring high-quality, reliable repair with genuine parts.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-medal"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>Experienced Workers</h3>
                                            <p>Verified, certified appliance engineers with multi-brand repair mastery across Srinagar.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-md-6 col-12">
                                    <div class="process-item">
                                        <div class="process-icon">
                                            <i class="fi flaticon-gift-box"></i>
                                        </div>
                                        <div class="process-text">
                                            <h3>Modern Equipment Use</h3>
                                            <p>State-of-the-art testing tools ensuring swift, safe, and precise doorstep troubleshooting.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic FAQ Accordion for Service Single -->
                    <div class="service-faq-section mt-5">
                        <h3 class="mb-4" style="font-size: 24px; font-weight: 700;">Frequently Asked Questions</h3>
                        <div class="accordion">
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
                                    $is_active = ($faq_idx === 1);
                                    ?>
                                    <div class="accordion-item <?php echo $is_active ? 'active' : ''; ?>">
                                        <button class="accordion-header"><?php the_title(); ?></button>
                                        <div class="accordion-content">
                                            <p><?php echo get_the_content(); ?></p>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            else :
                                ?>
                                <div class="accordion-item active">
                                    <button class="accordion-header">How quickly can a technician visit my location in Srinagar?</button>
                                    <div class="accordion-content">
                                        <p>We offer same-day doorstep service across Srinagar, typically arriving within 2 to 4 hours of booking confirmation.</p>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <button class="accordion-header">Do you use genuine manufacturer replacement parts?</button>
                                    <div class="accordion-content">
                                        <p>Yes, 100% genuine and brand-authorized spare parts are used for all repairs with warranty coverage.</p>
                                    </div>
                                </div>
                                <div class="accordion-item">
                                    <button class="accordion-header">What is your service warranty policy?</button>
                                    <div class="accordion-content">
                                        <p>We provide a comprehensive warranty on replaced spare parts and workmanship so you have total peace of mind.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Booking CTA Box -->
                    <div class="service-booking-cta p-4 mt-5 rounded-3" style="background: #eef2fd; border-left: 4px solid #3860D2;">
                        <div class="row align-items-center">
                            <div class="col-md-8 col-12">
                                <h3 class="mb-1" style="font-size: 20px;">Book Same-Day <?php the_title(); ?></h3>
                                <p class="mb-0 text-muted">Get your appliance fixed today by Srinagar's trusted experts.</p>
                            </div>
                            <div class="col-md-4 col-12 text-md-end mt-3 mt-md-0">
                                <a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', home_url('/appointment/'))); ?>" class="theme-btn-s2">Book Appointment</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Services Sidebar -->
            <div class="col-lg-4 col-12 order-lg-1">
                <div class="service-sidebar">
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
                    <div class="service-info">
                        <div class="icon">
                            <i class="fi flaticon-phone-call"></i>
                        </div>
                        <h2>Need Immediate Appliance Repair?</h2>
                        <span>Call Technician Anytime</span>
                        <?php 
                        $phone = get_theme_mod('contact_phone', '+91 9622917697'); 
                        $clean_phone = preg_replace('/[^0-9+]/', '', $phone);
                        ?>
                        <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="num">
                            <span><?php echo esc_html($phone); ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end of service-single-page -->

<?php get_footer(); ?>
