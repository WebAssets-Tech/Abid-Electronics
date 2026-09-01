<?php 
/* Template Name: Contact Page */ 
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

<!--start of contact-page -->
<section class="contact-page section-padding">
    <div class="container">
        <div class="office-info">
            <div class="row">
                <div class="col col-lg-4 col-md-6 col-12">
                    <div class="office-info-item">
                        <div class="office-info-icon">
                            <div class="icon">
                                <i class="fi flaticon-home-address"></i>
                            </div>
                        </div>
                        <div class="office-info-text">
                            <h2>Address</h2>
                            <p><?php echo nl2br(esc_html(get_theme_mod('contact_address', "Bemina Crossing, Chattabal\nSrinagar 190001, J&K"))); ?></p>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4 col-md-6 col-12">
                    <div class="office-info-item active">
                        <div class="office-info-icon">
                            <div class="icon">
                                <i class="fi flaticon-phone-call"></i>
                            </div>
                        </div>
                        <div class="office-info-text">
                            <h2>Phone Numbers</h2>
                            <p>
                                <?php 
                                $phone1 = get_theme_mod('contact_phone', '+91 9622917697');
                                $phone2 = get_theme_mod('contact_phone_sec');
                                $clean_p1 = preg_replace('/[^0-9]/', '', (string)$phone1);
                                $clean_p2 = preg_replace('/[^0-9]/', '', (string)$phone2);
                                ?>
                                <a href="tel:<?php echo esc_attr($clean_p1 ? '+' . $clean_p1 : '+919622917697'); ?>"><?php echo esc_html($phone1); ?></a>
                                <?php if (!empty($phone2) && $clean_p2 !== $clean_p1) : ?>
                                    <a href="tel:<?php echo esc_attr('+' . $clean_p2); ?>"><?php echo esc_html($phone2); ?></a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col col-lg-4 col-md-6 col-12">
                    <div class="office-info-item">
                        <div class="office-info-icon">
                            <div class="icon">
                                <i class="fi flaticon-mail-1"></i>
                            </div>
                        </div>
                        <div class="office-info-text">
                            <h2>Email Support</h2>
                            <p>
                                <?php 
                                $email1 = get_theme_mod('contact_email', 'support@abidelectronics.com');
                                $email2 = get_theme_mod('contact_email_2', 'abidelectronicshub@gmail.com');
                                ?>
                                <a href="mailto:<?php echo esc_attr($email1); ?>"><?php echo esc_html($email1); ?></a>
                                <?php if (!empty($email2) && strtolower(trim($email2)) !== strtolower(trim($email1))) : ?>
                                    <a href="mailto:<?php echo esc_attr($email2); ?>"><?php echo esc_html($email2); ?></a>
                                <?php endif; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="contact-wrap">
            <div class="row">
                <div class="col-lg-6 col-12">
                    <div class="contact-left">
                        <h2>Get In Touch</h2>
                        <p>Need urgent doorstep repair for your refrigerator, washing machine, AC, or geyser? Call us directly or send a message below for same-day service across Srinagar.</p>
                        <div class="map">
                            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3304.437440556166!2d74.7875588!3d34.0839326!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x38e185bde829f1c1%3A0x7fdecf4f1f7b5def!2sAbid%20Electronics%20Service%20Hub!5e0!3m2!1sen!2sin!4v1788086696693!5m2!1sen!2sin" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="contact-right">
                        <div class="title">
                            <h2>Send Us A Message</h2>
                            <p>Fill out the details below and our technician will contact you shortly.</p>
                        </div>
                        <form class="contact-form contact-validation-active" id="contact-form" method="post" action="">
                            <div class="input-item">
                                <input id="name" name="name" class="fild" type="text" placeholder="Your Full Name*" required>
                                <label for="name"><i class="flaticon-user"></i></label>
                            </div>
                            <div class="input-item">
                                <input id="email" name="email" class="fild" type="email" placeholder="Email Address (Optional)">
                                <label for="email"><i class="flaticon-email"></i></label>
                            </div>
                            <div class="input-item">
                                <input id="phone" name="phone" class="fild" type="tel" placeholder="Phone Number*" required>
                                <label for="phone"><i class="flaticon-phone-call"></i></label>
                            </div>
                            <div class="input-item">
                                <input id="subject" name="subject" class="fild" type="text" placeholder="Appliance Model / Service Needed (Optional)">
                                <label for="subject"><i class="flaticon-bag"></i></label>
                            </div>
                            <div class="input-item">
                                <textarea id="message" name="note" class="fild textarea" placeholder="Describe the issue or repair required (Optional)"></textarea>
                                <label for="message"><i class="flaticon-edit"></i></label>
                            </div>
                            <div class="input-item submitbtn">
                                <input class="fild" type="submit" value="Submit Request">
                            </div>
                            <div class="clearfix error-handling-messages">
                                <div id="success">Thank you! Your inquiry has been sent successfully. We will call you back shortly.</div>
                                <div id="error">An error occurred while sending your request. Please call us directly at <?php echo esc_html(get_theme_mod('contact_phone', '+91 9622917697')); ?>.</div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!--end of contact-page -->

<?php get_footer(); ?>
