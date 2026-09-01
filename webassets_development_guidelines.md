# WebAssets Theme Development Guidelines (SOP)

## 1. Introduction & Core Philosophy
Welcome to the WebAssets Theme Development Team. This document serves as our definitive Standard Operating Procedure (SOP) for developing custom WordPress themes. 

> [!IMPORTANT]
> **Our Signature Website Making Style:**
> We do **not** use page builders like Elementor, WPBakery, or Divi under any circumstances. 
> Our methodology relies entirely on taking a beautiful, pre-designed static HTML template and converting it into a lightning-fast, fully dynamic, and highly customizable WordPress theme using core WordPress functions, Custom Post Types (CPTs), Custom Taxonomies, and the Theme Customizer.

**The Workflow:**
As a developer at WebAssets, you will be handed an HTML/CSS/JS folder containing static files (e.g., `index.html`, `about.html`, `portfolio.html`). Your job is to transform these static HTML files into dynamic PHP templates seamlessly. The site administrator must be given full control over the content from the native WordPress dashboard without touching code or using a heavy frontend builder.

---

## 2. Global Architecture & Folder Structure

When you create a new theme for WebAssets, you must adhere to a strict folder and file structure. This ensures absolute consistency across all our projects, allowing any team member to pick up where another left off.

### Core File Structure:
```text
/wp-content/themes/WebAssets-Project/
│
├── assets/                 # All CSS, JS, Images, Fonts mapped directly from the static HTML folder
├── inc/                    # Modularized backend functions (CPTs, Logic)
│   ├── brand-functions.php
│   ├── industry-functions.php
│   ├── portfolio-functions.php
│   ├── service-functions.php
│   └── ...
│
├── style.css               # Required WordPress Theme metadata and global styles
├── screenshot.png          # Required Theme Cover Image (1200x900px)
├── functions.php           # The central nervous system of the theme
├── wp_bootstrap_navwalker.php # Required file for Bootstrap dropdown menus
│
├── header.php              # Global Header (Menu, Logo, Topbar)
├── footer.php              # Global Footer (Widgets, Copyright, Footer Logo)
├── sidebar.php             # Global Sidebar fallback
│
├── index.php               # Fallback template / Blog home body
├── front-page.php          # Homepage template (optional, or use page templates)
├── page.php                # Default static page template layout
├── single.php              # Default single blog post layout
├── 404.php                 # Global "Not Found" error page
│
├── archive.php             # Archive loop template for categories/tags/CPTs
├── category.php            # Category specific loop template
├── author.php              # Author specific loop template
├── search.php              # Search results loop template
├── comments.php            # Native WordPress Comments loop and form
│
└── Custom Page Templates:
    ├── aboutus.php         # Template Name: About Us
    ├── contact.php         # Template Name: Contact
    ├── page-portfolio.php  # Template Name: Portfolio
    ├── page-pricing.php    # Template Name: Pricing
    ├── single-portfolio.php# Single layout for Portfolio CPT
    └── single-services.php # Single layout for Services CPT
```

---

## 3. Initial Theme Setup: `style.css` & `screenshot.png`

Every WebAssets theme must correctly identify itself to WordPress. This is handled by two specific files in the root directory.

### 1. `style.css` (The Theme Metadata)
At the very top of `style.css`, you MUST include the following metadata block. This tells WordPress the name of the theme and the author. Do not remove this comment block, or the theme will break.

```css
/*
Theme Name: WebAssets [Client Name]
Author: Zahid Nazir
Version: 1.0.0
Description: This is a multipurpose theme developed by Zahid Nazir for building websites for different niches without using heavy page builders. This theme is developed using HTML, CSS, Bootstrap, Tailwind CSS, JavaScript, PHP, and WordPress.
Author URI: https://www.webassets.tech
*/

/* Don't remove the upper comment. It's the metadata of the theme without which the theme won't work */
/*================================================
Theme Styles Below
==================================================*/
@charset "UTF-8";

/* Add global WordPress resets here, or import the static HTML stylesheet */
```

### 2. `screenshot.png` (The Theme Cover)
You must create a `screenshot.png` file and place it in the root directory. 
- **Dimensions:** 1200px by 900px.
- **Content:** This should be a high-quality mockup or screenshot of the homepage design. This image is what the client sees in the *Appearance > Themes* dashboard.

---

## 4. Backend Strategy: `functions.php`

The `functions.php` file is the brain of our themes. Because we don't use Elementor, this file handles everything from registering menus and adding theme support to initializing security protocols.

> [!CAUTION]
> **The Golden Rule:** Every theme we build must follow a universally same pattern for `functions.php`. The Custom Site Logo, Footer Logo, Contact Emails, Phones, Addresses, and Social Media links must be editable from the *Appearance > Customize* menu.

### 4.1 Theme Setup, Menus & Nav Walker Integration
Before you add Customizer settings, you must tell WordPress that your theme supports standard features (logos, thumbnails, title tags) and register the menus.

```php
<?php
// Add Nav Walker for Bootstrap Menu Support
function register_navwalker() {
    require_once get_template_directory() . '/wp_bootstrap_navwalker.php';
}
add_action('after_setup_theme', 'register_navwalker');

// Registering standard menus
register_nav_menus([
    "header-menu"   => "Header Menu",
    "footer-menu"   => "Footer Menu",
    "company-links" => "Company links",
    "footer-strip-links" => "Footer Strip Links",
]);

function theme_customization() {
    // Support for site logo
    add_theme_support('custom-logo', [
        "height"      => 100,
        "width"       => 100,
        "flex-height" => true,
        "flex-width"  => true,
        "header-text" => ["site-title", "site-description"],
    ]);

    // Core Supports
    add_theme_support('menus');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');

    // Add Support for Custom Backgrounds
    add_theme_support('custom-background', [
        'default-color' => 'FFF',
        'default-image' => get_template_directory_uri() . '/assets/images/bg.jpg',
    ]);
}
add_action('after_setup_theme', 'theme_customization');
```

### 4.2 The "Golden Customizer" Block
You MUST copy and paste the exact code below into every new `functions.php` file. This code registers the standard fields we use across all agency websites.

```php
// ✅ Add Custom Footer Logo Support Using Customizer API
function webassets_customize_register($wp_customize)
{
    // 1. Footer Logo Section
    $wp_customize->add_section('footer_logo_section', [
        'title'    => __('Footer Logo', 'webassets'),
        'priority' => 30,
    ]);

    $wp_customize->add_setting('footer_logo', [
        'capability'        => 'edit_theme_options',
        'sanitize_callback' => 'absint', // Ensures the input is an ID
        'type'              => 'theme_mod',
    ]);

    $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'footer_logo', [
        'label'     => __('Upload Footer Logo', 'webassets'),
        'section'   => 'footer_logo_section',
        'settings'  => 'footer_logo',
        'mime_type' => 'image',
    ]));

    // 2. Contact Email
    $wp_customize->add_setting('contact_email', [
        'default'           => 'info@example.com',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('contact_email', [
        'label'   => __('Contact Email', 'webassets'),
        'section' => 'title_tagline', // Placed in Site Identity section
        'type'    => 'email',
    ]);

    // 3. Contact Phone
    $wp_customize->add_setting('contact_phone', [
        'default'           => '123-456-7890',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_phone', [
        'label'   => __('Contact Phone', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 4. Secondary Contact Phone
    $wp_customize->add_setting('contact_phone_sec', [
        'default'           => '',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_phone_sec', [
        'label'   => __('Secondary Contact Phone', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 5. Contact Address
    $wp_customize->add_setting('contact_address', [
        'default'           => '123 Main St, Anytown, USA',
        'sanitize_callback' => 'sanitize_text_field',
    ]);
    $wp_customize->add_control('contact_address', [
        'label'   => __('Contact Address', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'text',
    ]);

    // 6. Google Map Link
    $wp_customize->add_setting('google_map_link', [
        'default'           => 'https://g.co/kgs/ANjbN5N',
        'sanitize_callback' => 'esc_url_raw',
    ]);
    $wp_customize->add_control('google_map_link', [
        'label'   => __('Google Map Link', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'url',
    ]);

    // 7. Another Email (Support)
    $wp_customize->add_setting('contact_email_2', [
        'default'           => 'support@example.com',
        'sanitize_callback' => 'sanitize_email',
    ]);
    $wp_customize->add_control('contact_email_2', [
        'label'   => __('Another Email', 'webassets'),
        'section' => 'title_tagline',
        'type'    => 'email',
    ]);

    // 8. Social Media Links
    $socials = [
        'instagram' => 'Instagram URL',
        'twitter'   => 'Twitter URL',
        'whatsapp'  => 'WhatsApp URL',
        'facebook'  => 'Facebook URL'
    ];

    foreach ($socials as $key => $label) {
        $wp_customize->add_setting('social_' . $key, [
            'default'           => '#',
            'sanitize_callback' => 'esc_url_raw',
        ]);
        $wp_customize->add_control('social_' . $key, [
            'label'   => __($label, 'webassets'),
            'section' => 'title_tagline',
            'type'    => 'url',
        ]);
    }
}
add_action('customize_register', 'webassets_customize_register');

// ✅ Helper Function to Display Footer Logo on the frontend
function get_footer_logo()
{
    $footer_logo_id = get_theme_mod('footer_logo');
    if ($footer_logo_id) {
        return wp_get_attachment_image_url($footer_logo_id, 'full');
    }
    return false;
}
```

### 4.3 Standard Sidebar Registrations
Our HTML templates usually have multiple widget areas (Main Sidebar, About Us Sidebar, Footer Columns, and Topbar Contact details). Register them using a standard function:

```php
function webassets_sidebar_registration() {
    register_sidebar([
        "name"          => "Main Sidebar",
        "id"            => "sidebar",
        "before_widget" => "<div class='widget'>",
        "after_widget"  => "</div>",
        "before_title"  => "<h2 class='widget-title'>",
        "after_title"   => "</h2>",
    ]);

    // Register Footer Widget Areas (1 through 4)
    for ($i = 1; $i <= 4; $i++) {
        register_sidebar([
            'name'          => __('Footer ' . $i, 'webassets'),
            'id'            => 'footer-' . $i,
            'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
            'after_widget'  => '</div>',
            'before_title'  => '<h3>',
            'after_title'   => '</h3>',
        ]);
    }
}
add_action("widgets_init", "webassets_sidebar_registration");
```

### 4.4 Security & Comment Spam Prevention
To prevent bot spam, we universally apply Google ReCAPTCHA to our comment forms and explicitly disable pingbacks and anonymous REST API comments.

```php
// Add Google ReCAPTCHA to Comment Form
add_filter('comment_form_submit_field', 'webassets_add_recaptcha_to_submit_field', 10, 2);
function webassets_add_recaptcha_to_submit_field($submit_field, $args) {
    $recaptcha_html = '<div class="form-input" style="margin-bottom: 20px;">
        <div class="g-recaptcha" data-sitekey="YOUR_SITE_KEY"></div>
    </div>';
    return $recaptcha_html . $submit_field;
}

// Disable REST API anonymous comments
add_filter('rest_allow_anonymous_comments', '__return_false');

// Disable XML-RPC pingbacks
add_filter('xmlrpc_methods', function($methods) {
    unset($methods['pingback.ping']);
    return $methods;
});
```

### 4.5 Modularizing Proprietary Code & CPTs (`/inc/` Directory)
Never write all your Custom Post Types directly into `functions.php`. Modularize them into the `/inc/` folder. This also applies to our proprietary agency modules like the **Website Planner Engine**, **AI Assistant**, and **Licensing Logic**. 

```php
// Including Proprietary Modules
require_once get_template_directory() . '/inc/licensing/init.php';
require get_template_directory() . '/inc/website-planner/wp-functions.php';
// ... Include other planner modules
if (file_exists(get_template_directory() . '/webassets-ai-assistant/wordpress-integration.php')) {
    require_once get_template_directory() . '/webassets-ai-assistant/wordpress-integration.php';
}

// Including standard CPTs
$inc_files = [
    '/inc/portfolio-functions.php',
    '/inc/service-functions.php',
    '/inc/team-functions.php'
];

foreach ($inc_files as $file) {
    if (file_exists(get_template_directory() . $file)) {
        require_once get_template_directory() . $file;
    }
}
```

---

## 5. Frontend Strategy: Slicing the HTML Correctly

### `header.php`
- **Scope:** From `<!DOCTYPE html>` up to the end of the visual header `<header>` section.
- **Critical Inclusions:**
  - `<?php wp_head(); ?>` right before `</head>`.
- **Nav Walker Usage:** Instead of hardcoding the `<ul>` for the menu, call the Bootstrap Nav Walker we registered in step 4.1:
  ```php
  <nav id="mobile-menu">
      <?php
      wp_nav_menu([
          'theme_location' => 'header-menu',
          'container' => false,
          'menu_class' => '',
          'fallback_cb' => 'wp_bootstrap_navwalker::fallback',
          'walker' => new wp_bootstrap_navwalker(),
      ]);
      ?>
  </nav>
  ```
- **Applying Customizer Variables:**
  Instead of hardcoding the email address from the HTML template, echo the setting we registered in `functions.php`:
  ```php
  <!-- HTML Topbar -->
  <a href="mailto:<?php echo esc_attr(get_theme_mod('contact_email', 'info@example.com')); ?>">
      <?php echo esc_html(get_theme_mod('contact_email', 'info@example.com')); ?>
  </a>
  ```

### `footer.php`
- **Scope:** From the start of the `<footer>` tag to `</html>`.
- **Critical Inclusions:** 
  - `<?php wp_footer(); ?>` right before `</body>`.
- **Applying the Footer Logo:**
  ```php
  <div class="footer-logo">
      <?php 
      $footer_logo_url = get_footer_logo();
      if ($footer_logo_url) {
          echo '<img src="' . esc_url($footer_logo_url) . '" alt="Footer Logo">';
      }
      ?>
  </div>
  ```

### `index.php` / Page Templates
Files like `index.php`, `page.php`, and custom templates represent the "meat" of the page.
- Always begin with `<?php get_header(); ?>`.
- Always end with `<?php get_footer(); ?>`.

---

## 6. Dynamic Content: Creating Custom Post Types (CPTs)

Whenever you see a repeating element in the HTML mockup (e.g., a list of services, a grid of team members, or a portfolio gallery), **you must create a Custom Post Type for it.**

### Example: Creating the "Portfolio" CPT (`/inc/portfolio-functions.php`)
```php
<?php
function portfolio_post_type() {
    register_post_type('portfolio', [
        'labels'      => [
            'name'          => __('Portfolio', 'webassets'),
            'singular_name' => __('Portfolio Item', 'webassets'),
            'add_new'       => __('Add New Project', 'webassets'),
            'edit_item'     => __('Edit Project', 'webassets'),
        ],
        'public'      => true,
        'supports'    => ['title', 'editor', 'thumbnail', 'custom-fields', 'comments'],
        'has_archive' => true,
        'taxonomies'  => ['portfolio_category'],
        'menu_icon'   => 'dashicons-portfolio',
        'rewrite'     => ['slug' => 'portfolio'],
    ]);
}
add_action('init', 'portfolio_post_type');
```

### Displaying the CPT on the Frontend
If the HTML homepage has a grid of 6 portfolio items, replace the hardcoded static blocks with a `WP_Query` loop:

```php
<div class="portfolio-grid">
    <?php
    $portfolio_args = [
        'post_type'      => 'portfolio',
        'post_status'    => 'publish',
        'posts_per_page' => 6,
    ];
    $portfolio_query = new WP_Query($portfolio_args);

    if ($portfolio_query->have_posts()) :
        while ($portfolio_query->have_posts()) : $portfolio_query->the_post(); ?>
            
            <div class="portfolio-item">
                <div class="portfolio-image">
                    <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } ?>
                </div>
                <h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
                <p><?php echo wp_trim_words(get_the_content(), 15, '...'); ?></p>
            </div>

        <?php endwhile; wp_reset_postdata(); 
    else: ?>
        <p>No projects found.</p>
    <?php endif; ?>
</div>
```

---

## 7. Execution Plan (For The Developer)

1. **Analyze the Static HTML:** Identify Header, Footer, and repeating grids (Services, Testimonials).
2. **Setup WordPress Theme Structure:** Create `style.css` (with the mandatory header comment), `screenshot.png` (1200x900px), and `functions.php`.
3. **Insert the Golden Blocks:** Copy-paste the Golden Customizer Code, Theme Supports, Menu Registrations, ReCAPTCHA logic, and Sidebar Registration code into `functions.php`.
4. **Slice the Files:** Extract HTML into `header.php`, `footer.php`, `index.php`, `page.php`, and `single.php`.
5. **Implement Navigation:** Use `wp_bootstrap_navwalker.php` to render the dynamic menus in `header.php`.
6. **Connect Assets:** Safely replace hardcoded paths in `header.php` with `<?php echo get_template_directory_uri(); ?>/assets/css/...` or enqueue them via functions.
7. **Apply Customizer Data:** In your `header.php` and `footer.php`, replace static emails/phones/socials with `get_theme_mod('contact_email')`, etc.
8. **Modularize CPTs:** Create your `inc/` folder. Register Post Types for Services, Team, Testimonials, etc.
9. **Build Single Templates:** Create `single-portfolio.php`, `single-services.php` to handle individual CPT pages.
10. **Build Archive & Custom Templates:** Create `archive.php`, `category.php`, `aboutus.php` and wrap them in `get_header();` and `get_footer();`. Loop through your data using `WP_Query`.

By adhering strictly to this SOP, we maintain exact code uniformity across the agency. If you are handed an HTML template, you now know exactly how to structure the engine behind it.
