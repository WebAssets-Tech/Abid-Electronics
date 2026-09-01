<?php
/**
 * The template for displaying single Team Member details
 *
 * @package WebAssets
 */

get_header(); 
?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2><?php the_title(); ?></h2>
                    <h3>Specialist Profile</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- .team-pg-area start -->
<div class="team-pg-area section-padding">
    <div class="container">
        <div class="team-single-wrap">
            <div class="team-info-wrap mb-5">
                <div class="row align-items-center">
                    <div class="col-lg-5 col-12 mb-4 mb-lg-0">
                        <div class="team-info-img">
                            <?php if (has_post_thumbnail()) : ?>
                                <?php the_post_thumbnail('full', ['class' => 'img-fluid rounded-4 w-100 shadow-sm']); ?>
                            <?php else : ?>
                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/at-single.jpg" alt="<?php the_title_attribute(); ?>" class="img-fluid rounded-4 w-100 shadow-sm">
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-lg-7 col-12">
                        <div class="team-info-text ps-lg-4">
                            <h2><?php the_title(); ?></h2>
                            <?php
                            $designation   = get_post_meta(get_the_ID(), '_team_designation', true);
                            $practice_area = get_post_meta(get_the_ID(), '_team_practice_area', true);
                            $experience    = get_post_meta(get_the_ID(), '_team_experience', true);
                            $phone         = get_post_meta(get_the_ID(), '_team_phone', true);
                            $email         = get_post_meta(get_the_ID(), '_team_email', true);

                            if (empty($phone)) {
                                $phone = get_theme_mod('contact_phone', '+91 9622917697');
                            }
                            if (empty($email)) {
                                $email = get_theme_mod('contact_email', 'support@abidelectronics.com');
                            }
                            ?>
                            <ul class="list-unstyled mb-4">
                                <?php if (!empty($designation)) : ?>
                                    <li class="mb-2"><strong>Position:</strong> <span><?php echo esc_html($designation); ?></span></li>
                                <?php endif; ?>
                                <?php if (!empty($practice_area)) : ?>
                                    <li class="mb-2"><strong>Practice Area:</strong> <span><?php echo esc_html($practice_area); ?></span></li>
                                <?php endif; ?>
                                <?php if (!empty($experience)) : ?>
                                    <li class="mb-2"><strong>Experience:</strong> <span><?php echo esc_html($experience); ?></span></li>
                                <?php endif; ?>
                                <li class="mb-2"><strong>Service Location:</strong> <span>Srinagar &amp; Adjacent Areas, Kashmir</span></li>
                                <li class="mb-2"><strong>Direct Helpline:</strong> <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>"><?php echo esc_html($phone); ?></a></li>
                                <li class="mb-2"><strong>Email Support:</strong> <a href="mailto:<?php echo esc_attr($email); ?>"><?php echo esc_html($email); ?></a></li>
                            </ul>

                            <div class="team-booking-action mt-4">
                                <a href="<?php echo esc_url(home_url('/appointment/')); ?>" class="theme-btn-s2 me-3">Book Doorstep Service</a>
                                <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="btn btn-outline-primary px-4 py-2" style="border-radius: 50px; font-weight: 500;">Call Technician</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Technician Biography / Experience Overview -->
            <div class="team-bio-wrap pt-4 border-top">
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="entry-content">
                            <h3>About <?php the_title(); ?></h3>
                            <?php
                            while (have_posts()) :
                                the_post();
                                $content = get_the_content();
                                if (!empty(trim($content))) {
                                    the_content();
                                } else {
                                    ?>
                                    <p><?php the_title(); ?> is an integral member of the Abid Electronics technical engineering team, specializing in multi-brand on-site diagnosis and repair across Srinagar.</p>
                                    <p>With extensive hands-on experience in component-level fault resolution, refrigerant management, motor drive restoration, and micro-PCB circuitry, they adhere to rigorous safety and factory tolerances for every doorstep visit.</p>
                                    <?php
                                }
                            endwhile;
                            ?>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                        <div class="p-4 rounded-4 shadow-sm" style="background: #F3F5FC;">
                            <h4 class="mb-3" style="font-size: 18px;">Why Choose Our Specialists?</h4>
                            <ul class="list-unstyled mb-0" style="line-height: 2;">
                                <li><i class="ti-check text-primary me-2"></i> Verified Background &amp; ID</li>
                                <li><i class="ti-check text-primary me-2"></i> Factory Trained on Top Brands</li>
                                <li><i class="ti-check text-primary me-2"></i> 100% Genuine Spare Parts</li>
                                <li><i class="ti-check text-primary me-2"></i> Post-Repair Warranty Included</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- .team-pg-area end -->

<?php get_footer(); ?>
