<?php
/**
 * The template for displaying all pages
 *
 * This is the template that displays all pages by default.
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
                    <h3><?php echo esc_html(get_bloginfo('name')); ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start page content -->
<section class="wpo-page-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 col-12">
                <div class="wpo-page-content">
                    <?php
                    while ( have_posts() ) :
                        the_post();
                        ?>
                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <div class="entry-content">
                                <?php
                                the_content();

                                wp_link_pages([
                                    'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'webassets' ),
                                    'after'  => '</div>',
                                ]);
                                ?>
                            </div>
                        </article>
                        <?php
                        if ( comments_open() || get_comments_number() ) :
                            comments_template();
                        endif;
                    endwhile;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end page content -->

<?php get_footer(); ?>
