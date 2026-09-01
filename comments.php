<?php
/**
 * The template for displaying comments
 *
 * @package WebAssets
 */

if (post_password_required()) {
    return;
}

if (!function_exists('webassets_custom_comment')) {
    function webassets_custom_comment($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        ?>
        <li <?php comment_class('comment'); ?> id="comment-<?php comment_ID(); ?>">
            <div id="div-comment-<?php comment_ID(); ?>">
                <div class="comment-theme">
                    <div class="comment-image">
                        <?php echo get_avatar($comment, 65, '', esc_attr(get_comment_author()), ['class' => 'img-fluid rounded-circle']); ?>
                    </div>
                </div>
                <div class="comment-main-area">
                    <div class="comment-wrapper">
                        <div class="comments-meta">
                            <h4>
                                <?php comment_author(); ?>
                                <span class="comments-date"><?php comment_date('j F Y'); ?> at <?php comment_time(); ?></span>
                            </h4>
                        </div>
                        <div class="comment-area">
                            <?php if ($comment->comment_approved == '0') : ?>
                                <p class="text-muted fst-italic">Your comment is awaiting moderation.</p>
                            <?php endif; ?>
                            <?php comment_text(); ?>
                            <div class="comments-reply mt-2">
                                <?php 
                                comment_reply_link(array_merge($args, [
                                    'depth'      => $depth,
                                    'max_depth'  => $args['max_depth'],
                                    'reply_text' => '<i class="ti-back-right"></i> Reply'
                                ])); 
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php
    }
}
?>

<div id="comments" class="comments-area mt-5">
    <?php if (have_comments()) : ?>
        <div class="comments-section">
            <h3 class="comments-title">
                <?php
                $comment_count = get_comments_number();
                if ('1' === $comment_count) {
                    printf(esc_html__('1 Comment', 'webassets'));
                } else {
                    printf(
                        /* translators: 1: comment count number. */
                        esc_html(_nx('%1$s Comment', '%1$s Comments', $comment_count, 'comments title', 'webassets')),
                        number_format_i18n($comment_count)
                    );
                }
                ?>
            </h3>

            <ol class="comments">
                <?php
                wp_list_comments([
                    'style'       => 'ol',
                    'short_ping'  => true,
                    'callback'    => 'webassets_custom_comment',
                ]);
                ?>
            </ol>

            <?php if (get_comment_pages_count() > 1 && get_option('page_comments')) : ?>
                <nav class="comment-navigation pagination-wrapper my-4">
                    <div class="nav-previous"><?php previous_comments_link(__('&larr; Older Comments', 'webassets')); ?></div>
                    <div class="nav-next"><?php next_comments_link(__('Newer Comments &rarr;', 'webassets')); ?></div>
                </nav>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!comments_open() && get_comments_number() && post_type_supports(get_post_type(), 'comments')) : ?>
        <p class="no-comments alert alert-info mt-4"><?php esc_html_e('Comments are closed.', 'webassets'); ?></p>
    <?php endif; ?>

    <?php
    $commenter     = wp_get_current_commenter();
    $req           = get_option('require_name_email');
    $aria_req      = ($req ? " aria-required='true' required" : '');
    $consent       = empty($commenter['comment_author_email']) ? '' : ' checked="checked"';

    $fields = [
        'author' => '<div class="row"><div class="col-md-6 col-12 mb-3">' .
                    '<input id="author" name="author" class="form-control" type="text" value="' . esc_attr($commenter['comment_author']) . '" placeholder="' . esc_attr__('Your Name*', 'webassets') . '"' . $aria_req . ' />' .
                    '</div>',
        'email'  => '<div class="col-md-6 col-12 mb-3">' .
                    '<input id="email" name="email" class="form-control" type="email" value="' . esc_attr($commenter['comment_author_email']) . '" placeholder="' . esc_attr__('Your Email*', 'webassets') . '"' . $aria_req . ' />' .
                    '</div></div>',
        'url'    => '<div class="mb-3">' .
                    '<input id="url" name="url" class="form-control" type="url" value="' . esc_attr($commenter['comment_author_url']) . '" placeholder="' . esc_attr__('Website', 'webassets') . '" />' .
                    '</div>',
        'cookies' => '<div class="terms mb-3">' .
                     '<input class="checkbox" type="checkbox" id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" value="yes"' . $consent . ' /> ' .
                     '<label for="wp-comment-cookies-consent">' . esc_html__('Save my name, email, and website in this browser for the next time I comment.', 'webassets') . '</label>' .
                     '</div>',
    ];

    comment_form([
        'title_reply'          => esc_html__('Write your comment', 'webassets'),
        'title_reply_to'       => esc_html__('Leave a Reply to %s', 'webassets'),
        'title_reply_before'   => '<h3 id="reply-title" class="comment-reply-title mb-4">',
        'title_reply_after'    => '</h3>',
        'class_form'           => 'comment-form comment-respond',
        'class_submit'         => 'theme-btn-s2',
        'label_submit'         => esc_html__('Send Message', 'webassets'),
        'submit_button'        => '<div class="form-submit mt-3"><input name="%1$s" type="submit" id="%2$s" class="%3$s" value="%4$s" /></div>',
        'comment_field'        => '<div class="form-textarea mb-3">' .
                                  '<textarea id="comment" name="comment" class="form-control" rows="5" placeholder="' . esc_attr__('Enter your Message*', 'webassets') . '" required="required"></textarea>' .
                                  '</div>',
        'fields'               => $fields,
    ]);
    ?>
</div>
