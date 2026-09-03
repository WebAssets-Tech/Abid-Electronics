        <!-- Start footer -->
        <footer class="footer-common footer-section-s1">
            <div class="footer-wrap">
                <div class="footer-topbar moving-cursor-wrap">
                    <div class="container">
                        <div class="wraper">
                            <div class="marquee_container">
                                <div>
                                    <h2 class="marquee-s2">
                                        <small>Schedule a Free Appliance Evaluation. Plan Your Maintenance Consultation</small>
                                    </h2>
                                </div>
                            </div>
                            <div class="booking-btn moving-cursor"><a class="btn-wrapper btn-move"
                                    href="<?php echo esc_url(get_theme_mod('bottom_marquee_btn_link', home_url('/contact/'))); ?>"><small><?php echo esc_html(get_theme_mod('bottom_marquee_btn_text', 'Get Schedule Now')); ?></small></a></div>
                        </div>
                    </div>
                </div>
                <div class="container">
                    <div class="footer">
                        <?php if ( is_active_sidebar( 'footer-1' ) ) : ?>
                            <div class="item widget-newsletter fade_bottom">
                                <?php dynamic_sidebar( 'footer-1' ); ?>
                            </div>
                        <?php else : ?>
                            <div class="item widget-newsletter fade_bottom">
                                <h2>Get your appliance fixed today</h2>
                                <div class="newsletter">
                                    <form id="newsletter-form" class="form-fild">
                                        <div class="input-items">
                                            <input class="fild" type="email" name="email" placeholder="Email Address" required>
                                        </div>
                                        <div class="input-btn">
                                            <button type="submit" class="theme-btn-s2">Sign Up</button>
                                        </div>
                                        <div id="n-success" style="display: none; margin-top: 10px; font-size: 13px; font-weight: 500;"></div>
                                    </form>
                                </div>

                                <!-- Below Newsletter: Clean White Card with Theme Colors -->
                                <div class="footer-newsletter-actions bg-white p-3 p-sm-4 rounded-3 shadow-sm mt-4 text-start">
                                    <div class="footer-actions-header mb-2">
                                        <h4 class="mb-0" style="font-size: 15px; font-weight: 700; color: #111827;">Quick Booking &amp; Support</h4>
                                        <span class="text-muted" style="font-size: 12px;">Immediate technician assistance in Srinagar</span>
                                    </div>

                                    <!-- Book Appointment Button (Theme Royal Blue) -->
                                    <div class="footer-cta-btn my-3">
                                        <?php $appoint_link = get_theme_mod('hero_btn1_link', home_url('/appointment/')); ?>
                                        <a href="<?php echo esc_url($appoint_link); ?>" class="theme-btn-s2 d-flex align-items-center justify-content-center gap-2 py-2 px-3 text-decoration-none w-100 text-center" style="font-size: 13px; font-weight: 600; border-radius: 6px; background: #3860D2; border-color: #3860D2; color: #ffffff;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-calendar2-check" viewBox="0 0 16 16">
                                                <path d="M10.854 8.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L7.5 10.793l2.646-2.647a.5.5 0 0 1 .708 0"/>
                                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5M2 2a1 1 0 0 0-1 1v11a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1z"/>
                                                <path d="M2.5 4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H3a.5.5 0 0 1-.5-.5z"/>
                                            </svg>
                                            <span><?php echo esc_html(get_theme_mod('hero_btn1_text', 'Book An Appointment')); ?></span>
                                        </a>
                                    </div>

                                    <!-- Small Call & WhatsApp Contact Buttons -->
                                    <?php 
                                    $phone_num = get_theme_mod('contact_phone', '+91 9622917697');
                                    $clean_phone = preg_replace('/[^0-9+]/', '', $phone_num);
                                    $wa_link = get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697');
                                    ?>
                                    <div class="footer-contact-btns d-flex gap-2 mb-3">
                                        <a href="tel:<?php echo esc_attr($clean_phone); ?>" class="btn flex-fill d-inline-flex align-items-center justify-content-center gap-2 py-2 px-2 text-decoration-none" style="font-size: 12px; font-weight: 600; border-radius: 6px; background: #eef2fd; color: #3860D2; border: 1px solid #d0defe;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-telephone-fill" viewBox="0 0 16 16">
                                                <path fill-rule="evenodd" d="M1.885.511a1.745 1.745 0 0 1 2.61.163L6.29 2.98c.329.423.445.974.315 1.494l-.547 2.19a.68.68 0 0 0 .178.643l2.457 2.457a.68.68 0 0 0 .644.178l2.189-.547a1.75 1.75 0 0 1 1.494.315l2.306 1.794c.829.645.905 1.87.163 2.611l-1.034 1.034c-.74.74-1.846 1.065-2.877.702a18.6 18.6 0 0 1-7.01-4.42 18.6 18.6 0 0 1-4.42-7.009c-.362-1.03-.037-2.137.703-2.877z"/>
                                            </svg>
                                            <span>Call Us</span>
                                        </a>
                                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="btn flex-fill d-inline-flex align-items-center justify-content-center gap-2 py-2 px-2 text-decoration-none" style="font-size: 12px; font-weight: 600; border-radius: 6px; background-color: #25D366; color: #ffffff; border: 0;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.707 1.916.807 2.05c.099.133 1.39 2.123 3.37 2.977.47.203.837.324 1.123.415.474.152.905.13 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                            <span>WhatsApp</span>
                                        </a>
                                    </div>

                                    <!-- Social Media Icons Row -->
                                    <div class="footer-social-links d-flex align-items-center justify-content-center gap-2 pt-1">
                                        <?php 
                                        $ig = get_theme_mod('social_instagram', '#');
                                        $fb = get_theme_mod('social_facebook', '#');
                                        $tw = get_theme_mod('social_twitter', '#');
                                        ?>
                                        <?php if ($ig) : ?>
                                            <a href="<?php echo esc_url($ig); ?>" target="_blank" class="footer-social-icon" title="Instagram">
                                                <i class="ti-instagram"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($fb) : ?>
                                            <a href="<?php echo esc_url($fb); ?>" target="_blank" class="footer-social-icon" title="Facebook">
                                                <i class="ti-facebook"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($tw) : ?>
                                            <a href="<?php echo esc_url($tw); ?>" target="_blank" class="footer-social-icon" title="Twitter">
                                                <i class="ti-twitter-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" class="footer-social-icon" title="WhatsApp">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="currentColor" class="bi bi-whatsapp" viewBox="0 0 16 16">
                                                <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.707 1.916.807 2.05c.099.133 1.39 2.123 3.37 2.977.47.203.837.324 1.123.415.474.152.905.13 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'footer-2' ) ) : ?>
                            <div class="item fade_bottom">
                                <?php dynamic_sidebar( 'footer-2' ); ?>
                            </div>
                        <?php else : ?>
                            <div class="item fade_bottom">
                                <h2 class="title"><?php echo esc_html(get_theme_mod('footer_col2_title', 'Our Services')); ?></h2>
                                <?php
                                if (has_nav_menu('footer-services')) {
                                    wp_nav_menu([
                                        'theme_location' => 'footer-services',
                                        'container'      => false,
                                        'menu_class'     => '',
                                        'fallback_cb'    => false,
                                    ]);
                                } else {
                                    $footer_services = new WP_Query([
                                        'post_type'      => 'services',
                                        'posts_per_page' => 5,
                                        'post_status'    => 'publish',
                                        'orderby'        => 'menu_order',
                                        'order'          => 'ASC',
                                    ]);
                                    if ($footer_services->have_posts()) :
                                        echo '<ul>';
                                        while ($footer_services->have_posts()) : $footer_services->the_post();
                                            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                        endwhile;
                                        wp_reset_postdata();
                                        echo '</ul>';
                                    else :
                                        ?>
                                        <ul>
                                            <li><a href="<?php echo esc_url(home_url('/service/refrigerator-repair/')); ?>">Refrigerator Repair</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/service/washing-machine-repair/')); ?>">Washing Machine Repair</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/service/ac-repair-service/')); ?>">AC Service</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/service/geyser-repair-service/')); ?>">Geyser Repair</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/service/microwave-oven-repair/')); ?>">Microwave Repair</a></li>
                                        </ul>
                                        <?php
                                    endif;
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'footer-3' ) ) : ?>
                            <div class="item fade_bottom">
                                <?php dynamic_sidebar( 'footer-3' ); ?>
                            </div>
                        <?php else : ?>
                            <div class="item fade_bottom">
                                <h2 class="title"><?php echo esc_html(get_theme_mod('footer_col3_title', 'Quick Links')); ?></h2>
                                <?php
                                if (has_nav_menu('footer-menu')) {
                                    wp_nav_menu([
                                        'theme_location' => 'footer-menu',
                                        'container'      => false,
                                        'menu_class'     => '',
                                        'fallback_cb'    => false,
                                    ]);
                                } elseif (has_nav_menu('company-links')) {
                                    wp_nav_menu([
                                        'theme_location' => 'company-links',
                                        'container'      => false,
                                        'menu_class'     => '',
                                        'fallback_cb'    => false,
                                    ]);
                                } else {
                                    ?>
                                    <ul>
                                        <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
                                        <li><a href="<?php echo esc_url(home_url('/about/')); ?>">About Us</a></li>
                                        <li><a href="<?php echo esc_url(home_url('/services/')); ?>">Our Services</a></li>
                                        <li><a href="<?php echo esc_url(home_url('/gallery/')); ?>">Work Gallery</a></li>
                                        <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Contact Us</a></li>
                                        <li><a href="<?php echo esc_url(home_url('/appointment/')); ?>">Book Appointment</a></li>
                                    </ul>
                                    <?php
                                }
                                ?>
                            </div>
                        <?php endif; ?>

                        <?php if ( is_active_sidebar( 'footer-4' ) ) : ?>
                            <div class="item fade_bottom">
                                <?php dynamic_sidebar( 'footer-4' ); ?>
                            </div>
                        <?php else : ?>
                            <div class="item fade_bottom">
                                <h2 class="title"><?php echo esc_html(get_theme_mod('footer_col4_title', 'Areas We Serve')); ?></h2>
                                <?php
                                if (has_nav_menu('footer-areas')) {
                                    wp_nav_menu([
                                        'theme_location' => 'footer-areas',
                                        'container'      => false,
                                        'menu_class'     => '',
                                        'fallback_cb'    => false,
                                    ]);
                                } else {
                                    $footer_locs = new WP_Query([
                                        'post_type'      => 'locations',
                                        'posts_per_page' => 5,
                                        'post_status'    => 'publish',
                                    ]);
                                    if ($footer_locs->have_posts()) :
                                        echo '<ul>';
                                        while ($footer_locs->have_posts()) : $footer_locs->the_post();
                                            echo '<li><a href="' . esc_url(get_permalink()) . '">' . esc_html(get_the_title()) . '</a></li>';
                                        endwhile;
                                        wp_reset_postdata();
                                        echo '</ul>';
                                    else :
                                        ?>
                                        <ul>
                                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Srinagar City</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Chattabal</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Bemina</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Tengpora</a></li>
                                            <li><a href="<?php echo esc_url(home_url('/contact/')); ?>">Rajbagh &amp; Lal Chowk</a></li>
                                        </ul>
                                        <?php
                                    endif;
                                }
                                ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="footer-lower">
                    <div class="container">
                        <div class="lower-footer-wrap">
                            <div class="row align-items-center">
                                <div class="col-lg-6 col-12">
                                    <p class="copyright">Made with &copy; <?php echo date('Y'); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>. All rights reserved.</p>
                                </div>
                                <div class="col-lg-6 col-12 text-lg-end text-center mt-2 mt-lg-0">
                                    <p class="copyright mb-0">Designed &amp; Developed by <a href="https://www.webassets.tech" target="_blank" rel="noopener noreferrer" style="color: #3860D2; font-weight: 600;">WebAssets</a></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </footer>
        <!-- end footer -->

    </div>
    <!-- end of page-wrapper -->

    <?php wp_footer(); ?>
    
    <!-- All JavaScript files -->
    <!-- Enqueueing Scripts Manually per static template -->
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/jquery.min.js"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/modernizr.custom.js"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/jquery-plugin-collection.js"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/gsap-script.js"></script>
    <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/js/script.js?v=<?php echo file_exists(get_template_directory() . '/assets/js/script.js') ? filemtime(get_template_directory() . '/assets/js/script.js') : '2.1'; ?>"></script>

</body>
</html>
