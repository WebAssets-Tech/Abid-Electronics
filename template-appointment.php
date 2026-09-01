<?php
/* Template Name: Appointment Page */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2><?php the_title(); ?></h2>
                    <h3><?php echo esc_html(get_bloginfo('name')); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!--start of booking-page -->
<section class="contact-page section-padding">
    <div class="container">
        <!-- Optional: Office Info Top Bar (Compact) -->
        <div class="office-info">
            <div class="row justify-content-center">
                <div class="col col-lg-3 col-md-6 col-12">
                    <div class="office-info-item text-center">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-1.svg" alt="Refrigerator" style="width: 50px; margin-bottom: 10px;">
                        <h4 style="font-size: 16px;">Refrigerator</h4>
                    </div>
                </div>
                <div class="col col-lg-3 col-md-6 col-12">
                    <div class="office-info-item text-center">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-2.svg" alt="Washing Machine" style="width: 50px; margin-bottom: 10px;">
                        <h4 style="font-size: 16px;">Washing Machine</h4>
                    </div>
                </div>
                <div class="col col-lg-3 col-md-6 col-12">
                    <div class="office-info-item text-center">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-3.svg" alt="Air Conditioner" style="width: 50px; margin-bottom: 10px;">
                        <h4 style="font-size: 16px;">Air Conditioner</h4>
                    </div>
                </div>
                <div class="col col-lg-3 col-md-6 col-12">
                    <div class="office-info-item text-center">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-4.svg" alt="Microwave" style="width: 50px; margin-bottom: 10px;">
                        <h4 style="font-size: 16px;">Microwave</h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-wrap">
            <div class="row">
                <!-- Left Pitch Column -->
                <div class="col-lg-6 col-12">
                    <div class="contact-left">
                        <h2>Srinagar's #1 Appliance Repair Hub</h2>
                        <p>Don't let a broken appliance ruin your day. Our certified technicians are ready to fix it fast, with genuine parts and a solid warranty.</p>
                        
                        <!-- Theme Native About Features List -->
                        <div class="wpo-about-section" style="padding: 0; margin-bottom: 30px;">
                            <div class="about-max" style="margin: 0; display: block;">
                                <div class="about-wrap">
                                    <div class="about-items" style="padding: 15px; margin-bottom: 15px; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 8px;">
                                        <div class="icon" style="width: 50px; height: 50px; background: #fdf2ed; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ti-check-box" style="font-size: 24px; color: #ff5e14;"></i>
                                        </div>
                                        <div class="about-text" style="padding-left: 20px;">
                                            <h3 style="font-size: 18px; margin: 0; font-weight: 700;">5-Star Rated Service</h3>
                                        </div>
                                    </div>
                                    <div class="about-items" style="padding: 15px; margin-bottom: 15px; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 8px;">
                                        <div class="icon" style="width: 50px; height: 50px; background: #fdf2ed; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ti-settings" style="font-size: 24px; color: #ff5e14;"></i>
                                        </div>
                                        <div class="about-text" style="padding-left: 20px;">
                                            <h3 style="font-size: 18px; margin: 0; font-weight: 700;">Genuine Spare Parts</h3>
                                        </div>
                                    </div>
                                    <div class="about-items" style="padding: 15px; margin-bottom: 15px; background: #fff; box-shadow: 0 5px 20px rgba(0,0,0,0.05); border-radius: 8px;">
                                        <div class="icon" style="width: 50px; height: 50px; background: #fdf2ed; border-radius: 5px; display: flex; align-items: center; justify-content: center;">
                                            <i class="ti-thumb-up" style="font-size: 24px; color: #ff5e14;"></i>
                                        </div>
                                        <div class="about-text" style="padding-left: 20px;">
                                            <h3 style="font-size: 18px; margin: 0; font-weight: 700;">Post-Repair Warranty</h3>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Theme Native Buttons -->
                        <div class="hero-btns mt-4 mb-5" style="display: flex; gap: 15px; flex-wrap: wrap;">
                            <a href="<?php echo esc_url(get_theme_mod('hero_phone_link', 'tel:+919622917697')); ?>" class="theme-btn-s2"><i class="ti-headphone-alt" style="margin-right: 8px;"></i> Call Now</a>
                            <a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>" class="theme-btn" style="background:#25D366; border-color:#25D366; color:#fff;"><i class="fa fa-whatsapp" style="margin-right: 8px;"></i> WhatsApp Us</a>
                        </div>
                        
                        <!-- Trust Certificates -->
                        <div class="row">
                            <div class="col-6 text-center">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-1.png" alt="J&K Shops & Establishment Certificate" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 150px; object-fit: contain;">
                                <p style="font-size: 12px; font-weight: 600; line-height: 1.2;">Govt of J&K</p>
                            </div>
                            <div class="col-6 text-center">
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/about/certificate-2.png" alt="MSME Udyam Registration Certificate" class="img-fluid rounded border shadow-sm mb-2" style="max-height: 150px; object-fit: contain;">
                                <p style="font-size: 12px; font-weight: 600; line-height: 1.2;">MSME India</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Form Column -->
                <div class="col-lg-6 col-12">
                    <div class="contact-right" style="background: #fff; padding: 40px; border-radius: 10px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);">
                        <div class="title" style="margin-bottom: 30px;">
                            <h2 style="font-size: 28px; margin-bottom: 10px;">Book a Technician</h2>
                            <p style="font-size: 16px;">We will call you back within 5 minutes to confirm.</p>
                        </div>
                        <div class="wpo-contact-form-area" style="padding: 0; box-shadow: none; background: transparent;">
                            <form method="post" class="contact-validation-active" id="contact-form-main" action="">
                                <div class="row">
                                    <div class="col col-lg-12 col-md-12 col-12">
                                        <input type="text" class="form-control" name="name" id="name" placeholder="Full Name*" required>
                                    </div>
                                    <div class="col col-lg-12 col-md-12 col-12">
                                        <input type="tel" class="form-control" name="phone" id="phone" placeholder="Phone Number*" required>
                                    </div>
                                    <div class="col col-lg-12 col-md-12 col-12">
                                        <select name="subject" class="form-control" style="height: 55px; border: 1px solid rgba(0,0,0,.08); background: #fdf2ed; border-radius: 5px; margin-bottom: 25px; width: 100%; padding: 0 20px;">
                                            <option disabled="disabled" selected value="">Choose an Appliance / Service (Optional)</option>
                                            <?php
                                            $services_dropdown_query = new WP_Query([
                                                'post_type'      => 'services',
                                                'posts_per_page' => -1,
                                                'order'          => 'ASC'
                                            ]);
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
                                    <div class="col col-lg-12 col-md-12 col-12">
                                        <input type="text" class="form-control" name="adress" id="adress" placeholder="Your Address / Area in Srinagar (Optional)">
                                    </div>
                                    <div class="col col-lg-12 col-12">
                                        <textarea class="form-control" name="note" id="note" placeholder="Describe the problem..." rows="3"></textarea>
                                    </div>
                                    <div class="col col-lg-12 col-12 mt-3">
                                        <div class="submit-area w-100">
                                            <button type="submit" class="theme-btn w-100" style="border-radius: 5px;">Confirm Appointment</button>
                                            <div id="loader">
                                                <i class="ti-reload"></i>
                                            </div>
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
        </div>
    </div>
</section>
<!--end of booking-page -->

<?php get_footer(); ?>
