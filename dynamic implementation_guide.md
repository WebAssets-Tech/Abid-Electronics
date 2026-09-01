# Abid Electronics — Full WordPress Theme Implementation Guide
### How We Converted a Static HTML Template into a Fully Dynamic WordPress Theme

---

> [!IMPORTANT]
> This document is the **definitive implementation record** for the Abid Electronics WordPress theme project (`WebAssets` theme). It covers every step taken, every mistake made, every problem encountered, and every fix applied — in exact chronological and procedural order. Future developers must read this before touching any file.

---

## Part 1: The Starting Point — What We Were Given

### The Raw Material

We received a pre-designed static HTML template for **Abid Electronics**, a multi-brand home appliance repair shop in Srinagar, Kashmir. The template consisted of:

- `index-static.html` — the homepage (the primary source of truth for the homepage layout)
- `about.html`, `contact.html`, `service.html`, `blog.html`, `faq.html`, etc. — inner pages
- An `/assets/` folder containing all CSS, JS, images, fonts, and icon libraries

This was a **pure HTML/CSS/JS template** — completely static. Nothing was connected to a database, nothing was dynamic. All content (text, images, links) was hardcoded directly into the HTML files.

### The Goal

Transform this static template into a **fully dynamic WordPress theme** where:

1. **The Site Administrator** can edit all homepage content from the WordPress **Appearance → Customize** panel without touching code.
2. **Repeating content** (Services, FAQ, Testimonials, Blog Posts, Brand Partners, Work Gallery) is managed through the WordPress **Dashboard** using **Custom Post Types (CPTs)**.
3. **Every link, phone number, address, and image path** is editable — nothing hardcoded.
4. The final result must look **visually identical** to the static HTML template on the frontend.

### The Development Guidelines (SOP)

The client had a `webassets_development_guidelines.md` file in the theme root. This was the agency's Standard Operating Procedure — the law. Key rules:

- ❌ **No page builders** (no Elementor, WPBakery, Divi).
- ✅ **Convert static HTML → dynamic PHP** using core WordPress functions.
- ✅ Use **Custom Post Types** for all repeating elements.
- ✅ Use the **Theme Customizer** (`get_theme_mod()`) for all editable non-repeating content.
- ✅ All CPT registrations go in `/inc/` files loaded via a loop in `functions.php`.
- ✅ `index.php` is the **single source of truth** for the homepage. Inline `WP_Query` loops directly in `index.php`.
- ✅ No one-off script files dumped in the theme root.

---

## Part 2: Theme Architecture — What Files Exist and Why

### Final File Structure (Post-Implementation)

```
/wp-content/themes/WebAssets/
│
├── assets/                         ← Static CSS, JS, images from HTML template (unchanged)
│
├── inc/                            ← All CPT registrations + homepage Customizer
│   ├── brand-functions.php         ← Partner Brands CPT
│   ├── faq-functions.php           ← FAQ CPT
│   ├── homepage-customizer.php     ← ALL homepage Customizer settings (sections, settings, controls)
│   ├── location-functions.php      ← Locations CPT (future use)
│   ├── service-functions.php       ← Services CPT + meta boxes for icon URL and price
│   ├── testimonial-functions.php   ← Testimonials CPT + meta box for designation
│   └── work-gallery-functions.php  ← Work Gallery CPT
│
├── style.css                       ← WordPress theme metadata header + global CSS imports
├── screenshot.png                  ← Theme thumbnail shown in WP Appearance panel (1200×900)
├── functions.php                   ← Theme brain: loads inc/ files, registers menus, enqueues assets
│
├── header.php                      ← Global header: DOCTYPE → end of <header> tag
├── footer.php                      ← Global footer: start of <footer> → </html>
├── sidebar.php                     ← Fallback sidebar (required by WordPress)
│
├── index.php                       ← THE HOMEPAGE. All sections inline. WP_Query loops inline.
├── page.php                        ← Default static page template
├── single.php                      ← Default single blog post layout
├── single-services.php             ← Single Service CPT page template
├── template-contact.php            ← Contact page template (Template Name: Contact)
│
├── wp_bootstrap_navwalker.php      ← Bootstrap 5 nav walker for dropdown menus
├── mail-contact.php                ← Contact form email handler (AJAX)
│
├── index-static.html               ← KEEP THIS. The original static HTML. Reference always.
└── webassets_development_guidelines.md ← SOP document. Read before touching anything.
```

> [!NOTE]
> The `.html` pages (about.html, contact.html, etc.) in the root are the original template files. They are NOT served by WordPress. They are reference documents only. Do not delete them.

---

## Part 3: Step-by-Step — What We Actually Did

### Step 1: Analyse the Static HTML (`index-static.html`)

The first step was a thorough audit of `index-static.html` to identify every section and classify it as either:

- **Customizer-controlled** — a single piece of content that an admin would edit occasionally (e.g., a headline, phone number, button label).
- **CPT-controlled** — a repeating element that needs to be a database record (e.g., services, testimonials).

**Homepage Sections Identified:**

| Section | Type | Method |
|---|---|---|
| Hero topbar (phone, WhatsApp, Book link) | Customizer | `get_theme_mod()` |
| Hero headline & subheadline | Customizer | `get_theme_mod()` |
| Hero CTA buttons | Customizer | `get_theme_mod()` |
| About section cards | Customizer | `get_theme_mod()` |
| Marquee strip | Customizer | `get_theme_mod()` |
| Services cards | CPT | `WP_Query` on `services` CPT |
| Why Choose Us feature cards | Customizer | `get_theme_mod()` (6 items, manageable in Customizer) |
| Video section | Customizer | `get_theme_mod()` |
| Stats/Odometer | Customizer | `get_theme_mod()` |
| Work Gallery | CPT | `WP_Query` on `work_gallery` CPT |
| How It Works (Process) | Customizer | `get_theme_mod()` (3 static steps) |
| FAQ | CPT | `WP_Query` on `faq` CPT |
| Brand Partners | CPT | `WP_Query` on `partner_brand` CPT |
| Testimonials | CPT | `WP_Query` on `testimonials` CPT |
| Bottom marquee | Customizer | `get_theme_mod()` |
| CTA section | Customizer | `get_theme_mod()` |
| Blog section | WP_Query | `WP_Query` on standard `post` type |

**Why did we put "Why Choose Us" in Customizer instead of a CPT?**

There are exactly 6 feature cards in the static HTML, they don't have complex data, and the client is unlikely to add a 7th. CPTs are better when the count is unknown or large. For small, fixed-count things, Customizer fields are cleaner.

---

### Step 2: Setting Up `style.css` and Theme Recognition

The mandatory WordPress theme header comment was placed at the top of `style.css`:

```css
/*
Theme Name: WebAssets
Author: Zahid Nazir
Version: 1.0.0
Description: Abid Electronics theme — multi-brand appliance repair, Srinagar.
Author URI: https://www.webassets.tech
*/
```

Without this, WordPress cannot recognize the folder as a theme. This is the **first thing** done on any new theme project.

---

### Step 3: Creating `functions.php` — The Theme Brain

`functions.php` was built following the "Golden Pattern" from the SOP:

1. **Theme Supports registered:**
   ```php
   add_theme_support('custom-logo');
   add_theme_support('title-tag');
   add_theme_support('post-thumbnails');
   ```

2. **Menus registered:** `header-menu`, `footer-menu`, `company-links`, `footer-strip-links`

3. **Assets enqueued:** All the template's CSS and JS files were enqueued via `wp_enqueue_scripts` using `get_template_directory_uri()` to generate the correct absolute URL. This is critical — never use a relative path for assets in WordPress themes.

4. **The Golden Customizer block** for global site settings (contact email, phone, address, social links, footer logo) was added.

5. **The `/inc/` loader loop** was added. This is the pattern that loads all CPT files:
   ```php
   $inc_files = [
       '/inc/service-functions.php',
       '/inc/faq-functions.php',
       '/inc/brand-functions.php',
       '/inc/testimonial-functions.php',
       '/inc/work-gallery-functions.php',
       '/inc/location-functions.php',
       '/inc/homepage-customizer.php',
   ];
   foreach ($inc_files as $file) {
       if (file_exists(get_template_directory() . $file)) {
           require_once get_template_directory() . $file;
       }
   }
   ```
   The `file_exists()` check prevents fatal PHP errors if a file is accidentally deleted.

---

### Step 4: Creating the Custom Post Types (CPTs)

Each CPT was created as a separate file in `/inc/`. The pattern for every CPT file is identical:

1. `register_post_type()` → registers the CPT
2. `register_taxonomy()` → optional, registers a category for the CPT
3. `add_meta_boxes()` → adds custom fields panel in the post editor
4. A callback function renders the meta box HTML
5. A `save_post` hook saves the meta field data to the database

**CPTs Created:**

#### `inc/service-functions.php` — Services CPT
- **Post Type Slug:** `services`
- **Dashboard Icon:** `dashicons-admin-tools`
- **Supports:** title, editor, thumbnail, custom-fields
- **Custom Meta Fields:**
  - `_service_icon` — URL of an SVG icon for the "pill" card (the collapsed state)
  - `_service_price` — starting price display
- **Why meta fields?** The "Why Choose Us" pill cards needed a *different* icon for each service. Rather than trying to get the icon from the featured image, we added a dedicated `_service_icon` URL meta field. In the absence of this, the code uses a position-based fallback array.

#### `inc/faq-functions.php` — FAQ CPT
- **Post Type Slug:** `faq`
- **Supports:** title, editor
- **The Title** = the question; **The Content** = the answer.

#### `inc/brand-functions.php` — Partner Brands CPT
- **Post Type Slug:** `partner_brand`
- **Supports:** title, thumbnail
- **The Featured Image** = the brand logo (e.g., LG, Samsung, Whirlpool)

#### `inc/testimonial-functions.php` — Testimonials CPT
- **Post Type Slug:** `testimonials`
- **Supports:** title, editor, thumbnail
- **Custom Meta Fields:**
  - `_testimonial_designation` — the reviewer's job title/designation
- **The Title** = reviewer name; **The Content** = the review text; **Thumbnail** = reviewer photo.

#### `inc/work-gallery-functions.php` — Work Gallery CPT
- **Post Type Slug:** `work_gallery`
- **Supports:** title, thumbnail
- **Purpose:** The project/portfolio carousel on the homepage. Each post = one project card with a featured image and title.

#### `inc/location-functions.php` — Locations CPT (Future Use)
- Created for potential future service area pages.

---

### Step 5: Slicing `index-static.html` into `index.php`

The `index-static.html` file was split:
- Everything from `<header>` upwards → `header.php`
- Everything from `<footer>` downwards → `footer.php`
- Everything in between → `index.php`

At the top of `index.php`: `<?php get_header(); ?>`
At the bottom: `<?php get_footer(); ?>`

This is the standard WordPress template structure. The `get_header()` and `get_footer()` calls pull in the dynamically generated header and footer with all enqueued scripts, menus, and Customizer variables.

---

### Step 6: Creating `inc/homepage-customizer.php`

This file contains the `homepage_customize_register()` function, hooked to `customize_register`. Every homepage section that is **not a CPT** gets its own Customizer section here.

**The Pattern for every Customizer setting:**

```php
// 1. Add a Section (the panel grouping)
$wp_customize->add_section('hero_section', [
    'title'    => __('Hero Section', 'webassets'),
    'priority' => 31,
]);

// 2. Add a Setting (the data container + sanitizer)
$wp_customize->add_setting('hero_headline', [
    'default'           => 'Default text here',
    'sanitize_callback' => 'sanitize_text_field', // ALWAYS sanitize!
]);

// 3. Add a Control (the UI widget in the Customizer panel)
$wp_customize->add_control('hero_headline', [
    'label'   => 'Headline',
    'section' => 'hero_section',
    'type'    => 'text',
]);
```

**For Image Uploads, the pattern is different:**
```php
$wp_customize->add_setting('cta_bg_image', [
    'default'           => '',
    'sanitize_callback' => 'esc_url_raw',
]);
$wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'cta_bg_image', [
    'label'    => 'Background Image',
    'section'  => 'cta_section',
    'settings' => 'cta_bg_image',
]));
```

**For Media (returning the attachment ID instead of URL):**
```php
$wp_customize->add_setting('about_card3_image', [
    'sanitize_callback' => 'absint', // absint because it stores an attachment ID
    'type'              => 'theme_mod',
]);
$wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'about_card3_image', [
    'label'     => 'Card 3 Image',
    'section'   => 'about_section',
    'mime_type' => 'image',
]));
// On the frontend, use wp_get_attachment_image($id, 'full') to render it
```

**Sections Registered in `homepage-customizer.php`:**

| Section ID | Panel Name | Priority |
|---|---|---|
| `hero_section` | Hero Section | 31 |
| `about_section` | About Section | 32 |
| `marquee_section` | Marquee Strips | 33 |
| `services_section_settings` | Services Section | 33.5 |
| `features_section` | Why Choose Us | 34 |
| `gallery_section` | Work Gallery | 34.5 |
| `process_section` | Process/History | 34.6 |
| `video_section` | Video Section | 35 |
| `stats_section` | Stats (Odometer) | 36 |
| `faq_section` | FAQ Section | 36.1 |
| `brands_section` | Brands Section | 36.2 |
| `blog_section_settings` | Blog Section | 37 |
| `cta_section` | Bottom CTA | 38 |

---

### Step 7: Making `index.php` Fully Dynamic (Section by Section)

This was the main body of work. Each section of `index-static.html` was converted.

#### 7.1 Hero Section

**Static HTML:**
```html
<li><a href="tel:+919622917697"><i class="flaticon-phone"></i>+91 9622917697</a></li>
<li><a href="https://wa.me/919622917697">WhatsApp Us</a></li>
<span>Srinagar's Most Trusted Multi-Brand Appliance Repair Hub.</span>
<h2>5-Star Rated, Same-Day Doorstep Service, All Brands & Appliances</h2>
<a href="contact.html" class="theme-btn-s2">Book An Appointment</a>
<a href="tel:+919622917697" class="theme-btn-s3">Call 9622917697</a>
```

**Dynamic PHP:**
```php
<li><a href="<?php echo esc_url(get_theme_mod('hero_phone_link', 'tel:+919622917697')); ?>">
    <?php echo esc_html(get_theme_mod('hero_phone', '+91 9622917697')); ?></a></li>
<li><a href="<?php echo esc_url(get_theme_mod('hero_whatsapp_link', 'https://wa.me/919622917697')); ?>">WhatsApp Us</a></li>
<span><?php echo esc_html(get_theme_mod('hero_headline', "Srinagar's Most Trusted...")); ?></span>
<h2><?php echo wp_kses_post(get_theme_mod('hero_subheadline', '5-Star Rated...')); ?></h2>
<a href="<?php echo esc_url(get_theme_mod('hero_btn1_link', 'contact.html')); ?>" class="theme-btn-s2">
    <?php echo esc_html(get_theme_mod('hero_btn1_text', 'Book An Appointment')); ?></a>
```

**Key Rule:** Always use `esc_url()` on URLs, `esc_html()` on plain text, `wp_kses_post()` on text that contains HTML tags like `<br>` or `<span>`.

#### 7.2 Marquee Section

The marquee had 3 `<h2>` items, duplicated in two `<div class="marquee">` containers (so the CSS infinite scroll animation works seamlessly — two copies create the illusion of a continuous loop).

**Problem:** The original Customizer had a single `marquee_text` field with a `|` separator, which was never connected to the actual `<h2>` elements.

**Fix:** Replaced with 3 individual `marquee_item_1`, `marquee_item_2`, `marquee_item_3` fields. The PHP loop reads all three and renders them inside BOTH marquee divs:

```php
<?php
$marquee_items = [];
for ($m = 1; $m <= 3; $m++) {
    $item = get_theme_mod("marquee_item_$m");
    if ($item) $marquee_items[] = $item;
}
if (empty($marquee_items)) {
    $marquee_items = ['Same-Day Doorstep Service Across Srinagar', 'Call 9622917697', "Srinagar's 5-Star Rated Appliance Repair Hub"];
}
?>
<div class="marquee">
    <?php foreach ($marquee_items as $m_item): ?>
        <h2><img src="...marquee-shape.png" alt="icon"> <?php echo esc_html($m_item); ?></h2>
    <?php endforeach; ?>
</div>
<div class="marquee"><!-- same loop again for seamless scroll animation --></div>
```

#### 7.3 Services Section (CPT Loop)

**Static HTML (6 hardcoded cards):**
```html
<div class="service-items">
    <div class="service-default">
        <div class="service-bg">
            <img src="assets/images/service/icon-1.svg"> <!-- different icon per card -->
            <h3>Refrigerator & Fridge Repair</h3>
        </div>
    </div>
    <div class="service-expanded">
        <img class="service-image" src="assets/images/service/2.jpg">
        <div class="service-content">
            <h4><a href="contact.html">Refrigerator & Fridge Repair</a></h4>
            <p>Doorstep fridge repair...</p>
        </div>
    </div>
</div>
```

**Dynamic PHP (CPT loop):**
```php
<?php
$services_query   = new WP_Query(['post_type' => 'services', 'posts_per_page' => -1, 'order' => 'ASC']);
$service_icon_map = [1 => 'icon-1.svg', 2 => 'icon-2.svg', 3 => 'icon-1.svg', 4 => 'icon-3.svg', 5 => 'icon-4.svg'];
$service_img_map  = [1 => '2.jpg', 2 => '3.jpg', 3 => '1.jpg', 4 => '4.jpg', 5 => '5.jpg'];
$service_durations = [1 => '1000ms', 2 => '1200ms', 3 => '1400ms', 4 => '1600ms', 5 => '1800ms'];
$service_idx = 0;

if ($services_query->have_posts()): while ($services_query->have_posts()): $services_query->the_post();
    $service_idx++;
    $meta_icon = get_post_meta(get_the_ID(), '_service_icon', true);
    $icon_url  = $meta_icon ?: get_template_directory_uri() . '/assets/images/service/' . ($service_icon_map[$service_idx] ?? 'icon-1.svg');
    $fallback_img_url = get_template_directory_uri() . '/assets/images/service/' . ($service_img_map[$service_idx] ?? '2.jpg');
    $active_cls = ($service_idx === 3) ? ' active' : '';
?>
    <div class="service-items<?php echo $active_cls; ?> wow fadeInUp" data-wow-duration="<?php echo $service_durations[$service_idx]; ?>">
        <div class="service-bg">
            <img src="<?php echo esc_url($icon_url); ?>">
            <h3><?php the_title(); ?></h3>
        </div>
        <div class="service-expanded">
            <?php if (has_post_thumbnail()) { the_post_thumbnail('full', ['class' => 'service-image']); } else { ?>
                <img class="service-image" src="<?php echo esc_url($fallback_img_url); ?>">
            <?php } ?>
            <h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
            <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
        </div>
    </div>
<?php endwhile; wp_reset_postdata(); endif; ?>
```

**Critical learnings:**
- Always call `wp_reset_postdata()` after every custom `WP_Query` loop. Failing to do this corrupts the global `$post` object and breaks every subsequent `the_title()`, `the_permalink()`, etc. on the page.
- The `active` class on service item 3 is from the original template design — it controls which service card is visually highlighted by default. We preserve this using `$service_idx === 3`.
- The `data-wow-duration` was hardcoded differently per card in the static HTML. We replicated this with the `$service_durations` position-indexed array.

#### 7.4 "Why Choose Us" Features Section

The 6 feature "pill" cards were previously 6 separate static `<div>` blocks. We replaced all 6 with a single PHP `for` loop:

```php
<?php for ($i = 1; $i <= 6; $i++):
    $f_text = get_theme_mod("feature_text_$i");
    $f_link = get_theme_mod("feature_link_$i", 'contact.html');
    $f_img  = get_theme_mod("feature_image_$i") ?: get_template_directory_uri() . "/assets/images/feature/$i.svg";
    if (!$f_text) { continue; } // skip if text is empty
?>
    <div class="feature-items">
        <div class="icon"><img src="<?php echo esc_url($f_img); ?>"></div>
        <div class="text"><h3><a href="<?php echo esc_url($f_link); ?>"><?php echo esc_html($f_text); ?></a></h3></div>
        <div class="arrow-icon">
            <a href="<?php echo esc_url($f_link); ?>">
                <img src=".../arrow-top.png" class="icon-active">
                <img src=".../arrow-top-hover.png" class="icon-hover">
            </a>
        </div>
    </div>
<?php endfor; ?>
```

The `continue` statement means if a Customizer field is left blank, the card is simply not rendered. This gives the admin the ability to disable individual cards.

#### 7.5 Stats (Odometer) Section

Same pattern — 4 hardcoded stat blocks replaced with a loop:

```php
<?php for ($i = 1; $i <= 4; $i++):
    $s_num = get_theme_mod("stat_number_$i");
    if (!$s_num) { continue; }
?>
    <div class="odometer-items" data-wow-delay="0.<?php echo $i - 1; ?>s">
        <h2>
            <span class="odometer" data-count="<?php echo esc_attr($s_num); ?>">
                <?php echo esc_html(str_repeat('0', strlen($s_num))); ?>
            </span>
            <span class="small"><?php echo esc_html(get_theme_mod("stat_suffix_$i", '+')); ?></span>
        </h2>
        <h3><?php echo esc_html(get_theme_mod("stat_label_$i", '')); ?></h3>
    </div>
<?php endfor; ?>
```

**Why `str_repeat('0', strlen($s_num))`?**
The Odometer.js library animates from the initial text content to the `data-count` value. The initial content should be the same number of digits as the target (all zeros). For `337`, the initial content is `000`. For `10`, it's `00`. This makes the animation look correct from the start.

#### 7.6 Work Gallery Section (CPT Loop)

```php
<?php
$work_query = new WP_Query(['post_type' => 'work_gallery', 'posts_per_page' => -1]);
if ($work_query->have_posts()): while ($work_query->have_posts()): $work_query->the_post();
?>
    <div class="project-card">
        <div class="image">
            <?php if (has_post_thumbnail()) { the_post_thumbnail('full'); } else { ?>
                <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/project/1.jpg">
            <?php } ?>
            <div class="content">
                <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            </div>
        </div>
    </div>
<?php endwhile; wp_reset_postdata(); endif; ?>
```

#### 7.7 "How It Works" (Process) Section

The static HTML had the wrong content here — it literally said "Since our inception in 2014, we have been dedicated to pioneering the electricity industry in **Japan**". This had nothing to do with Abid Electronics.

We replaced it completely with 3 Customizer-controlled steps:

```php
<?php
$process_defaults = ['Book a Service', 'Get a Free Estimate', 'Professional Work Execution'];
$durations = ['1000ms', '1200ms', '1400ms'];
for ($i = 1; $i <= 3; $i++):
    $step = get_theme_mod("process_step_$i", $process_defaults[$i - 1]);
?>
    <div class="history-item wow fadeInUp" data-wow-duration="<?php echo $durations[$i - 1]; ?>">
        <h2>0<?php echo $i; ?></h2>
        <div class="text"><h3><?php echo esc_html($step); ?></h3></div>
    </div>
<?php endfor; ?>
```

#### 7.8 FAQ Section (CPT Loop)

The FAQ uses WordPress's built-in Accordion pattern. The HTML template uses Bootstrap Accordion (`accordion-item`, `accordion-header`, `accordion-button`, `accordion-collapse`). The FAQ CPT title = question, content = answer.

```php
<?php
$faq_query = new WP_Query(['post_type' => 'faq', 'posts_per_page' => -1]);
if ($faq_query->have_posts()): $faq_count = 0; while ($faq_query->have_posts()): $faq_query->the_post();
    $faq_count++;
?>
    <div class="accordion-item">
        <h3 class="accordion-header" id="heading<?php echo $faq_count; ?>">
            <button class="accordion-button <?php echo $faq_count > 1 ? 'collapsed' : ''; ?>"
                type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $faq_count; ?>"
                aria-expanded="<?php echo $faq_count === 1 ? 'true' : 'false'; ?>"
                aria-controls="collapse<?php echo $faq_count; ?>">
                <?php the_title(); ?>
            </button>
        </h3>
        <div id="collapse<?php echo $faq_count; ?>" class="accordion-collapse collapse <?php echo $faq_count === 1 ? 'show' : ''; ?>">
            <div class="accordion-body">
                <?php the_content(); ?>
            </div>
        </div>
    </div>
<?php endwhile; wp_reset_postdata(); endif; ?>
```

**Note the `$faq_count` logic:** The first FAQ item must have `class="accordion-button"` (without `collapsed`) and its collapse div must have `class="show"` so that the first answer is visible by default. All subsequent items get `collapsed` + no `show`. This is a Bootstrap requirement.

#### 7.9 Brand Partners Section (CPT Loop)

```php
<?php
$brands_query = new WP_Query(['post_type' => 'partner_brand', 'posts_per_page' => -1]);
if ($brands_query->have_posts()): while ($brands_query->have_posts()): $brands_query->the_post();
?>
    <li>
        <?php if (has_post_thumbnail()) { the_post_thumbnail('full'); } else { ?>
            <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/brand-logo/1.png" alt="logo">
        <?php } ?>
    </li>
<?php endwhile; wp_reset_postdata(); endif; ?>
```

#### 7.10 Testimonials Section (CPT Loop, with Slick Slider)

The testimonials use a **synced slider** pattern (`.slider-for` + `.slider-nav`) where two separate Slick carousels are linked. The images (reviewer photos) are in the first carousel; the text content is in the second. This requires **two separate `WP_Query` loops** over the same CPT:

```php
<!-- Loop 1: Image carousel (slider-for) -->
<?php
$testi_query = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
if ($testi_query->have_posts()): while ($testi_query->have_posts()): $testi_query->the_post();
?>
    <div class="item">
        <?php if (has_post_thumbnail()) { the_post_thumbnail('full'); } ?>
        <ul><!-- 5 star icons --></ul>
    </div>
<?php endwhile; wp_reset_postdata(); endif; ?>

<!-- Loop 2: Content carousel (slider-nav) -->
<?php
$testi_query2 = new WP_Query(['post_type' => 'testimonials', 'posts_per_page' => -1]);
if ($testi_query2->have_posts()): while ($testi_query2->have_posts()): $testi_query2->the_post();
?>
    <div class="content">
        <p><?php echo wp_strip_all_tags(get_the_content()); ?></p>
        <h3><?php the_title(); ?></h3>
        <span><?php echo esc_html(get_post_meta(get_the_ID(), '_testimonial_designation', true)); ?></span>
    </div>
<?php endwhile; wp_reset_postdata(); endif; ?>
```

#### 7.11 Bottom CTA Section (Customizer + Dynamic Background Image)

The CTA section had a background image hardcoded in the CSS stylesheet. We moved it to be controlled via the Customizer using an inline style:

```php
<?php
$cta_bg    = get_theme_mod("cta_bg_image");
$cta_style = $cta_bg ? "style=\"background: url('" . esc_url($cta_bg) . "') no-repeat right; background-size: cover;\"" : "";
?>
<section class="wpo-cta-section" <?php echo $cta_style; ?>>
```

If no image is uploaded in the Customizer, `$cta_style` is empty and the CSS default applies. If the admin uploads an image, it overrides the CSS background.

#### 7.12 Blog Section (Standard `post` Query)

The blog section used completely wrong placeholder content in the static HTML — it showed "Innovations in Cardiac Care" and "The Importance of Regular Heart Screenings". We replaced both hardcoded cards with a real `WP_Query` on the standard WordPress `post` post type:

```php
<?php
$blog_query = new WP_Query(['post_type' => 'post', 'posts_per_page' => 2, 'post_status' => 'publish']);
$blog_cols  = ['col-lg-5', 'col-lg-7'];
$blog_anims = ['fadeInLeftSlow', 'fadeInRightSlow'];
$blog_idx   = 0;

if ($blog_query->have_posts()): while ($blog_query->have_posts()): $blog_query->the_post();
    $col  = $blog_cols[$blog_idx] ?? 'col-lg-6';
    $anim = $blog_anims[$blog_idx] ?? 'fadeInUp';
    $tag  = $blog_idx === 0 ? 'h2' : 'h3';
?>
    <div class="<?php echo $col; ?> col-md-12 col-12">
        <div class="blog-card wow <?php echo $anim; ?>" data-wow-duration="1000ms">
            <div class="blog-image">
                <?php if (has_post_thumbnail()) { the_post_thumbnail('large'); } else { ?>
                    <img src=".../assets/images/blog/img-<?php echo $blog_idx + 1; ?>.jpg">
                <?php } ?>
            </div>
            <div class="blog-text">
                <<?php echo $tag; ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $tag; ?>>
                <div class="blog-bottom">
                    <a class="more-btn" href="<?php the_permalink(); ?>">Learn More</a>
                </div>
            </div>
        </div>
    </div>
<?php $blog_idx++; endwhile; wp_reset_postdata(); endif; ?>
```

The layout uses the **asymmetric column pattern** from the static HTML (first card is `col-lg-5`, second is `col-lg-7`). The `$blog_idx` counter drives the correct column and animation class per card.

---

## Part 4: Database Seeding — Populating the CPTs with Initial Data

When the CPT loops were first added to `index.php`, the sections appeared completely empty because there were no posts in the database yet. To make the homepage look like the static HTML immediately, we needed to seed the database.

**What was seeded:**

| CPT | Posts Created | Details |
|---|---|---|
| Services (`services`) | 5 posts | Refrigerator Repair, Washing Machine Repair, AC Repair & Service, Geyser & Water Heater, Microwave & Commercial |
| Work Gallery (`work_gallery`) | 4 posts | Project 1–4 |
| FAQ (`faq`) | 5 posts | Standard appliance repair FAQ questions |
| Partner Brands (`partner_brand`) | 12 posts | LG, Samsung, Whirlpool, Godrej, Panasonic, Sony, Daikin, Haier, Onida, Videocon, IFB, Bosch |
| Testimonials (`testimonials`) | 2 posts | Two customer reviews |
| Blog (`post`) | 2 posts | Two appliance maintenance tip articles |

Seeding was done using a temporary PHP script that used `wp_insert_post()` and `update_post_meta()`. After seeding was confirmed, the script was deleted from the theme root.

> [!CAUTION]
> **Seed scripts must always be deleted after use.** A seeder script left in the theme root is a security vulnerability — anyone who knows the filename can trigger it and insert arbitrary data into your database.

---

## Part 5: Problems Encountered and How They Were Fixed

### Problem 1: `front-page.php` vs `index.php` Template Hierarchy Conflict

**What happened:** WordPress has a strict template hierarchy. If a file named `front-page.php` exists in the theme folder, WordPress uses it for the homepage instead of `index.php`. Early in development, a `front-page.php` was created. All dynamic PHP was then carefully added to `index.php` — but the browser kept loading the old, static `front-page.php`. The dynamic code had zero effect on the homepage.

**Fix:** The user correctly identified this and explicitly requested that `front-page.php` be deleted. Without `front-page.php`, WordPress falls through the hierarchy and uses `index.php` for the homepage.

**Rule for Future:** On this project, **`index.php` is the homepage template**. Do not create `front-page.php`. If a future project needs both a static homepage and a blog archive, use `front-page.php` for the homepage and `home.php` for the blog archive. But never have both serving the same purpose.

### Problem 2: Static Content Left Behind After Loop Conversion

**What happened:** When replacing static HTML blocks with dynamic PHP loops, the old hardcoded `<div>` blocks were sometimes not fully removed. This resulted in a page that showed both the dynamic CPT output AND the old static cards below it.

**Fix:** Perform a complete replacement — delete the entire static block, including its opening and closing `<div>` tags, and replace the whole thing with the PHP loop. Never append the loop below the static block.

### Problem 3: Hardcoded Placeholder Text from the HTML Template

The original HTML template was designed for a generic electrical company, not Abid Electronics. It contained placeholder text that had nothing to do with the actual business:

- "Since our inception in 2014, we have been dedicated to pioneering the electricity industry in **Japan**" (History section)
- "Innovations in **Cardiac Care**: What's Next for Heart Health?" (Blog section)
- "communication and utilizes cutting edge logistic planning to get your shipment completed on time" (Description placeholders everywhere)

**Fix:** Every piece of dummy content was replaced with business-appropriate defaults hardcoded into the `get_theme_mod()` calls (as the second `$default` parameter). The admin can then change these via the Customizer.

### Problem 4: Service Icons Were All the Same (`icon-1.svg`)

**What happened:** When the services loop was first written, all 6 service cards showed `icon-1.svg` because the code had `icon-1.svg` hardcoded instead of using a position-based lookup.

**Fix:** We added a `$service_icon_map` array keyed by position index, mapping each service to its corresponding SVG icon exactly as it appeared in `index-static.html`. We also added a `_service_icon` meta field to the Services CPT so the admin can upload a fully custom icon URL per service.

### Problem 5: The Odometer Section Was Still Static After "Fix"

**What happened:** An earlier attempt to make the stats dynamic had not properly connected the `get_theme_mod()` calls. The Customizer was registering the settings, but `index.php` still had hardcoded `data-count="10"`, `data-count="337"`, etc.

**Fix:** Replaced all 4 hardcoded stat blocks with the PHP `for` loop reading from `stat_number_$i`, `stat_suffix_$i`, and `stat_label_$i`.

### Problem 6: Marquee Items Were Not Connected

**What happened:** The original Customizer had a single `marquee_text` setting that stored all 3 items separated by `|`. But the `index.php` still had 3 hardcoded `<h2>` elements that never read from this setting.

**Fix:** Changed strategy entirely — replaced the single combined field with 3 individual Customizer fields (`marquee_item_1`, `marquee_item_2`, `marquee_item_3`). Updated `index.php` to build an array from these 3 fields and loop through it for both marquee strips.

### Problem 7: CTA Buttons Were Still `appoinment.html`

The original static HTML links all pointed to `appoinment.html` (a misspelling of "appointment"). This page doesn't exist in WordPress. Every "Book An Appointment" button was a broken 404 link.

**Fix:** All `appoinment.html` references were replaced with `get_theme_mod()` calls, with `contact.html` as the default. The admin can update these URLs through the Customizer to point to the proper WordPress contact page URL once it's created.

### Problem 8: Asset Paths Were Still Relative (`assets/images/...`)

**What happened:** The static HTML uses relative paths like `src="assets/images/service/icon-1.svg"`. In WordPress, these paths must be absolute, rooted at the theme directory.

**Fix:** All asset paths replaced with:
```php
get_template_directory_uri() . '/assets/images/service/icon-1.svg'
```

Or in HTML context:
```php
<?php echo esc_url(get_template_directory_uri()); ?>/assets/images/service/icon-1.svg
```

### Problem 9: 19 "Builder Script" Files in Theme Root

**What happened:** During early stages, a series of one-off PHP scripts were created in the theme root to perform tasks: `builder-service.php`, `builder-faq.php`, `builder-brands.php`, `seeder.php`, `add_fallbacks.php`, etc. Each one was a throwaway script.

**Why this is wrong:** These scripts clutter the theme root, can be accidentally triggered via the browser (security risk), and violate the agency SOP which states all code must be in `inc/` files or `index.php`.

**Fix:** All 19 builder scripts and temporary files were deleted. The `inc/` directory holds all permanent backend code. No one-off scripts in the theme root, ever.

### Problem 10: `wp_reset_postdata()` Accidentally Forgotten

**What happened:** After a custom `WP_Query` loop, if `wp_reset_postdata()` is not called, the global WordPress `$post` variable is left pointing to the last post from the custom query. Any subsequent template tag (`the_title()`, `the_permalink()`, `get_the_ID()`) on the page will return data from the wrong post.

**Fix:** Every single `WP_Query` loop follows this pattern without exception:
```php
if ($query->have_posts()): while ($query->have_posts()): $query->the_post();
    // ... template tags
endwhile; wp_reset_postdata(); endif;
```

---

## Part 6: What to Do (and Not Do) in Future Projects

### ✅ Always Do

1. **Read `index-static.html` first.** Map every section before writing a single line of PHP.
2. **Follow the CPT vs. Customizer decision rule:**
   - Repeating items with unknown count → CPT
   - Fixed-count editable text/links/images → Customizer
3. **Add `sanitize_callback` to every `add_setting()`:**
   - `sanitize_text_field` for single-line text
   - `sanitize_textarea_field` for multi-line text
   - `wp_kses_post` for HTML content
   - `esc_url_raw` for URLs
   - `sanitize_email` for emails
   - `absint` for attachment IDs (media controls)
4. **Always wrap every `new WP_Query()` loop with `wp_reset_postdata()`.**
5. **Always use `get_template_directory_uri()` for asset paths.**
6. **Use meaningful `$default` values** in `get_theme_mod()`. If the Customizer has never been saved, the default is what the site shows. Make it real content, not `Lorem ipsum`.
7. **Delete seeder scripts immediately** after confirming the data is in the database.
8. **Keep `index-static.html` in the theme root** as a permanent reference document.

### ❌ Never Do

1. **Never create `front-page.php`** on this project. `index.php` is the homepage.
2. **Never hardcode `appoinment.html` or any `.html` page links** in `index.php`. Always use `get_theme_mod()` with a sensible default.
3. **Never dump one-off scripts in the theme root.** Build functionality into the correct `/inc/` file or directly into `index.php`.
4. **Never have the same section in both `index.php` and another template file** being loaded for the same URL.
5. **Never use relative asset paths** like `src="assets/images/..."` in PHP template files.
6. **Never forget to call `wp_reset_postdata()`** after a custom loop.
7. **Never use a page builder plugin.** The SOP is explicit: core WordPress functions only.
8. **Never use `echo get_the_content()`** — always use `the_content()` or `wp_kses_post(get_the_content())` to allow WordPress filters to run (shortcodes, embeds, etc.).

---

## Part 7: The `header.php` and `footer.php` Pattern

### `header.php` Structure

```
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>  ← MANDATORY. Loads all enqueued styles, WP scripts, SEO plugins.
</head>
<body <?php body_class(); ?>>
    <?php wp_body_open(); ?> ← For plugins that need to inject into body
    
    <header>
        <!-- Logo: uses the_custom_logo() or wp_get_attachment_image -->
        <!-- Nav: uses wp_nav_menu() with wp_bootstrap_navwalker walker -->
        <!-- Topbar: uses get_theme_mod() for phone, email, address, socials -->
    </header>
```

### `footer.php` Structure

```
    <footer>
        <!-- Footer logo: get_footer_logo() helper from functions.php -->
        <!-- Nav: wp_nav_menu() for footer menus -->
        <!-- Contact info: get_theme_mod('contact_email'), get_theme_mod('contact_phone'), etc. -->
        <!-- Social links: get_theme_mod('social_instagram'), etc. -->
    </footer>
    
    <?php wp_footer(); ?> ← MANDATORY. Loads enqueued JS. DO NOT MOVE.
</body>
</html>
```

---

## Part 8: Security and WordPress Escaping Rules

Every output in a WordPress theme must be escaped before being printed. The rules:

| Context | Function to Use |
|---|---|
| HTML text output | `esc_html()` |
| HTML attribute output | `esc_attr()` |
| URL output (`href`, `src`) | `esc_url()` |
| URL storage (in DB) | `esc_url_raw()` |
| HTML with allowed tags | `wp_kses_post()` |
| Email addresses | `sanitize_email()` |
| Integer IDs | `absint()` |
| Textarea text | `sanitize_textarea_field()` |

Never use `echo $_POST[...]` or `echo $_GET[...]` directly. Never use `echo $variable` without escaping when that variable came from user input or the database.

---

## Part 10: Phase 2 — Inner Pages, Template System, and UI Fixes (Checkpoints 10–16)

This section documents everything done after the initial homepage dynamic conversion was complete. Phase 2 focused on building out all inner pages, fixing bugs, and hardening the theme.

---

### 10.1 Template Name Tags — Assigning PHP Templates to WordPress Pages

**What was done:** Every page-level PHP template file was given a WordPress `Template Name` header comment. Without this comment, WordPress cannot associate the PHP file with a specific page — it becomes invisible in the "Page Attributes → Template" dropdown in the WP Admin page editor.

**The exact comment block pattern:**
```php
<?php
/* Template Name: Page Name Here */
get_header(); ?>
```

**Placement:** The `/* Template Name: */` comment must be on the **very first meaningful line** of the PHP file, immediately before `get_header()`. It cannot be inside a function or after any output.

**Files that received Template Name headers:**

| PHP File | Template Name |
|---|---|
| `template-about.php` | `About Page` |
| `template-contact.php` | `Contact Page` |
| `template-faq.php` | `FAQ Page` |
| `template-appointment.php` | `Appointment Page` |
| `template-blog.php` | `Archive Blog` |
| `template-gallery.php` | `Work Gallery` |
| `template-services.php` | `Services Page` |
| `template-team.php` | `Team Page` |

**How to use in WordPress Admin:**
1. Go to Pages → Add New (or edit existing page)
2. In the right-hand sidebar, find "Page Attributes"
3. Select the desired template from the "Template" dropdown
4. Publish/Update the page

The page will now render using the assigned PHP template file instead of the default `page.php`.

> [!IMPORTANT]
> The `template-blog.php` was renamed conceptually to **"Archive Blog"** to distinguish it from the single post view. It shows a grid of all blog posts using `WP_Query` on the `post` post type, paginated. It is NOT the same as `single.php` (which handles individual post display).

---

### 10.5 Single Service Page — "Our Work Process" Section

**What was requested:** The "Our Work Process" section from `project-single.html` should appear statically on every single service detail page.

**Source reference:** `project-single.html` contained a `.wpo-p-details-section > .process-wrap` block with 3 process steps in a 3-column row.

**Why static (not dynamic):**
The work process (Book → Diagnose → Repair) is the same for every service. It doesn't need to be customizable per-service. Static HTML avoids unnecessary CPT overhead and ensures it always displays even before any data is in the database.

**Where it was added:** In `single-services.php`, immediately after the main service content block (`.service-details-body`) and before the booking CTA.

**The static HTML added to `single-services.php`:**
```html
<!-- Static Work Process from project-single.html -->
<div class="wpo-p-details-section">
    <div class="process-wrap mt-4">
        <h5>Our Work Process</h5>
        <div class="row">
            <div class="col-lg-4 col-md-6 col-12">
                <div class="process-item">
                    <div class="process-icon">
                        <i class="fi flaticon-handshake"></i>
                    </div>
                    <div class="process-text">
                        <h3>Quality We Ensure</h3>
                        <p>Thorough diagnostic inspection ensuring high-quality, reliable repair with genuine parts.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="process-item">
                    <div class="process-icon">
                        <i class="fi flaticon-medal"></i>
                    </div>
                    <div class="process-text">
                        <h3>Experienced Workers</h3>
                        <p>Verified, certified appliance engineers with multi-brand repair mastery across Srinagar.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6 col-12">
                <div class="process-item">
                    <div class="process-icon">
                        <i class="fi flaticon-gift-box"></i>
                    </div>
                    <div class="process-text">
                        <h3>Modern Equipment Use</h3>
                        <p>State-of-the-art testing tools ensuring swift, safe, and precise doorstep troubleshooting.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

**CSS classes used:** `.wpo-p-details-section`, `.process-wrap`, `.process-item`, `.process-icon`, `.process-text` — all sourced from the original `style.css` included in the template assets.

---

### 10.6 Single Service Page — Dynamic FAQ Accordion

**What was requested:** An FAQ section should appear on every service detail page, pulling from the `faq` Custom Post Type.

**Implementation in `single-services.php`:**

```php
<!-- Dynamic FAQ Accordion for Service Single -->
<div class="service-faq-section mt-5">
    <h3 class="mb-4" style="font-size: 24px; font-weight: 700;">Frequently Asked Questions</h3>
    <div class="accordion">
        <?php
        $faq_query = new WP_Query([
            'post_type'      => 'faq',
            'posts_per_page' => 5,
            'order'          => 'ASC'
        ]);

        $faq_idx = 0;
        if ($faq_query->have_posts()) :
            while ($faq_query->have_posts()) : $faq_query->the_post();
                $faq_idx++;
                $is_active = ($faq_idx === 1);
                ?>
                <div class="accordion-item <?php echo $is_active ? 'active' : ''; ?>">
                    <button class="accordion-header"><?php the_title(); ?></button>
                    <div class="accordion-content">
                        <p><?php echo get_the_content(); ?></p>
                    </div>
                </div>
                <?php
            endwhile;
            wp_reset_postdata();
        else :
            // Fallback static FAQs if no FAQ CPT posts exist
            ?>
            <div class="accordion-item active">
                <button class="accordion-header">How quickly can a technician visit my location in Srinagar?</button>
                <div class="accordion-content">
                    <p>We offer same-day doorstep service across Srinagar, typically arriving within 2 to 4 hours of booking confirmation.</p>
                </div>
            </div>
            <!-- ... more fallback items ... -->
        <?php endif; ?>
    </div>
</div>
```

**Key design decisions:**
- First FAQ item is always `active` (open by default) to improve user engagement
- The FAQ accordion uses the existing theme accordion JS (no additional scripts needed)
- Fallback static FAQs ensure the section never appears empty, even if no FAQ posts exist in the database
- `posts_per_page => 5` limits the section to 5 questions — enough for UX without overwhelming

---

### 10.7 Single Service Page — Fallback Content System

**The Problem:**
When a Services CPT post is created in WordPress Admin, the editor (TinyMCE/Gutenberg) is left empty by most users — they just set the title and feature image. But the service detail page needs a body description.

**The Solution:**
The `single-services.php` template checks if the editor content is empty:

```php
<?php
while (have_posts()) :
    the_post();
    $content = get_the_content();
    if (!empty(trim($content))) {
        the_content(); // Show what the editor wrote
    } else {
        // Show auto-generated SEO-friendly default text
        ?>
        <p class="lead">At Abid Electronics, we provide professional doorstep <?php the_title(); ?> across Srinagar with certified technicians...</p>
        <p>Whether you're experiencing electrical faults, cooling failures...</p>
        <h3>Service Features & Guarantees:</h3>
        <ul>
            <li>Same-day emergency doorstep service within 2 to 4 hours of booking.</li>
            <li>100% genuine, manufacturer-approved replacement spare parts.</li>
            <!-- etc. -->
        </ul>
        <?php
    }
endwhile;
?>
```

**Why this is the right approach:**
- The page never shows a blank body section
- SEO is always served with relevant keyword-rich content
- If the admin writes custom content for a specific service, it takes over automatically
- The fallback uses `the_title()` to reference the specific service name, making it contextually relevant

> [!IMPORTANT]
> The fallback content appears in the browser but is **NOT** in the WordPress editor. This is intentional. If you want to customize the content for a specific service, go to the WP Admin → Services → Edit that service → add content to the editor body → Update. The template will detect it and show your custom content instead.

---

### 10.8 Single Blog Post — Comments System via `comments.php`

**What was requested:** Remove the inline comments HTML from `single.php` and use the theme's `comments.php` file instead.

**Before:**
The original `single.php` had a large inline HTML block with hardcoded comment form fields, comment list structure, and pagination — all mixed directly into the template file.

**After:**
```php
<!-- Comments Template -->
<?php
if (comments_open() || get_comments_number()) {
    comments_template();
}
?>
```

`comments_template()` is the WordPress function that loads `comments.php` from the theme directory. This is the WordPress standard pattern.

**The `comments.php` file includes:**

1. **Password protection guard:**
```php
if (post_password_required()) { return; }
```

2. **Custom comment callback** (`webassets_custom_comment()`) — formats each comment with:
   - Gravatar avatar (65px, circular)
   - Author name + date/time
   - "Awaiting moderation" notice (if not yet approved)
   - Comment text
   - Reply link with custom arrow icon

3. **Comment list** rendered via `wp_list_comments()` using the custom callback

4. **Comment pagination** for posts with many comments

5. **Comment form** built with WordPress's native `comment_form()` function — the fields are customized to match the theme's Bootstrap form styling (`.form-control`, `.theme-btn-s2`)

**Why separate `comments.php` is important:**
- Can be reused on any template by calling `comments_template()`
- The single file is the only place to update comment UI — changes reflect everywhere
- WordPress plugin compatibility: plugins (like Akismet, Disqus) hook into `comment_form()` and `wp_list_comments()` — they cannot hook into hardcoded HTML

---

### 10.9 Work Gallery Page — Fancybox Lightbox Fix

**The Problem:**
The Work Gallery page (`template-gallery.php`) displayed images in a grid, but clicking on an image did not open a lightbox popup. The images were wrapped in `<a>` tags but Fancybox was not triggering.

**Root Cause:**
The theme's built-in Fancybox JS (loaded via `assets/js/jquery.fancybox.pack.js`) is initialized with a `$('.fancybox').fancybox()` selector — it looks for elements with the class `fancybox`. The initial implementation used `data-fancybox="gallery"` (the newer Fancybox v3+ attribute syntax) but the theme ships with Fancybox v2, which uses the class-based initialization.

**The Fix:**
Changed the `<a>` tag to use the `fancybox` class and `data-fancybox-group` attribute (Fancybox v2 syntax):

```php
<div class="project-items">
    <a href="<?php echo esc_url($full_img_url); ?>" 
       class="fancybox d-block" 
       data-fancybox-group="gallery" 
       title="<?php the_title_attribute(); ?>">
        <div class="project-image">
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
```

**Key decisions:**
- The entire `.project-items` box (both image AND title overlay) is wrapped in the single `<a>` tag — clicking anywhere on the card opens the lightbox
- `data-fancybox-group="gallery"` groups all images together so the lightbox has left/right navigation arrows to cycle through them
- `class="fancybox d-block"` ensures Fancybox v2 initializes on this element AND the link fills its container (`.d-block`)

**Fallback:** If no gallery posts exist, a user-friendly message is shown:
```html
<div class="col-12 text-center py-5">
    <p>No gallery images uploaded yet. Please add images under Work Gallery in the admin dashboard.</p>
</div>
```

---

### 10.10 "Archive Blog" vs Regular Blog — Clarification

**What "Archive Blog" means:**
The template file `template-blog.php` (Template Name: Archive Blog) is the **full blog listing page** — it shows all posts in a paginated grid. It is assigned to a WordPress Page (e.g., the "Blog" page in the nav menu).

This is DIFFERENT from:
- `single.php` — renders one individual blog post
- `archive.php` — WordPress's built-in archive template for categories, tags, and date archives

**Why "Archive Blog" as the Template Name:**
The word "Archive" clarifies that this page is a collection/archive of all posts, not a single article. This is standard WordPress terminology. It helps distinguish this template from the service archive (`template-services.php`) and the gallery archive (`template-gallery.php`).

**The blog listing template shows:**
- Post featured image (fallback to placeholder if none set)
- Post date
- Post title (linked to single post)
- Excerpt (first 20 words via `wp_trim_words()`)
- "Read More" link
- Pagination (using `paginate_links()`)
- Sidebar with: Search, Categories, Recent Posts, Emergency CTA banner

---

### 10.11 Service Content Architecture — How Text Gets to the Service Detail Page

**This is a common point of confusion.** When viewing a service page like `/service/ac-repair-service/`, you may notice body text that is NOT visible in the WordPress editor for that service post.

**The answer:** That text is the **fallback content** hardcoded into `single-services.php` (see Section 10.7). It appears **only when the WordPress editor is empty** for that service.

**Three layers of content on a service page:**

| Layer | Source | How to Edit |
|---|---|---|
| Page title | CPT post title | WP Admin → Services → Edit |
| Body description | Post editor content (if filled) | WP Admin → Services → Edit → Body |
| Body description (fallback) | Hardcoded in `single-services.php` | Edit the PHP file |
| Featured image | Post thumbnail | WP Admin → Services → Edit → Featured Image |
| Service price | `_service_price` custom field | WP Admin → Services → Edit → Service Pricing meta box |
| Work Process section | Static in `single-services.php` | Edit the PHP file (same for all services) |
| FAQ section | `faq` CPT posts | WP Admin → FAQ → Add New |
| Services sidebar list | All CPT posts of type `services` | Automatically populated |
### 10.10 Dynamic Footer Navigation, CPT Fallbacks & Widget Areas

**What was done:** Converted all footer link columns (`Our Services`, `Quick Links`, `Areas We Serve`) from hardcoded static HTML to fully dynamic WordPress navigation menus and CPT database queries, following the agency SOP.

**Architecture:**
1. **WordPress Menu Locations Registered (`functions.php`):**
   - `footer-services` → Dedicated Services Menu
   - `footer-menu` / `company-links` → Quick Links Menu
   - `footer-areas` → Areas / Locations Menu

2. **Column 2: Our Services (`footer.php`):**
   - Priority 1: `is_active_sidebar('footer-2')` → Displays WordPress widget.
   - Priority 2: `has_nav_menu('footer-services')` → Renders custom WordPress menu.
   - Priority 3: Dynamic `WP_Query(['post_type' => 'services', 'posts_per_page' => 5])` → Pulls top services automatically from database.
   - Priority 4: Dynamic permalink fallback links using `home_url()`.

3. **Column 3: Quick Links (`footer.php`):**
   - Priority 1: `is_active_sidebar('footer-3')` → Displays WordPress widget.
   - Priority 2: `has_nav_menu('footer-menu')` or `company-links` → Renders assigned menu.
   - Priority 3: Standard site pages (Home, About Us, Services, Gallery, Contact, Appointment) via `home_url()`.

4. **Column 4: Areas We Serve (`footer.php`):**
   - Priority 1: `is_active_sidebar('footer-4')` → Displays WordPress widget.
   - Priority 2: `has_nav_menu('footer-areas')` → Renders assigned locations menu.
   - Priority 3: Dynamic `WP_Query(['post_type' => 'locations', 'posts_per_page' => 5])` → Pulls serviced locations from database.
   - Priority 4: Standard area fallback links via `home_url()`.

5. **Column Titles in Customizer (`inc/homepage-customizer.php`):**
   - Added `footer_col2_title`, `footer_col3_title`, and `footer_col4_title` under Customizer section *Footer Column Titles*.

---

## Part 9: Future Development Checklist

When working on the next homepage section or a new inner page, follow this exact order:

- [ ] Read `index-static.html` for the target section's HTML structure
- [ ] Classify: CPT or Customizer?
- [ ] If CPT: Create `/inc/[name]-functions.php`, register post type, register meta boxes, add file to `functions.php` loader array
- [ ] If Customizer: Add section + settings + controls to `inc/homepage-customizer.php`
- [ ] In `index.php`: Replace the static HTML block completely with the dynamic PHP (loop or `get_theme_mod()`)
- [ ] Add fallback images/text so the section never appears broken if no data is saved
- [ ] Run `php -l index.php` to verify no PHP syntax errors
- [ ] Seed the CPT with initial data via a temporary script, then delete the script
- [ ] Verify in the browser that output matches `index-static.html` visually
- [ ] Verify in Customizer that changing a setting reflects on the homepage live preview

---

## Summary

We converted a **100% static HTML template** into a **100% dynamic WordPress theme** without using any page builder, following the agency's SOP exactly. The core technical mechanism was:

1. **`get_theme_mod(key, default)`** — the Customizer bridge. Every editable piece of non-repeating content flows through this function.
2. **`new WP_Query([...])`** — the CPT bridge. Every repeating content element is a database record queried with this class.
3. **`get_template_directory_uri()`** — the assets bridge. Every image, CSS, and JS file is referenced through this function so paths are always correct regardless of the WordPress installation URL.
4. **`/inc/` modular architecture** — all business logic is kept separate from presentation. `functions.php` loads the modules; `index.php` displays the output.

The theme is now maintainable, scalable, and 100% client-friendly. No code needs to be touched to change any homepage content — everything flows through the WordPress dashboard.
