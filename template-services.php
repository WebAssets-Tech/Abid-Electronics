<?php
/* Template Name: All Services */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2>Multi-Brand Appliance Repair Services</h2>
                    <h3>Our Services</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!--start service card / box section-->
<section class="wpo-service-section-s2 section-padding">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-12">
                <div class="wpo-section-title text-center">
                    <span><i><img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/title-icon-2.png" alt="icon"></i><?php echo esc_html(get_theme_mod('services_label', 'Our Services')); ?></span>
                    <h2 class="poort-text poort-in-right"><?php echo esc_html(get_theme_mod('services_title', 'Multi-Brand Appliance Repair Tailored for You')); ?></h2>
                    <p><?php echo esc_html(get_theme_mod('services_desc', 'We provide comprehensive repair and maintenance for all your home and commercial appliances, with genuine parts and same-day doorstep service across Srinagar.')); ?></p>
                </div>
            </div>
        </div>
        <div class="row">
            <?php
            $services_query = new WP_Query([
                'post_type'      => 'services',
                'posts_per_page' => -1,
                'order'          => 'ASC'
            ]);
            $service_idx = 0;
            if ($services_query->have_posts()) :
                while ($services_query->have_posts()) : $services_query->the_post();
                    $service_idx++;
                    $delay = (($service_idx - 1) % 3 == 0) ? '1000ms' : ((($service_idx - 1) % 3 == 1) ? '1200ms' : '1400ms');
                    $active_class = (($service_idx - 1) % 3 == 1) ? ' active' : '';
                    ?>
                    <div class="col-lg-4 col-md-6 col-12 mb-4">
                        <div class="service-wrap<?php echo $active_class; ?> wow fadeInUp" data-wow-duration="<?php echo esc_attr($delay); ?>">
                            <div class="service-item">
                                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                <p><?php echo wp_trim_words(get_the_excerpt(), 14, '...'); ?></p>
                            </div>
                            <div class="image">
                                <?php 
                                if (has_post_thumbnail()) {
                                    the_post_thumbnail('full', ['alt' => get_the_title()]);
                                } else {
                                    $fallback_num = (($service_idx - 1) % 4) + 2; // images 2, 3, 4, 5
                                    ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/image-<?php echo $fallback_num; ?>.jpg" alt="<?php the_title_attribute(); ?>">
                                    <?php
                                }
                                ?>
                            </div>
                            <div class="icon">
                                <a href="<?php the_permalink(); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/arrow-2.svg" alt="icon">
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php
                endwhile;
                wp_reset_postdata();
            else :
                ?>
                <div class="col-12 text-center py-5">
                    <p>No services found. Please publish services in WordPress Admin.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<!--End service-->

<?php get_footer(); ?>
