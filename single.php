<?php
/**
 * The template for displaying all single blog posts
 *
 * @package WebAssets
 */

get_header(); ?>

<!-- start of breadcumb -->
<div class="breadcumb-area">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="breadcumb-wrap">
                    <h2><?php the_title(); ?></h2>
                    <h3>Article Details</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start blog-single-section -->
<section class="blog-single-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col col-lg-8 col-md-12 col-12">
                <div class="blog-content">
                    <?php while (have_posts()) : the_post(); ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class('post format-standard-image'); ?>>
                            <div class="entry-media mb-4">
                                <?php if (has_post_thumbnail()) : ?>
                                    <?php the_post_thumbnail('full', ['class' => 'img-fluid w-100 rounded-4 shadow-sm', 'alt' => get_the_title()]); ?>
                                <?php else : ?>
                                    <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/blog-details/img-1.jpg" alt="<?php the_title_attribute(); ?>" class="img-fluid w-100 rounded-4 shadow-sm">
                                <?php endif; ?>
                            </div>

                            <div class="entry-meta mb-3">
                                <ul class="d-flex flex-wrap list-unstyled gap-3 text-muted">
                                    <li><i class="fi flaticon-calendar me-1"></i> <?php echo get_the_date(); ?></li>
                                    <li><i class="fi flaticon-user me-1"></i> <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a></li>
                                    <li><i class="fi ti-comment-alt me-1"></i> <a href="#comments"><?php comments_number('0 Comments', '1 Comment', '% Comments'); ?></a></li>
                                </ul>
                            </div>

                            <div class="entry-details mb-5">
                                <h2 class="mb-4"><?php the_title(); ?></h2>
                                <div class="entry-body" style="font-size: 17px; line-height: 1.8; color: #444;">
                                    <?php the_content(); ?>
                                </div>
                            </div>

                            <!-- Post Tags -->
                            <?php if (has_tag()) : ?>
                                <div class="tag-share-s2 py-3 border-top border-bottom my-4">
                                    <div class="tag d-flex align-items-center flex-wrap gap-2">
                                        <span class="fw-bold me-2"><i class="ti-tag me-1"></i> Tags:</span>
                                        <?php the_tags('', ' ', ''); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Post Pagination Links (Next / Prev) -->
                            <div class="more-posts my-4 p-4 rounded-3" style="background: #F3F5FC;">
                                <div class="row align-items-center">
                                    <div class="col-6">
                                        <?php 
                                        $prev_post = get_previous_post();
                                        if (!empty($prev_post)) : ?>
                                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" class="text-decoration-none">
                                                <small class="text-muted d-block"><i class="ti-arrow-left"></i> Previous Post</small>
                                                <strong><?php echo esc_html(wp_trim_words($prev_post->post_title, 6)); ?></strong>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-6 text-end">
                                        <?php 
                                        $next_post = get_next_post();
                                        if (!empty($next_post)) : ?>
                                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" class="text-decoration-none">
                                                <small class="text-muted d-block">Next Post <i class="ti-arrow-right"></i></small>
                                                <strong><?php echo esc_html(wp_trim_words($next_post->post_title, 6)); ?></strong>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Comments Template -->
                            <?php
                            if (comments_open() || get_comments_number()) {
                                comments_template();
                            }
                            ?>
                        </article>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Blog Sidebar -->
            <div class="col col-lg-4 col-md-12 col-12">
                <div class="blog-sidebar">
                    <!-- Search Widget -->
                    <div class="widget search-widget">
                        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
                            <div>
                                <input type="text" class="form-control" placeholder="Search articles..." name="s" value="<?php echo get_search_query(); ?>">
                                <button type="submit"><i class="ti-search"></i></button>
                            </div>
                        </form>
                    </div>

                    <!-- Categories Widget -->
                    <div class="widget category-widget">
                        <h3>Categories</h3>
                        <ul>
                            <?php 
                            wp_list_categories([
                                'title_li'    => '',
                                'show_count'  => true,
                                'hide_empty'  => false,
                            ]); 
                            ?>
                        </ul>
                    </div>

                    <!-- Recent Posts Widget -->
                    <div class="widget recent-post-widget">
                        <h3>Recent Articles</h3>
                        <div class="posts">
                            <?php
                            $recent = new WP_Query(['posts_per_page' => 4, 'post_status' => 'publish']);
                            if ($recent->have_posts()) :
                                while ($recent->have_posts()) : $recent->the_post();
                                    ?>
                                    <div class="post">
                                        <div class="img-holder">
                                            <?php if (has_post_thumbnail()) : ?>
                                                <?php the_post_thumbnail('thumbnail'); ?>
                                            <?php else : ?>
                                                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/recent-posts/img-1.jpg" alt="<?php the_title_attribute(); ?>">
                                            <?php endif; ?>
                                        </div>
                                        <div class="details">
                                            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
                                            <span class="date"><?php echo get_the_date(); ?></span>
                                        </div>
                                    </div>
                                    <?php
                                endwhile;
                                wp_reset_postdata();
                            endif;
                            ?>
                        </div>
                    </div>

                    <!-- Emergency Assistance Banner -->
                    <div class="widget emergency-card p-4 rounded-3 text-center" style="background: #3860D2; color: #fff;">
                        <h4 class="text-white mb-2">Need Immediate Doorstep Repair?</h4>
                        <p class="text-white-50 mb-3 fs-6">Srinagar's leading technicians for refrigerators, washing machines, and ACs.</p>
                        <?php $phone = get_theme_mod('contact_phone', '+91 9622917697'); ?>
                        <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $phone)); ?>" class="btn btn-light rounded-pill px-4 py-2 font-weight-bold">
                            <i class="ti-mobile me-1"></i> <?php echo esc_html($phone); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end blog-single-section -->

<?php get_footer(); ?>
