<?php
/* Template Name: Team Page */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2>Expert Technicians &amp; Engineers</h2>
                    <h3>Our Team</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!--start team-section -->
<section class="wpo-team-section style-2 section-padding">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-6 col-12 text-center mb-5">
                <div class="wpo-section-title">
                    <span>Our Specialists</span>
                    <h2 class="poort-text poort-in-right">Meet Our Certified Appliance Repair Team</h2>
                    <p>Trained, background-verified, and certified technicians delivering prompt doorstep repair across Srinagar.</p>
                </div>
            </div>
        </div>
        <div class="team-wrap">
            <div class="row">
                <?php
                $team_query = new WP_Query([
                    'post_type'      => 'team',
                    'posts_per_page' => -1,
                    'order'          => 'ASC'
                ]);

                $member_idx = 0;
                if ($team_query->have_posts()) :
                    while ($team_query->have_posts()) : $team_query->the_post();
                        $member_idx++;
                        $delay = (($member_idx - 1) % 4 == 0) ? '1000ms' : ((($member_idx - 1) % 4 == 1) ? '1200ms' : ((($member_idx - 1) % 4 == 2) ? '1400ms' : '1600ms'));
                        $designation = get_post_meta(get_the_ID(), '_team_designation', true);
                        if (empty($designation)) {
                            $designation = 'Senior Technician';
                        }
                        $fb = get_post_meta(get_the_ID(), '_team_facebook', true);
                        $tw = get_post_meta(get_the_ID(), '_team_twitter', true);
                        $ig = get_post_meta(get_the_ID(), '_team_instagram', true);
                        $ln = get_post_meta(get_the_ID(), '_team_linkedin', true);
                        
                        $fallback_img = get_template_directory_uri() . '/assets/images/teams/' . ((($member_idx - 1) % 4) + 1) . '.jpg';
                        ?>
                        <div class="col-lg-3 col-sm-6 col-12 mb-4">
                            <div class="team-card wow fadeInUp" data-wow-duration="<?php echo esc_attr($delay); ?>">
                                <div class="image">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('full', ['alt' => get_the_title()]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url($fallback_img); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php endif; ?>
                                    <ul class="social-links">
                                        <?php if (!empty($fb)) : ?><li><a href="<?php echo esc_url($fb); ?>" target="_blank"><i class="ti-facebook"></i></a></li><?php endif; ?>
                                        <?php if (!empty($tw)) : ?><li><a href="<?php echo esc_url($tw); ?>" target="_blank"><i class="ti-twitter-alt"></i></a></li><?php endif; ?>
                                        <?php if (!empty($ig)) : ?><li><a href="<?php echo esc_url($ig); ?>" target="_blank"><i class="ti-instagram"></i></a></li><?php endif; ?>
                                        <?php if (!empty($ln)) : ?><li><a href="<?php echo esc_url($ln); ?>" target="_blank"><i class="ti-linkedin"></i></a></li><?php endif; ?>
                                        <?php if (empty($fb) && empty($tw) && empty($ig) && empty($ln)) : ?>
                                            <li><a href="tel:<?php echo esc_attr(get_theme_mod('contact_phone', '+919622917697')); ?>"><i class="ti-mobile"></i></a></li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                                <div class="text">
                                    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                                    <span><?php echo esc_html($designation); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="col-12 text-center py-5">
                        <p>No team members listed yet. Add technicians under Team Members in WordPress admin.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!--end team-section -->

<?php get_footer(); ?>
