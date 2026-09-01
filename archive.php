<?php
/**
 * The archive template for categories, tags, dates, and authors
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
                    <h2><?php the_archive_title(); ?></h2>
                    <h3><?php the_archive_description(); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start blog-pg-section -->
<section class="blog-pg-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col col-lg-8 col-md-12 col-12">
                <div class="blog-content">
                    <?php if (have_posts()) : 
                        $blog_idx = 0;
                        while (have_posts()) : the_post(); 
                            $blog_idx++;
                            $fallback_img_num = (($blog_idx - 1) % 6) + 1;
                            ?>
                            <article id="post-<?php the_ID(); ?>" <?php post_class('post format-standard-image mb-5'); ?>>
                                <div class="entry-media">
                                    <?php if (has_post_thumbnail()) : ?>
                                        <a href="<?php the_permalink(); ?>">
                                            <?php the_post_thumbnail('full', ['class' => 'img-fluid w-100 rounded-3', 'alt' => get_the_title()]); ?>
                                        </a>
                                    <?php else : ?>
                                        <a href="<?php the_permalink(); ?>">
                                            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/blog/img-<?php echo esc_attr($fallback_img_num); ?>.jpg" alt="<?php the_title_attribute(); ?>" class="img-fluid w-100 rounded-3">
                                        </a>
                                    <?php endif; ?>
                                    <span><?php echo get_the_date('d'); ?> <br> <?php echo get_the_date('M'); ?></span>
                                </div>
                                <div class="entry-meta">
                                    <ul>
                                        <li><i class="fi flaticon-user"></i> <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>"><?php the_author(); ?></a></li>
                                        <li><i class="fi ti-comment-alt"></i> <a href="<?php comments_link(); ?>"><?php comments_number('0 Comments', '1 Comment', '% Comments'); ?></a></li>
                                        <li><i class="fi flaticon-calendar"></i> <?php echo get_the_date(); ?></li>
                                    </ul>
                                </div>
                                <div class="entry-details">
                                    <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                                    <p><?php echo wp_trim_words(get_the_excerpt(), 28, '...'); ?></p>
                                    <a href="<?php the_permalink(); ?>" class="read-more">Read More <i class="ti-arrow-right"></i></a>
                                </div>
                            </article>
                        <?php endwhile; ?>

                        <!-- Pagination -->
                        <div class="pagination-wrapper pagination-wrapper-left my-4">
                            <?php
                            the_posts_pagination([
                                'mid_size'  => 2,
                                'prev_text' => '<i class="ti-arrow-left"></i>',
                                'next_text' => '<i class="ti-arrow-right"></i>',
                            ]);
                            ?>
                        </div>

                    <?php else : ?>
                        <div class="no-posts-found py-5">
                            <h3>No Articles Found</h3>
                            <p>There are no posts published in this archive currently.</p>
                        </div>
                    <?php endif; ?>
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
<!-- end blog-pg-section -->

<?php get_footer(); ?>
