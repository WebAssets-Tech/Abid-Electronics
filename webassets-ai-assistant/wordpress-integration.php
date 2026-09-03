<?php
/**
 * WebAssets AI Assistant — WordPress Integration Bootstrap
 * 
 * This file encapsulates all WordPress-specific enqueuing, settings page
 * loading, and REST api/proxy bootstrapping.
 */

if (!defined('ABSPATH')) exit;

$dir = dirname(__FILE__);

// 1. Include Backend Modules
if (file_exists($dir . '/includes/ai-logger.php')) {
    require_once $dir . '/includes/ai-logger.php';
}
if (file_exists($dir . '/ai-settings.php')) {
    require_once $dir . '/ai-settings.php';
}
if (file_exists($dir . '/ai-leads.php')) {
    require_once $dir . '/ai-leads.php';
}
if (file_exists($dir . '/ai-logs.php')) {
    require_once $dir . '/ai-logs.php';
}
if (file_exists($dir . '/ai-proxy.php')) {
    require_once $dir . '/ai-proxy.php';
}


// 1.5 Auto-create logging table if it doesn't exist (throttled check)
add_action('admin_init', function() {
    if (class_exists('WebAssetsAI_Logger') && !get_transient('waai_log_table_created')) {
        WebAssetsAI_Logger::create_table();
        set_transient('waai_log_table_created', 1, DAY_IN_SECONDS);
    }
});

// 2. Ensure the AI Assistant JS is loaded as an ES module
add_filter('script_loader_tag', function($tag, $handle, $src) {
    if ($handle === 'webassets-ai-widget-script') {
        return '<script type="module" src="' . esc_url($src) . '"></script>';
    }
    return $tag;
}, 10, 3);

// 3. Frontend: enqueue widget assets + pass admin config to JS via waaiConfig
add_action('wp_enqueue_scripts', 'webassets_enqueue_ai_assistant_assets');
function webassets_enqueue_ai_assistant_assets() {
    $widget_enabled = get_option('waai_enabled', '1');
    if ($widget_enabled !== '1') return; // Respect the on/off toggle

    $dir = dirname(__FILE__);
    $uri = get_template_directory_uri() . '/webassets-ai-assistant';

    if (!file_exists($dir . '/ai-widget.js')) return;

    wp_enqueue_style(
        'webassets-ai-widget-style',
        $uri . '/ai-widget.css',
        [],
        '3.0.27'
    );
    wp_enqueue_script(
        'webassets-ai-widget-script',
        $uri . '/ai-widget.js',
        [],
        '3.0.27',
        true
    );

    // Voice langs removed (hardcoded universal mode)

    $site_name = get_bloginfo('name');

    // Build suggestion chips
    $suggestions = [
        ['label' => get_option('waai_suggestion_1_label', '💻 Our Services'), 'query' => get_option('waai_suggestion_1_query', 'Tell me about your services')],
        ['label' => get_option('waai_suggestion_2_label', '📅 Book Consultation'),  'query' => get_option('waai_suggestion_2_query', 'How can I book a free consultation?')],
        ['label' => get_option('waai_suggestion_3_label', sprintf('📞 Contact %s', esc_html($site_name))),  'query' => get_option('waai_suggestion_3_query', 'How to contact support?')],
    ];

    $default_initials = '';
    $words = explode(' ', $site_name);
    foreach ($words as $w) {
        $default_initials .= strtoupper(substr($w, 0, 1));
    }
    $default_initials = substr($default_initials, 0, 2);
    if (empty($default_initials)) $default_initials = 'AI';

    // Fetch dynamic WP pages for auto-indexing, respecting the admin post type filter
    $auto_pages = [];
    if (function_exists('get_post_types') && function_exists('get_posts')) {
        $public_post_types = get_post_types(['public' => true]);
        if (isset($public_post_types['attachment'])) unset($public_post_types['attachment']);

        $allowed_types = get_option('waai_sitemap_post_types', 'not_set');
        if ($allowed_types === 'not_set') {
            $post_types_to_query = array_values($public_post_types);
        } else {
            $allowed_array = is_array($allowed_types) ? $allowed_types : (empty($allowed_types) ? [] : [$allowed_types]);
            $post_types_to_query = array_values(array_intersect(array_values($public_post_types), $allowed_array));
        }

        if (!empty($post_types_to_query)) {
            $wp_posts = get_posts([
                'post_type'      => $post_types_to_query,
                'post_status'    => 'publish',
                'posts_per_page' => 300,
                'orderby'        => 'menu_order date',
                'order'          => 'DESC'
            ]);
            foreach ($wp_posts as $p) {
                if (get_option('page_on_front') == $p->ID) continue;
                $auto_pages[] = [
                    'name'     => $p->post_title,
                    'selector' => get_permalink($p->ID)
                ];
            }
        }
    }

    $manual_sections = get_option('waai_agentic_sections', []);
    $all_sections = array_merge(is_array($manual_sections) ? $manual_sections : [], $auto_pages);

    // Parse custom loading tips/insights
    $default_tips = [
        "A WhatsApp button on your site can increase customer inquiries by up to 300%.",
        "Our custom websites include tailored UI/UX design and AI automated workflows.",
        "Dynamic websites with editable dashboards make content updates quick and code-free.",
        "Did you know? Page load speed is one of Google's main search ranking factors.",
        "WebAssets offers premium cloud web hosting from just ₹1,500/year.",
        "We build local SEO strategies specifically tailored for Kashmir businesses.",
        "All our custom packages come with 3 months of free dedicated support."
    ];
    $raw_tips = get_option('waai_loading_tips', '');
    if (is_array($raw_tips)) {
        $loading_tips = array_filter(array_map('trim', $raw_tips));
    } else {
        $loading_tips = array_filter(array_map('trim', explode("\n", $raw_tips)));
    }
    if (empty($loading_tips)) {
        $loading_tips = $default_tips;
    }

    // Parse custom thinking steps
    $default_thinking_steps = [
        "🔍 Analyzing your request...",
        "🧠 Querying knowledge base...",
        "📂 Checking company services...",
        "⚙️ Formulating response...",
        "✍️ Finalizing reply..."
    ];
    $raw_thinking_steps = get_option('waai_thinking_steps', '');
    if (is_array($raw_thinking_steps)) {
        $thinking_steps = array_filter(array_map('trim', $raw_thinking_steps));
    } else {
        $thinking_steps = array_filter(array_map('trim', explode("\n", $raw_thinking_steps)));
    }
    if (empty($thinking_steps)) {
        $thinking_steps = $default_thinking_steps;
    }

    // Pass all settings to the widget JS
    wp_localize_script('webassets-ai-widget-script', 'waaiConfig', [
        'widgetTitle'    => get_option('waai_widget_title',    sprintf('%s Assistant', esc_html($site_name))),
        'widgetSubtitle' => get_option('waai_widget_subtitle', 'Online'),
        'avatarInitials' => get_option('waai_avatar_initials', $default_initials),
        'accentColor'    => get_option('waai_accent_color',    '#5f39ff'),
        'welcomeMessage' => get_option('waai_welcome_message', sprintf('Hi! I am your %s Assistant. Ask me anything about our services, products, pricing, and more!', esc_html($site_name))),
        'loadingTips'    => array_values($loading_tips),
        'thinkingSteps'  => array_values($thinking_steps),
        'voiceGreeting'  => get_option('waai_voice_greeting',  sprintf('Welcome to %s! How can I help you today?', esc_html($site_name))),
        'suggestions'    => $suggestions,
        'calendarType'   => get_option('waai_calendar_type',   'disabled'),
        'calendlyUrl'    => get_option('waai_calendly_url',    ''),
        'googleCalUrl'   => get_option('waai_google_calendar_url', ''),
        'customCalUrl'   => get_option('waai_custom_calendar_url', ''),
        'whatsappNumber' => get_option('waai_whatsapp_number') ?: (get_theme_mod('social_whatsapp') ?: '9622917697'),
        'proactiveDelay' => (int) get_option('waai_proactive_delay', 30),
        'stylesheetPath' => $uri . '/ai-widget.css',
        'chatUrl'        => $uri . '/ai-proxy.php',
        'leadUrl'        => $uri . '/ai-proxy.php',
        'whatsappUrl'    => $uri . '/ai-proxy.php',
        'companyEmail'   => get_option('waai_company_email', get_option('admin_email')),
        'speechEngine'   => get_option('waai_speech_engine', 'web_speech'),
        'sttEngine'      => get_option('waai_stt_engine', 'browser'),
        'elevenLabsKey'  => get_option('waai_elevenlabs_api_key', ''),
        'elevenLabsVoiceId' => get_option('waai_elevenlabs_voice_id', ''),
        'groqApiKey'     => get_option('waai_api_key', ''),
        'groqTtsVoice'   => get_option('waai_groq_tts_voice', 'troy'),
        'groqTtsModel'   => get_option('waai_groq_tts_model', 'canopylabs/orpheus-v1-english'),
        'hasSarvamKey'   => !empty(get_option('waai_sarvam_api_key', '')),
        'allowInterruptions' => get_option('waai_enable_interruptions', '1') === '1' ? '1' : '0',
        'nonce'          => wp_create_nonce('waai_secure_action'),
        
        // Agentic Configs
        'enableAgentic'              => get_option('waai_agentic_enabled', '0'),
        'enableTaskQueue'            => get_option('waai_enable_task_queue', '1'),
        'enableConversationalMemory' => get_option('waai_enable_conversational_memory', '1'),
        'enablePostNav'              => get_option('waai_enable_post_nav', '1'),
        'enablePageText'             => get_option('waai_enable_page_text', '1'),
        'enablePageLinks'            => get_option('waai_enable_page_links', '1'),
        
        'siteIndex'      => get_option('waai_enable_site_index', '1') === '1' ? [
            'services' => get_option('waai_services', []),
            'products' => get_option('waai_products', []),
            'sections' => $all_sections
        ] : null
    ]);
}

// 4. Render widget tag in footer
add_action('wp_footer', 'webassets_render_ai_assistant_widget');
function webassets_render_ai_assistant_widget() {
    $widget_enabled = get_option('waai_enabled', '1');
    if ($widget_enabled !== '1') return;

    $dir = dirname(__FILE__);
    $uri = get_template_directory_uri() . '/webassets-ai-assistant';

    if (!file_exists($dir . '/ai-widget.js')) return;

    echo '<ai-assistant-widget mode="wordpress" stylesheet-path="' . esc_url($uri . '/ai-widget.css') . '"></ai-assistant-widget>';
}

// 5. AJAX Endpoint: Fetch filtered site links for the Settings Auto Map button
add_action('wp_ajax_waai_auto_map_links', 'waai_auto_map_links_callback');
function waai_auto_map_links_callback() {
    check_ajax_referer('waai_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);

    $all_pages = [];
    if (function_exists('get_post_types') && function_exists('get_posts')) {
        $public_post_types = get_post_types(['public' => true]);
        if (isset($public_post_types['attachment'])) unset($public_post_types['attachment']);

        $allowed_types = get_option('waai_sitemap_post_types', 'not_set');
        if ($allowed_types === 'not_set') {
            $post_types_to_query = array_values($public_post_types);
        } else {
            $allowed_array = is_array($allowed_types) ? $allowed_types : (empty($allowed_types) ? [] : [$allowed_types]);
            $post_types_to_query = array_values(array_intersect(array_values($public_post_types), $allowed_array));
        }

        $wp_posts = get_posts([
            'post_type'      => $post_types_to_query,
            'post_status'    => 'publish',
            'posts_per_page' => 300,
            'orderby'        => 'menu_order date',
            'order'          => 'ASC',
        ]);

        foreach ($wp_posts as $p) {
            if (get_option('page_on_front') == $p->ID) continue;
            $all_pages[] = [
                'name'     => $p->post_title,
                'selector' => get_permalink($p->ID),
            ];
        }
    }

    wp_send_json_success(['links' => $all_pages]);
}

// 6. AJAX Endpoint: Return all public post types (for Detect Post Types button)
add_action('wp_ajax_waai_get_post_types', 'waai_get_post_types_callback');
function waai_get_post_types_callback() {
    check_ajax_referer('waai_admin_nonce', 'nonce');
    if (!current_user_can('manage_options')) wp_send_json_error('Forbidden', 403);

    $post_types = [];
    if (function_exists('get_post_types')) {
        $all = get_post_types(['public' => true], 'objects');
        unset($all['attachment']);
        foreach ($all as $slug => $obj) {
            $post_types[] = [
                'slug'  => $slug,
                'label' => $obj->labels->name,
            ];
        }
    }

    wp_send_json_success(['post_types' => $post_types]);
}
