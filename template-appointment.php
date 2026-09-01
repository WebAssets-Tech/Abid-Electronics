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

<!--start booking-section-->
<section class="wpo-booking-section section-padding">
    <div class="container">
        <div class="wpo-contact-form-area">
            <div class="wpo-section-title">
                <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i>Book an appointment</span>
                <h2 class="poort-text poort-in-right">Need Same-Day Doorstep Appliance Repair? We’re just a call or booking away.</h2>
                <p>Select your appliance service, provide your contact details, and our certified technician will visit your location anywhere across Srinagar.</p>
            </div>
            <form method="post" class="contact-validation-active" id="contact-form-main" action="">
                <div class="row">
                    <div class="col col-lg-6 col-md-6 col-12">
                        <input type="text" class="form-control" name="name" id="name" placeholder="Full Name*" required>
                    </div>
                    <div class="col col-lg-6 col-md-6 col-12">
                        <input type="email" class="form-control" name="email" id="email" placeholder="Email Address*" required>
                    </div>
                    <div class="col col-lg-6 col-md-6 col-12">
                        <input type="tel" class="form-control" name="phone" id="phone" placeholder="Phone Number (e.g. +91 9622917697)*" required>
                    </div>
                    <div class="col col-lg-6 col-md-6 col-12">
                        <select name="subject" class="form-control" required>
                            <option disabled="disabled" selected value="">Choose an Appliance / Service*</option>
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
                        <input type="text" class="form-control" name="adress" id="adress" placeholder="Your Address / Area in Srinagar (e.g. Chattabal, Bemina, Rajbagh)*" required>
                    </div>
                    <div class="col col-lg-12 col-md-12 col-12">
                        <textarea class="form-control" name="note" id="note" placeholder="Describe the problem with your appliance (e.g., fridge not cooling, washer spinning issue)..." rows="4"></textarea>
                    </div>
                    <div class="col col-lg-12 col-md-12 col-12">
                        <div class="submit-area">
                            <button type="submit" class="theme-btn">Confirm Appointment</button>
                            <div id="loader">
                                <i class="ti-reload"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="clearfix error-handling-messages">
                    <div id="success">Thank you! Your appointment request has been submitted. We will contact you shortly to confirm technician arrival.</div>
                    <div id="error">An error occurred while submitting your appointment. Please call us directly at <?php echo esc_html(get_theme_mod('contact_phone', '+91 9622917697')); ?>.</div>
                </div>
            </form>
        </div>
    </div>
</section>
<!--end booking-section -->

<?php get_footer(); ?>
