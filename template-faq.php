<?php
/* Template Name: FAQ Page */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2>Help &amp; Frequent Questions</h2>
                    <h3>Frequently Asked Questions</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- Start wpo-faq-section -->
<section class="wpo-faq-section style-2 section-padding">
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
                        <a href="<?php echo esc_url(home_url('/appointment/')); ?>" class="theme-btn-s2 me-3">Book An Appointment</a>
                        <a href="<?php echo esc_url(home_url('/contact/')); ?>" class="btn btn-outline-primary px-4 py-2" style="border-radius: 50px; font-weight: 500;">Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-6 col-12">
                    <div class="wpo-faq-items wow fadeInRightSlow" data-wow-duration="1000ms">
                        <div class="accordion" id="accordionExample">
                            <?php
                            $faq_query = new WP_Query([
                                'post_type'      => 'faq',
                                'posts_per_page' => -1,
                                'order'          => 'ASC'
                            ]);

                            $faq_idx = 0;
                            if ($faq_query->have_posts()) :
                                while ($faq_query->have_posts()) : $faq_query->the_post();
                                    $faq_idx++;
                                    $is_first = ($faq_idx === 1);
                                    $collapse_id = 'faqCollapse' . $faq_idx;
                                    $heading_id  = 'faqHeading' . $faq_idx;
                                    ?>
                                    <div class="accordion-item mb-3">
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
                            else :
                                ?>
                                <div class="accordion-item">
                                    <div class="accordion-body">
                                        <p>No FAQ items published yet. Please add questions under FAQs in WordPress Admin.</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- End wpo-faq-section -->

<?php get_footer(); ?>
