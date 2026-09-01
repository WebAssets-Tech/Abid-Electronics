<?php
/**
 * The template for displaying 404 pages (not found)
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
                    <h2>Page Not Found</h2>
                    <h3>Error 404</h3>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end of breadcumb-->

<!-- start error-404-section -->
<section class="error-404-section section-padding">
    <div class="container">
        <div class="row">
            <div class="col col-xs-12 text-center">
                <div class="content clearfix">
                    <div class="error mb-4">
                        <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/404.png" alt="404 Error" class="img-fluid" style="max-width: 500px;">
                    </div>
                    <div class="error-message">
                        <h2 class="mb-3">Oops! We can't find that page.</h2>
                        <p class="text-muted mb-4" style="max-width: 550px; margin: 0 auto 25px;">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable. Try searching below or return to the homepage.</p>
                        
                        <div class="d-flex justify-content-center mb-4">
                            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="max-width: 420px; width: 100%;">
                                <div class="input-group">
                                    <input type="text" class="form-control py-2 px-3" placeholder="Search for appliance services..." name="s">
                                    <button class="btn btn-primary px-4" type="submit"><i class="ti-search"></i> Search</button>
                                </div>
                            </form>
                        </div>

                        <a href="<?php echo esc_url(home_url('/')); ?>" class="theme-btn-s2">Back to Home</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- end error-404-section -->

<?php get_footer(); ?>
