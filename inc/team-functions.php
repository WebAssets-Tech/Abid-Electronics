<?php
/**
 * Team Custom Post Type and Meta Fields
 *
 * @package WebAssets
 */

// Register Team CPT
function webassets_team_post_type() {
    register_post_type('team', [
        'labels' => [
            'name'          => __('Team Members', 'webassets'),
            'singular_name' => __('Team Member', 'webassets'),
            'add_new'       => __('Add New Member', 'webassets'),
            'edit_item'     => __('Edit Team Member', 'webassets'),
            'new_item'      => __('New Team Member', 'webassets'),
            'all_items'     => __('All Team Members', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor', 'thumbnail'],
        'has_archive' => true,
        'menu_icon'   => 'dashicons-groups',
        'rewrite'     => ['slug' => 'team'],
    ]);
}
add_action('init', 'webassets_team_post_type');

// Meta Box for Team Details
function webassets_team_meta_boxes() {
    add_meta_box(
        'team_details_meta',
        __('Technician Details & Social Links', 'webassets'),
        'webassets_team_meta_callback',
        'team',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'webassets_team_meta_boxes');

function webassets_team_meta_callback($post) {
    wp_nonce_field('webassets_save_team_meta', 'team_meta_nonce');

    $designation   = get_post_meta($post->ID, '_team_designation', true);
    $practice_area = get_post_meta($post->ID, '_team_practice_area', true);
    $experience    = get_post_meta($post->ID, '_team_experience', true);
    $phone         = get_post_meta($post->ID, '_team_phone', true);
    $email         = get_post_meta($post->ID, '_team_email', true);
    $facebook      = get_post_meta($post->ID, '_team_facebook', true);
    $twitter       = get_post_meta($post->ID, '_team_twitter', true);
    $instagram     = get_post_meta($post->ID, '_team_instagram', true);
    $linkedin      = get_post_meta($post->ID, '_team_linkedin', true);
    ?>
    <table class="form-table" style="width: 100%;">
        <tr>
            <th><label for="team_designation"><?php _e('Designation / Role', 'webassets'); ?></label></th>
            <td><input type="text" id="team_designation" name="team_designation" value="<?php echo esc_attr($designation); ?>" class="regular-text" placeholder="e.g. Master Appliance Engineer" /></td>
        </tr>
        <tr>
            <th><label for="team_practice_area"><?php _e('Practice Area / Specialization', 'webassets'); ?></label></th>
            <td><input type="text" id="team_practice_area" name="team_practice_area" value="<?php echo esc_attr($practice_area); ?>" class="regular-text" placeholder="e.g. Refrigerators & Washing Machines" /></td>
        </tr>
        <tr>
            <th><label for="team_experience"><?php _e('Experience', 'webassets'); ?></label></th>
            <td><input type="text" id="team_experience" name="team_experience" value="<?php echo esc_attr($experience); ?>" class="regular-text" placeholder="e.g. 10+ Years" /></td>
        </tr>
        <tr>
            <th><label for="team_phone"><?php _e('Direct Phone', 'webassets'); ?></label></th>
            <td><input type="text" id="team_phone" name="team_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" placeholder="+91 9622917697" /></td>
        </tr>
        <tr>
            <th><label for="team_email"><?php _e('Direct Email', 'webassets'); ?></label></th>
            <td><input type="email" id="team_email" name="team_email" value="<?php echo esc_attr($email); ?>" class="regular-text" placeholder="technician@abidelectronics.com" /></td>
        </tr>
        <tr>
            <th><label for="team_facebook"><?php _e('Facebook Profile URL', 'webassets'); ?></label></th>
            <td><input type="url" id="team_facebook" name="team_facebook" value="<?php echo esc_attr($facebook); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="team_twitter"><?php _e('Twitter / X Profile URL', 'webassets'); ?></label></th>
            <td><input type="url" id="team_twitter" name="team_twitter" value="<?php echo esc_attr($twitter); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="team_instagram"><?php _e('Instagram Profile URL', 'webassets'); ?></label></th>
            <td><input type="url" id="team_instagram" name="team_instagram" value="<?php echo esc_attr($instagram); ?>" class="regular-text" /></td>
        </tr>
        <tr>
            <th><label for="team_linkedin"><?php _e('LinkedIn Profile URL', 'webassets'); ?></label></th>
            <td><input type="url" id="team_linkedin" name="team_linkedin" value="<?php echo esc_attr($linkedin); ?>" class="regular-text" /></td>
        </tr>
    </table>
    <?php
}

function webassets_save_team_meta($post_id) {
    if (!isset($_POST['team_meta_nonce']) || !wp_verify_nonce($_POST['team_meta_nonce'], 'webassets_save_team_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    $fields = [
        'team_designation'   => '_team_designation',
        'team_practice_area' => '_team_practice_area',
        'team_experience'    => '_team_experience',
        'team_phone'         => '_team_phone',
        'team_email'         => '_team_email',
        'team_facebook'      => '_team_facebook',
        'team_twitter'       => '_team_twitter',
        'team_instagram'     => '_team_instagram',
        'team_linkedin'      => '_team_linkedin',
    ];

    foreach ($fields as $input_name => $meta_key) {
        if (isset($_POST[$input_name])) {
            $value = ($input_name === 'team_email') ? sanitize_email($_POST[$input_name]) : sanitize_text_field($_POST[$input_name]);
            update_post_meta($post_id, $meta_key, $value);
        }
    }
}
add_action('save_post_team', 'webassets_save_team_meta');

// Auto-seed initial team members if none exist
function webassets_seed_team_members() {
    if (!is_admin()) {
        return;
    }

    $existing = get_posts([
        'post_type'      => 'team',
        'posts_per_page' => 1,
        'post_status'    => 'any',
    ]);

    if (empty($existing)) {
        $seed_members = [
            [
                'title'       => 'Abid Nazir',
                'designation' => 'Founder & Lead Appliance Engineer',
                'area'        => 'Multi-Brand Diagnostics & Inverter Systems',
                'exp'         => '15+ Years',
                'phone'       => '+91 9622917697',
                'email'       => 'abid@abidelectronics.com',
                'bio'         => 'With over 15 years of hands-on expertise across Srinagar, Abid oversees all complex motherboard, inverter compressor, and multi-brand diagnostic repairs, ensuring factory-standard doorstep solutions.',
            ],
            [
                'title'       => 'Mohammad Rafiq',
                'designation' => 'Senior Refrigerator & Cooling Specialist',
                'area'        => 'Single & Double Door Refrigerators, Deep Freezers',
                'exp'         => '12+ Years',
                'phone'       => '+91 9622917697',
                'email'       => 'support@abidelectronics.com',
                'bio'         => 'Specializing in cryogenic gas recharging, thermostat calibration, and defrost circuit diagnostics, Rafiq delivers high-precision on-site cooling fixes across all Srinagar areas.',
            ],
            [
                'title'       => 'Tariq Ahmad',
                'designation' => 'Washing Machine & Motor Specialist',
                'area'        => 'Front Load, Top Load & Semi-Automatic Washers',
                'exp'         => '9+ Years',
                'phone'       => '+91 9622917697',
                'email'       => 'support@abidelectronics.com',
                'bio'         => 'Tariq has diagnosed and rebuilt hundreds of drive motors, drum bearings, drain pumps, and balance sensors for LG, Samsung, Whirlpool, and IFB washing machines.',
            ],
            [
                'title'       => 'Zahid Nazir',
                'designation' => 'Electronics & Micro-PCB Technician',
                'area'        => 'Control Boards, Microwave Ovens & Inverter AC Units',
                'exp'         => '8+ Years',
                'phone'       => '+91 9622917697',
                'email'       => 'support@abidelectronics.com',
                'bio'         => 'An electronics wizard adept at component-level micro-soldering, relay replacements, and inverter board restoration to save customers from costly full-unit replacements.',
            ],
        ];

        foreach ($seed_members as $member) {
            $post_id = wp_insert_post([
                'post_title'   => $member['title'],
                'post_content' => $member['bio'],
                'post_status'  => 'publish',
                'post_type'    => 'team',
            ]);

            if ($post_id && !is_wp_error($post_id)) {
                update_post_meta($post_id, '_team_designation', $member['designation']);
                update_post_meta($post_id, '_team_practice_area', $member['area']);
                update_post_meta($post_id, '_team_experience', $member['exp']);
                update_post_meta($post_id, '_team_phone', $member['phone']);
                update_post_meta($post_id, '_team_email', $member['email']);
            }
        }
    }
}
add_action('admin_init', 'webassets_seed_team_members');
