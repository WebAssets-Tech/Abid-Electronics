<?php
/* Template Name: Work Gallery */
get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2>Repair Gallery &amp; Recent Work</h2>
                    <h3>Work Gallery</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start wpo-project-section -->
<section class="wpo-project-section-s2 section-padding">
    <div class="wpo-project-wrap">
        <div class="container">
            <div class="row justify-content-center mb-4">
                <div class="col-lg-8 col-12 text-center">
                    <div class="wpo-section-title">
                        <span>Our Work Gallery</span>
                        <h2 class="poort-text poort-in-right">Recent Appliance Repairs Across Srinagar</h2>
                        <p>Take a look at our on-site doorstep repairs for refrigerators, washing machines, ACs, and geysers across Kashmir.</p>
                    </div>
                </div>
            </div>
            <div class="project-wrap">
                <?php
                $gallery_query = new WP_Query([
                    'post_type'      => 'work_gallery',
                    'posts_per_page' => -1,
                    'order'          => 'DESC'
                ]);

                $item_idx = 0;
                if ($gallery_query->have_posts()) :
                    while ($gallery_query->have_posts()) : $gallery_query->the_post();
                        $item_idx++;
                        $media_type = get_post_meta(get_the_ID(), '_gallery_media_type', true);
                        $video_url  = get_post_meta(get_the_ID(), '_gallery_video_url', true);
                        $is_video   = ($media_type === 'video' && !empty($video_url));
                        $full_img_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'full') : (get_template_directory_uri() . '/assets/images/project/' . (($item_idx % 8) + 1) . '.jpg');
                        $target_url = $is_video ? $video_url : $full_img_url;
                        $btn_class  = $is_video ? 'video-btn d-block' : 'fancybox d-block';
                        $group_attr = $is_video ? '' : 'data-fancybox-group="gallery"';
                        ?>
                        <div class="project-items">
                            <a href="<?php echo esc_url($target_url); ?>" class="<?php echo esc_attr($btn_class); ?>" <?php echo $group_attr; ?> title="<?php the_title_attribute(); ?>">
                                <div class="project-image">
                                    <?php if ($is_video) : ?>
                                        <span class="gallery-video-badge"><i class="ti-video-clapper"></i> Video</span>
                                        <div class="gallery-play-btn"><i class="ti-control-play"></i></div>
                                    <?php endif; ?>

                                    <?php if (has_post_thumbnail()) : ?>
                                        <?php the_post_thumbnail('full', ['alt' => get_the_title()]); ?>
                                    <?php else : ?>
                                        <img src="<?php echo esc_url($full_img_url); ?>" alt="<?php the_title_attribute(); ?>">
                                    <?php endif; ?>
                                    
                                    <div class="project-text">
                                        <h3><?php the_title(); ?></h3>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                    ?>
                    <div class="col-12 text-center py-5">
                        <p>No gallery images uploaded yet. Please add images under Work Gallery in the admin dashboard.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<!-- end of wpo-project-section -->

<?php get_footer(); ?>
