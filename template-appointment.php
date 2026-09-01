<?php
/* Template Name: Appointment Page */
get_header(); ?>

<style>
/* Custom styles for landing page */
.wpo-landing-hero {
    padding: 60px 0 40px;
    background: #f8f9fa;
}
.landing-pitch h2 {
    font-size: 42px;
    font-weight: 800;
    margin-bottom: 20px;
    line-height: 1.2;
}
.landing-pitch p {
    font-size: 18px;
    margin-bottom: 30px;
}
.landing-features {
    list-style: none;
    padding: 0;
    margin-bottom: 30px;
}
.landing-features li {
    font-size: 18px;
    margin-bottom: 12px;
    display: flex;
    align-items: center;
}
.landing-features li i {
    color: #ff5e14;
    font-size: 24px;
    margin-right: 10px;
}
.landing-cta-btns {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}
.landing-cta-btns a.call-btn {
    background: #ff5e14;
    color: #fff;
    padding: 12px 30px;
    border-radius: 5px;
    font-weight: 700;
    font-size: 18px;
    display: inline-flex;
    align-items: center;
    transition: 0.3s;
}
.landing-cta-btns a.call-btn:hover {
    background: #e04b08;
    color: #fff;
}
.landing-cta-btns a.wa-btn {
    background: #25D366;
    color: #fff;
    padding: 12px 30px;
    border-radius: 5px;
    font-weight: 700;
    font-size: 18px;
    display: inline-flex;
    align-items: center;
    transition: 0.3s;
}
.landing-cta-btns a.wa-btn:hover {
    background: #1eb956;
    color: #fff;
}
.landing-cta-btns a i {
    margin-right: 8px;
    font-size: 20px;
}
.form-card {
    background: #fff;
    padding: 40px;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
}
.form-card-title {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 20px;
    text-align: center;
}
.services-strip {
    background: #fff;
    padding: 30px 0;
    border-top: 1px solid #eee;
    border-bottom: 1px solid #eee;
}
.service-icon-box {
    text-align: center;
}
.service-icon-box img {
    height: 50px;
    margin-bottom: 10px;
}
.service-icon-box h5 {
    font-size: 16px;
    margin: 0;
}
.certificate-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important; }
</style>

<!-- start landing hero -->
<section class="wpo-landing-hero">
    <div class="container">
        <div class="row align-items-center">
            <!-- Left Pitch -->
            <div class="col-lg-6 col-12 mb-5 mb-lg-0 wow fadeInLeft" data-wow-duration="1200ms">
                <div class="landing-pitch">
                    <span class="sub-title d-block mb-2 text-primary fw-bold" style="letter-spacing: 1px; color:#ff5e14 !important;">Srinagar's #1 Appliance Repair</span>
                    <h2>Same-Day Doorstep Appliance Repair</h2>
                    <p>Don't let a broken appliance ruin your day. Our certified technicians are ready to fix it fast, with genuine parts and a solid warranty.</p>
                    
                    <ul class="landing-features">
                        <li><i class="ti-check-box"></i> 5-Star Rated Service</li>
                        <li><i class="ti-check-box"></i> Genuine Spare Parts</li>
                        <li><i class="ti-check-box"></i> Post-Repair Warranty</li>
                        <li><i class="ti-check-box"></i> Certified Expert Technicians</li>
                    </ul>

                    <div class="landing-cta-btns mt-4">
                        <a href="<?php echo esc_url(get_theme_mod('hero_phone_link', 'tel:+919622917697')); ?>" class="call-btn"><i class="ti-headphone-alt"></i> Call Now</a>
                        <a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>" class="wa-btn"><i class="ti-mobile"></i> WhatsApp Us</a>
                    </div>
                </div>
            </div>
            
            <!-- Right Form -->
            <div class="col-lg-6 col-12 wow fadeInRight" data-wow-duration="1200ms">
                <div class="form-card wpo-contact-form-area" style="padding: 30px;">
                    <h3 class="form-card-title">Book a Technician <br><span style="font-size:16px; font-weight:normal;">(Get a Call Back in 5 Mins)</span></h3>
                    <form method="post" class="contact-validation-active" id="contact-form-main" action="">
                        <div class="row">
                            <div class="col col-lg-12 col-12">
                                <input type="text" class="form-control" name="name" id="name" placeholder="Full Name*" required>
                            </div>
                            <div class="col col-lg-6 col-md-6 col-12">
                                <input type="email" class="form-control" name="email" id="email" placeholder="Email Address (Optional)">
                            </div>
                            <div class="col col-lg-6 col-md-6 col-12">
                                <input type="tel" class="form-control" name="phone" id="phone" placeholder="Phone Number*" required>
                            </div>
                            <div class="col col-lg-12 col-12">
                                <select name="subject" class="form-control">
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
                            <div class="col col-lg-12 col-12">
                                <input type="text" class="form-control" name="adress" id="adress" placeholder="Your Address / Area in Srinagar (Optional)">
                            </div>
                            <div class="col col-lg-12 col-12">
                                <textarea class="form-control" name="note" id="note" placeholder="Describe the problem..." rows="3"></textarea>
                            </div>
                            <div class="col col-lg-12 col-12">
                                <div class="submit-area w-100 mt-2">
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
</section>
<!-- end landing hero -->

<!-- start services strip -->
<section class="services-strip">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-1.svg" alt="Refrigerator">
                    <h5>Refrigerator</h5>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3 mb-md-0">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-2.svg" alt="Washing Machine">
                    <h5>Washing Machine</h5>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-3.svg" alt="Air Conditioner">
                    <h5>Air Conditioner</h5>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="service-icon-box">
                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-4.svg" alt="Microwave">
                    <h5>Microwave</h5>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end services strip -->

<!-- start certifications section -->
<section class="wpo-certifications-section section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-12 col-12 text-center">
                <div class="section-title mb-4 wow fadeInUp" data-wow-duration="1000ms">
                    <span class="sub-title d-block mb-2 text-primary fw-bold" style="letter-spacing: 1px;">Recognized & Registered</span>
                    <h2 class="mb-3">Government Registrations & Certifications</h2>
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

<?php get_footer(); ?>
