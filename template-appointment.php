<?php
/* Template Name: Appointment Page */
get_header(); ?>

<style>
/* Multi-step form essential styles */
.step { display: none; }
.step.active { display: block; animation: fadeIn 0.4s; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.step-indicator { display: flex; justify-content: space-between; margin-bottom: 25px; position: relative; }
.step-indicator::before { content: ''; position: absolute; top: 15px; left: 0; right: 0; height: 3px; background: #eee; z-index: 1; }
.step-dot { width: 35px; height: 35px; border-radius: 50%; background: #eee; color: #777; display: flex; align-items: center; justify-content: center; font-weight: bold; position: relative; z-index: 2; transition: 0.3s; border: 3px solid #fff; }
.step-dot.active { background: #ff5e14; color: #fff; box-shadow: 0 0 0 3px rgba(255,94,20,0.2); }
.step-dot.completed { background: #25D366; color: #fff; }
.btn-nav-group { display: flex; justify-content: space-between; margin-top: 20px; }
.btn-prev { background: #777 !important; color: #fff !important; }
.wpo-booking-section .wpo-contact-form-area { padding: 40px; box-shadow: 0 5px 30px rgba(0,0,0,0.05); background: #fff; border-radius: 10px; }
.file-upload-box { border: 2px dashed #ddd; border-radius: 5px; padding: 30px; text-align: center; cursor: pointer; transition: 0.3s; background: #fcfcfc; }
.file-upload-box:hover { border-color: #ff5e14; background: #fff; }
.file-upload-box i { font-size: 30px; color: #ff5e14; margin-bottom: 10px; display: block; }
.file-name-display { margin-top: 10px; font-weight: bold; color: #25D366; }
</style>

<!--start booking-section-->
<section class="wpo-booking-section section-padding pb-0" style="background: #f9f9f9;">
    <div class="container">
        <div class="row align-items-center">
            
            <div class="col-lg-6 col-12 mb-5 mb-lg-0 wow fadeInLeft" data-wow-duration="1200ms">
                <div class="wpo-section-title text-start mb-4">
                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Srinagar's #1 Appliance Repair</span>
                    <h2 class="poort-text poort-in-right">Need Same-Day Doorstep Appliance Repair?</h2>
                    <p style="font-size: 18px;">Don't let a broken appliance ruin your day. Our certified technicians are ready to fix it fast, with genuine parts and a solid warranty.</p>
                </div>
                
                <ul class="list-unstyled mb-4">
                    <li class="mb-3" style="font-size: 18px; font-weight: 500;"><i class="ti-check-box text-primary me-2"></i> 5-Star Rated Service</li>
                    <li class="mb-3" style="font-size: 18px; font-weight: 500;"><i class="ti-check-box text-primary me-2"></i> Genuine Spare Parts</li>
                    <li class="mb-3" style="font-size: 18px; font-weight: 500;"><i class="ti-check-box text-primary me-2"></i> Post-Repair Warranty</li>
                    <li class="mb-3" style="font-size: 18px; font-weight: 500;"><i class="ti-check-box text-primary me-2"></i> Certified Expert Technicians</li>
                </ul>

                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="<?php echo esc_url(get_theme_mod('hero_phone_link', 'tel:+919622917697')); ?>" class="theme-btn"><i class="ti-headphone-alt me-2"></i> Call Now</a>
                    <a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>" class="theme-btn" style="background-color: #25D366;"><i class="ti-mobile me-2"></i> WhatsApp Us</a>
                </div>
            </div>

            <div class="col-lg-6 col-12 wow fadeInRight" data-wow-duration="1200ms">
                <div class="wpo-contact-form-area mx-auto">
                    
                    <div class="text-center mb-4">
                        <h3 style="font-weight: 700; font-size: 24px;">Book a Technician</h3>
                        <p class="text-muted">(Get a Call Back in 5 Mins)</p>
                    </div>

                    <div class="step-indicator">
                        <div class="step-dot active" id="dot-1">1</div>
                        <div class="step-dot" id="dot-2">2</div>
                        <div class="step-dot" id="dot-3">3</div>
                    </div>

                    <!-- form must have enctype for file upload -->
                    <form method="post" class="contact-validation-active multi-step-form" id="contact-form-main" enctype="multipart/form-data">
                        
                        <!-- Step 1 -->
                        <div class="step step-1 active">
                            <div class="row">
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Full Name *</label>
                                    <input type="text" class="form-control" name="name" id="name" placeholder="Enter your full name" required>
                                </div>
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Phone Number *</label>
                                    <input type="tel" class="form-control" name="phone" id="phone" placeholder="e.g. +91 9622917697" required>
                                </div>
                            </div>
                            <div class="btn-nav-group justify-content-end">
                                <button type="button" class="theme-btn btn-next">Next Step <i class="ti-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 2 -->
                        <div class="step step-2">
                            <div class="row">
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Upload Photo of Appliance (Optional)</label>
                                    <div class="file-upload-box" onclick="document.getElementById('appliance_image').click();">
                                        <i class="ti-camera"></i>
                                        <span>Click to browse or take a photo</span>
                                        <input type="file" name="appliance_image" id="appliance_image" accept="image/*" style="display: none;">
                                        <div class="file-name-display" id="file-name-display"></div>
                                    </div>
                                </div>
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Describe the Problem (Optional)</label>
                                    <textarea class="form-control" name="note" id="note" placeholder="e.g., Fridge not cooling, washer spinning issue..." rows="3"></textarea>
                                </div>
                            </div>
                            <div class="btn-nav-group">
                                <button type="button" class="theme-btn btn-prev"><i class="ti-arrow-left me-2"></i> Back</button>
                                <button type="button" class="theme-btn btn-next">Next Step <i class="ti-arrow-right ms-2"></i></button>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="step step-3">
                            <div class="row">
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Select Appliance / Service</label>
                                    <select name="subject" id="subject" class="form-control">
                                        <option disabled="disabled" selected value="">Choose an Appliance...</option>
                                        <?php
                                        $services_dropdown_query = new WP_Query(['post_type' => 'services', 'posts_per_page' => -1, 'order' => 'ASC']);
                                        if ($services_dropdown_query->have_posts()) :
                                            while ($services_dropdown_query->have_posts()) : $services_dropdown_query->the_post();
                                                ?>
                                                <option value="<?php echo esc_attr(get_the_title()); ?>"><?php the_title(); ?></option>
                                                <?php
                                            endwhile;
                                            wp_reset_postdata();
                                        else :
                                            ?>
                                            <option value="Refrigerator & Fridge Repair">Refrigerator & Fridge Repair</option>
                                            <option value="Washing Machine Repair">Washing Machine Repair</option>
                                            <option value="AC Repair & Service">AC Repair & Service</option>
                                            <option value="Geyser & Water Heater">Geyser & Water Heater</option>
                                            <option value="Microwave & Commercial">Microwave & Commercial</option>
                                        <?php endif; ?>
                                        <option value="Other Appliance Repair">Other Appliance Repair</option>
                                    </select>
                                </div>
                                <div class="col-12 form-group mb-3">
                                    <label class="fw-bold mb-2">Your Address / Area in Srinagar (Optional)</label>
                                    <input type="text" class="form-control" name="adress" id="adress" placeholder="Enter area name...">
                                </div>
                            </div>
                            <div class="btn-nav-group">
                                <button type="button" class="theme-btn btn-prev"><i class="ti-arrow-left me-2"></i> Back</button>
                                <div class="submit-area" style="margin-top:0;">
                                    <button type="submit" class="theme-btn btn-submit">Confirm Appointment</button>
                                    <div id="loader"><i class="ti-reload"></i></div>
                                </div>
                            </div>
                        </div>

                        <div class="clearfix error-handling-messages mt-3">
                            <div id="success">Thank you! Your appointment request has been submitted. We will contact you shortly.</div>
                            <div id="error">An error occurred while submitting. Please call us directly at <?php echo esc_html(get_theme_mod('contact_phone', '+91 9622917697')); ?>.</div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</section>
<!--end booking-section -->

<!--start service-strip-->
<section class="wpo-service-section section-padding pt-5 pb-5 bg-white border-top border-bottom mt-5">
    <div class="container">
        <div class="row align-items-center justify-content-center text-center">
            <?php
            $services_query = new WP_Query(['post_type' => 'services', 'posts_per_page' => 4, 'order' => 'ASC']);
            $service_icon_map = [1 => 'icon-1.svg', 2 => 'icon-2.svg', 3 => 'icon-1.svg', 4 => 'icon-3.svg'];
            $service_idx = 0;
            if ($services_query->have_posts()): 
                while ($services_query->have_posts()): $services_query->the_post();
                $service_idx++;
                $meta_icon = get_post_meta(get_the_ID(), '_service_icon', true);
                $icon_file = $service_icon_map[$service_idx] ?? 'icon-1.svg';
                $icon_url  = $meta_icon ? $meta_icon : (get_template_directory_uri() . '/assets/images/service/' . $icon_file);
            ?>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url($icon_url); ?>" alt="<?php the_title_attribute(); ?>" style="height: 50px; margin-bottom: 10px;">
                    <h5 style="font-size: 16px; margin: 0; font-weight:600;"><?php the_title(); ?></h5>
                </div>
            </div>
            <?php 
                endwhile;
                wp_reset_postdata();
            endif; 
            ?>
        </div>
    </div>
</section>
<!--end service-strip-->


<!-- start certifications section -->
<section class="wpo-certifications-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12 col-12 text-center">
                <div class="wpo-section-title mb-4 wow fadeInUp" data-wow-duration="1000ms">
                    <span>Recognized & Registered</span>
                    <h2 class="poort-text poort-in-right">Government Registrations & Certifications</h2>
                    <p class="text-muted">Abid Electronics Service Hub is a fully registered and certified appliance repair business, recognized by the Government of Jammu & Kashmir and the Ministry of MSME.</p>
                </div>
            </div>
        </div>
        <div class="row justify-content-center mt-4">
            <div class="col-lg-5 col-md-6 col-12 mb-4 wow fadeInLeft" data-wow-duration="1200ms">
                <div class="certificate-card bg-white p-3 shadow-sm rounded-3 text-center border" style="transition: transform 0.3s ease;">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-1.png" alt="J&K Shops & Establishment Certificate" class="img-fluid rounded border" style="max-height: 400px; object-fit: contain;">
                    <h4 class="mt-3 mb-1" style="font-size: 16px; font-weight: 700;">J&K Shops & Establishment Act 1966</h4>
                    <p class="text-muted mb-0" style="font-size: 13px;">Registration Certificate</p>
                </div>
            </div>
            <div class="col-lg-5 col-md-6 col-12 mb-4 wow fadeInRight" data-wow-duration="1200ms">
                <div class="certificate-card bg-white p-3 shadow-sm rounded-3 text-center border" style="transition: transform 0.3s ease;">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-2.png" alt="MSME Udyam Registration Certificate" class="img-fluid rounded border" style="max-height: 400px; object-fit: contain;">
                    <h4 class="mt-3 mb-1" style="font-size: 16px; font-weight: 700;">MSME Udyam Registration</h4>
                    <p class="text-muted mb-0" style="font-size: 13px;">Government of India</p>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end certifications section -->


<!--start project section-->
<section class="wpo-project-section pb-0" style="background:#f8f9fa; padding-top:100px;">
    <div class="project-wrapper">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7 col-12">
                    <div class="wpo-section-title-s2">
                        <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Work gallery</span>
                        <h2 class="poort-text poort-in-right">we've powered over 200+ successful projects.</h2>
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

<!-- Start wpo-faq-section -->
<section class="wpo-faq-section style-2 section-padding" style="background:#f8f9fa;">
    <div class="container">
        <div class="wpo-faq-wrap">
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-box wow fadeInLeftSlow" data-wow-duration="1000ms">
                        <div class="wpo-section-title-s2 mb-4">
                            <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Common Queries</span>
                            <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('faq_title', 'Frequently Asked Questions')); ?></h2>
                            <p><?php echo esc_html(get_theme_mod('faq_desc', 'Got questions about our doorstep repair service, diagnostic charges, or warranty coverage in Srinagar? Browse our common answers below or reach out directly.')); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-items wow fadeInRightSlow" data-wow-duration="1000ms">
                        <div class="accordion" id="accordionExample">
                            <?php
                            $faq_query = new WP_Query(['post_type' => 'faq', 'posts_per_page' => -1, 'order' => 'ASC']);
                            $faq_idx = 0;
                            if ($faq_query->have_posts()) :
                                while ($faq_query->have_posts()) : $faq_query->the_post();
                                    $faq_idx++;
                                    $is_first = ($faq_idx === 1);
                                    $collapse_id = 'faqCollapse' . $faq_idx;
                                    $heading_id  = 'faqHeading' . $faq_idx;
                                    ?>
                                    <div class="accordion-item mb-3 border">
                                        <h3 class="accordion-header" id="<?php echo esc_attr($heading_id); ?>">
                                            <button class="accordion-button<?php echo $is_first ? '' : ' collapsed'; ?>" type="button" data-bs-toggle="collapse"
                                                data-bs-target="#<?php echo esc_attr($collapse_id); ?>" aria-expanded="<?php echo $is_first ? 'true' : 'false'; ?>"
                                                aria-controls="<?php echo esc_attr($collapse_id); ?>">
                                                <?php the_title(); ?>
                                            </button>
                                        </h3>
                                        <div id="<?php echo esc_attr($collapse_id); ?>" class="accordion-collapse collapse<?php echo $is_first ? ' show' : ''; ?>"
                                            aria-labelledby="<?php echo esc_attr($heading_id); ?>" data-bs-parent="#accordionExample">
                                            <div class="accordion-body">
                                                <?php the_content(); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end faq section -->

<?php get_footer(); ?>
