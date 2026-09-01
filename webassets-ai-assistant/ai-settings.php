<?php
/**
 * WebAssets AI Assistant — WordPress Admin Settings Page
 * Provides a full admin UI to configure the AI assistant without touching any code.
 *
 * Registers: Settings > WebAssets AI (main menu)
 * Sub-pages:  Settings  |  Leads (registered by ai-leads.php)
 */

if (!defined('ABSPATH')) exit;

class WebAssetsAI_Settings {

    public function __construct() {
        add_action('admin_menu',   [$this, 'register_menu']);
        add_action('admin_init',   [$this, 'register_settings']);
    }

    public function register_menu() {
        add_menu_page(
            'AI Assistant Settings',
            'AI Assistant',
            'manage_options',
            'webassets-ai',
            [$this, 'render_page'],
            'dashicons-format-chat',
            56
        );
        add_submenu_page(
            'webassets-ai',
            'AI Settings',
            'Settings',
            'manage_options',
            'webassets-ai',
            [$this, 'render_page']
        );
    }

    public function register_settings() {
        $fields = [
            // AI Provider
            'waai_provider', 'waai_model', 'waai_api_key',
            'waai_api_key_groq', 'waai_api_key_openrouter', 'waai_api_key_openai', 'waai_api_key_gemini', 'waai_api_key_custom', 'waai_api_key_anthropic',
            'waai_model_groq', 'waai_model_openrouter', 'waai_model_openai', 'waai_model_gemini', 'waai_model_custom', 'waai_model_anthropic',
            'waai_custom_endpoint',
            'waai_enable_long_context_fallback', 'waai_long_context_provider', 'waai_long_context_model', 'waai_long_context_api_key',
            'waai_max_tokens', 'waai_temperature',
            // Messages
            'waai_welcome_message', 'waai_voice_greeting',
            // Company Profile
            'waai_company_name', 'waai_company_tagline', 'waai_company_location',
            'waai_company_phone', 'waai_company_email', 'waai_company_website',
            'waai_company_description',
            // Knowledge
            'waai_services', 'waai_products', 'waai_gallery', 'waai_tone_rules', 'waai_faq_examples',
            // Leads
            'waai_lead_email', 'waai_sheets_webhook', 'waai_save_leads_db', 'waai_lead_form_enabled',
            // Calendar
            'waai_calendar_type', 'waai_calendly_url', 'waai_google_calendar_url', 'waai_custom_calendar_url',
            // Widget UI
            'waai_enabled', 'waai_widget_title', 'waai_widget_subtitle', 'waai_avatar_initials', 'waai_accent_color',
            'waai_suggestion_1_label', 'waai_suggestion_1_query',
            'waai_suggestion_2_label', 'waai_suggestion_2_query',
            'waai_suggestion_3_label', 'waai_suggestion_3_query',
            'waai_proactive_delay', 'waai_whatsapp_number',
            'waai_speech_engine', 'waai_elevenlabs_api_key', 'waai_elevenlabs_voice_id', 'waai_enable_interruptions',
            'waai_piper_url',
            'waai_groq_tts_voice', 'waai_groq_tts_model',
            'waai_sarvam_api_key',
            // Security
            'waai_rate_limiting_enabled', 'waai_rate_limit', 'waai_max_input_chars',
            'waai_rate_limit_tts', 'waai_rate_limit_sarvam', 'waai_rate_limit_lead', 'waai_rate_limit_whatsapp_email',
            // WhatsApp API Settings
            'waai_whatsapp_api_enabled', 'waai_whatsapp_app_key', 'waai_whatsapp_auth_key',
            // Email Settings
            'waai_email_api_enabled', 'waai_email_method',
            'waai_smtp_host', 'waai_smtp_port', 'waai_smtp_secure',
            'waai_smtp_user', 'waai_smtp_pass',
            'waai_email_from_address', 'waai_email_from_name',
            // Agentic Navigation & Features
            'waai_agentic_enabled', 'waai_agentic_sections',
            'waai_enable_site_index', 'waai_enable_conversational_memory', 'waai_enable_task_queue', 'waai_enable_post_nav',
            'waai_enable_action_scroll', 'waai_enable_action_interact', 'waai_enable_action_navigate', 'waai_enable_action_read',
            // Context Management
            'waai_enable_page_text', 'waai_enable_page_links', 'waai_history_limit',
            'waai_enable_services', 'waai_enable_products', 'waai_enable_gallery', 'waai_enable_faq',
            'waai_loading_tips', 'waai_thinking_steps',
            // Sitemap Post Type Filter
            'waai_sitemap_post_types',
        ];
        $url_fields = [
            'waai_calendly_url', 'waai_google_calendar_url', 'waai_custom_calendar_url',
            'waai_sheets_webhook', 'waai_company_website'
        ];
        foreach ($fields as $field) {
            $callback = in_array($field, $url_fields) ? [$this, 'sanitize_url_setting'] : [$this, 'sanitize_setting'];
            register_setting('waai_settings_group', $field, [
                'sanitize_callback' => $callback,
            ]);
        }
    }

    public function sanitize_setting($value) {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                if (is_array($item)) {
                    $sanitized[$key] = [];
                    foreach ($item as $sub_key => $sub_val) {
                        if ($sub_key === 'url' || $sub_key === 'image') {
                            $sanitized[$key][$sub_key] = function_exists('esc_url_raw') ? esc_url_raw(trim($sub_val)) : filter_var(trim($sub_val), FILTER_SANITIZE_URL);
                        } else {
                            $sanitized[$key][$sub_key] = sanitize_text_field($sub_val);
                        }
                    }
                } else {
                    if ($key === 'url') {
                        $sanitized[$key] = function_exists('esc_url_raw') ? esc_url_raw(trim($item)) : filter_var(trim($item), FILTER_SANITIZE_URL);
                    } else {
                        $sanitized[$key] = sanitize_text_field($item);
                    }
                }
            }
            return $sanitized;
        }
        return sanitize_text_field($value);
    }

    public function sanitize_url_setting($value) {
        if (function_exists('esc_url_raw')) {
            return esc_url_raw(trim($value));
        }
        return filter_var(trim($value), FILTER_SANITIZE_URL);
    }

    private function get($key, $default = '') {
        return get_option($key, $default);
    }

    public function render_page() {
        if (!current_user_can('manage_options')) return;
        $saved = isset($_GET['settings-updated']);

        // Load all options
        $provider          = $this->get('waai_provider', 'groq');
        $model             = $this->get('waai_model', 'llama-3.1-8b-instant');
        $api_key           = $this->get('waai_api_key', '');
        
        $api_key_groq       = $this->get('waai_api_key_groq', '');
        $api_key_openrouter = $this->get('waai_api_key_openrouter', '');
        $api_key_openai     = $this->get('waai_api_key_openai', '');
        $api_key_gemini     = $this->get('waai_api_key_gemini', '');
        $api_key_custom     = $this->get('waai_api_key_custom', '');
        $api_key_anthropic  = $this->get('waai_api_key_anthropic', '');

        // Per-provider model memory (defaults fall back to known good models per provider)
        $model_groq       = $this->get('waai_model_groq',       ($provider === 'groq'       ? $model : 'llama-3.1-8b-instant'));
        $model_openrouter = $this->get('waai_model_openrouter', ($provider === 'openrouter' ? $model : 'meta-llama/llama-3-8b-instruct:free'));
        $model_openai     = $this->get('waai_model_openai',     ($provider === 'openai'     ? $model : 'gpt-4o-mini'));
        $model_gemini     = $this->get('waai_model_gemini',     ($provider === 'gemini'     ? $model : 'gemini-1.5-flash'));
        $model_custom     = $this->get('waai_model_custom',     ($provider === 'custom'     ? $model : ''));
        $model_anthropic  = $this->get('waai_model_anthropic',  ($provider === 'anthropic'  ? $model : 'claude-sonnet-4-5'));
        
        $custom_endpoint    = $this->get('waai_custom_endpoint', '');

        // Migration fallback: if provider-specific key is empty but main key is set, assign it
        if (empty($api_key_groq) && $provider === 'groq') $api_key_groq = $api_key;
        if (empty($api_key_openrouter) && $provider === 'openrouter') $api_key_openrouter = $api_key;
        if (empty($api_key_openai) && $provider === 'openai') $api_key_openai = $api_key;
        if (empty($api_key_gemini) && $provider === 'gemini') $api_key_gemini = $api_key;
        if (empty($api_key_custom) && $provider === 'custom') $api_key_custom = $api_key;
        if (empty($api_key_anthropic) && $provider === 'anthropic') $api_key_anthropic = $api_key;
        
        $enable_long_context_fallback = $this->get('waai_enable_long_context_fallback', '0');
        $long_context_provider        = $this->get('waai_long_context_provider', 'openrouter');
        $long_context_model           = $this->get('waai_long_context_model', 'google/gemini-1.5-flash');
        $long_context_api_key         = $this->get('waai_long_context_api_key', '');
        
        $max_tokens        = $this->get('waai_max_tokens', 1000);
        $temperature       = $this->get('waai_temperature', 0.7);
        $site_name = get_bloginfo('name');
        $site_desc = get_bloginfo('description');
        $admin_email = get_option('admin_email');
        $site_url = get_site_url();

        $default_initials = '';
        $words = explode(' ', $site_name);
        foreach ($words as $w) {
            $default_initials .= strtoupper(substr($w, 0, 1));
        }
        $default_initials = substr($default_initials, 0, 2);
        if (empty($default_initials)) $default_initials = 'AI';

        $welcome_message   = $this->get('waai_welcome_message', sprintf("Hi! I am your %s Assistant. Ask me anything about our services, products, or pricing!", esc_html($site_name)));
        $voice_greeting    = $this->get('waai_voice_greeting', sprintf("Welcome to %s! How can I help you today?", esc_html($site_name)));

        $company_name      = $this->get('waai_company_name', $site_name);
        $company_tagline   = $this->get('waai_company_tagline', $site_desc);
        $company_location  = $this->get('waai_company_location', '');
        $company_phone     = $this->get('waai_company_phone', '');
        $company_email     = $this->get('waai_company_email', $admin_email);
        $company_website   = $this->get('waai_company_website', $site_url);
        $company_desc      = $this->get('waai_company_description', $site_desc);
        $services          = $this->get('waai_services', []);
        $products          = $this->get('waai_products', []);
        $gallery           = $this->get('waai_gallery', []);
        $tone_rules        = $this->get('waai_tone_rules', "Keep replies brief, engaging, and professional. Always end with a clear call-to-action (book consultation, visit product page, or contact us). Never make up prices or facts. If unsure, invite them to contact the team directly.");
        $faq_examples      = $this->get('waai_faq_examples', []);

        $lead_email        = $this->get('waai_lead_email', $admin_email);
        $sheets_webhook    = $this->get('waai_sheets_webhook', '');
        $save_leads_db     = $this->get('waai_save_leads_db', '1');
        $lead_form_enabled = $this->get('waai_lead_form_enabled', '1');

        $calendar_type     = $this->get('waai_calendar_type', 'disabled');
        $calendly_url      = $this->get('waai_calendly_url', '');
        $google_cal_url    = $this->get('waai_google_calendar_url', '');
        $custom_cal_url    = $this->get('waai_custom_calendar_url', '');

        $widget_enabled    = $this->get('waai_enabled', '1');
        $widget_title      = $this->get('waai_widget_title', sprintf("%s Assistant", esc_html($site_name)));
        $widget_subtitle   = $this->get('waai_widget_subtitle', 'Online');
        $avatar_initials   = $this->get('waai_avatar_initials', $default_initials);
        $accent_color      = $this->get('waai_accent_color', '#5f39ff');
        $sug1_label        = $this->get('waai_suggestion_1_label', '💻 Our Services');
        $sug1_query        = $this->get('waai_suggestion_1_query', 'Tell me about your services');
        $sug2_label        = $this->get('waai_suggestion_2_label', '📅 Book Consultation');
        $sug2_query        = $this->get('waai_suggestion_2_query', 'How can I book a free consultation?');
        $sug3_label        = $this->get('waai_suggestion_3_label', sprintf('📞 Contact %s', esc_html($site_name)));
        $sug3_query        = $this->get('waai_suggestion_3_query', 'How to contact support?');
        $proactive_delay   = $this->get('waai_proactive_delay', 30);
        $whatsapp_number   = $this->get('waai_whatsapp_number', '');

        $speech_engine       = $this->get('waai_speech_engine', 'web_speech');
        $enable_interruptions = $this->get('waai_enable_interruptions', '1');
        $sarvam_api_key      = $this->get('waai_sarvam_api_key', '');
        $elevenlabs_api_key  = $this->get('waai_elevenlabs_api_key', '');
        $elevenlabs_voice_id = $this->get('waai_elevenlabs_voice_id', '');
        $piper_url           = $this->get('waai_piper_url', 'http://127.0.0.1:5000');
        $groq_tts_voice      = $this->get('waai_groq_tts_voice', 'troy');
        $groq_tts_model      = $this->get('waai_groq_tts_model', 'canopylabs/orpheus-v1-english');

        $rate_limiting_enabled = $this->get('waai_rate_limiting_enabled', '1');
        $rate_limit        = $this->get('waai_rate_limit', 50);
        $max_input_chars   = $this->get('waai_max_input_chars', 1000);
        $rate_limit_tts    = $this->get('waai_rate_limit_tts', 500);
        $rate_limit_sarvam = $this->get('waai_rate_limit_sarvam', 500);
        $rate_limit_lead   = $this->get('waai_rate_limit_lead', 10);
        $rate_limit_wa_email = $this->get('waai_rate_limit_whatsapp_email', 5);

        $whatsapp_api_enabled = $this->get('waai_whatsapp_api_enabled', '1');
        $whatsapp_app_key     = $this->get('waai_whatsapp_app_key', 'c5a6057f-324c-40aa-a568-e47bb91bb2f3');
        $whatsapp_auth_key    = $this->get('waai_whatsapp_auth_key', 'NcDjp7sOdkiUDwfvcSCvGQLh7p6zLRYJ1');

        $email_api_enabled    = $this->get('waai_email_api_enabled', '0');
        $email_method         = $this->get('waai_email_method', 'wp_mail');
        $smtp_host            = $this->get('waai_smtp_host', '');
        $smtp_port            = $this->get('waai_smtp_port', '587');
        $smtp_secure          = $this->get('waai_smtp_secure', 'tls');
        $smtp_user            = $this->get('waai_smtp_user', '');
        $smtp_pass            = $this->get('waai_smtp_pass', '');
        $email_from_address   = $this->get('waai_email_from_address', $admin_email);
        $email_from_name      = $this->get('waai_email_from_name', $site_name);

        $agentic_enabled      = $this->get('waai_agentic_enabled', '0');
        $agentic_sections     = $this->get('waai_agentic_sections', []);
        
        $enable_site_index = $this->get('waai_enable_site_index', '1');
        $enable_conversational_memory = $this->get('waai_enable_conversational_memory', '1');
        $enable_task_queue = $this->get('waai_enable_task_queue', '1');
        $enable_post_nav = $this->get('waai_enable_post_nav', '1');
        
        $enable_action_scroll = $this->get('waai_enable_action_scroll', '1');
        $enable_action_interact = $this->get('waai_enable_action_interact', '1');
        $enable_action_navigate = $this->get('waai_enable_action_navigate', '1');
        $enable_action_read = $this->get('waai_enable_action_read', '1');

        $enable_page_text = $this->get('waai_enable_page_text', '1');
        $enable_page_links = $this->get('waai_enable_page_links', '1');
        $history_limit    = $this->get('waai_history_limit', '20');
        $enable_services  = $this->get('waai_enable_services', '1');
        $enable_products  = $this->get('waai_enable_products', '1');
        $enable_gallery   = $this->get('waai_enable_gallery', '1');
        $enable_faq       = $this->get('waai_enable_faq', '1');
        $loading_tips_raw = $this->get('waai_loading_tips', "A WhatsApp button on your site can increase customer inquiries by up to 300%.\nOur custom websites include tailored UI/UX design and AI automated workflows.\nDynamic websites with editable dashboards make content updates quick and code-free.\nDid you know? Page load speed is one of Google's main search ranking factors.\nWebAssets offers premium cloud web hosting from just ₹1,500/year.\nWe build local SEO strategies specifically tailored for Kashmir businesses.\nAll our custom packages come with 3 months of free dedicated support.");
        if (is_array($loading_tips_raw)) {
            $loading_tips = array_filter(array_map('trim', $loading_tips_raw));
        } else {
            $loading_tips = array_filter(array_map('trim', explode("\n", $loading_tips_raw)));
        }
        if (empty($loading_tips)) $loading_tips = [""];

        $thinking_steps_raw = $this->get('waai_thinking_steps', "🔍 Analyzing your request...\n🧠 Querying knowledge base...\n📂 Checking company services...\n⚙️ Formulating response...\n✍️ Finalizing reply...");
        if (is_array($thinking_steps_raw)) {
            $thinking_steps = array_filter(array_map('trim', $thinking_steps_raw));
        } else {
            $thinking_steps = array_filter(array_map('trim', explode("\n", $thinking_steps_raw)));
        }
        if (empty($thinking_steps)) $thinking_steps = ["🔍 Analyzing your request..."];

        // Sitemap Post Type Filter
        $sitemap_post_types = get_option('waai_sitemap_post_types', 'not_set');

        // Pad arrays to minimums
        if (!is_array($services)) $services = [];
        if (!is_array($products)) $products = [];
        if (!is_array($gallery)) $gallery = [];
        if (!is_array($faq_examples)) $faq_examples = [];
        if (!is_array($agentic_sections)) $agentic_sections = [];
        while (count($services) < 3)     $services[]     = ['title' => '', 'description' => '', 'url' => ''];
        while (count($products) < 3)     $products[]     = ['name'  => '', 'description' => '', 'url' => ''];
        while (count($faq_examples) < 5) $faq_examples[] = ['q'     => '', 'a'           => ''];
        ?>
        <div class="wrap waai-wrap">

        <!-- Header -->
        <div class="waai-header">
            <div class="waai-header-left">
                <span class="waai-header-icon">🤖</span>
                <div>
                    <h1>AI Assistant Settings</h1>
                    <p>Configure your AI chatbot from this panel — no code editing required.</p>
                </div>
            </div>
            <?php if ($saved): ?>
            <div class="waai-notice-success">✅ Settings saved successfully!</div>
            <?php endif; ?>
        </div>

        <!-- Tab Navigation -->
        <nav class="waai-tabs">
            <a class="waai-tab active" data-tab="ai" href="#tab-ai">🤖 AI Config</a>
            <a class="waai-tab" data-tab="company" href="#tab-company">🏢 Knowledge & Rules</a>
            <a class="waai-tab" data-tab="agent" href="#tab-agent">🪄 Agentic</a>
            <a class="waai-tab" data-tab="integrations" href="#tab-integrations">🔌 Integrations &amp; Leads</a>
            <a class="waai-tab" data-tab="widget" href="#tab-widget">💬 Widget UI</a>
            <a class="waai-tab" data-tab="security" href="#tab-security">🔒 Security</a>
        </nav>

        <form method="post" action="options.php" class="waai-form" novalidate>
            <?php settings_fields('waai_settings_group'); ?>

            <!-- ============================================================
                 TAB 1: AI Configuration
                 ============================================================ -->
            <div id="tab-ai" class="waai-panel active">

                <div class="waai-section">
                    <h2 class="waai-section-title">AI Provider & Model</h2>
                    <p class="waai-section-desc">Choose your AI provider. Get a free key at <a href="https://console.groq.com" target="_blank">Groq</a>, <a href="https://openrouter.ai" target="_blank">OpenRouter</a>, <a href="https://platform.openai.com" target="_blank">OpenAI</a>, or <a href="https://aistudio.google.com" target="_blank">Google AI Studio</a>.</p>

                    <div class="waai-row">
                        <label>AI Provider</label>
                        <div class="waai-field">
                            <select name="waai_provider" id="waai-provider" onchange="waaiProviderHint(this.value)">
                                <option value="groq"        <?= selected($provider, 'groq',        false) ?>>Groq — Free &amp; Very Fast (Recommended)</option>
                                <option value="openrouter"  <?= selected($provider, 'openrouter',  false) ?>>OpenRouter — Many Free Models</option>
                                <option value="openai"      <?= selected($provider, 'openai',      false) ?>>OpenAI / ChatGPT</option>
                                <option value="gemini"      <?= selected($provider, 'gemini',      false) ?>>Google Gemini</option>
                                <option value="anthropic"   <?= selected($provider, 'anthropic',   false) ?>>Anthropic Claude</option>
                                <option value="custom"      <?= selected($provider, 'custom',      false) ?>>Custom (OpenAI Compatible) — e.g. Cerebras, DeepSeek</option>
                            </select>
                        </div>
                    </div>

                    <div class="waai-row" id="waai-custom-endpoint-row" style="display: <?= ($provider === 'custom') ? 'flex' : 'none' ?>;">
                        <label>Custom Endpoint URL</label>
                        <div class="waai-field">
                            <input type="url" name="waai_custom_endpoint" value="<?= esc_attr($custom_endpoint) ?>" placeholder="e.g. https://api.cerebras.ai/v1/chat/completions">
                            <p class="waai-hint">Specify the full endpoint URL for the chat completions API (must be OpenAI-compatible).</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Model Name</label>
                        <div class="waai-field">
                            <input type="text" name="waai_model" value="<?= esc_attr($model) ?>" placeholder="e.g. llama-3.1-8b-instant">
                            <p class="waai-hint" id="waai-model-hint"></p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>API Key <span class="req">*</span></label>
                        <div class="waai-field">
                            <div class="waai-pw-wrap">
                                <input type="password" name="waai_api_key" id="waai-api-key" value="<?= esc_attr($api_key) ?>" placeholder="Paste your API key here">
                                <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-api-key', this)">Show</button>
                            </div>
                            <input type="hidden" name="waai_api_key_groq" id="waai-api-key-groq" value="<?= esc_attr($api_key_groq) ?>">
                            <input type="hidden" name="waai_api_key_openrouter" id="waai-api-key-openrouter" value="<?= esc_attr($api_key_openrouter) ?>">
                            <input type="hidden" name="waai_api_key_openai" id="waai-api-key-openai" value="<?= esc_attr($api_key_openai) ?>">
                            <input type="hidden" name="waai_api_key_gemini" id="waai-api-key-gemini" value="<?= esc_attr($api_key_gemini) ?>">
                            <input type="hidden" name="waai_api_key_custom" id="waai-api-key-custom" value="<?= esc_attr($api_key_custom) ?>">
                            <input type="hidden" name="waai_api_key_anthropic" id="waai-api-key-anthropic" value="<?= esc_attr($api_key_anthropic) ?>">
                            <!-- Per-provider model memory hidden fields -->
                            <input type="hidden" name="waai_model_groq" id="waai-model-groq" value="<?= esc_attr($model_groq) ?>">
                            <input type="hidden" name="waai_model_openrouter" id="waai-model-openrouter" value="<?= esc_attr($model_openrouter) ?>">
                            <input type="hidden" name="waai_model_openai" id="waai-model-openai" value="<?= esc_attr($model_openai) ?>">
                            <input type="hidden" name="waai_model_gemini" id="waai-model-gemini" value="<?= esc_attr($model_gemini) ?>">
                            <input type="hidden" name="waai_model_custom" id="waai-model-custom" value="<?= esc_attr($model_custom) ?>">
                            <input type="hidden" name="waai_model_anthropic" id="waai-model-anthropic" value="<?= esc_attr($model_anthropic) ?>">
                            <p class="waai-hint">🔒 Stored securely in the WordPress database. Never visible to website visitors.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Max Tokens</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_max_tokens" value="<?= esc_attr($max_tokens) ?>" min="100" max="4000" step="50" style="width:120px">
                            <span class="waai-hint">Higher = longer AI answers. Recommended: 800–1200</span>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Temperature</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_temperature" value="<?= esc_attr($temperature) ?>" min="0" max="1" step="0.05" style="width:100px">
                            <span class="waai-hint">0 = factual &amp; precise, 1 = creative. Recommended: 0.65</span>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Long-Context LLM Fallback (For Blogs & Large Pages)</h2>
                    <p class="waai-section-desc">When users visit extremely long pages (like deep blog posts), fast models like Groq often throw Token Rate Limit errors. Enable this feature to seamlessly route requests on massive pages to a secondary, high-capacity model (like OpenRouter's Gemini Flash or Claude 3 Haiku).</p>

                    <div class="waai-row">
                        <label>Enable Long-Context Routing</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_long_context_fallback" value="1" <?= checked($enable_long_context_fallback, '1', false) ?>><span class="waai-slider"></span></span>
                                Automatically use fallback provider when reading very long blog posts (> 5,000 characters).
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Fallback Provider</label>
                        <div class="waai-field">
                            <select name="waai_long_context_provider">
                                <option value="openrouter" <?= selected($long_context_provider, 'openrouter', false) ?>>OpenRouter</option>
                                <option value="openai"     <?= selected($long_context_provider, 'openai',     false) ?>>OpenAI</option>
                                <option value="gemini"     <?= selected($long_context_provider, 'gemini',     false) ?>>Google Gemini</option>
                                <option value="anthropic"  <?= selected($long_context_provider, 'anthropic',  false) ?>>Anthropic Claude</option>
                            </select>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Fallback Model Name</label>
                        <div class="waai-field">
                            <input type="text" name="waai_long_context_model" value="<?= esc_attr($long_context_model) ?>" placeholder="e.g. google/gemini-1.5-flash">
                            <p class="waai-hint">Recommended for OpenRouter: <code>google/gemini-1.5-flash</code> or <code>anthropic/claude-3-haiku</code></p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Fallback API Key</label>
                        <div class="waai-field">
                            <div class="waai-pw-wrap">
                                <input type="password" name="waai_long_context_api_key" id="waai-lc-api-key" value="<?= esc_attr($long_context_api_key) ?>" placeholder="API key for fallback provider">
                                <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-lc-api-key', this)">Show</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Chat Messages</h2>

                    <div class="waai-row">
                        <label>Chat Welcome Message</label>
                        <div class="waai-field">
                            <textarea name="waai_welcome_message" rows="2"><?= esc_textarea($welcome_message) ?></textarea>
                            <p class="waai-hint">Shown when a visitor first opens the chat widget (after a 800ms typing animation).</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Voice Call Greeting</label>
                        <div class="waai-field">
                            <input type="text" name="waai_voice_greeting" value="<?= esc_attr($voice_greeting) ?>">
                            <p class="waai-hint">Spoken aloud when the Live Voice Call connects.</p>
                        </div>
                    </div>
                </div>
            </div><!-- /tab-ai -->

            <!-- ============================================================
                 TAB 2: Knowledge & Rules
                 ============================================================ -->
            <div id="tab-company" class="waai-panel">

                <div class="waai-section">
                    <h2 class="waai-section-title">Company Profile</h2>
                    <p class="waai-section-desc">Everything here is used to build the AI's system prompt dynamically. The AI learns directly from what you enter — no manual prompt editing ever needed.</p>

                    <div class="waai-row">
                        <label>Company Name</label>
                        <div class="waai-field"><input type="text" name="waai_company_name" value="<?= esc_attr($company_name) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Tagline / Slogan</label>
                        <div class="waai-field"><input type="text" name="waai_company_tagline" value="<?= esc_attr($company_tagline) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Location</label>
                        <div class="waai-field"><input type="text" name="waai_company_location" value="<?= esc_attr($company_location) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Phone Number</label>
                        <div class="waai-field"><input type="text" name="waai_company_phone" value="<?= esc_attr($company_phone) ?>" placeholder="+91 ..."></div>
                    </div>
                    <div class="waai-row">
                        <label>Email Address</label>
                        <div class="waai-field"><input type="email" name="waai_company_email" value="<?= esc_attr($company_email) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Website URL</label>
                        <div class="waai-field"><input type="url" name="waai_company_website" value="<?= esc_attr($company_website) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Company Description</label>
                        <div class="waai-field">
                            <textarea name="waai_company_description" rows="4" placeholder="Describe your company, core work, target audience, and mission..."><?= esc_textarea($company_desc) ?></textarea>
                            <p class="waai-hint">2–4 sentences. The AI uses this to introduce your company to visitors.</p>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Services <span class="waai-badge">Up to 8</span></h2>
                    <p class="waai-section-desc">List every service you offer. Include a URL if it has a dedicated page on your website.</p>
                    <div id="waai-services-list">
                        <?php foreach ($services as $i => $svc): ?>
                        <div class="waai-rep-row">
                            <div class="waai-rep-fields">
                                <input type="text" name="waai_services[<?= $i ?>][title]"       value="<?= esc_attr($svc['title']       ?? '') ?>" placeholder="Service Title (e.g. Custom Web Apps)">
                                <input type="text" name="waai_services[<?= $i ?>][description]" value="<?= esc_attr($svc['description'] ?? '') ?>" placeholder="One sentence description">
                                <input type="url"  name="waai_services[<?= $i ?>][url]"         value="<?= esc_attr($svc['url']         ?? '') ?>" placeholder="Service page URL (optional)">
                            </div>
                            <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="waai-add-btn" onclick="waaiAddRow('services', ['Service Title','One sentence description','Service URL (optional)'], ['title','description','url'])">+ Add Service</button>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Key Products <span class="waai-badge">Up to 5</span></h2>
                    <p class="waai-section-desc">Add your key products with their correct URLs. The AI uses these URLs when directing visitors to your products.</p>
                    <div id="waai-products-list">
                        <?php foreach ($products as $i => $prod): ?>
                        <div class="waai-rep-row">
                            <div class="waai-rep-fields">
                                <input type="text" name="waai_products[<?= $i ?>][name]"        value="<?= esc_attr($prod['name']        ?? '') ?>" placeholder="Product Name (e.g. CloudApp)">
                                <input type="text" name="waai_products[<?= $i ?>][description]" value="<?= esc_attr($prod['description'] ?? '') ?>" placeholder="What does it do?">
                                <input type="url"  name="waai_products[<?= $i ?>][url]"         value="<?= esc_attr($prod['url']         ?? '') ?>" placeholder="Product URL (e.g. https://example.com/product)">
                            </div>
                            <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="waai-add-btn" onclick="waaiAddRow('products', ['Product Name','What does it do?','Product URL'], ['name','description','url'])">+ Add Product</button>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">AI Media Gallery <span class="waai-badge">Swipable Carousel</span></h2>
                    <p class="waai-section-desc">Add rich media items (products, portfolio pieces, or dishes). The AI can inject these into the chat as a beautiful, swipable image carousel when the user asks for examples.</p>
                    <div id="waai-gallery-list">
                        <?php foreach ($gallery as $i => $item): ?>
                        <div class="waai-rep-row">
                            <div class="waai-rep-fields">
                                <input type="text" name="waai_gallery[<?= $i ?>][title]"        value="<?= esc_attr($item['title']       ?? '') ?>" placeholder="Item Title (e.g. E-Commerce Store)">
                                <input type="text" name="waai_gallery[<?= $i ?>][description]"  value="<?= esc_attr($item['description'] ?? '') ?>" placeholder="Short Description">
                                <input type="url"  name="waai_gallery[<?= $i ?>][image]"        value="<?= esc_attr($item['image']       ?? '') ?>" placeholder="Image URL (e.g. https://.../image.jpg)">
                            </div>
                            <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" class="waai-add-btn" onclick="waaiAddRow('gallery', ['Item Title','Short Description','Image URL'], ['title','description','image'])">+ Add Media Item</button>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">AI Tone &amp; Rules</h2>
                    <div class="waai-row">
                        <label>Instructions for the AI</label>
                        <div class="waai-field">
                            <textarea name="waai_tone_rules" rows="5" placeholder="e.g. Keep replies under 3 paragraphs. Always suggest booking a consultation. Never make up prices..."><?= esc_textarea($tone_rules) ?></textarea>
                            <p class="waai-hint">These rules are injected into every conversation. Be specific — this directly controls how the AI speaks and behaves.</p>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">AI Loading Tips &amp; Insights</h2>
                    <p class="waai-section-desc">Add custom tips or facts to show visitors while the AI is formulating its response. These help keep the user engaged during API loading delays.</p>
                    <div class="waai-row">
                        <label>Loading Tips / Facts</label>
                        <div class="waai-field">
                            <div id="waai-tips-list">
                                <?php foreach ($loading_tips as $tip): ?>
                                    <div class="waai-rep-row">
                                        <div class="waai-rep-fields">
                                            <input type="text" name="waai_loading_tips[]" value="<?= esc_attr($tip) ?>" placeholder="e.g. A WhatsApp button on your site can increase inquiries by 3x...">
                                        </div>
                                        <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="waai-add-btn" onclick="waaiAddTipRow()">+ Add Tip</button>
                            <p class="waai-hint">Add individual tips or facts to show visitors while the AI is formulating its response.</p>
                        </div>
                    </div>
                    
                    <div class="waai-row" style="margin-top: 20px;">
                        <label>AI Thinking Steps</label>
                        <div class="waai-field">
                            <div id="waai-thinking-steps-list">
                                <?php foreach ($thinking_steps as $step): ?>
                                    <div class="waai-rep-row">
                                        <div class="waai-rep-fields">
                                            <input type="text" name="waai_thinking_steps[]" value="<?= esc_attr($step) ?>" placeholder="e.g. 🔍 Analyzing your request...">
                                        </div>
                                        <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="waai-add-btn" onclick="waaiAddThinkingStepRow()">+ Add Step</button>
                            <p class="waai-hint">Add the sequential steps the AI shows while it's "thinking".</p>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Training Examples (Few-Shot Q&amp;A) <span class="waai-badge">5 pairs</span></h2>
                    <p class="waai-section-desc">Show the AI exactly how to answer common questions. These examples train it to match your brand voice and business context perfectly.</p>
                    <?php for ($i = 0; $i < 5; $i++):
                        $ex = $faq_examples[$i] ?? ['q' => '', 'a' => ''];
                    ?>
                    <div class="waai-example">
                        <div class="waai-example-q">
                            <span class="waai-ex-label">User Question <?= $i + 1 ?></span>
                            <input type="text" name="waai_faq_examples[<?= $i ?>][q]" value="<?= esc_attr($ex['q']) ?>" placeholder="e.g. How much does a website cost?">
                        </div>
                        <div class="waai-example-a">
                            <span class="waai-ex-label">AI Answer <?= $i + 1 ?></span>
                            <textarea name="waai_faq_examples[<?= $i ?>][a]" rows="2" placeholder="e.g. Our website pricing is custom-scoped to your needs. A basic business site typically starts around ₹15,000..."><?= esc_textarea($ex['a']) ?></textarea>
                        </div>
                    </div>
                    <?php endfor; ?>
                </div>
                <div class="waai-section">
                    <h2 class="waai-section-title">Context &amp; Memory Management</h2>
                    <p class="waai-section-desc">Control what gets sent to the AI on every request. Disabling heavy features here significantly reduces API token usage.</p>

                    <div class="waai-row">
                        <label>Live Page Text Scraping</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_page_text" value="1" <?= checked($enable_page_text, '1', false) ?>><span class="waai-slider"></span></span>
                                Send up to 3,000 characters of the active webpage's text to the AI
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Live Page Links &amp; Buttons</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_page_links" value="1" <?= checked($enable_page_links, '1', false) ?>><span class="waai-slider"></span></span>
                                Send clickable elements so the AI can help users navigate
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Chat History Limit</label>
                        <div class="waai-field">
                            <select name="waai_history_limit">
                                <option value="5" <?= selected($history_limit, '5', false) ?>>Last 5 messages (Lowest Cost)</option>
                                <option value="10" <?= selected($history_limit, '10', false) ?>>Last 10 messages (Balanced)</option>
                                <option value="20" <?= selected($history_limit, '20', false) ?>>Last 20 messages (Maximum Memory)</option>
                            </select>
                            <p class="waai-hint">How many previous messages the AI remembers in an active conversation.</p>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Knowledge Injection</h2>
                    <p class="waai-section-desc">Toggle off sections you don't need injected into every single prompt.</p>

                    <div class="waai-row">
                        <label>Inject Services</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_services" value="1" <?= checked($enable_services, '1', false) ?>><span class="waai-slider"></span></span>
                                Include "Services" list in system prompt
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Inject Products</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_products" value="1" <?= checked($enable_products, '1', false) ?>><span class="waai-slider"></span></span>
                                Include "Products" list in system prompt
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Inject Media Gallery</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_gallery" value="1" <?= checked($enable_gallery, '1', false) ?>><span class="waai-slider"></span></span>
                                Include "AI Media Gallery" carousel items in system prompt
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Inject FAQ Examples</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enable_faq" value="1" <?= checked($enable_faq, '1', false) ?>><span class="waai-slider"></span></span>
                                Include "FAQ Training Examples" in system prompt
                            </label>
                        </div>
                    </div>
                </div>
            </div><!-- /tab-company -->

            <!-- ============================================================
                 TAB: Agentic Navigation
                 ============================================================ -->
            <div id="tab-agent" class="waai-panel">
                <div class="waai-section">
                    <h2 class="waai-section-title">Agentic Website Navigation</h2>
                    <p class="waai-section-desc">Enable the AI to act as a co-pilot, automatically scrolling the page or directing the user to specific sections when asked.</p>
                    <div class="waai-row">
                        <label>Enable Agentic</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_agentic_enabled" value="1" <?= checked($agentic_enabled, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Give the AI ability to execute DOM navigation commands
                            </label>
                            <p class="waai-hint">Requires a model that supports function calling (e.g. Llama-3 or GPT-4o).</p>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Agentic Features & Context Controls</h2>
                    <p class="waai-section-desc">Control what data the AI can remember and access to save tokens or improve performance.</p>
                    
                    <div class="waai-row">
                        <label>Global Site Index</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_site_index" value="1" <?= checked($enable_site_index, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Inject the entire site map into the prompt for Semantic Routing
                            </label>
                        </div>
                    </div>
                    
                    <div class="waai-row">
                        <label>Conversational Memory</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_conversational_memory" value="1" <?= checked($enable_conversational_memory, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Store and pass the 'last_action' context to the LLM
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Multi-Step Task Queue</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_task_queue" value="1" <?= checked($enable_task_queue, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Allow the AI to chain commands across page redirects
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Post-Nav Awareness</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_post_nav" value="1" <?= checked($enable_post_nav, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Enable silent contextual queries after the AI redirects the user
                            </label>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Allowed Actions (Tool Gating)</h2>
                    <p class="waai-section-desc">Selectively enable or disable specific tools to restrict what the AI can do on the page.</p>

                    <div class="waai-row">
                        <label>Allow Scrolling</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_action_scroll" value="1" <?= checked($enable_action_scroll, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Allow the AI to use <code>scroll_to_element</code>
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Allow Clicking & Forms</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_action_interact" value="1" <?= checked($enable_action_interact, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Allow the AI to use <code>interact_with_element</code>
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Allow Navigation</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_action_navigate" value="1" <?= checked($enable_action_navigate, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Allow the AI to use <code>navigate_website</code> to redirect the user
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Allow Reading/Scraping</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_enable_action_read" value="1" <?= checked($enable_action_read, '1', false) ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Allow the AI to use <code>read_page_content</code>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Website Sections Mapping <span class="waai-badge">DOM Tools</span></h2>
                    <p class="waai-section-desc">Define the sections on your current page so the AI knows what it can scroll to. Use valid CSS Selectors (e.g. <code>#pricing-table</code>).</p>
                    <div id="waai-agentic_sections-list">
                        <?php foreach ($agentic_sections as $i => $sec): ?>
                        <div class="waai-rep-row">
                            <div class="waai-rep-fields">
                                <input type="text" name="waai_agentic_sections[<?= $i ?>][name]"     value="<?= esc_attr($sec['name']     ?? '') ?>" placeholder="Section Name (e.g. Pricing or Contact)">
                                <input type="text" name="waai_agentic_sections[<?= $i ?>][selector]" value="<?= esc_attr($sec['selector'] ?? '') ?>" placeholder="CSS Selector or URL (e.g. #pricing or /contact/)">
                            </div>
                            <button type="button" class="waai-del-row" onclick="this.closest('.waai-rep-row').remove()">✕</button>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 15px;">
                        <button type="button" class="waai-add-btn" onclick="waaiAddRow('agentic_sections', ['Section Name','CSS Selector or URL'], ['name','selector'])">+ Add Section</button>
                        <button type="button" class="waai-add-btn" style="background: #e3deff; color: #5f39ff; border: 1px solid #d0c7ff;" onclick="waaiAutoMapSections(this)">🪄 Auto Map Homepage Sections</button>
                        <button type="button" class="waai-add-btn" style="background: #ffe3de; color: #ff5f39; border: 1px solid #ffd0c7;" onclick="waaiAutoMapLinks(this)">🔗 Auto Map Site Links</button>
                    </div>
                </div>

                <!-- Sitemap Post Type Filter -->
                <div class="waai-section" id="waai-cpt-filter-section">
                    <h2 class="waai-section-title">🗂️ Sitemap Post Type Filter <span class="waai-badge">Global Sitemap</span></h2>
                    <p class="waai-section-desc">Control which WordPress post types contribute links to the AI's Global Sitemap. Uncheck types like <code>pricing_plan</code> to prevent junk URLs from filling the AI context window.</p>

                    <div class="waai-row">
                        <label>Included Post Types</label>
                        <div class="waai-field">
                            <button type="button" class="waai-add-btn" style="margin-bottom:14px;" onclick="waaiDetectPostTypes(this)">🔍 Detect Post Types</button>
                            <div id="waai-cpt-list" style="display:flex; flex-direction:column; gap:8px; margin-top:4px;">
                                <input type="hidden" name="waai_sitemap_post_types" value="">
                                <?php
                                $all_public = function_exists('get_post_types') ? get_post_types(['public' => true], 'objects') : [];
                                unset($all_public['attachment']);

                                if ($sitemap_post_types === 'not_set') {
                                    $checked_types = array_keys($all_public);
                                } else {
                                    $checked_types = is_array($sitemap_post_types) ? $sitemap_post_types : (empty($sitemap_post_types) ? [] : [$sitemap_post_types]);
                                }

                                foreach ($all_public as $slug => $obj):
                                    $checked = in_array($slug, $checked_types);
                                ?>
                                <label style="display:flex; align-items:center; gap:8px; font-weight:400; cursor:pointer;">
                                    <input type="checkbox"
                                           name="waai_sitemap_post_types[]"
                                           value="<?= esc_attr($slug) ?>"
                                           <?= $checked ? 'checked' : '' ?>
                                           style="width:16px; height:16px; cursor:pointer;">
                                    <span><strong><?= esc_html($slug) ?></strong> &mdash; <?= esc_html($obj->labels->name) ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                            <p class="waai-hint" style="margin-top:10px;">Click <strong>Detect Post Types</strong> to refresh the list from your live WordPress installation. Then uncheck any types you want to exclude, and click <strong>Save Settings</strong> below.</p>
                        </div>
                    </div>
                </div>

            </div><!-- /tab-agent -->

            <!-- ============================================================
                 TAB: Integrations & Leads
                 (Consolidated: Lead Capture + Calendar + WhatsApp API + Email API)
                 ============================================================ -->
            <div id="tab-integrations" class="waai-panel">

                <!-- ─── Section 1: Lead Capture ─── -->
                <div class="waai-section">
                    <h2 class="waai-section-title">📋 Lead Capture Settings</h2>
                    <p class="waai-section-desc">Configure where new leads from the chat widget are saved. All three destinations can be active simultaneously.</p>

                    <div class="waai-row">
                        <label>Lead Form</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_lead_form_enabled" value="1" <?= checked($lead_form_enabled, '1') ?>><span class="waai-slider"></span></span>
                                Enable contact form overlay in the chat widget
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Notification Email</label>
                        <div class="waai-field">
                            <input type="email" name="waai_lead_email" value="<?= esc_attr($lead_email) ?>" placeholder="owner@yourcompany.com">
                            <p class="waai-hint">An email is sent here every time a visitor submits the lead form.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>WordPress DB</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_save_leads_db" value="1" <?= checked($save_leads_db, '1') ?>><span class="waai-slider"></span></span>
                                Save leads to the <strong>AI Assistant → Leads</strong> admin table
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Google Sheets Webhook</label>
                        <div class="waai-field">
                            <input type="url" name="waai_sheets_webhook" value="<?= esc_attr($sheets_webhook) ?>" placeholder="https://script.google.com/macros/s/...">
                            <p class="waai-hint">Leave empty to skip Google Sheets sync. See <a href="<?= esc_url(get_template_directory_uri() . '/webassets-ai-assistant/README.md') ?>" target="_blank">README.md</a> for setup steps.</p>
                        </div>
                    </div>

                    <div class="waai-info-box">
                        <strong>📊 View &amp; Manage Leads</strong><br>
                        Go to <a href="<?= admin_url('admin.php?page=waai-leads') ?>"><strong>AI Assistant → Leads</strong></a> to browse, search, and export all captured leads.
                    </div>
                </div>

                <!-- ─── Section 2: Booking Calendar ─── -->
                <div class="waai-section">
                    <h2 class="waai-section-title">📅 Booking Calendar</h2>
                    <p class="waai-section-desc">When the AI detects that a visitor wants to book a consultation, it opens the booking interface you select here.</p>

                    <div class="waai-row">
                        <label>Calendar Provider</label>
                        <div class="waai-field">
                            <div class="waai-radio-group">
                                <?php
                                $cal_options = [
                                    'calendly' => ['📅', 'Calendly'],
                                    'google'   => ['📆', 'Google Calendar'],
                                    'custom'   => ['🔗', 'Custom URL'],
                                    'disabled' => ['🚫', 'Disabled'],
                                ];
                                foreach ($cal_options as $val => [$icon, $label]):
                                ?>
                                <label class="waai-radio-card <?= $calendar_type === $val ? 'active' : '' ?>">
                                    <input type="radio" name="waai_calendar_type" value="<?= $val ?>" <?= checked($calendar_type, $val) ?> onchange="waaiCalSwitch('<?= $val ?>', this)">
                                    <span class="waai-radio-icon"><?= $icon ?></span>
                                    <span class="waai-radio-label"><?= $label ?></span>
                                </label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div id="waai-cal-calendly" class="waai-cal-sub" style="<?= $calendar_type !== 'calendly' ? 'display:none' : '' ?>">
                        <div class="waai-row">
                            <label>Calendly URL</label>
                            <div class="waai-field">
                                <input type="url" name="waai_calendly_url" value="<?= esc_attr($calendly_url) ?>" placeholder="https://calendly.com/your-name/30min">
                                <p class="waai-hint">Calendly account → Share → Copy event link.</p>
                            </div>
                        </div>
                    </div>

                    <div id="waai-cal-google" class="waai-cal-sub" style="<?= $calendar_type !== 'google' ? 'display:none' : '' ?>">
                        <div class="waai-row">
                            <label>Google Calendar Embed URL</label>
                            <div class="waai-field">
                                <input type="url" name="waai_google_calendar_url" value="<?= esc_attr($google_cal_url) ?>" placeholder="https://calendar.google.com/calendar/embed?src=...">
                                <p class="waai-hint">Google Calendar → Settings → Integrate calendar → Copy embed URL.</p>
                            </div>
                        </div>
                    </div>

                    <div id="waai-cal-custom" class="waai-cal-sub" style="<?= $calendar_type !== 'custom' ? 'display:none' : '' ?>">
                        <div class="waai-row">
                            <label>Custom Booking URL</label>
                            <div class="waai-field">
                                <input type="url" name="waai_custom_calendar_url" value="<?= esc_attr($custom_cal_url) ?>" placeholder="https://your-booking-page.com">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ─── Section 3: WhatsApp Send API ─── -->
                <div class="waai-section">
                    <h2 class="waai-section-title">💬 WhatsApp Send API Configuration</h2>
                    <p class="waai-section-desc">Allow visitors to request details, services, or pricing to be sent directly to their WhatsApp number using the API.</p>

                    <div class="waai-row">
                        <label>Enable WhatsApp Send API</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_whatsapp_api_enabled" value="1" <?= checked($whatsapp_api_enabled, '1') ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Enable forwarding details/pricing/services to WhatsApp
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>WhatsApp App Key</label>
                        <div class="waai-field">
                            <input type="text" name="waai_whatsapp_app_key" value="<?= esc_attr($whatsapp_app_key) ?>" placeholder="Enter App Key">
                            <p class="waai-hint">The application key for authorizing WhatsApp API transactions.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>WhatsApp Auth Key</label>
                        <div class="waai-field">
                            <div class="waai-pw-wrap">
                                <input type="password" id="waai-whatsapp-auth-key" name="waai_whatsapp_auth_key" value="<?= esc_attr($whatsapp_auth_key) ?>" placeholder="Enter Auth Key">
                                <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-whatsapp-auth-key', this)">Show</button>
                            </div>
                            <p class="waai-hint">The authentication key for your WhatsApp user account.</p>
                        </div>
                    </div>
                </div>

                <!-- ─── Section 4: Email API ─── -->
                <div class="waai-section">
                    <h2 class="waai-section-title">✉️ Email Forwarding Configuration</h2>
                    <p class="waai-section-desc">Allow visitors to request details, services, or pricing to be sent directly to their email address.</p>

                    <div class="waai-row">
                        <label>Enable Email Sending</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_email_api_enabled" value="1" <?= checked($email_api_enabled, '1') ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Enable AI to forward details/pricing/services to email
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Email Delivery Method</label>
                        <div class="waai-field">
                            <select name="waai_email_method" id="waai-email-method" onchange="document.getElementById('waai-smtp-settings').style.display = this.value === 'smtp' ? 'block' : 'none';">
                                <option value="wp_mail" <?= selected($email_method, 'wp_mail', false) ?>>WordPress Default (wp_mail)</option>
                                <option value="smtp" <?= selected($email_method, 'smtp', false) ?>>Custom SMTP Configuration</option>
                            </select>
                            <p class="waai-hint">Select how emails should be dispatched. If you already use an SMTP plugin, "WordPress Default" will use it.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>From Name</label>
                        <div class="waai-field">
                            <input type="text" name="waai_email_from_name" value="<?= esc_attr($email_from_name) ?>" placeholder="Your Company Name">
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>From Address</label>
                        <div class="waai-field">
                            <input type="email" name="waai_email_from_address" value="<?= esc_attr($email_from_address) ?>" placeholder="no-reply@yourdomain.com">
                        </div>
                    </div>

                    <div id="waai-smtp-settings" style="<?= $email_method === 'smtp' ? 'display:block;' : 'display:none;' ?>">
                        <div class="waai-row">
                            <label>SMTP Host</label>
                            <div class="waai-field">
                                <input type="text" name="waai_smtp_host" value="<?= esc_attr($smtp_host) ?>" placeholder="smtp.gmail.com">
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>SMTP Port</label>
                            <div class="waai-field">
                                <input type="number" name="waai_smtp_port" value="<?= esc_attr($smtp_port) ?>" placeholder="587" style="width: 100px;">
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>SMTP Encryption</label>
                            <div class="waai-field">
                                <select name="waai_smtp_secure" style="width: 100px;">
                                    <option value="tls" <?= selected($smtp_secure, 'tls', false) ?>>TLS</option>
                                    <option value="ssl" <?= selected($smtp_secure, 'ssl', false) ?>>SSL</option>
                                    <option value="none" <?= selected($smtp_secure, 'none', false) ?>>None</option>
                                </select>
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>SMTP Username</label>
                            <div class="waai-field">
                                <input type="text" name="waai_smtp_user" value="<?= esc_attr($smtp_user) ?>" placeholder="you@domain.com">
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>SMTP Password</label>
                            <div class="waai-field">
                                <div class="waai-pw-wrap">
                                    <input type="password" id="waai-smtp-pass" name="waai_smtp_pass" value="<?= esc_attr($smtp_pass) ?>">
                                    <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-smtp-pass', this)">Show</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div><!-- /tab-integrations -->

            <!-- ============================================================
                 TAB 5: Widget UI
                 ============================================================ -->
            <div id="tab-widget" class="waai-panel">

                <div class="waai-section">
                    <h2 class="waai-section-title">Widget Appearance</h2>

                    <div class="waai-row">
                        <label>Widget Enabled</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle"><input type="checkbox" name="waai_enabled" value="1" <?= checked($widget_enabled, '1') ?>><span class="waai-slider"></span></span>
                                Show the AI chat widget on all pages
                            </label>
                        </div>
                    </div>
                    <div class="waai-row">
                        <label>Widget Title</label>
                        <div class="waai-field"><input type="text" name="waai_widget_title" value="<?= esc_attr($widget_title) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Widget Subtitle</label>
                        <div class="waai-field"><input type="text" name="waai_widget_subtitle" value="<?= esc_attr($widget_subtitle) ?>"></div>
                    </div>
                    <div class="waai-row">
                        <label>Avatar Initials</label>
                        <div class="waai-field">
                            <input type="text" name="waai_avatar_initials" value="<?= esc_attr($avatar_initials) ?>" maxlength="3" style="width:80px">
                            <p class="waai-hint">2–3 letters shown in the avatar circle.</p>
                        </div>
                    </div>
                    <div class="waai-row">
                        <label>Accent Color</label>
                        <div class="waai-field waai-inline">
                            <input type="color" name="waai_accent_color" id="waai-color" value="<?= esc_attr($accent_color) ?>" oninput="document.getElementById('waai-hex').value=this.value">
                            <input type="text" id="waai-hex" value="<?= esc_attr($accent_color) ?>" style="width:110px" readonly>
                        </div>
                    </div>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Quick Suggestion Chips</h2>
                    <p class="waai-section-desc">The 3 quick-reply buttons shown at the bottom of the chat when first opened.</p>
                    <?php
                    $chips = [
                        ['n'=>1,'label'=>$sug1_label,'query'=>$sug1_query],
                        ['n'=>2,'label'=>$sug2_label,'query'=>$sug2_query],
                        ['n'=>3,'label'=>$sug3_label,'query'=>$sug3_query],
                    ];
                    foreach ($chips as $c): ?>
                    <div class="waai-chip-row">
                        <span class="waai-chip-num"><?= $c['n'] ?></span>
                        <input type="text" name="waai_suggestion_<?= $c['n'] ?>_label" value="<?= esc_attr($c['label']) ?>" placeholder="Button label (with emoji)">
                        <input type="text" name="waai_suggestion_<?= $c['n'] ?>_query" value="<?= esc_attr($c['query']) ?>" placeholder="Message sent to AI when clicked">
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="waai-section">
                    <h2 class="waai-section-title">Voice &amp; Engagement</h2>

                    <div class="waai-row">
                        <label>Text-to-Speech Engine</label>
                        <div class="waai-field">
                            <select name="waai_speech_engine" onchange="waaiSpeechSwitch(this.value)">
                                <option value="web_speech" <?= selected($speech_engine, 'web_speech', false) ?>>Browser Native (Free)</option>
                                <option value="piper" <?= selected($speech_engine, 'piper', false) ?>>Piper (Local Server — Free)</option>
                                <option value="sarvam" <?= selected($speech_engine, 'sarvam', false) ?>>Sarvam AI (Premium API — High Quality Hindi)</option>
                                <option value="groq_orpheus" <?= selected($speech_engine, 'groq_orpheus', false) ?>>Groq Orpheus — Uses your AI API Key</option>
                                <option value="elevenlabs" <?= selected($speech_engine, 'elevenlabs', false) ?>>ElevenLabs (Premium API)</option>
                            </select>
                            <p class="waai-hint">Choose your text-to-speech engine. Groq Orpheus reuses the API key from the AI Config tab.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Enable Voice Interruptions</label>
                        <div class="waai-field">
                            <label class="waai-toggle">
                                <input type="checkbox" name="waai_enable_interruptions" value="1" <?= checked($enable_interruptions, '1', false) ?>>
                                <span class="waai-slider"></span>
                            </label>
                            <p class="waai-hint">
                                When ON, the AI can be interrupted naturally while speaking (Full Duplex mode).<br>
                                When OFF, the microphone is disabled while the AI speaks (Walkie-Talkie mode). <b>Turn this OFF to prevent STT hallucinations when using Sarvam's Indian accent voices in English.</b>
                            </p>
                        </div>
                    </div>

                    <div id="waai-sarvam-settings" style="<?= ($speech_engine === 'sarvam') ? 'display:block;' : 'display:none;' ?>">
                        <div class="waai-row">
                            <label>Sarvam API Key</label>
                            <div class="waai-field">
                                <div class="waai-pw-wrap">
                                    <input type="password" name="waai_sarvam_api_key" id="waai-sarvam-key" value="<?= esc_attr($sarvam_api_key) ?>" placeholder="Paste your Sarvam api-subscription-key">
                                    <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-sarvam-key', this)">Show</button>
                                </div>
                                <p class="waai-hint">Get your key from <a href="https://dashboard.sarvam.ai/" target="_blank">dashboard.sarvam.ai</a>.</p>
                            </div>
                        </div>
                    </div>

                    <div id="waai-groq-tts-settings" style="<?= $speech_engine === 'groq_orpheus' ? 'display:block;' : 'display:none;' ?>">
                        <div class="waai-row">
                            <label>TTS Model</label>
                            <div class="waai-field">
                                <div class="waai-radio-group">
                                    <label class="waai-radio-card <?= $groq_tts_model === 'canopylabs/orpheus-v1-english' ? 'active' : '' ?>">
                                        <input type="radio" name="waai_groq_tts_model" value="canopylabs/orpheus-v1-english" <?= checked($groq_tts_model, 'canopylabs/orpheus-v1-english') ?> onchange="waaiGroqModelSwitch(this.value, this)">
                                        <span class="waai-radio-icon">🇬🇧</span>
                                        <span class="waai-radio-label">English</span>
                                    </label>
                                    <label class="waai-radio-card <?= $groq_tts_model === 'canopylabs/orpheus-arabic-saudi' ? 'active' : '' ?>">
                                        <input type="radio" name="waai_groq_tts_model" value="canopylabs/orpheus-arabic-saudi" <?= checked($groq_tts_model, 'canopylabs/orpheus-arabic-saudi') ?> onchange="waaiGroqModelSwitch(this.value, this)">
                                        <span class="waai-radio-icon">🇸🇦</span>
                                        <span class="waai-radio-label">Arabic (Saudi)</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>Voice</label>
                            <div class="waai-field">
                                <select name="waai_groq_tts_voice" id="waai-groq-voice">
                                    <optgroup label="English Voices" id="waai-groq-voices-en" <?= $groq_tts_model !== 'canopylabs/orpheus-v1-english' ? 'style="display:none"' : '' ?>>
                                        <option value="troy"   <?= selected($groq_tts_voice, 'troy',   false) ?>>Troy (Male)</option>
                                        <option value="austin" <?= selected($groq_tts_voice, 'austin', false) ?>>Austin (Male)</option>
                                        <option value="daniel" <?= selected($groq_tts_voice, 'daniel', false) ?>>Daniel (Male)</option>
                                        <option value="autumn" <?= selected($groq_tts_voice, 'autumn', false) ?>>Autumn (Female)</option>
                                        <option value="diana"  <?= selected($groq_tts_voice, 'diana',  false) ?>>Diana (Female)</option>
                                        <option value="hannah" <?= selected($groq_tts_voice, 'hannah', false) ?>>Hannah (Female)</option>
                                    </optgroup>
                                    <optgroup label="Arabic Saudi Voices" id="waai-groq-voices-ar" <?= $groq_tts_model !== 'canopylabs/orpheus-arabic-saudi' ? 'style="display:none"' : '' ?>>
                                        <option value="abdullah" <?= selected($groq_tts_voice, 'abdullah', false) ?>>Abdullah (Male)</option>
                                        <option value="fahad"    <?= selected($groq_tts_voice, 'fahad',    false) ?>>Fahad (Male)</option>
                                        <option value="sultan"   <?= selected($groq_tts_voice, 'sultan',   false) ?>>Sultan (Male)</option>
                                        <option value="lulwa"    <?= selected($groq_tts_voice, 'lulwa',    false) ?>>Lulwa (Female)</option>
                                        <option value="noura"    <?= selected($groq_tts_voice, 'noura',    false) ?>>Noura (Female)</option>
                                        <option value="aisha"    <?= selected($groq_tts_voice, 'aisha',    false) ?>>Aisha (Female)</option>
                                    </optgroup>
                                </select>
                                <p class="waai-hint">Select a voice persona. English voices support vocal directions like <code>[cheerful]</code>, <code>[whisper]</code>.</p>
                            </div>
                        </div>
                        <div class="waai-info-box">
                            <strong>💡 No extra API key needed!</strong><br>
                            Groq Orpheus uses the same Groq API key you configured in the <strong>AI Config</strong> tab. Just pick a voice and save.
                        </div>
                    </div>

                    <div id="waai-elevenlabs-settings" style="<?= $speech_engine === 'elevenlabs' ? 'display:block;' : 'display:none;' ?>">
                        <div class="waai-row">
                            <label>ElevenLabs API Key</label>
                            <div class="waai-field">
                                <div class="waai-pw-wrap">
                                    <input type="password" name="waai_elevenlabs_api_key" id="waai-elevenlabs-key" value="<?= esc_attr($elevenlabs_api_key) ?>" placeholder="Paste your ElevenLabs API key">
                                    <button type="button" class="waai-pw-btn" onclick="waaiTogglePw('waai-elevenlabs-key', this)">Show</button>
                                </div>
                            </div>
                        </div>
                        <div class="waai-row">
                            <label>ElevenLabs Voice ID</label>
                            <div class="waai-field">
                                <input type="text" name="waai_elevenlabs_voice_id" value="<?= esc_attr($elevenlabs_voice_id) ?>" placeholder="e.g. pNInz6obpgDQGcFmaJcg">
                                <p class="waai-hint">Find your Voice ID in the ElevenLabs VoiceLab.</p>
                            </div>
                        </div>
                    </div>

                    <div id="waai-piper-settings" style="<?= $speech_engine === 'piper' ? 'display:block;' : 'display:none;' ?>">
                        <div class="waai-row">
                            <label>Piper API URL</label>
                            <div class="waai-field">
                                <input type="text" name="waai_piper_url" value="<?= esc_attr($piper_url) ?>" placeholder="e.g. http://127.0.0.1:5000">
                                <p class="waai-hint">The local URL of your Piper HTTP Server (typically http://127.0.0.1:5000). Handled securely by the backend proxy to prevent HTTPS mixed content issues.</p>
                            </div>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>WhatsApp Number</label>
                        <div class="waai-field">
                            <input type="tel" name="waai_whatsapp_number" value="<?= esc_attr($whatsapp_number) ?>" placeholder="919876543210 (country code + number, no + or spaces)">
                            <p class="waai-hint">A WhatsApp button appears in the chat header. Leave empty to hide it.</p>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Proactive Trigger</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_proactive_delay" value="<?= esc_attr($proactive_delay) ?>" min="0" max="300" style="width:100px">
                            <span>seconds after page load (0 = disabled)</span>
                        </div>
                        <p class="waai-hint" style="margin-left:180px">The chat button pulses and shows a greeting after this many seconds on any page.</p>
                    </div>
                </div>
            </div><!-- /tab-widget -->



            <!-- ============================================================
                 TAB 7: Security
                 ============================================================ -->
            <div id="tab-security" class="waai-panel">

                <!-- Section 1: Global Toggle -->
                <div class="waai-section">
                    <h2 class="waai-section-title">🔒 Rate Limiting &amp; Abuse Protection</h2>
                    <p class="waai-section-desc">Protect your API quota and server from abuse. All limits are per-IP per hour unless stated otherwise.</p>

                    <div class="waai-row">
                        <label>Enable Rate Limiting</label>
                        <div class="waai-field">
                            <label class="waai-toggle-row">
                                <span class="waai-toggle">
                                    <input type="checkbox" name="waai_rate_limiting_enabled" value="1" <?= checked($rate_limiting_enabled, '1') ?>>
                                    <span class="waai-slider"></span>
                                </span>
                                Enable all rate limiting &amp; abuse protection
                            </label>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Max Input Length</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_max_input_chars" value="<?= esc_attr($max_input_chars) ?>" min="100" max="5000" style="width:120px">
                            <span>characters per user message. Longer messages are truncated. Recommended: <strong>1000</strong></span>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Per-Action Limits -->
                <div class="waai-section">
                    <h2 class="waai-section-title">⏱️ Per-Action Rate Limits <span class="waai-badge">per IP / hour</span></h2>
                    <p class="waai-section-desc">Set the maximum number of requests each action can make per IP address per hour. Set to <code>0</code> to disable limiting for that action.</p>

                    <div class="waai-row">
                        <label>Chat (AI Replies)</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_rate_limit" value="<?= esc_attr($rate_limit) ?>" min="0" max="10000" style="width:100px">
                            <span>requests / IP / hour. Recommended: <strong>50</strong></span>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Piper TTS (Local)</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_rate_limit_tts" value="<?= esc_attr($rate_limit_tts) ?>" min="0" max="10000" style="width:100px">
                            <span>requests / IP / hour. Recommended: <strong>500</strong> (many small chunks per response)</span>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Sarvam TTS (API)</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_rate_limit_sarvam" value="<?= esc_attr($rate_limit_sarvam) ?>" min="0" max="10000" style="width:100px">
                            <span>requests / IP / hour. Recommended: <strong>500</strong> (many small chunks per voice session)</span>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>Lead Submissions</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_rate_limit_lead" value="<?= esc_attr($rate_limit_lead) ?>" min="0" max="1000" style="width:100px">
                            <span>submissions / IP / hour. Recommended: <strong>10</strong></span>
                        </div>
                    </div>

                    <div class="waai-row">
                        <label>WhatsApp &amp; Email Send</label>
                        <div class="waai-field waai-inline">
                            <input type="number" name="waai_rate_limit_whatsapp_email" value="<?= esc_attr($rate_limit_wa_email) ?>" min="0" max="1000" style="width:100px">
                            <span>sends / IP / hour. Recommended: <strong>5</strong> (prevents spam abuse)</span>
                        </div>
                    </div>

                    <div class="waai-info-box">
                        ⚠️ <strong>Important:</strong> TTS limits count each audio <em>chunk</em>, not each full AI response. A single voice reply is split into 3–8 chunks, so keep TTS limits high (400+) to avoid interruptions during voice calls.
                    </div>
                </div>

            </div><!-- /tab-security -->

            <!-- Sticky Save Bar -->
            <div class="waai-save-bar">
                <?php submit_button('💾 Save All Settings', 'primary large', 'submit', false); ?>
                <span class="waai-save-note">Changes apply immediately to the live chat widget.</span>
            </div>

        </form>
        </div><!-- /wrap -->

        <style>
        /* =====================================================
           WebAssets AI — Admin Page Styles
           ===================================================== */
        .waai-wrap { max-width: 1100px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
        .waai-header { display:flex; align-items:center; justify-content:space-between; padding:20px 0 18px; border-bottom:2px solid #f0f0f0; margin-bottom:0; }
        .waai-header-left { display:flex; align-items:center; gap:16px; }
        .waai-header-icon { font-size:40px; line-height:1; }
        .waai-header h1 { margin:0; font-size:22px; color:#1d2327; }
        .waai-header p { margin:4px 0 0; color:#646970; font-size:13px; }
        .waai-notice-success { background:#d1fae5; border:1px solid #10b981; color:#065f46; padding:10px 20px; border-radius:8px; font-weight:600; font-size:13px; }

        /* Tabs */
        .waai-tabs { display:flex; gap:2px; border-bottom:2px solid #e5e7eb; flex-wrap:wrap; margin-top:24px; }
        .waai-tab { padding:10px 18px; text-decoration:none; color:#555; font-size:13px; font-weight:600; border-radius:8px 8px 0 0; border:1px solid transparent; border-bottom:none; transition:all .2s; margin-bottom:-2px; cursor:pointer; }
        .waai-tab:hover { color:#5f39ff; background:rgba(95,57,255,.06); }
        .waai-tab.active { color:#5f39ff; background:#fff; border-color:#e5e7eb; border-bottom-color:#fff; }

        /* Panels */
        .waai-panel { display:none; background:#fff; border:1px solid #e5e7eb; border-top:none; border-radius:0 0 12px 12px; }
        .waai-panel.active { display:block; }

        /* Sections */
        .waai-section { padding:28px 32px; border-bottom:1px solid #f4f4f5; }
        .waai-section:last-child { border-bottom:none; }
        .waai-section-title { font-size:15px; font-weight:700; color:#1d2327; margin:0 0 6px; display:flex; align-items:center; gap:10px; }
        .waai-section-desc { font-size:13px; color:#6b7280; margin:0 0 22px; line-height:1.6; }
        .waai-badge { background:#ede9fe; color:#5f39ff; font-size:11px; font-weight:700; padding:2px 9px; border-radius:10px; }
        .req { color:#ef4444; }

        /* Field rows */
        .waai-row { display:flex; align-items:flex-start; gap:24px; margin-bottom:18px; }
        .waai-row > label:first-child { width:170px; min-width:170px; font-size:13px; font-weight:600; color:#374151; padding-top:9px; }
        .waai-field { flex:1; }
        .waai-field input[type=text], .waai-field input[type=email], .waai-field input[type=url],
        .waai-field input[type=tel], .waai-field input[type=number], .waai-field input[type=password],
        .waai-field textarea, .waai-field select {
            width:100%; padding:9px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px;
            font-family:inherit; outline:none; transition:border-color .2s; box-sizing:border-box; background:#fff;
        }
        .waai-field input:focus, .waai-field textarea:focus, .waai-field select:focus { border-color:#5f39ff; box-shadow:0 0 0 3px rgba(95,57,255,.1); }
        .waai-field textarea { resize:vertical; min-height:80px; }
        .waai-inline { display:flex; align-items:center; gap:12px; }
        .waai-inline input { flex:0 0 auto !important; }
        .waai-hint { font-size:12px; color:#6b7280; margin:5px 0 0; line-height:1.5; }
        .waai-hint a { color:#5f39ff; }

        /* Password field */
        .waai-pw-wrap { display:flex; gap:8px; }
        .waai-pw-wrap input { flex:1; }
        .waai-pw-btn { padding:8px 14px; border:1px solid #d1d5db; border-radius:7px; background:#f9fafb; cursor:pointer; font-size:12px; font-weight:600; color:#555; white-space:nowrap; }
        .waai-pw-btn:hover { background:#5f39ff; color:#fff; border-color:#5f39ff; }

        /* Toggle switch */
        .waai-toggle-row { display:flex; align-items:center; gap:12px; cursor:pointer; font-size:13px; color:#374151; padding-top:6px; }
        .waai-toggle { position:relative; display:inline-block; width:44px; height:24px; flex-shrink:0; }
        .waai-toggle input { opacity:0; width:0; height:0; }
        .waai-slider { position:absolute; cursor:pointer; inset:0; background:#d1d5db; border-radius:24px; transition:.3s; }
        .waai-slider:before { content:""; position:absolute; height:18px; width:18px; left:3px; bottom:3px; background:#fff; border-radius:50%; transition:.3s; box-shadow:0 1px 3px rgba(0,0,0,.2); }
        .waai-toggle input:checked + .waai-slider { background:#5f39ff; }
        .waai-toggle input:checked + .waai-slider:before { transform:translateX(20px); }

        /* Repeater rows */
        .waai-rep-row { display:flex; align-items:center; gap:10px; margin-bottom:8px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:10px 12px; }
        .waai-rep-fields { flex:1; display:flex; gap:8px; flex-wrap:wrap; }
        .waai-rep-fields input { flex:1; min-width:140px; padding:8px 10px; border:1px solid #d1d5db; border-radius:6px; font-size:12px; background:#fff; }
        .waai-rep-fields input:focus { outline:none; border-color:#5f39ff; }
        .waai-del-row { background:none; border:none; color:#ef4444; cursor:pointer; font-size:18px; line-height:1; padding:4px 8px; border-radius:4px; }
        .waai-del-row:hover { background:#fee2e2; }
        .waai-add-btn { margin-top:6px; padding:8px 16px; background:#ede9fe; color:#5f39ff; border:1px dashed #a78bfa; border-radius:7px; cursor:pointer; font-weight:600; font-size:13px; }
        .waai-add-btn:hover { background:#5f39ff; color:#fff; border-style:solid; }

        /* Calendar radio cards */
        .waai-radio-group { display:flex; gap:12px; flex-wrap:wrap; }
        .waai-radio-card { display:flex; flex-direction:column; align-items:center; gap:6px; padding:16px 20px; border:2px solid #e5e7eb; border-radius:10px; cursor:pointer; transition:all .2s; min-width:100px; text-align:center; }
        .waai-radio-card input { display:none; }
        .waai-radio-card.active { border-color:#5f39ff; background:#ede9fe; }
        .waai-radio-icon { font-size:26px; }
        .waai-radio-label { font-size:12px; font-weight:700; color:#1d2327; }
        .waai-cal-sub { margin-top:18px; padding:16px 20px; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; }
        .waai-cal-sub .waai-row { margin-bottom:0; }

        /* Chip rows */
        .waai-chip-row { display:flex; align-items:center; gap:10px; margin-bottom:10px; }
        .waai-chip-num { width:24px; height:24px; background:#5f39ff; color:#fff; border-radius:50%; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
        .waai-chip-row input { flex:1; padding:8px 12px; border:1px solid #d1d5db; border-radius:7px; font-size:13px; }
        .waai-chip-row input:focus { outline:none; border-color:#5f39ff; }

        /* Checkboxes */
        .waai-checkboxes { display:flex; flex-direction:column; gap:10px; padding-top:4px; }
        .waai-checkboxes label { display:flex; align-items:center; gap:10px; font-size:13px; cursor:pointer; color:#374151; }
        .waai-checkboxes input[type=checkbox] { width:16px; height:16px; accent-color:#5f39ff; }

        /* Training examples */
        .waai-example { background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:14px 16px; margin-bottom:12px; display:grid; gap:10px; }
        .waai-ex-label { display:block; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#5f39ff; margin-bottom:4px; }
        .waai-example input, .waai-example textarea { width:100%; padding:8px 12px; border:1px solid #d1d5db; border-radius:6px; font-size:13px; font-family:inherit; box-sizing:border-box; }
        .waai-example input:focus, .waai-example textarea:focus { outline:none; border-color:#5f39ff; }

        /* Info box */
        .waai-info-box { background:#ede9fe; border:1px solid #c4b5fd; color:#4c1d95; border-radius:8px; padding:14px 18px; font-size:13px; margin-top:18px; line-height:1.6; }
        .waai-info-box a { color:#5f39ff; font-weight:600; }

        /* Save bar */
        .waai-save-bar { position:sticky; bottom:0; background:#fff; border-top:1px solid #e5e7eb; padding:14px 32px; box-shadow:0 -4px 20px rgba(0,0,0,.07); z-index:10; display:flex; align-items:center; gap:20px; }
        .waai-save-bar .button-primary { background:#5f39ff !important; border-color:#5f39ff !important; color:#fff !important; font-size:14px; padding:8px 28px; height:auto; border-radius:7px !important; }
        .waai-save-bar .button-primary:hover { background:#4822eb !important; border-color:#4822eb !important; }
        .waai-save-note { font-size:12px; color:#6b7280; }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .waai-header { flex-direction: column; align-items: flex-start; gap: 16px; }
            .waai-row { flex-direction: column; gap: 8px; }
            .waai-row > label:first-child { width: 100%; min-width: 100%; }
            .waai-section { padding: 20px; }
            .waai-save-bar { flex-direction: column; align-items: stretch; padding: 16px 20px; gap: 12px; }
            .waai-save-bar .button-primary { width: 100%; text-align: center; }
            .waai-save-note { text-align: center; }
            .waai-inline { flex-wrap: wrap; }
            .waai-rep-fields { flex-direction: column; width: 100%; }
            .waai-rep-row { flex-direction: column; align-items: stretch; }
            .waai-del-row { align-self: flex-end; }
        }
        </style>

        <script>
        /* Tab switching */
        document.querySelectorAll('.waai-tab').forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelectorAll('.waai-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.waai-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                var panel = document.getElementById('tab-' + this.dataset.tab);
                if (panel) panel.classList.add('active');
            });
        });

        /* API key show/hide */
        function waaiTogglePw(id, btn) {
            var el = document.getElementById(id);
            el.type = el.type === 'password' ? 'text' : 'password';
            btn.textContent = el.type === 'password' ? 'Show' : 'Hide';
        }

        /* Model hints per provider */
        var modelHints = {
            groq:       'Recommended: llama-3.1-8b-instant (fast & free), llama-3.3-70b-versatile (powerful)',
            openrouter: 'Free recommended: meta-llama/llama-3-8b-instruct:free, google/gemma-7b-it:free',
            openai:     'Recommended: gpt-4o-mini (affordable), gpt-4o (most powerful)',
            gemini:     'Recommended: gemini-1.5-flash (fast), gemini-1.5-pro (powerful)',
            anthropic:  'Recommended: claude-sonnet-4-5 (balanced), claude-opus-4-5 (powerful), claude-haiku-3-5 (fast)',
            custom:     'Enter any model name supported by your custom OpenAI-compatible endpoint.',
        };
        var lastProvider = document.getElementById('waai-provider').value;
        function waaiProviderHint(val) {
            // 1. Sync the current visible key AND model to the last provider's hidden fields
            var visibleKeyEl   = document.getElementById('waai-api-key');
            var visibleModelEl = document.querySelector('input[name="waai_model"]');

            var lastHiddenKeyEl   = document.getElementById('waai-api-key-' + lastProvider);
            var lastHiddenModelEl = document.getElementById('waai-model-' + lastProvider);
            if (visibleKeyEl && lastHiddenKeyEl) {
                lastHiddenKeyEl.value = visibleKeyEl.value;
            }
            if (visibleModelEl && lastHiddenModelEl) {
                lastHiddenModelEl.value = visibleModelEl.value;
            }

            // 2. Load the new provider's saved key AND model into the visible inputs
            var newHiddenKeyEl   = document.getElementById('waai-api-key-' + val);
            var newHiddenModelEl = document.getElementById('waai-model-' + val);
            if (visibleKeyEl && newHiddenKeyEl) {
                visibleKeyEl.value = newHiddenKeyEl.value;
            }
            if (visibleModelEl && newHiddenModelEl && newHiddenModelEl.value) {
                visibleModelEl.value = newHiddenModelEl.value;
            }

            // 3. Update lastProvider
            lastProvider = val;

            // 4. Show/hide custom endpoint row
            var customRow = document.getElementById('waai-custom-endpoint-row');
            if (customRow) {
                customRow.style.display = (val === 'custom') ? 'flex' : 'none';
            }

            // 5. Update model hint
            var hint = document.getElementById('waai-model-hint');
            if (hint) hint.textContent = modelHints[val] || '';
        }
        waaiProviderHint(document.getElementById('waai-provider').value);

        // Keep visible key AND model synced live to current provider's hidden fields
        document.addEventListener('DOMContentLoaded', function() {
            var visibleKeyEl = document.getElementById('waai-api-key');
            if (visibleKeyEl) {
                visibleKeyEl.addEventListener('input', function() {
                    var currentProvider = document.getElementById('waai-provider').value;
                    var hiddenEl = document.getElementById('waai-api-key-' + currentProvider);
                    if (hiddenEl) hiddenEl.value = this.value;
                });
            }
            var visibleModelEl = document.querySelector('input[name="waai_model"]');
            if (visibleModelEl) {
                visibleModelEl.addEventListener('input', function() {
                    var currentProvider = document.getElementById('waai-provider').value;
                    var hiddenEl = document.getElementById('waai-model-' + currentProvider);
                    if (hiddenEl) hiddenEl.value = this.value;
                });
            }
        });

        /* Calendar provider switcher */
        function waaiCalSwitch(type, radioEl) {
            document.querySelectorAll('.waai-cal-sub').forEach(function(el) { el.style.display = 'none'; });
            document.querySelectorAll('.waai-radio-card').forEach(function(c) { c.classList.remove('active'); });
            if (type !== 'disabled') {
                var sub = document.getElementById('waai-cal-' + type);
                if (sub) sub.style.display = 'block';
            }
            radioEl.closest('.waai-radio-card').classList.add('active');
        }

        /* Speech engine switcher */
        function waaiSpeechSwitch() {
            var ttsVal = document.querySelector('select[name="waai_speech_engine"]').value;
            document.getElementById('waai-groq-tts-settings').style.display = ttsVal === 'groq_orpheus' ? 'block' : 'none';
            document.getElementById('waai-elevenlabs-settings').style.display = ttsVal === 'elevenlabs' ? 'block' : 'none';
            document.getElementById('waai-piper-settings').style.display = ttsVal === 'piper' ? 'block' : 'none';
            document.getElementById('waai-sarvam-settings').style.display = ttsVal === 'sarvam' ? 'block' : 'none';
        }
        /* Groq TTS model switcher — toggles voice optgroups */
        function waaiGroqModelSwitch(model, radioEl) {
            var enGroup = document.getElementById('waai-groq-voices-en');
            var arGroup = document.getElementById('waai-groq-voices-ar');
            var sel     = document.getElementById('waai-groq-voice');
            if (model === 'canopylabs/orpheus-v1-english') {
                enGroup.style.display = '';
                arGroup.style.display = 'none';
                sel.value = 'troy';
            } else {
                enGroup.style.display = 'none';
                arGroup.style.display = '';
                sel.value = 'abdullah';
            }
            /* Update radio card active states */
            radioEl.closest('.waai-radio-group').querySelectorAll('.waai-radio-card').forEach(function(c) { c.classList.remove('active'); });
            radioEl.closest('.waai-radio-card').classList.add('active');
        }

        /* Repeater: add row */
        var rowIdx = { services: <?= is_array($services) ? count($services) : 0 ?>, products: <?= is_array($products) ? count($products) : 0 ?>, gallery: <?= is_array($gallery) ? count($gallery) : 0 ?>, agentic_sections: <?= is_array($agentic_sections) ? count($agentic_sections) : 0 ?> };
        function waaiAddRow(type, placeholders, fields) {
            var list = document.getElementById('waai-' + type + '-list');
            var idx  = rowIdx[type]++;
            var row  = document.createElement('div');
            row.className = 'waai-rep-row';
            var types = ['text','text','url'];
            var fHtml = fields.map(function(f,i) {
                return '<input type="'+types[i]+'" name="waai_'+type+'['+idx+']['+f+']" placeholder="'+placeholders[i]+'">';
            }).join('');
            row.innerHTML = '<div class="waai-rep-fields">' + fHtml + '</div><button type="button" class="waai-del-row" onclick="this.closest(\'.waai-rep-row\').remove()">✕</button>';
            list.appendChild(row);
        }

        function waaiAddTipRow() {
            var list = document.getElementById('waai-tips-list');
            var row  = document.createElement('div');
            row.className = 'waai-rep-row';
            row.innerHTML = '<div class="waai-rep-fields"><input type="text" name="waai_loading_tips[]" placeholder="e.g. Add a fun fact or tip..."></div><button type="button" class="waai-del-row" onclick="this.closest(\'.waai-rep-row\').remove()">✕</button>';
            list.appendChild(row);
        }

        function waaiAddThinkingStepRow() {
            var list = document.getElementById('waai-thinking-steps-list');
            var row  = document.createElement('div');
            row.className = 'waai-rep-row';
            row.innerHTML = '<div class="waai-rep-fields"><input type="text" name="waai_thinking_steps[]" placeholder="e.g. 🔍 Analyzing your request..."></div><button type="button" class="waai-del-row" onclick="this.closest(\'.waai-rep-row\').remove()">✕</button>';
            list.appendChild(row);
        }

        function waaiAutoMapSections(btn) {
            var originalText = btn.innerHTML;
            btn.innerHTML = "⏳ Scanning Homepage...";
            btn.disabled = true;

            var siteUrl = '<?= esc_url(home_url('/')) ?>';
            console.log("Fetching homepage from:", siteUrl);

            fetch(siteUrl)
                .then(res => res.text())
                .then(html => {
                    console.log("Fetched HTML length:", html.length);
                    var parser = new DOMParser();
                    var doc = parser.parseFromString(html, 'text/html');
                    var sections = doc.querySelectorAll('section[id], div[id]');
                    console.log("Found sections with IDs:", sections.length);
                    
                    var addedCount = 0;
                    sections.forEach(function(el) {
                        var id = el.id;
                        if (!id || id === 'page' || id === 'content' || id.startsWith('wp') || id === 'primary' || id === 'secondary' || id === 'main') return;
                        
                        var name = id.replace(/-/g, ' ').replace(/(^\w|\s\w)/g, m => m.toUpperCase());
                        var selector = '#' + id;
                        
                        // Check for duplicates
                        var existingInputs = document.querySelectorAll('#waai-agentic_sections-list input[placeholder="CSS Selector or URL (e.g. #pricing or /contact/)"]');
                        var exists = false;
                        existingInputs.forEach(function(input) {
                            if (input.value === selector) exists = true;
                        });

                        console.log("Processing ID:", id, "| Name:", name, "| Exists:", exists);

                        if (!exists) {
                            waaiAddRow('agentic_sections', ['Section Name','CSS Selector or URL'], ['name','selector']);
                            var newInputs = document.querySelectorAll('#waai-agentic_sections-list .waai-rep-row:last-child input');
                            if (newInputs.length >= 2) {
                                newInputs[0].value = name;
                                newInputs[1].value = selector;
                                addedCount++;
                            }
                        }
                    });

                    btn.innerHTML = addedCount > 0 ? "✅ Added " + addedCount + " Sections!" : "✔️ No New Sections Found";
                    setTimeout(function() { btn.innerHTML = originalText; btn.disabled = false; }, 3000);
                })
                .catch(err => {
                    alert("Failed to scan homepage. You may need to add sections manually.");
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                });
        }

        function waaiAutoMapLinks(btn) {
            var originalText = btn.innerHTML;
            btn.innerHTML = "⏳ Scanning Links...";
            btn.disabled = true;

            var data = new FormData();
            data.append('action', 'waai_auto_map_links');
            data.append('nonce', '<?= wp_create_nonce("waai_admin_nonce") ?>');

            fetch('<?= esc_url(admin_url("admin-ajax.php")) ?>', {
                method: 'POST',
                body: data
            })
            .then(res => res.json())
            .then(data => {
                if(data.success && data.data.links) {
                    var addedCount = 0;
                    data.data.links.forEach(function(link) {
                        var selector = link.selector;
                        var name = link.name;
                        
                        var existingInputs = document.querySelectorAll('#waai-agentic_sections-list input[placeholder="CSS Selector or URL (e.g. #pricing or /contact/)"]');
                        var exists = false;
                        existingInputs.forEach(function(input) {
                            if (input.value === selector) exists = true;
                        });

                        if (!exists) {
                            waaiAddRow('agentic_sections', ['Section Name','CSS Selector or URL'], ['name','selector']);
                            var newInputs = document.querySelectorAll('#waai-agentic_sections-list .waai-rep-row:last-child input');
                            if (newInputs.length >= 2) {
                                newInputs[0].value = name;
                                newInputs[1].value = selector;
                                addedCount++;
                            }
                        }
                    });
                    btn.innerHTML = addedCount > 0 ? "✅ Added " + addedCount + " Links!" : "✔️ No New Links Found";
                } else {
                    btn.innerHTML = "❌ Error fetching links";
                }
                setTimeout(function() { btn.innerHTML = originalText; btn.disabled = false; }, 3000);
            })
            .catch(err => {
                btn.innerHTML = "❌ Network Error";
                setTimeout(function() { btn.innerHTML = originalText; btn.disabled = false; }, 3000);
            });
        }

        function waaiDetectPostTypes(btn) {
            var originalText = btn.innerHTML;
            btn.innerHTML = '⏳ Detecting...';
            btn.disabled = true;

            var data = new FormData();
            data.append('action', 'waai_get_post_types');
            data.append('nonce', '<?= wp_create_nonce("waai_admin_nonce") ?>');

            fetch('<?= esc_url(admin_url("admin-ajax.php")) ?>', { method: 'POST', body: data })
            .then(res => res.json())
            .then(resp => {
                if (resp.success && resp.data.post_types) {
                    var container = document.getElementById('waai-cpt-list');
                    // Get currently checked values before refresh
                    var totalCheckboxes = container.querySelectorAll('input[type=checkbox]').length;
                    var currentlyChecked = Array.from(container.querySelectorAll('input[type=checkbox]:checked')).map(el => el.value);
                    container.innerHTML = '';

                    // Re-insert hidden field so that form submission works if everything is unchecked
                    var hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'waai_sitemap_post_types';
                    hiddenInput.value = '';
                    container.appendChild(hiddenInput);

                    resp.data.post_types.forEach(function(pt) {
                        // A type is checked if it was previously checked, OR if we had no checkboxes at all on load (default all on)
                        var isChecked = (totalCheckboxes === 0) || currentlyChecked.includes(pt.slug);
                        var label = document.createElement('label');
                        label.style.cssText = 'display:flex; align-items:center; gap:8px; font-weight:400; cursor:pointer;';
                        label.innerHTML =
                            '<input type="checkbox" name="waai_sitemap_post_types[]" value="' + pt.slug + '"' +
                            (isChecked ? ' checked' : '') +
                            ' style="width:16px; height:16px; cursor:pointer;">' +
                            '<span><strong>' + pt.slug + '</strong> &mdash; ' + pt.label + '</span>';
                        container.appendChild(label);
                    });
                    btn.innerHTML = '✅ ' + resp.data.post_types.length + ' Types Detected';
                } else {
                    btn.innerHTML = '❌ Error';
                }
                setTimeout(function() { btn.innerHTML = originalText; btn.disabled = false; }, 3000);
            })
            .catch(() => {
                btn.innerHTML = '❌ Network Error';
                setTimeout(function() { btn.innerHTML = originalText; btn.disabled = false; }, 3000);
            });
        }
        </script>
        <?php
    }
}

new WebAssetsAI_Settings();
