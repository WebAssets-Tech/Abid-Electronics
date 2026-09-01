<?php
/**
 * WebAssets AI Assistant — Backend Proxy
 *
 * Handles POST requests directly for chat, lead submission, and WhatsApp forwarding.
 * Supports running within WordPress or completely standalone.
 */

if (!defined('AI_ASSISTANT_PATH')) {
    define('AI_ASSISTANT_PATH', dirname(__FILE__));
}

// 1. Try to load WordPress if running directly but inside a WordPress installation.
if (!defined('ABSPATH') && basename($_SERVER['SCRIPT_FILENAME']) === 'ai-proxy.php') {
    $possible_paths = [
        dirname(__FILE__) . '/../../../../wp-load.php',
        dirname(__FILE__) . '/../../../wp-load.php',
        dirname(__FILE__) . '/../../wp-load.php',
        dirname(__FILE__) . '/../wp-load.php',
        $_SERVER['DOCUMENT_ROOT'] . '/wp-load.php',
    ];
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
}

// 2. Direct Request Handling (unified fetch endpoint)
require_once dirname(__FILE__) . '/includes/ai-logger.php';
require_once dirname(__FILE__) . '/includes/prompt-builder.php';

// Run non-destructive schema migration (adds session_id / trace_id columns if missing)
if (class_exists('WebAssetsAI_Logger') && function_exists('dbDelta')) {
    static $waai_schema_migrated = false;
    if (!$waai_schema_migrated) {
        WebAssetsAI_Logger::create_table();
        $waai_schema_migrated = true;
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'ai-proxy.php') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Headers: Content-Type, Authorization, X-WAAI-Nonce, X-WAAI-Session-ID, X-WAAI-Trace-ID');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        exit(0);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // --- 0. Extract correlation IDs from client headers ----------------
        // These are set as $GLOBALS so waai_log() picks them up automatically
        // on every log call anywhere in this request lifecycle.
        $GLOBALS['waai_session_id'] = preg_replace('/[^a-f0-9\-]/i', '', $_SERVER['HTTP_X_WAAI_SESSION_ID'] ?? '');
        $GLOBALS['waai_trace_id']   = preg_replace('/[^a-f0-9\-]/i', '', $_SERVER['HTTP_X_WAAI_TRACE_ID']   ?? '');

        // --- 1. Nonce / CSRF Verification ---
        $nonce = $_SERVER['HTTP_X_WAAI_NONCE'] ?? '';
        if (function_exists('wp_verify_nonce')) {
            if (!wp_verify_nonce($nonce, 'waai_secure_action')) {
                waai_log('WARNING', 'Nonce Validation Failed', ['nonce' => $nonce, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
                echo json_encode(['success' => false, 'error' => 'Invalid security token. Please refresh the page.']);
                exit;
            }
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $input = $_POST;
        }

        // Determine action based on input parameter or payload structure
        $action = $input['action'] ?? null;
        if (!$action) {
            if (isset($input['to'])) {
                $action = 'whatsapp';
            } elseif (isset($input['lead_data'])) {
                $action = 'lead';
            } elseif (isset($input['email_data']) || $action === 'email') {
                $action = 'email';
            } elseif (isset($input['message'])) {
                $action = 'chat';
            } else {
                $action = 'chat';
            }
        }

        // --- 2. Action Whitelisting ---
        $allowed_actions = ['chat', 'lead', 'whatsapp', 'email', 'tts', 'sarvam_tts', 'stt', 'waai_process_dom_background', 'waai_process_sitemap_background', 'waai_process_page_content_background'];
        if (!in_array($action, $allowed_actions)) {
            waai_log('WARNING', 'Action Whitelist Failed', ['action' => $action]);
            echo json_encode(['success' => false, 'error' => 'Invalid action type.']);
            exit;
        }

        // --- 3. Rate Limiting ---
        if (function_exists('waai_check_rate_limit')) {
            $rate_error = waai_check_rate_limit($action);
            if ($rate_error) {
                waai_log('WARNING', 'Rate Limit Exceeded', ['action' => $action, 'ip' => $_SERVER['REMOTE_ADDR'] ?? '']);
                echo json_encode(['success' => false, 'error' => $rate_error]);
                exit;
            }
        }

        if ($action === 'chat') {

            $message = $input['message'] ?? '';
            if (function_exists('sanitize_text_field')) {
                $message = sanitize_text_field($message);
            } else {
                $message = htmlspecialchars(strip_tags(trim($message)));
            }

            $user_phone = $input['user_phone'] ?? '';
            if (function_exists('sanitize_text_field')) {
                $user_phone = sanitize_text_field($user_phone);
            } else {
                $user_phone = htmlspecialchars(strip_tags(trim($user_phone)));
            }

            $user_email = $input['user_email'] ?? '';
            if (function_exists('sanitize_email')) {
                $user_email = sanitize_email($user_email);
            } else {
                $user_email = filter_var(trim($user_email), FILTER_SANITIZE_EMAIL);
            }

            $history = isset($input['history']) && is_array($input['history']) ? $input['history'] : [];
            $page_context = isset($input['page_context']) && is_array($input['page_context']) ? $input['page_context'] : null;
            $last_action = isset($input['last_action']) && is_array($input['last_action']) ? $input['last_action'] : null;
            echo json_encode(waai_query_ai($message, $history, $user_phone, $user_email, $page_context, $last_action));

        } elseif ($action === 'lead') {
            $lead_data = isset($input['lead_data']) && is_array($input['lead_data']) ? $input['lead_data'] : [];
            
            // Content safety check on raw lead data
            $raw_query = $lead_data['query'] ?? '';
            $raw_name = $lead_data['name'] ?? '';
            if (!waai_is_safe_content($raw_query, true) || !waai_is_safe_content($raw_name, true)) {
                waai_log('WARNING', 'Lead Content Safety Blocked', ['name' => $raw_name, 'query' => $raw_query]);
                echo json_encode(['success' => false, 'error' => 'Lead data contains restricted or unsafe content.']);
                exit;
            }

            echo json_encode(waai_submit_lead($lead_data));

        } elseif ($action === 'whatsapp') {
            $to = $input['to'] ?? '';
            $message = $input['message'] ?? '';

            // Content safety check on raw message before sanitization
            if (!waai_is_safe_content($message, true)) {
                waai_log('WARNING', 'WhatsApp Content Safety Blocked', ['message' => $message]);
                echo json_encode(['success' => false, 'error' => 'Message contains restricted or unsafe content.']);
                exit;
            }

            if (function_exists('sanitize_text_field')) {
                $to = sanitize_text_field($to);
            } else {
                $to = htmlspecialchars(strip_tags(trim($to)));
            }

            if (function_exists('sanitize_textarea_field')) {
                $message = sanitize_textarea_field($message);
            } else {
                $message = htmlspecialchars(strip_tags(trim($message)));
            }

            echo json_encode(waai_send_whatsapp_message($to, $message));

        } elseif ($action === 'email') {
            $to = $input['to'] ?? '';
            $subject = $input['subject'] ?? 'Notification';
            $message = $input['message'] ?? '';

            // Content safety check on raw message and subject before sanitization
            if (!waai_is_safe_content($message, true) || !waai_is_safe_content($subject, true)) {
                waai_log('WARNING', 'Email Content Safety Blocked', ['subject' => $subject, 'message' => $message]);
                echo json_encode(['success' => false, 'error' => 'Message or Subject contains restricted content.']);
                exit;
            }

            if (function_exists('sanitize_email')) {
                $to = sanitize_email($to);
            } else {
                $to = filter_var(trim($to), FILTER_SANITIZE_EMAIL);
            }

            if (function_exists('sanitize_text_field')) {
                $subject = sanitize_text_field($subject);
            } else {
                $subject = htmlspecialchars(strip_tags(trim($subject)));
            }

            // Keep basic HTML for message
            $message = trim($message);

            echo json_encode(waai_send_email_message($to, $subject, $message));

        } elseif ($action === 'tts') {
            $text = $input['text'] ?? '';
            if (function_exists('sanitize_text_field')) {
                $text = sanitize_text_field($text);
            } else {
                $text = htmlspecialchars(strip_tags(trim($text)));
            }

            if (empty($text)) {
                echo json_encode(['success' => false, 'error' => 'No text provided.']);
                exit;
            }

            // Retrieve Piper URL from config
            $piper_url = waai_config('waai_piper_url', 'http://127.0.0.1:5000');

            // Set up cURL to forward text to Piper HTTP server (expects raw text/plain)
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $piper_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $text);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: text/plain; charset=utf-8'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10); // 10 seconds timeout

            $audio_data = curl_exec($ch);
            $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err   = curl_error($ch);
            curl_close($ch);

            if ($http_code !== 200 || !$audio_data) {
                waai_log('ERROR', 'Piper TTS curl failed', ['http_code' => $http_code, 'curl_err' => $curl_err, 'url' => $piper_url]);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'TTS synthesis failed.']);
                exit;
            }

            // Ensure the data is actually a WAV file (must start with 'RIFF')
            if (substr($audio_data, 0, 4) !== 'RIFF') {
                waai_log('ERROR', 'Piper TTS returned invalid audio data (not a WAV file)', ['http_code' => $http_code, 'response_preview' => substr($audio_data, 0, 100)]);
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'TTS backend returned invalid audio.']);
                exit;
            }

            // Stream raw WAV audio back directly
            if (ob_get_length()) {
                ob_clean();
            }
            header('Content-Type: audio/wav');
            echo $audio_data;
            exit;
            
        } elseif ($action === 'sarvam_tts') {
            $text = $input['text'] ?? '';
            if (empty($text)) {
                echo json_encode(['success' => false, 'error' => 'No text provided.']);
                exit;
            }
            
            $api_key = waai_config('waai_sarvam_api_key', '');
            if (empty($api_key)) {
                echo json_encode(['success' => false, 'error' => 'Sarvam API Key not configured.']);
                exit;
            }

            $lang = $input['language_code'] ?? 'hi-IN';
            
            $payload = json_encode([
                "text" => $text,
                "speaker" => "shubh",
                "target_language_code" => $lang,
                "pace" => 1.0,
                "model" => "bulbul:v3"
            ]);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.sarvam.ai/text-to-speech');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'api-subscription-key: ' . $api_key,
                'Content-Type: application/json'
            ]);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_err = curl_error($ch);
            curl_close($ch);

            if ($http_code !== 200 || !$result) {
                $error_details = json_encode([
                    'success' => false, 
                    'error' => 'Sarvam TTS failed.', 
                    'http_code' => $http_code, 
                    'curl_error' => $curl_err, 
                    'response' => $result,
                    'payload_sent' => $payload
                ]);
                waai_log('ERROR', 'Sarvam TTS failed', ['http_code' => $http_code, 'curl_err' => $curl_err, 'response' => $result]);
                file_put_contents(dirname(__FILE__) . '/sarvam_debug.log', "[" . date('Y-m-d H:i:s') . "] TTS ERROR: " . $error_details . "\n", FILE_APPEND);
                http_response_code(500);
                echo $error_details;
                exit;
            }
            
            // Expected response: {"audio": "base64_encoded_audio..."}
            echo $result;
            exit;

        } elseif ($action === 'stt') {
            // Groq Whisper STT processing
            $api_key = waai_config('waai_api_key_groq', '');
            if (empty($api_key)) {
                $api_key = waai_config('waai_api_key', '');
            }

            if (empty($api_key)) {
                waai_log('ERROR', 'STT failed: Groq API key is missing.');
                echo json_encode(['success' => false, 'error' => 'STT API key is not configured.']);
                exit;
            }

            if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
                waai_log('ERROR', 'STT failed: No audio file uploaded.');
                echo json_encode(['success' => false, 'error' => 'No audio file uploaded.']);
                exit;
            }

            $file_path = $_FILES['audio']['tmp_name'];
            $file_name = $_FILES['audio']['name'] ?? 'audio.webm';
            $mime_type = $_FILES['audio']['type'] ?? 'audio/webm';

            $cfile = new CURLFile($file_path, $mime_type, $file_name);

            $post_fields = [
                'file' => $cfile,
                'model' => 'whisper-large-v3',
                'language' => 'en',
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://api.groq.com/openai/v1/audio/transcriptions');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $api_key
            ]);

            $result = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curl_error = curl_error($ch);
            curl_close($ch);

            if ($curl_error || $http_code !== 200) {
                waai_log('ERROR', 'Groq Whisper STT API Error', ['http_code' => $http_code, 'error' => $curl_error, 'response' => $result]);
                echo json_encode(['success' => false, 'error' => 'Failed to transcribe audio.']);
                exit;
            }

            $response_data = json_decode($result, true);
            if (isset($response_data['text'])) {
                $text = trim($response_data['text']);
                
                // Filter out common Whisper silence hallucinations
                $hallucinations = [
                    'thank you.', 'thank you', 'bye.', 'bye', 
                    'thanks.', 'thanks', 'thanks for watching.', 
                    'thanks for watching!', 'subtitles by amara.org',
                    'you'
                ];
                
                if (in_array(strtolower($text), $hallucinations)) {
                    $text = ''; // Treat as silence
                }
                
                echo json_encode(['success' => true, 'text' => $text]);
            } else {
                waai_log('ERROR', 'Groq Whisper STT Invalid JSON', ['response' => $result]);
                echo json_encode(['success' => false, 'error' => 'Invalid STT response format.']);
            }
            exit;


        } elseif ($action === 'waai_process_dom_background') {
            $interactables_raw = $input['interactables'] ?? [];
            // Handle both JSON body (already an array) and FormData (JSON string)
            if (is_string($interactables_raw)) {
                $interactables = json_decode($interactables_raw, true);
            } else {
                $interactables = $interactables_raw;
            }
            if (!is_array($interactables)) $interactables = [];
            
            echo json_encode(waai_compress_interactables_background($interactables));
            exit;

        } elseif ($action === 'waai_process_sitemap_background') {
            
            // Get all published pages, respecting the admin-configured post type filter
            $all_pages = [];
            if (function_exists('get_post_types') && function_exists('get_posts')) {
                $public_post_types = get_post_types(['public' => true]);
                // Always exclude raw media attachments
                if (isset($public_post_types['attachment'])) unset($public_post_types['attachment']);

                // Apply admin filter: if the user has configured allowed types, use only those
                $allowed_types = function_exists('get_option') ? get_option('waai_sitemap_post_types', 'not_set') : 'not_set';
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
                        $all_pages[] = [
                            'name' => $p->post_title,
                            'url'  => get_permalink($p->ID)
                        ];
                    }
                }
            }
            
            // Add custom services
            $services = function_exists('get_option') ? get_option('waai_services', []) : [];
            if (is_array($services)) {
                foreach ($services as $svc) {
                    if (!empty($svc['title']) && !empty($svc['url'])) {
                        $all_pages[] = ['name' => $svc['title'], 'url' => $svc['url']];
                    }
                }
            }
            
            // Add custom products
            $products = function_exists('get_option') ? get_option('waai_products', []) : [];
            if (is_array($products)) {
                foreach ($products as $prod) {
                    if (!empty($prod['name']) && !empty($prod['url'])) {
                        $all_pages[] = ['name' => $prod['name'], 'url' => $prod['url']];
                    }
                }
            }
            
            echo json_encode(waai_compress_sitemap_background($all_pages));
            exit;

        } elseif ($action === 'waai_process_page_content_background') {
            $page_content_raw = $input['page_content'] ?? '';
            $page_title       = $input['page_title']   ?? '';
            $page_url         = $input['page_url']     ?? '';
            if (is_string($page_content_raw) && strlen($page_content_raw) > 50) {
                echo json_encode(waai_summarize_page_content_background($page_content_raw, $page_title, $page_url));
            } else {
                waai_log('WARNING', 'Background Summarizer Blocked', ['reason' => 'No page content provided or too short', 'length' => strlen($page_content_raw)]);
                echo json_encode(['success' => false, 'error' => 'No page content provided']);
            }
            exit;

        } else {
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        exit;
    }
}

/* =============================================================================
   HELPER: READ CONFIG FROM WP_OPTIONS (with hardcoded fallbacks for standalone)
   ============================================================================= */
function waai_config($key, $fallback = '') {
    if (function_exists('get_option')) {
        return get_option($key, $fallback);
    }
    // Standalone fallbacks — edit these if not using WordPress
    $standalone = [
        'waai_provider'         => 'groq',
        'waai_model'            => 'llama-3.1-8b-instant',
        'waai_api_key'          => '',
        'waai_max_tokens'       => 1000,
        'waai_temperature'      => 0.7,
        'waai_rate_limiting_enabled' => '1',
        'waai_rate_limit'       => 60,
        'waai_max_input_chars'  => 1000,
        'waai_lead_email'       => '',
        'waai_sheets_webhook'   => '',
        'waai_save_leads_db'    => '0',
        'waai_whatsapp_api_enabled' => '1',
        'waai_whatsapp_app_key'     => 'c5a6057f-324c-40aa-a568-e47bb91bb2f3',
        'waai_whatsapp_auth_key'    => 'NcDjp7sOdkiUDwfvcSCvGQLh7p6zLRYJ1',
    ];
    return $standalone[$key] ?? $fallback;
}

/* =============================================================================
   RATE LIMITING & IP THROTTLING
   ============================================================================= */
function waai_check_rate_limit($action = 'chat') {
    // Check if rate limiting is enabled
    $rl_enabled = waai_config('waai_rate_limiting_enabled', '1');
    if ($rl_enabled !== '1') {
        return null;
    }

    $ip    = waai_sanitize_field($_SERVER['REMOTE_ADDR'] ?? 'unknown');
    $key   = 'waai_rl_' . $action . '_' . md5($ip);

    // Dynamic Limits (per hour) — all configurable from the Security tab in AI Settings
    $limit = 50; // default fallback
    if ($action === 'whatsapp' || $action === 'email') {
        $limit = (int) waai_config('waai_rate_limit_whatsapp_email', 5);
    } elseif ($action === 'lead') {
        $limit = (int) waai_config('waai_rate_limit_lead', 10);
    } elseif ($action === 'tts') {
        $limit = (int) waai_config('waai_rate_limit_tts', 500);
    } elseif ($action === 'sarvam_tts') {
        $limit = (int) waai_config('waai_rate_limit_sarvam', 500);
    }

    // Chat uses its own dedicated config key (was already configurable, keeping it)
    if ($action === 'chat') {
        $limit = (int) waai_config('waai_rate_limit', 50);
        if ($limit <= 0) return null; // 0 = disabled for this action
    }

    if ($limit <= 0) return null; // 0 = disabled for this action

    if (function_exists('get_transient') && function_exists('set_transient')) {
        $count = (int) get_transient($key);
        if ($count >= $limit) {
            return "Too many {$action} requests. Please wait before sending again.";
        }
        if ($count === 0) {
            set_transient($key, 1, 3600); // 1 hour
        } else {
            set_transient($key, $count + 1, 3600);
        }
    } else {
        // Standalone fallback: Session-based rate limit
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $now = time();
        if (!isset($_SESSION[$key . '_reset']) || $_SESSION[$key . '_reset'] < $now) {
            $_SESSION[$key . '_reset'] = $now + 3600;
            $_SESSION[$key . '_count'] = 1;
        } else {
            $_SESSION[$key . '_count'] = ($_SESSION[$key . '_count'] ?? 0) + 1;
        }

        if ($_SESSION[$key . '_count'] > $limit) {
            return "Too many {$action} requests. Please wait before sending again.";
        }
    }

    return null;
}

/* =============================================================================
   DYNAMIC SYSTEM PROMPT BUILDER
   ============================================================================= */

function waai_build_system_prompt($user_phone = '', $user_email = '', $page_context = null, $last_action = null) {
    $site_url = '';
    if (function_exists('get_site_url')) {
        $site_url = get_site_url();
    } else {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $proto = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $site_url = $proto . '://' . $host;
    }

    $name     = waai_config('waai_company_name',        'Abid Electronics Service Hub');
    $tagline  = waai_config('waai_company_tagline',     'Srinagar\'s 5-Star Rated Multi-Brand Appliance Repair Hub');
    $location = waai_config('waai_company_location',    'Bemina Crossing, Chattabal, Srinagar, Jammu & Kashmir 190001');
    $phone    = waai_config('waai_company_phone',       '9622917697');
    $email    = waai_config('waai_company_email',       'support@abidelectronics.in');
    $website  = waai_config('waai_company_website',     $site_url);
    $desc     = waai_config('waai_company_description', 'Multi-brand doorstep home and commercial appliance repair service in Srinagar. Specializing in Refrigerator, Washing Machine, AC, Geyser, Microwave, and Bakery Display Counter repairs with 100% genuine parts and same-day doorstep service.');
    $tone     = waai_config('waai_tone_rules',          'Be a polite, expert local appliance technician assistant for Srinagar. Provide clear diagnostic answers, mention genuine parts and same-day doorstep service, and encourage the user to book a repair visit or call 9622917697.');

    $contact_parts = [];
    if ($phone)   $contact_parts[] = "Phone: {$phone}";
    if ($email)   $contact_parts[] = "Email: {$email}";
    if ($website) $contact_parts[] = "Website: {$website}";
    $contact_text = implode(' | ', $contact_parts);

    $prompt = "";
    
    // 1. Identity & Company Info
    $prompt .= waai_build_identity_prompt($name, $tagline, $location, $contact_text, $desc);
    
    // 2. Services
    if (waai_config('waai_enable_services', '1') === '1') {
        $services = waai_config('waai_services', []);
        if (is_array($services)) {
            $prompt .= waai_build_services_prompt(array_filter($services, fn($s) => !empty($s['title'])));
        }
    }
    
    // 3. Products
    if (waai_config('waai_enable_products', '1') === '1') {
        $products = waai_config('waai_products', []);
        if (is_array($products)) {
            $prompt .= waai_build_products_prompt(array_filter($products, fn($p) => !empty($p['name'])));
        }
    }
    
    // 3.5 Media Gallery
    if (waai_config('waai_enable_gallery', '1') === '1') {
        $gallery = waai_config('waai_gallery', []);
        if (is_array($gallery) && !empty($gallery)) {
            if (function_exists('waai_build_gallery_prompt')) {
                $prompt .= waai_build_gallery_prompt(array_filter($gallery, fn($g) => !empty($g['title']) && !empty($g['image'])));
            }
        }
    }
    
    // 4. Tone, Rules, and Anti-Hallucination
    $prompt .= waai_build_rules_prompt($tone);
    $prompt .= waai_build_anti_hallucination_prompt();
    $prompt .= waai_build_tool_rules_prompt();
    
    // 5. User Context (Memory & State)
    $prompt .= waai_build_context_prompt($user_phone, $user_email, $last_action);
    
    // 6. Action Tag Generators (Overlays)
    if (waai_config('waai_whatsapp_api_enabled', '1') === '1') {
        $prompt .= waai_build_whatsapp_prompt();
    }
    if (waai_config('waai_email_api_enabled', '0') === '1') {
        $prompt .= waai_build_email_prompt();
    }
    
    // 7. Few-Shot Examples
    if (waai_config('waai_enable_faq', '1') === '1') {
        $examples = waai_config('waai_faq_examples', []);
        if (is_array($examples)) {
            $prompt .= waai_build_examples_prompt(array_filter($examples, fn($e) => !empty($e['q']) && !empty($e['a'])));
        }
    }

    // 8. Dynamic Page Context (Live links/elements on user screen)
    if ($page_context) {
        if (waai_config('waai_enable_page_text', '1') !== '1') {
            unset($page_context['page_content']);
        }
        if (waai_config('waai_enable_page_links', '1') !== '1') {
            unset($page_context['interactables']);
        }
        if (!empty($page_context['page_content']) || !empty($page_context['interactables'])) {
            $prompt .= waai_build_page_context_prompt($page_context);
        }
        if (waai_config('waai_enable_site_index', '1') === '1' && !empty($page_context['global_sitemap'])) {
            $prompt .= waai_build_global_sitemap_prompt($page_context);
        }
    }
    
    waai_log('DEBUG', 'System Prompt Built', ['length' => strlen($prompt)]);

    return trim($prompt);
}

/**
 * waai_extract_action_intent — JSON-first action parser with regex fallback.
 *
 * Strategy:
 *  1. Try to locate a well-formed JSON action block emitted by the LLM:
 *       __ACTION__ {"type":"whatsapp","params":{"to":"...","message":"..."}} __END_ACTION__
 *  2. If not found, fall back to the legacy bracket-tag regex patterns so existing
 *     prompts continue working without breaking changes.
 *
 * The JSON-first approach is deterministic: no regex matching on freeform text,
 * no brackets that the LLM may accidentally omit, and trivially extensible for
 * new action types just by adding them to the type whitelist.
 */
function waai_extract_action_intent(&$reply) {
    $action = null;

    // -----------------------------------------------------------------------
    // Pass 1: Try to parse a structured JSON action block
    // Format: __ACTION__ { ... } __END_ACTION__
    // -----------------------------------------------------------------------
    $json_pattern = '/__ACTION__\s*(\{[\s\S]*?\})\s*__END_ACTION__/i';
    if (preg_match($json_pattern, $reply, $json_matches)) {
        $decoded = json_decode(trim($json_matches[1]), true);
        if (is_array($decoded) && !empty($decoded['type'])) {
            $type   = strtolower(trim($decoded['type']));
            $params = isset($decoded['params']) && is_array($decoded['params'])
                      ? $decoded['params']
                      : [];

            $allowed_types = [];
            if (waai_config('waai_whatsapp_api_enabled', '1') === '1') {
                $allowed_types[] = 'whatsapp';
            }
            if (waai_config('waai_email_api_enabled', '0') === '1') {
                $allowed_types[] = 'email';
            }
            if (waai_config('waai_lead_form_enabled', '1') === '1') {
                $allowed_types[] = 'lead_form';
            }
            if (waai_config('waai_calendar_type', 'disabled') !== 'disabled') {
                $allowed_types[] = 'calendar';
            }
            if (in_array($type, $allowed_types)) {
                // Run the server-side policy gate on outgoing params
                $policy_error = waai_validate_outgoing_message($type, $params);
                if ($policy_error) {
                    waai_log('WARNING', 'Action Policy Blocked (JSON)', [
                        'type'  => $type,
                        'error' => $policy_error,
                    ]);
                    // Strip the action block from reply but don't propagate the action
                    $reply = preg_replace($json_pattern, '', $reply);
                    $reply = preg_replace('/[ \t]+/', ' ', $reply);
                    $reply = trim($reply);
                    return null;
                }

                $action = ['type' => $type, 'params' => $params];
                $reply  = preg_replace($json_pattern, '', $reply);
                $reply  = preg_replace('/[ \t]+/', ' ', $reply);
                $reply  = trim($reply);

                waai_log('INFO', 'Action Detected (JSON)', ['action' => $action]);
                return $action;
            }
        }
        // Malformed JSON block — strip it so it doesn't pollute the reply
        $reply = preg_replace($json_pattern, '', $reply);
        $reply = preg_replace('/[ \t]+/', ' ', $reply);
        $reply = trim($reply);
    }

    // -----------------------------------------------------------------------
    // Pass 2: Legacy regex fallback — keep backward compat while migrating prompts
    // -----------------------------------------------------------------------

    // 2a. WhatsApp
    if (waai_config('waai_whatsapp_api_enabled', '1') === '1') {
        $whatsapp_regex = '/\[SEND_WHATSAPP:\s*(\+?\d+)\s*\|\s*([\s\S]*?)\]/i';
        if (preg_match($whatsapp_regex, $reply, $matches)) {
            $params = ['to' => trim($matches[1]), 'message' => trim($matches[2])];
            $policy_error = waai_validate_outgoing_message('whatsapp', $params);
            if (!$policy_error) {
                $action = ['type' => 'whatsapp', 'params' => $params];
            } else {
                waai_log('WARNING', 'Action Policy Blocked (regex/whatsapp)', ['error' => $policy_error]);
            }
            $reply = preg_replace($whatsapp_regex, '', $reply);
        }
    }

    // 2b. Email
    if (waai_config('waai_email_api_enabled', '0') === '1') {
        $email_regex = '/\[SEND_EMAIL:\s*([^\s\|]+)\s*\|\s*([^\|]+)\s*\|\s*([\s\S]*?)\]/i';
        if (!$action && preg_match($email_regex, $reply, $matches)) {
            $params = [
                'to'      => trim($matches[1]),
                'subject' => trim($matches[2]),
                'message' => trim($matches[3]),
            ];
            $policy_error = waai_validate_outgoing_message('email', $params);
            if (!$policy_error) {
                $action = ['type' => 'email', 'params' => $params];
            } else {
                waai_log('WARNING', 'Action Policy Blocked (regex/email)', ['error' => $policy_error]);
            }
            $reply = preg_replace($email_regex, '', $reply);
        }
    }

    // 2c. Lead Form
    if (waai_config('waai_lead_form_enabled', '1') === '1') {
        $lead_form_regex = '/\[SHOW_LEAD_FORM\]/i';
        if (!$action && preg_match($lead_form_regex, $reply)) {
            $action = ['type' => 'lead_form', 'params' => (object) []];
            $reply  = preg_replace($lead_form_regex, '', $reply);
        }
    }

    // 2d. Calendar
    if (waai_config('waai_calendar_type', 'disabled') !== 'disabled') {
        $calendar_regex = '/\[SHOW_CALENDAR\]/i';
        if (!$action && preg_match($calendar_regex, $reply)) {
            $action = ['type' => 'calendar', 'params' => (object) []];
            $reply  = preg_replace($calendar_regex, '', $reply);
        }
    }

    // -----------------------------------------------------------------------
    // Pass 3: Strip any XML-style / leaked tool call syntax from reply text.
    // Some models hallucinate tool calls as raw XML or JSON in their response
    // text instead of using the proper tools API format.
    // These patterns are NEVER valid user-facing text and must always be removed.
    // -----------------------------------------------------------------------

    // 3a. Named XML tool call tags (opening, closing, self-closing)
    $xml_tool_tags = [
        'navigate_website',
        'interact_with_element',
        'scroll_page',
        'open_assistant_overlay',
        'get_page_content',
        'agentic_navigation',
        'agentic_interaction',
    ];
    foreach ($xml_tool_tags as $tag) {
        $reply = preg_replace('/<\/?' . preg_quote($tag, '/') . '[\s\S]*?>/i', '', $reply);
    }

    // 3b. <function=name,{...}>...</function> — full block
    $reply = preg_replace('/<function=[^>]*>[\s\S]*?<\/function>/i', '', $reply);
    // 3c. <function=name,...> — orphan opening tags (body already stripped)
    $reply = preg_replace('/<function=[^>]*>/i', '', $reply);
    // 3d. </function> — orphan closing tags left behind after opening tag was stripped
    $reply = preg_replace('/<\/function>/i', '', $reply);

    // 3e. Raw "ACTION {...}" blocks the model outputs without proper __ACTION__ sentinels.
    // Matches: ACTION {...} or ACTION: {...} or bare JSON objects starting with {"action":
    // that look like leaked agentic redirect/navigate payloads.
    $reply = preg_replace('/\bACTION\s*:?\s*\{[\s\S]*?\}/i', '', $reply);
    // Orphan JSON action objects like: {"action":"redirect","target_name":...}
    $reply = preg_replace('/\{"action"\s*:\s*"[^"]*"[\s\S]*?\}/i', '', $reply);

    // 3f. Sanitize action params content that leaked into reply as raw text (email body etc.)
    // Strip any __ACTION__ blocks that weren't caught by Pass 1 (missing END_ACTION sentinel)
    $reply = preg_replace('/__ACTION__[\s\S]*?(?:__END_ACTION__|$)/i', '', $reply);

    // 3g. Collapse <br> spam — malformed models sometimes inject hundreds of <br> tags.
    // More than 2 consecutive <br> tags in a chat reply is never intentional.
    $reply = preg_replace('/(\s*<br\s*\/?>\s*){3,}/i', '<br>', $reply);

    // Clean up response string
    $reply = preg_replace('/[ \t]+/', ' ', $reply);
    $reply = preg_replace('/\n{3,}/', "\n\n", $reply); // collapse triple+ newlines
    $reply = trim($reply);

    if ($action) {
        waai_log('INFO', 'Action Detected (regex fallback)', ['action' => $action]);
    }
    return $action;
}

/* =============================================================================
   SERVER-SIDE OUTGOING MESSAGE POLICY ENGINE
   =============================================================================
   This is the last security gate before any AI-generated content is dispatched
   to an external channel (WhatsApp, Email). Frontend validation is never trusted
   alone — this PHP layer enforces identical rules server-side.

   Returns null  → message is safe to dispatch.
   Returns string → human-readable error reason; the caller MUST block dispatch.
   ============================================================================= */
function waai_validate_outgoing_message($type, array $params) {

    $MAX_WA_LENGTH    = 1000;
    $MAX_EMAIL_LENGTH = 2000;
    $MAX_SUBJECT_LEN  = 150;

    // -----------------------------------------------------------------------
    // 1. Destination whitelist (phone / email)
    // -----------------------------------------------------------------------
    if ($type === 'whatsapp') {
        $to = preg_replace('/\D/', '', $params['to'] ?? '');
        if (empty($to) || strlen($to) < 7 || strlen($to) > 15) {
            return "Invalid or missing phone number ('{$to}').";
        }
    }

    if ($type === 'email') {
        $to = trim($params['to'] ?? '');
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return "Invalid or missing email address ('{$to}').";
        }
    }

    // -----------------------------------------------------------------------
    // 2. Message / content presence check
    // -----------------------------------------------------------------------
    $message = trim($params['message'] ?? '');
    if (in_array($type, ['whatsapp', 'email']) && empty($message)) {
        return "Message body cannot be empty.";
    }

    // -----------------------------------------------------------------------
    // 3. Length limits
    // -----------------------------------------------------------------------
    if ($type === 'whatsapp' && mb_strlen($message) > $MAX_WA_LENGTH) {
        return "WhatsApp message exceeds maximum length of {$MAX_WA_LENGTH} characters.";
    }

    if ($type === 'email') {
        if (mb_strlen($message) > $MAX_EMAIL_LENGTH) {
            return "Email message exceeds maximum length of {$MAX_EMAIL_LENGTH} characters.";
        }
        $subject = trim($params['subject'] ?? '');
        if (mb_strlen($subject) > $MAX_SUBJECT_LEN) {
            return "Email subject exceeds maximum length of {$MAX_SUBJECT_LEN} characters.";
        }
    }

    // -----------------------------------------------------------------------
    // 4. Content safety (XSS / injection / blocked words)
    // -----------------------------------------------------------------------
    if ($type === 'whatsapp' || $type === 'email') {
        if (!waai_is_safe_content($message, true)) {
            return "Message content failed safety check (blocked or unsafe content detected).";
        }
        if ($type === 'email') {
            $subject = trim($params['subject'] ?? '');
            if (!empty($subject) && !waai_is_safe_content($subject, true)) {
                return "Email subject failed safety check.";
            }
        }
    }

    // -----------------------------------------------------------------------
    // 5. Company-only enforcement: reject messages that appear to be competitor
    //    promotion or messages not about the company's own services.
    //    (Extendable — add more patterns as the knowledge base grows.)
    // -----------------------------------------------------------------------
    $company_name = strtolower(waai_config('waai_company_name', ''));
    if (!empty($company_name) && in_array($type, ['whatsapp', 'email'])) {
        // Allow pass-through — company name doesn't have to be in every message,
        // but the message must not actively promote a different company.
        // (More sophisticated checks can be added here if needed.)
    }

    return null; // All checks passed
}


/* =============================================================================
   CORE: QUERY AI SERVICE
   ============================================================================= */
function waai_query_ai($message, $history = [], $user_phone = '', $user_email = '', $page_context = null, $last_action = null) {
    // Validate message
    $message = trim($message);
    if (empty($message)) {
        return ['success' => false, 'error' => 'Empty message.'];
    }

    // Truncate to max input chars
    $max_chars = (int) waai_config('waai_max_input_chars', 1000);
    if (mb_strlen($message) > $max_chars) {
        $message = mb_substr($message, 0, $max_chars);
    }

    // Limit history length to save tokens
    $history_limit = (int) waai_config('waai_history_limit', 20);
    if ($history_limit > 0 && is_array($history) && count($history) > $history_limit) {
        $history = array_slice($history, -$history_limit);
    }

    $provider    = waai_config('waai_provider', 'groq');
    $api_key     = waai_config('waai_api_key_' . $provider, '');
    if (empty($api_key)) {
        $api_key = waai_config('waai_api_key', '');
    }

    // Migration fallback: if API key not saved in admin yet, try env variable
    if (empty($api_key)) {
        $api_key = getenv('AI_CHAT_API_KEY') ?: '';
    }

    $model       = waai_config('waai_model', 'llama-3.1-8b-instant');
    $max_tokens  = (int) waai_config('waai_max_tokens', 1000);
    $temperature = (float) waai_config('waai_temperature', 0.7);

    // Check for Long-Context Override for massive pages (e.g. blogs)
    if (isset($page_context['requires_long_context']) && $page_context['requires_long_context'] === true) {
        if (waai_config('waai_enable_long_context_fallback', '0') === '1') {
            $fallback_key = waai_config('waai_long_context_api_key', '');
            if (!empty($fallback_key)) {
                $provider = waai_config('waai_long_context_provider', 'openrouter');
                $model    = waai_config('waai_long_context_model', 'google/gemini-1.5-flash');
                $api_key  = $fallback_key;
            }
        }
    }

    if (empty($api_key)) {
        return [
            'success' => false,
            'error'   => 'AI assistant not configured yet. Please go to WordPress Admin → WebAssets AI → Settings and enter your API key.',
        ];
    }

    // Determine endpoint & headers by provider
    switch ($provider) {
        case 'openai':
            $endpoint   = 'https://api.openai.com/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'custom':
            $endpoint   = waai_config('waai_custom_endpoint', '');
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'gemini':
            // Gemini uses generateContent endpoint with key in URL
            $endpoint   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
            $auth_header = null;
            break;
        case 'anthropic':
            $endpoint   = 'https://api.anthropic.com/v1/messages';
            $auth_header = null; // Anthropic uses x-api-key header, handled in its own function
            break;
        case 'openrouter':
            $endpoint   = 'https://openrouter.ai/api/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'groq':
        default:
            $endpoint   = 'https://api.groq.com/openai/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
    }
    // last_action is now passed as an argument
    if (waai_config('waai_enable_conversational_memory', '1') !== '1') {
        $last_action = null;
    }
    $system_prompt = waai_build_system_prompt($user_phone, $user_email, $page_context, $last_action);

    // Gemini uses a different request/response format
    if ($provider === 'gemini') {
        return waai_query_gemini($endpoint, $system_prompt, $message, $history, $max_tokens, $temperature, $page_context);
    }

    // Anthropic Claude uses a different API format
    if ($provider === 'anthropic') {
        return waai_query_anthropic($endpoint, $api_key, $model, $system_prompt, $message, $history, $max_tokens, $temperature, $page_context);
    }

    // OpenAI-compatible format (Groq, OpenRouter, OpenAI)
    $messages = [['role' => 'system', 'content' => $system_prompt]];

    foreach ($history as $msg) {
        $role    = in_array($msg['role'] ?? '', ['user', 'assistant']) ? $msg['role'] : 'user';
        $content = trim($msg['content'] ?? '');
        if ($content) {
            // Strip any [SEND_WHATSAPP: ...] tags from history to prevent LLM feedback loops
            $content = preg_replace('/\[SEND_WHATSAPP:\s*(\+?\d+)\s*\|\s*([\s\S]*?)\]/i', '', $content);
            $content = trim($content);
            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $post_data = [
        'model'       => $model,
        'messages'    => $messages,
        'temperature' => $temperature,
        'max_tokens'  => $max_tokens,
    ];
    
    // --- DEBUG: Token Breakdown Analyzer ---
    $debug_log_path = dirname(__FILE__) . '/debug_prompt.txt';
    $tok = fn($str) => (int) ceil(strlen((string)$str) / 3.8); // ~3.8 chars per token estimate
    $sys_msg = $messages[0]['content'] ?? '';
    // Break down system prompt sections
    $sys_sections = [];
    $sections_to_check = [
        '[ROLE]',
        '[COMPANY]',
        '[SERVICES]',
        '[SAAS PRODUCTS]',
        '[TONE & RULES]',
        '[ANTI-HALLUCINATION & BUSINESS ACCURACY]',
        '[TOOL USAGE RULES]',
        '[PAGE VS SECTION NAVIGATION STRATEGY]',
        '[USER CONTEXT & MEMORY]',
        '[WHATSAPP FORWARDING]',
        '[EMAIL FORWARDING]',
        '[EXAMPLE CONVERSATIONS]',
        '[LANGUAGE]',
        '[CURRENT PAGE VIEWPORT]',
        '[GLOBAL WEBSITE DIRECTORY]'
    ];
    foreach ($sections_to_check as $sec) {
        // Add a newline to the search to ensure we only match the actual header, not inline mentions
        $pos = strpos($sys_msg, $sec . "\n");
        if ($pos !== false) {
            $sys_sections[$sec] = $pos;
        }
    }
    asort($sys_sections);
    $sec_keys = array_keys($sys_sections);
    $sec_sizes = [];
    for ($i = 0; $i < count($sec_keys); $i++) {
        $start = $sys_sections[$sec_keys[$i]];
        $end   = isset($sec_keys[$i+1]) ? $sys_sections[$sec_keys[$i+1]] : strlen($sys_msg);
        $sec_sizes[$sec_keys[$i]] = $end - $start;
    }
    $history_chars = array_sum(array_map(fn($m) => strlen($m['content'] ?? ''), array_slice($messages, 1, -1)));
    $user_msg_chars = strlen($messages[array_key_last($messages)]['content'] ?? '');
    $report  = "=== WAAI TOKEN BREAKDOWN (" . date('Y-m-d H:i:s') . ") ===\n\n";
    $report .= sprintf("SYSTEM PROMPT TOTAL  : %6d chars  ~%4d tokens\n", strlen($sys_msg), $tok($sys_msg));
    foreach ($sec_sizes as $sec => $chars) {
        $report .= sprintf("  %-30s: %5d chars  ~%4d tokens\n", $sec, $chars, $tok(str_repeat('x', $chars)));
    }
    $report .= sprintf("\nCONVERSATION HISTORY : %6d chars  ~%4d tokens  (%d messages)\n", $history_chars, $tok($history_chars), count($messages)-2);
    $report .= sprintf("USER MESSAGE         : %6d chars  ~%4d tokens\n", $user_msg_chars, $tok($user_msg_chars));
    $grand_total = strlen($sys_msg) + $history_chars + $user_msg_chars;
    $report .= sprintf("\nGRAND TOTAL EST.     : %6d chars  ~%4d tokens\n", $grand_total, $tok($grand_total));
    $report .= "\n\n=== FULL SYSTEM PROMPT ===\n" . $sys_msg;
    $report .= "\n\n=== CONVERSATION HISTORY & USER MESSAGE ===\n";
    foreach (array_slice($messages, 1) as $msg) {
        $report .= "[" . strtoupper($msg['role']) . "]\n" . $msg['content'] . "\n\n";
    }
    file_put_contents($debug_log_path, $report);
    // --- END DEBUG ---

    $headers = ['Content-Type: application/json'];
    if ($auth_header) $headers[] = $auth_header;
    if ($provider === 'openrouter') {
        $site_url = function_exists('get_site_url') ? get_site_url() : '';
        if ($site_url) $headers[] = "HTTP-Referer: {$site_url}";
    }

    $agentic_enabled = waai_config('waai_agentic_enabled', '0');
    if ($agentic_enabled === '1' && in_array($provider, ['groq', 'openai', 'openrouter', 'custom', 'anthropic'])) {
        require_once dirname(__FILE__) . '/ai-agent-tools.php';
        $agentic_sections = waai_get_all_agentic_sections();
        $toggles = [
            'scroll'   => waai_config('waai_enable_action_scroll', '1'),
            'interact' => waai_config('waai_enable_action_interact', '1'),
            'navigate' => waai_config('waai_enable_action_navigate', '1'),
            'read'     => waai_config('waai_enable_action_read', '1'),
        ];
        $tools = WebAssetsAIAgent::get_tools($agentic_sections, $toggles);
        if (!empty($tools)) {
            $post_data['tools'] = $tools;
            $post_data['tool_choice'] = 'auto';
        }
        if ($provider === 'anthropic') {
            return waai_query_anthropic_with_tools_loop($endpoint, $api_key, $model, $system_prompt, $messages, $post_data, $max_tokens);
        }
        return waai_query_openai_with_tools_loop($endpoint, $post_data, $headers);
    }

    return waai_curl_post($endpoint, $post_data, $headers, function($result) use ($message) {
        $choices = $result['choices'] ?? [];
        if (!empty($choices)) {
            $msg   = $choices[0]['message'] ?? [];
            $reply = $msg['content'] ?? null;
            
            if (is_null($reply) || $reply === '') {
                $reasoning = $msg['reasoning'] ?? '';
                if (empty($reasoning) && isset($msg['reasoning_content'])) {
                    $reasoning = $msg['reasoning_content'];
                }
                if (empty($reasoning) && isset($msg['reasoning_details'])) {
                    $details = $msg['reasoning_details'] ?? $msg['reasoningdetails'] ?? [];
                    if (is_array($details)) {
                        foreach ($details as $d) {
                            if (isset($d['text'])) {
                                $reasoning .= $d['text'] . ' ';
                            }
                        }
                    }
                }
                
                $agentic_enabled = waai_config('waai_agentic_enabled', '0');
                if ($agentic_enabled !== '1') {
                    $is_nav = false;
                    if ($reasoning && preg_match('/navigate|redirect|home|click|scroll/i', $reasoning)) {
                        $is_nav = true;
                    }
                    if (!$is_nav && $message && preg_match('/navigate|redirect|go to|take me|click|scroll/i', $message)) {
                        $is_nav = true;
                    }
                    if ($is_nav) {
                        return [
                            'success' => true,
                            'reply'   => "I'm sorry, but browser navigation and page interaction features are currently disabled in the settings, so I cannot perform this action right now. Please enable the 'Enable Agentic' option in the settings."
                        ];
                    }
                }
                
                if (!empty($reasoning)) {
                    return ['success' => true, 'reply' => trim($reasoning)];
                }
            } else {
                $action = waai_extract_action_intent($reply);
                $res = ['success' => true, 'reply' => $reply];
                if ($action) {
                    $res['action'] = $action;
                }
                return $res;
            }
        }
        return ['success' => false, 'error' => 'Unexpected API response: ' . json_encode($result)];
    });
}

/* -----------------------------------------------------------------------
   Anthropic Claude API handler (distinct format from OpenAI)
----------------------------------------------------------------------- */
function waai_query_anthropic($endpoint, $api_key, $model, $system_prompt, $message, $history, $max_tokens, $temperature, $page_context = null) {
    $messages = [];

    foreach ($history as $msg) {
        $role    = in_array($msg['role'] ?? '', ['user', 'assistant']) ? $msg['role'] : 'user';
        $content = trim($msg['content'] ?? '');
        if ($content) {
            $content = preg_replace('/\[SEND_WHATSAPP:\s*(\+?\d+)\s*\|\s*([\s\S]*?)\]/i', '', $content);
            $content = trim($content);
            if ($content !== '') {
                $messages[] = ['role' => $role, 'content' => $content];
            }
        }
    }
    $messages[] = ['role' => 'user', 'content' => $message];

    $post_data = [
        'model'      => $model,
        'system'     => $system_prompt,
        'messages'   => $messages,
        'max_tokens' => $max_tokens,
        'temperature'=> $temperature,
    ];

    $headers = [
        'Content-Type: application/json',
        "x-api-key: {$api_key}",
        'anthropic-version: 2023-06-01',
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($post_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    $result_raw = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_err   = curl_error($ch);
    curl_close($ch);

    if ($curl_err) {
        return ['success' => false, 'error' => 'cURL error: ' . $curl_err];
    }
    if ($http_code >= 400) {
        $err_data = json_decode($result_raw, true);
        $err_msg  = $err_data['error']['message'] ?? "HTTP error {$http_code}";
        return ['success' => false, 'error' => $err_msg];
    }

    $result = json_decode($result_raw, true);
    // Claude response: content is an array of blocks
    $text_reply = '';
    foreach ($result['content'] ?? [] as $block) {
        if (($block['type'] ?? '') === 'text') {
            $text_reply .= $block['text'];
        }
    }

    if (!$text_reply) {
        return ['success' => false, 'error' => 'Empty Anthropic response: ' . $result_raw];
    }

    $res = ['success' => true, 'reply' => $text_reply];
    $action = waai_extract_action_intent($text_reply);
    if ($action) $res['action'] = $action;
    return $res;
}

function waai_query_anthropic_with_tools_loop($endpoint, $api_key, $model, $system_prompt, $messages, $post_data, $max_tokens) {
    $anthropic_tools = [];
    foreach ($post_data['tools'] ?? [] as $tool) {
        $anthropic_tools[] = [
            'name'         => $tool['function']['name'],
            'description'  => $tool['function']['description'] ?? '',
            'input_schema' => $tool['function']['parameters'] ?? ['type' => 'object', 'properties' => []],
        ];
    }

    $headers = [
        'Content-Type: application/json',
        "x-api-key: {$api_key}",
        'anthropic-version: 2023-06-01',
    ];

    // Strip system message from messages array — Anthropic uses top-level 'system'
    $claude_messages = array_values(array_filter($messages, fn($m) => $m['role'] !== 'system'));

    $max_iterations = 6;
    for ($i = 0; $i < $max_iterations; $i++) {
        $payload = [
            'model'      => $model,
            'system'     => $system_prompt,
            'messages'   => $claude_messages,
            'max_tokens' => $max_tokens,
            'tools'      => $anthropic_tools,
        ];

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $raw       = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_err  = curl_error($ch);
        curl_close($ch);

        if ($curl_err) return ['success' => false, 'error' => 'cURL error: ' . $curl_err];
        if ($http_code >= 400) {
            $err_data = json_decode($raw, true);
            return ['success' => false, 'error' => $err_data['error']['message'] ?? "HTTP {$http_code}"];
        }

        $response = json_decode($raw, true);
        $stop_reason = $response['stop_reason'] ?? '';
        $content_blocks = $response['content'] ?? [];

        // Append assistant turn
        $claude_messages[] = ['role' => 'assistant', 'content' => $content_blocks];

        if ($stop_reason === 'tool_use') {
            // Process each tool_use block
            $tool_results = [];
            foreach ($content_blocks as $block) {
                if (($block['type'] ?? '') !== 'tool_use') continue;

                $tool_name  = $block['name'];
                $tool_input = $block['input'] ?? [];
                $tool_use_id = $block['id'];

                require_once dirname(__FILE__) . '/ai-agent-tools.php';
                $agentic_sections = waai_get_all_agentic_sections();
                $toggles = [
                    'scroll'   => waai_config('waai_enable_action_scroll', '1'),
                    'interact' => waai_config('waai_enable_action_interact', '1'),
                    'navigate' => waai_config('waai_enable_action_navigate', '1'),
                    'read'     => waai_config('waai_enable_action_read', '1'),
                ];
                $tool_result_content = WebAssetsAIAgent::execute_tool($tool_name, $tool_input, $agentic_sections, $toggles);

                $tool_results[] = [
                    'type'        => 'tool_result',
                    'tool_use_id' => $tool_use_id,
                    'content'     => is_string($tool_result_content) ? $tool_result_content : wp_json_encode($tool_result_content),
                ];
            }

            // Append tool results as user turn
            $claude_messages[] = ['role' => 'user', 'content' => $tool_results];
            continue;
        }

        // Final response — extract text
        $text_reply = '';
        foreach ($content_blocks as $block) {
            if (($block['type'] ?? '') === 'text') {
                $text_reply .= $block['text'];
            }
        }

        if (!$text_reply) {
            return ['success' => false, 'error' => 'Empty Anthropic tool-loop response'];
        }

        $res = ['success' => true, 'reply' => $text_reply];
        $action = waai_extract_action_intent($text_reply);
        if ($action) $res['action'] = $action;
        return $res;
    }

    return ['success' => false, 'error' => 'Anthropic tool loop exceeded max iterations'];
}

/* -----------------------------------------------------------------------
   Gemini API handler (different format)
----------------------------------------------------------------------- */
function waai_query_gemini($endpoint, $system_prompt, $message, $history, $max_tokens, $temperature, $page_context = null) {
    $contents = [];

    foreach ($history as $msg) {
        $role    = ($msg['role'] ?? 'user') === 'assistant' ? 'model' : 'user';
        $content = trim($msg['content'] ?? '');
        if ($content) {
            // Strip any [SEND_WHATSAPP: ...] tags from history to prevent LLM feedback loops
            $content = preg_replace('/\[SEND_WHATSAPP:\s*(\+?\d+)\s*\|\s*([\s\S]*?)\]/i', '', $content);
            $content = trim($content);
            if ($content !== '') {
                $contents[] = ['role' => $role, 'parts' => [['text' => $content]]];
            }
        }
    }
    $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

    $post_data = [
        'systemInstruction' => ['parts' => [['text' => $system_prompt]]],
        'contents'          => $contents,
        'generationConfig'  => [
            'temperature'    => $temperature,
            'maxOutputTokens'=> $max_tokens,
        ],
    ];

    $agentic_enabled = waai_config('waai_agentic_enabled', '0');
    if ($agentic_enabled === '1') {
        require_once dirname(__FILE__) . '/ai-agent-tools.php';
        $agentic_sections = waai_get_all_agentic_sections();
        $toggles = [
            'scroll'   => waai_config('waai_enable_action_scroll', '1'),
            'interact' => waai_config('waai_enable_action_interact', '1'),
            'navigate' => waai_config('waai_enable_action_navigate', '1'),
            'read'     => waai_config('waai_enable_action_read', '1'),
        ];
        $openai_tools = WebAssetsAIAgent::get_tools($agentic_sections, $toggles);
        $gemini_tools = waai_format_gemini_tools($openai_tools);
        if (!empty($gemini_tools)) {
            $post_data['tools'] = $gemini_tools;
        }
        return waai_query_gemini_with_tools_loop($endpoint, $post_data);
    }

    return waai_curl_post($endpoint, $post_data, ['Content-Type: application/json'], function($result) {
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($text) {
            $action = waai_extract_action_intent($text);
            $res = ['success' => true, 'reply' => $text];
            if ($action) {
                $res['action'] = $action;
            }
            return $res;
        }
        return ['success' => false, 'error' => 'Unexpected Gemini response: ' . json_encode($result)];
    });
}

/* -----------------------------------------------------------------------
   Helper: Get All Agentic Sections (Manual + Auto-Indexed WP Pages)
----------------------------------------------------------------------- */
function waai_get_all_agentic_sections() {
    $manual_sections = waai_config('waai_agentic_sections', []);
    if (!is_array($manual_sections)) $manual_sections = [];

    // The automatic 20-page fetch has been disabled to prevent token bloat in the navigate_website tool.
    // The AI now relies on the secondary-LLM processed background sitemap injected directly into the system prompt.
    $auto_pages = [];
    
    return array_merge($manual_sections, $auto_pages);
}

/* -----------------------------------------------------------------------
   Generic cURL POST helper
----------------------------------------------------------------------- */
function waai_curl_post($url, $data, $headers, $on_success) {
    waai_log('DEBUG', 'cURL Request Initiated', ['url' => $url]);
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        waai_log('ERROR', 'API Request Failed (cURL)', ['url' => $url, 'error' => $curl_error]);
        return ['success' => false, 'error' => 'Connection error: ' . $curl_error];
    }
    if ($http_code !== 200) {
        waai_log('ERROR', 'API Request Failed (HTTP)', ['url' => $url, 'code' => $http_code, 'response' => $response]);
        return ['success' => false, 'error' => "API error (HTTP {$http_code}): " . $response];
    }

    $result = json_decode($response, true);
    if (!$result) {
        waai_log('ERROR', 'API Request Failed (JSON)', ['url' => $url, 'response' => $response]);
        return ['success' => false, 'error' => 'Invalid JSON response from API.'];
    }

    return $on_success($result);
}

/* =============================================================================
   CORE: SUBMIT LEAD DATA
   ============================================================================= */
function waai_submit_lead($lead_data) {
    $name     = waai_sanitize_field($lead_data['name']  ?? '');
    $email    = function_exists('sanitize_email')
                ? sanitize_email($lead_data['email'] ?? '')
                : filter_var(trim($lead_data['email'] ?? ''), FILTER_SANITIZE_EMAIL);
    $phone    = waai_sanitize_field($lead_data['phone']  ?? '');
    $query    = waai_sanitize_field($lead_data['query']  ?? '');
    $page_url = function_exists('esc_url_raw')
                ? esc_url_raw($_SERVER['HTTP_REFERER'] ?? '')
                : filter_var(trim($_SERVER['HTTP_REFERER'] ?? ''), FILTER_SANITIZE_URL);

    $sheets_webhook  = waai_config('waai_sheets_webhook', '');
    $notify_email    = waai_config('waai_lead_email', function_exists('get_option') ? get_option('admin_email') : '');
    $save_to_db      = waai_config('waai_save_leads_db', '0');

    $db_saved     = false;
    $sheets_saved = false;
    $email_sent   = false;

    // --- 1. PRIMARY: WordPress DB ---
    if ($save_to_db === '1' && class_exists('WebAssetsAI_Leads')) {
        $db_saved = WebAssetsAI_Leads::insert([
            'name'         => $name,
            'email'        => $email,
            'phone'        => $phone,
            'query'        => $query,
            'page_url'     => $page_url,
            'email_sent'   => 0, // Tracked separately now
            'sheets_saved' => 0,
        ]);
        // If insert() doesn't return boolean, assume true if we got here without exception
        if ($db_saved === null) $db_saved = true; 
    }

    // --- 2. MIRROR: Google Sheets ---
    if (!empty($sheets_webhook) && strpos($sheets_webhook, 'http') === 0) {
        $payload = json_encode(['name'=>$name,'email'=>$email,'phone'=>$phone,'query'=>$query,'website'=>$page_url]);
        $sh = curl_init($sheets_webhook);
        curl_setopt_array($sh, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        curl_exec($sh);
        $sh_code     = curl_getinfo($sh, CURLINFO_HTTP_CODE);
        $sheets_saved = in_array($sh_code, [200, 302]);
        curl_close($sh);
    }

    // --- 3. NOTIFICATION / FALLBACK: Email ---
    if (!empty($notify_email)) {
        $subject  = "[{$name}] New Lead via AI Assistant Chat";
        if (!$db_saved && $save_to_db === '1') {
            $subject = "[URGENT: DB FAILED] " . $subject;
        }

        $body     = "A visitor submitted their contact details via the AI assistant:\n\n";
        $body    .= "Name:    {$name}\n";
        $body    .= "Email:   {$email}\n";
        $body    .= "Phone:   {$phone}\n";
        $body    .= "Query:   {$query}\n";
        $body    .= "Page:    {$page_url}\n";
        $body    .= "Time:    " . (function_exists('wp_date') ? wp_date('Y-m-d H:i:s') : date('Y-m-d H:i:s')) . "\n\n";
        $body    .= "Storage Status:\n";
        $body    .= "- WP DB: " . ($db_saved ? "Saved" : ($save_to_db === '1' ? "FAILED" : "Disabled")) . "\n";
        $body    .= "- Sheets: " . ($sheets_saved ? "Saved" : (empty($sheets_webhook) ? "Disabled" : "FAILED")) . "\n";

        // Pull configured 'From' name and address
        $from_name  = waai_config('waai_email_from_name', function_exists('get_bloginfo') ? get_bloginfo('name') : 'WebAssets AI');
        $from_email = waai_config('waai_email_from_address', function_exists('get_option') ? get_option('admin_email') : 'no-reply@webassets.tech');

        $headers = [
            'Content-Type: text/plain; charset=UTF-8',
            "From: {$from_name} <{$from_email}>"
        ];

        if (function_exists('wp_mail')) {
            $email_sent = wp_mail($notify_email, $subject, $body, $headers);
        } else {
            $host = 'localhost';
            if (function_exists('get_site_url')) {
                $site_url = get_site_url();
                $parsed = parse_url($site_url, PHP_URL_HOST);
                if ($parsed) {
                    $host = $parsed;
                }
            } elseif (!empty($_SERVER['HTTP_HOST'])) {
                $host = $_SERVER['HTTP_HOST'];
            }
            $host = explode(':', $host)[0];
            
            // Still fallback to the default PHP mail if WP isn't loaded (rare case)
            $headers[] = "Reply-To: {$from_email}";
            $header_str = implode("\r\n", $headers);
            $email_sent = mail($notify_email, $subject, $body, $header_str);
        }
    }

    $response = [
        'success'      => true,
        'db_saved'     => $db_saved,
        'email_sent'   => $email_sent,
        'sheets_saved' => $sheets_saved,
        'message'      => 'Lead successfully processed.',
    ];
    waai_log('INFO', 'Lead Processed', $response);
    return $response;
}

/* =============================================================================
   UTILITY
   ============================================================================= */
function waai_sanitize_field($value) {
    if (function_exists('sanitize_text_field')) {
        return sanitize_text_field($value);
    }
    return htmlspecialchars(strip_tags(trim($value)));
}

function waai_is_safe_content($str, $is_strict = true) {
    // Empty strings are safe — do not block empty optional fields like name/query
    if (empty($str)) return true;
    
    $lowerStr = strtolower($str);
    
    // Base blocklist (XSS, Injection)
    $blocked = ['<script', 'javascript:', 'onerror=', 'onload=', 'eval(', 'document.cookie', 'iframe', 'alert('];
    
    if ($is_strict) {
        // Strict blocklist for company-only restrictions (competitors, spam)
        $blocked = array_merge($blocked, ['competitor', 'scam', 'phishing', 'click here to win', 'free money', 'lottery']);
    }
    
    foreach ($blocked as $word) {
        if (strpos($lowerStr, $word) !== false) {
            return false;
        }
    }
    
    return true;
}

/* =============================================================================
   CORE: SEND WHATSAPP MESSAGE
   ============================================================================= */
function waai_send_whatsapp_message($to, $message) {
    $enabled = waai_config('waai_whatsapp_api_enabled', '1');
    if ($enabled !== '1') {
        return ['success' => false, 'error' => 'WhatsApp forwarding is disabled.'];
    }

    $app_key  = waai_config('waai_whatsapp_app_key', '52b2e6ea-259b-4a40-a1a3-a84acbedde09');
    $auth_key = waai_config('waai_whatsapp_auth_key', 'NcDjp7sOdkiUDwfvcSCvGQLh7p6zLRYJ1');

    if (empty($app_key) || empty($auth_key)) {
        return ['success' => false, 'error' => 'WhatsApp API configuration credentials are missing.'];
    }

    // Clean phone number (keep only digits)
    $to = preg_replace('/\D/', '', $to);
    if (empty($to)) {
        return ['success' => false, 'error' => 'Invalid phone number.'];
    }

    $message = trim($message);
    if (empty($message)) {
        return ['success' => false, 'error' => 'Message content is empty.'];
    }

    if (mb_strlen($message) > 1000) {
        return ['success' => false, 'error' => 'Message exceeds maximum length allowed (1000).'];
    }

    if (!waai_is_safe_content($message, true)) {
        return ['success' => false, 'error' => 'Message contains restricted or unsafe content.'];
    }

    $post_data = [
        'app_key'  => $app_key,
        'auth_key' => $auth_key,
        'to'       => $to,
        'type'     => 'text',
        'message'  => $message,
    ];

    $ch = curl_init('https://whatsapp.webassets.tech/api/whatsapp-web/send-message');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post_data,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
            'User-Agent: WebAssets-AI-Assistant/1.0'
        ]
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return ['success' => false, 'error' => 'Connection error: ' . $curl_error];
    }

    $result = json_decode($response, true);
    if (!$result) {
        $raw_preview = substr(strip_tags($response), 0, 100);
        return ['success' => false, 'error' => 'Invalid JSON from WhatsApp API. HttpCode: ' . $http_code . ' | Raw: ' . $raw_preview];
    }

    $is_success = false;
    $api_data = null;
    $error_msg = 'Unknown API error.';

    if (isset($result['success']) && $result['success']) {
        $is_success = true;
        if (isset($result['data'])) {
            $api_data = $result['data'];
            if (is_array($api_data) && isset($api_data['message'])) {
                $error_msg = $api_data['message'];
            }
        }
    } elseif (isset($result['data']['success']) && $result['data']['success']) {
        $is_success = true;
        $api_data = $result['data'];
    }

    if ($is_success) {
        waai_log('INFO', 'WhatsApp Message Sent', ['to' => $to, 'data' => $api_data]);
        return ['success' => true, 'data' => $api_data];
    }

    if (isset($result['message'])) {
        $error_msg = $result['message'];
    } elseif (isset($result['error'])) {
        $error_msg = is_array($result['error']) ? ($result['error']['message'] ?? json_encode($result['error'])) : $result['error'];
    } elseif (isset($result['data']['message'])) {
        $error_msg = $result['data']['message'];
    }

    waai_log('ERROR', 'WhatsApp Message Failed', ['to' => $to, 'error' => $error_msg]);
    return ['success' => false, 'error' => $error_msg];
}

/* =============================================================================
   EMAIL SENDING FUNCTIONALITY
   ============================================================================= */
function waai_send_email_message($to, $subject, $message) {
    if (waai_config('waai_email_api_enabled', '0') !== '1') {
        return ['success' => false, 'error' => 'Email sending is currently disabled.'];
    }

    if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Invalid email address provided.'];
    }

    if (empty($message)) {
        return ['success' => false, 'error' => 'Message content is empty.'];
    }

    if (mb_strlen($message) > 2000) {
        return ['success' => false, 'error' => 'Message exceeds maximum length allowed (2000).'];
    }

    if (!waai_is_safe_content($message, true) || !waai_is_safe_content($subject, true)) {
        return ['success' => false, 'error' => 'Message or Subject contains restricted content.'];
    }

    $email_method = waai_config('waai_email_method', 'wp_mail');
    
    // We rely on wp_mail to be available (which it should be if WP is loaded)
    if (!function_exists('wp_mail')) {
        // Fallback to basic PHP mail if WP isn't loaded
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $from_name = waai_config('waai_email_from_name', 'Company');
        $from_email = waai_config('waai_email_from_address', 'no-reply@domain.com');
        $headers .= "From: {$from_name} <{$from_email}>\r\n";
        
        $sent = mail($to, $subject, nl2br($message), $headers);
        if ($sent) {
            return ['success' => true];
        }
        return ['success' => false, 'error' => 'Failed to send email via standard PHP mail() fallback.'];
    }

    // Wrap the SMTP override logic in an anonymous function if using SMTP mode
    $phpmailer_action = null;
    if ($email_method === 'smtp') {
        $smtp_host   = waai_config('waai_smtp_host', '');
        $smtp_port   = waai_config('waai_smtp_port', '587');
        $smtp_secure = waai_config('waai_smtp_secure', 'tls');
        $smtp_user   = waai_config('waai_smtp_user', '');
        $smtp_pass   = waai_config('waai_smtp_pass', '');

        if (empty($smtp_host)) {
            return ['success' => false, 'error' => 'SMTP Host is not configured in settings.'];
        }

        $phpmailer_action = function($phpmailer) use ($smtp_host, $smtp_port, $smtp_secure, $smtp_user, $smtp_pass) {
            $phpmailer->isSMTP();
            $phpmailer->Host = $smtp_host;
            $phpmailer->Port = $smtp_port;
            
            if ($smtp_secure !== 'none') {
                $phpmailer->SMTPSecure = $smtp_secure;
            } else {
                $phpmailer->SMTPSecure = '';
                $phpmailer->SMTPAutoTLS = false;
            }
            
            if (!empty($smtp_user)) {
                $phpmailer->SMTPAuth = true;
                $phpmailer->Username = $smtp_user;
                $phpmailer->Password = $smtp_pass;
            } else {
                $phpmailer->SMTPAuth = false;
            }
        };
        add_action('phpmailer_init', $phpmailer_action, 999);
    }

    $from_name = waai_config('waai_email_from_name', get_bloginfo('name'));
    $from_email = waai_config('waai_email_from_address', get_option('admin_email'));
    
    // Set content type and from email via filters
    $filter_content_type = function() { return 'text/html'; };
    $filter_from_email = function($original_email) use ($from_email) { return !empty($from_email) ? $from_email : $original_email; };
    $filter_from_name = function($original_name) use ($from_name) { return !empty($from_name) ? $from_name : $original_name; };

    add_filter('wp_mail_content_type', $filter_content_type);
    add_filter('wp_mail_from', $filter_from_email);
    add_filter('wp_mail_from_name', $filter_from_name);

    $formatted_message = nl2br($message);
    $sent = wp_mail($to, $subject, $formatted_message);

    // Clean up filters and actions
    remove_filter('wp_mail_content_type', $filter_content_type);
    remove_filter('wp_mail_from', $filter_from_email);
    remove_filter('wp_mail_from_name', $filter_from_name);
    
    if ($phpmailer_action) {
        remove_action('phpmailer_init', $phpmailer_action, 999);
    }

    if ($sent) {
        waai_log('INFO', 'Email Sent', ['to' => $to, 'subject' => $subject, 'method' => $email_method]);
        return ['success' => true];
    } else {
        waai_log('ERROR', 'Email Failed', ['to' => $to, 'subject' => $subject, 'method' => $email_method]);
        return ['success' => false, 'error' => 'wp_mail failed to send the email. Check your SMTP or server mail configuration.'];
    }
}

/* =============================================================================
   AGENTIC HELPER FUNCTIONS & LOOPS
   ============================================================================= */

function waai_curl_post_direct($url, $data, $headers) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($data),
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);

    $response   = curl_exec($ch);
    $http_code  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    if ($curl_error) {
        return ['error' => 'Connection error: ' . $curl_error];
    }
    if ($http_code !== 200) {
        return ['error' => "API error (HTTP {$http_code}): " . $response];
    }

    $result = json_decode($response, true);
    if (!$result) {
        return ['error' => 'Invalid JSON response from API.'];
    }

    return $result;
}

function waai_wp_search_site($query) {
    $query = trim($query);
    if (empty($query)) return [];
    
    // Check if WordPress environment is loaded
    if (function_exists('get_posts')) {
        $post_types = function_exists('get_post_types') 
            ? array_values(get_post_types(['public' => true], 'names')) 
            : ['page', 'post', 'portfolio', 'Services'];

        // Exclude attachments from the search results
        if (($key = array_search('attachment', $post_types)) !== false) {
            unset($post_types[$key]);
        }

        $args = [
            's' => $query,
            'post_type' => array_values($post_types),
            'posts_per_page' => 5,
            'post_status' => 'publish'
        ];
        $posts = get_posts($args);
        $results = [];
        foreach ($posts as $p) {
            $results[] = [
                'title' => get_the_title($p->ID),
                'url' => str_replace(home_url(), '', get_permalink($p->ID))
            ];
        }
        return $results;
    }
    
    // Standalone fallback: search through static sections
    $sections = waai_config('waai_agentic_sections', []);
    $results = [];
    if (is_array($sections)) {
        foreach ($sections as $sec) {
            if (isset($sec['name']) && stripos($sec['name'], $query) !== false) {
                $results[] = [
                    'title' => $sec['name'],
                    'url' => $sec['selector']
                ];
            }
        }
    }
    return $results;
}

function waai_format_gemini_tools($openai_tools) {
    if (empty($openai_tools)) return [];
    
    $declarations = [];
    foreach ($openai_tools as $tool) {
        if ($tool['type'] === 'function') {
            $func = $tool['function'];
            
            $properties = [];
            if (isset($func['parameters']['properties'])) {
                foreach ($func['parameters']['properties'] as $propName => $prop) {
                    $newProp = $prop;
                    if (isset($prop['type'])) {
                        $newProp['type'] = strtoupper($prop['type']);
                    }
                    $properties[$propName] = $newProp;
                }
            }
            
            $declarations[] = [
                'name' => $func['name'],
                'description' => $func['description'] ?? '',
                'parameters' => [
                    'type' => 'OBJECT',
                    'properties' => $properties,
                    'required' => $func['parameters']['required'] ?? []
                ]
            ];
        }
    }
    
    return [['functionDeclarations' => $declarations]];
}

function waai_query_openai_with_tools_loop($endpoint, $post_data, $headers) {
    $loop_limit = 3;
    $loop_count = 0;
    $has_unresolved_tools = true;
    
    $client_actions = [];
    $messages = $post_data['messages'];

    while ($has_unresolved_tools && $loop_count < $loop_limit) {
        $loop_count++;
        $has_unresolved_tools = false;

        $post_data['messages'] = $messages;
        
        $response_data = waai_curl_post_direct($endpoint, $post_data, $headers);
        if (!$response_data || isset($response_data['error'])) {
            return [
                'success' => false,
                'error' => $response_data['error'] ?? 'API Request failed in tool loop.'
            ];
        }

        if (!isset($response_data['choices'][0]['message'])) {
            return ['success' => false, 'error' => 'Unexpected API response structure: ' . json_encode($response_data)];
        }

        $msg_resp = $response_data['choices'][0]['message'];
        
        if (isset($msg_resp['tool_calls']) && !empty($msg_resp['tool_calls'])) {
            $tool_calls = $msg_resp['tool_calls'];
            
            $messages[] = $msg_resp;
            
            $tool_responses = [];
            foreach ($tool_calls as $tc) {
                $func_name = $tc['function']['name'] ?? '';
                $args = json_decode($tc['function']['arguments'] ?? '{}', true);

                if ($func_name === 'search_site_directory') {
                    $search_query = $args['query'] ?? '';
                    $search_results = waai_wp_search_site($search_query);
                    
                    $tool_responses[] = [
                        'role' => 'tool',
                        'tool_call_id' => $tc['id'],
                        'name' => 'search_site_directory',
                        'content' => json_encode($search_results)
                    ];
                    $has_unresolved_tools = true;
                } else {
                    if ($func_name === 'navigate_website') {
                        require_once dirname(__FILE__) . '/ai-agent-tools.php';
                        $sections = waai_get_all_agentic_sections();
                        $action = $args['action'];
                        $selector = WebAssetsAIAgent::get_selector_for_target($args['target_name'], $sections, $action);
                        
                        // Auto-correct / Fallback if selector not found
                        if (empty($selector) && !empty($args['target_name'])) {
                            $search_res = waai_wp_search_site($args['target_name']);
                            if (!empty($search_res)) {
                                $selector = $search_res[0]['url'];
                                // If the LLM mistakenly chose scroll but we found a separate page, force redirect
                                if ($action === 'scroll' && (strpos($selector, '/') === 0 || strpos($selector, 'http') === 0)) {
                                    $action = 'redirect';
                                }
                            }
                        }
                        
                        $client_actions[] = [
                            'type' => 'agentic_navigation',
                            'params' => [
                                'action' => $action,
                                'selector' => $selector,
                                'target_name' => $args['target_name'],
                                'confidence' => $args['confidence'] ?? 1.0
                            ]
                        ];
                    } else if ($func_name === 'interact_with_element') {
                        $client_actions[] = [
                            'type' => 'agentic_interaction',
                            'params' => [
                                'action' => $args['action'],
                                'target_text' => $args['target_text'] ?? '',
                                'element_id' => $args['element_id'] ?? null,
                                'value' => $args['value'] ?? '',
                                'confidence' => $args['confidence'] ?? 1.0
                            ]
                        ];
                    } else if ($func_name === 'scroll_page') {
                        $client_actions[] = [
                            'type' => 'scroll_page',
                            'params' => [
                                'direction' => $args['direction'],
                                'amount_pixels' => $args['amount_pixels'] ?? null
                            ]
                        ];
                    } else if ($func_name === 'open_assistant_overlay') {
                        $client_actions[] = [
                            'type' => 'open_assistant_overlay',
                            'params' => [
                                'overlay_type' => $args['overlay_type']
                            ]
                        ];
                    }

                    $tool_responses[] = [
                        'role' => 'tool',
                        'tool_call_id' => $tc['id'],
                        'name' => $func_name,
                        'content' => json_encode(['status' => 'queued_for_client_execution'])
                    ];
                    $has_unresolved_tools = true;
                }
            }

            foreach ($tool_responses as $tr) {
                $messages[] = $tr;
            }

        } else {
            $reply = $msg_resp['content'] ?? '';
            $action = waai_extract_action_intent($reply);
            
            $res = [
                'success' => true,
                'reply' => $reply
            ];
            
            if ($action) {
                $res['action'] = $action;
            }
            if (!empty($client_actions)) {
                $res['actions'] = $client_actions;
            }
            return $res;
        }
    }

    $reply = $response_data['choices'][0]['message']['content'] ?? "Sure, I've queued those actions for you.";
    $action = waai_extract_action_intent($reply);
    
    $res = [
        'success' => true,
        'reply' => $reply
    ];
    if ($action) {
        $res['action'] = $action;
    }
    if (!empty($client_actions)) {
        $res['actions'] = $client_actions;
    }
    return $res;
}

function waai_query_gemini_with_tools_loop($endpoint, $post_data) {
    $loop_limit = 3;
    $loop_count = 0;
    $has_unresolved_tools = true;
    
    $client_actions = [];
    $contents = $post_data['contents'];

    while ($has_unresolved_tools && $loop_count < $loop_limit) {
        $loop_count++;
        $has_unresolved_tools = false;

        $post_data['contents'] = $contents;
        
        $response_data = waai_curl_post_direct($endpoint, $post_data, ['Content-Type: application/json']);
        if (!$response_data || isset($response_data['error'])) {
            return [
                'success' => false,
                'error' => $response_data['error'] ?? 'Gemini Request failed in tool loop.'
            ];
        }

        // Gemini thinking models return multiple parts: thought + functionCall (or thought + text).
        // We must iterate ALL parts, not just parts[0].
        $response_parts = $response_data['candidates'][0]['content']['parts'] ?? [];
        if (empty($response_parts)) {
            return ['success' => false, 'error' => 'Unexpected Gemini response structure: ' . json_encode($response_data)];
        }

        // Find any functionCall part among all response parts
        $function_call_part = null;
        $text_part          = null;
        foreach ($response_parts as $p) {
            if (isset($p['functionCall']) && $function_call_part === null) {
                $function_call_part = $p;
            }
            if (isset($p['text']) && $text_part === null) {
                $text_part = $p;
            }
        }

        if ($function_call_part !== null) {
            $fc        = $function_call_part['functionCall'];
            $func_name = $fc['name'] ?? '';
            $args      = $fc['args'] ?? [];

            // CRITICAL: Echo the ENTIRE parts array back (including any thought parts with
            // thought_signature). Gemini thinking models require this to be passed back
            // verbatim, or the next request will fail with HTTP 400 "missing thought_signature".
            $contents[] = [
                'role'  => 'model',
                'parts' => $response_parts
            ];

            if ($func_name === 'search_site_directory') {
                $search_query   = $args['query'] ?? '';
                $search_results = waai_wp_search_site($search_query);

                $contents[] = [
                    'role'  => 'user',
                    'parts' => [[
                        'functionResponse' => [
                            'name'     => 'search_site_directory',
                            'response' => ['output' => $search_results]
                        ]
                    ]]
                ];
                $has_unresolved_tools = true;
            } else {
                if ($func_name === 'navigate_website') {
                    require_once dirname(__FILE__) . '/ai-agent-tools.php';
                    $sections    = waai_get_all_agentic_sections();
                    $action      = $args['action'] ?? 'scroll';
                    $target_name = $args['target_name'] ?? '';
                    $selector    = WebAssetsAIAgent::get_selector_for_target($target_name, $sections, $action);
                    
                    // Auto-correct / Fallback if selector not found
                    if (empty($selector) && !empty($target_name)) {
                        $search_res = waai_wp_search_site($target_name);
                        if (!empty($search_res)) {
                            $selector = $search_res[0]['url'];
                            // If the LLM mistakenly chose scroll but we found a separate page, force redirect
                            if ($action === 'scroll' && (strpos($selector, '/') === 0 || strpos($selector, 'http') === 0)) {
                                $action = 'redirect';
                            }
                        }
                    }
                    
                    $client_actions[] = [
                        'type'   => 'agentic_navigation',
                        'params' => [
                            'action'      => $action,
                            'selector'    => $selector,
                            'target_name' => $target_name,
                            'confidence'  => $args['confidence'] ?? 1.0
                        ]
                    ];
                } else if ($func_name === 'interact_with_element') {
                    $client_actions[] = [
                        'type'   => 'agentic_interaction',
                        'params' => [
                            'action'      => $args['action'] ?? 'click',
                            'target_text' => $args['target_text'] ?? '',
                            'element_id'  => $args['element_id'] ?? null,
                            'value'       => $args['value'] ?? '',
                            'confidence'  => $args['confidence'] ?? 1.0
                        ]
                    ];
                } else if ($func_name === 'scroll_page') {
                    $client_actions[] = [
                        'type'   => 'scroll_page',
                        'params' => [
                            'direction'     => $args['direction'] ?? 'down',
                            'amount_pixels' => $args['amount_pixels'] ?? null
                        ]
                    ];
                } else if ($func_name === 'open_assistant_overlay') {
                    $client_actions[] = [
                        'type'   => 'open_assistant_overlay',
                        'params' => [
                            'overlay_type' => $args['overlay_type'] ?? 'lead_form'
                        ]
                    ];
                }

                $contents[] = [
                    'role'  => 'user',
                    'parts' => [[
                        'functionResponse' => [
                            'name'     => $func_name,
                            'response' => ['status' => 'queued_for_client_execution']
                        ]
                    ]]
                ];
                $has_unresolved_tools = true;
            }
        } else {
            // No function call — this is the final text reply
            $reply  = $text_part['text'] ?? '';
            $action = waai_extract_action_intent($reply);
            
            $res = [
                'success' => true,
                'reply'   => $reply
            ];
            if ($action) {
                $res['action'] = $action;
            }
            if (!empty($client_actions)) {
                $res['actions'] = $client_actions;
            }
            return $res;
        }
    }

    $reply  = $text_part['text'] ?? "Sure, I've queued those actions for you.";
    $action = waai_extract_action_intent($reply);
    
    $res = [
        'success' => true,
        'reply'   => $reply
    ];
    if ($action) {
        $res['action'] = $action;
    }
    if (!empty($client_actions)) {
        $res['actions'] = $client_actions;
    }
    return $res;
}

/* =============================================================================
   HELPER: BACKGROUND DOM COMPRESSION (SECONDARY LLM)
   ============================================================================= */
function waai_compress_interactables_background($interactables) {
    if (empty($interactables)) {
        return ['success' => true, 'compressed_map' => []];
    }
    
    // Use secondary LLM config directly — independent of the long-context fallback toggle.
    // Falls back to slicing only if no API key is configured.
    $provider = waai_config('waai_long_context_provider', 'openrouter');
    $model    = waai_config('waai_long_context_model', 'google/gemini-1.5-flash');
    $api_key  = waai_config('waai_long_context_api_key', '');
    
    if (empty($api_key)) {
        // No secondary LLM configured — return a basic trimmed list
        return ['success' => true, 'compressed_map' => $interactables];
    }
    
    // Determine endpoint & headers by provider
    switch ($provider) {
        case 'openai':
            $endpoint   = 'https://api.openai.com/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'gemini':
            $endpoint   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
            $auth_header = null;
            break;
        case 'anthropic':
            $endpoint   = 'https://api.anthropic.com/v1/messages';
            $auth_header = null;
            break;
        case 'openrouter':
        default:
            $endpoint   = 'https://openrouter.ai/api/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
    }
    
    $system_prompt = "You are a DOM optimizer. You will receive a JSON array of interactable elements from a webpage.
Your goal is to drastically compress this list into a clean JSON array of ONLY the most important, actionable elements.
- REMOVE entirely: social media links, privacy policy/terms, redundant header nav links if they are obvious boilerplate, empty links, or decorative buttons.
- KEEP: Primary CTAs (Buy, Subscribe, Contact), unique form inputs, unique page-specific navigation.
- MERGE: If multiple buttons have the exact same text and href, keep only one but preserve its waai_id.
- OUTPUT FORMAT: Return ONLY a valid JSON array. No markdown blocks, no conversational text.
Example output: [{\"waai_id\": 12, \"type\": \"button\", \"text\": \"Submit Form\"}]";

    $user_message = "Compress this list:\n" . wp_json_encode($interactables);
    
    if ($provider === 'gemini') {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $system_prompt . "\n\n" . $user_message]]]
            ]
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($result, true);
        $text_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    } elseif ($provider === 'anthropic') {
        $payload = [
            'model'      => $model,
            'system'     => $system_prompt,
            'messages'   => [['role' => 'user', 'content' => $user_message]],
            'max_tokens' => 1000,
        ];
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "x-api-key: {$api_key}",
            'anthropic-version: 2023-06-01',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result, true);
        $text_response = '';
        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') $text_response .= $block['text'];
        }
    } else {
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_message]
        ];
        
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.1
        ];
        
        // Only OpenAI fully supports response_format strictly everywhere, skip it for OpenRouter generic to prevent errors, prompt handles it.
        
        $headers = ['Content-Type: application/json'];
        if ($auth_header) $headers[] = $auth_header;
        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: ' . waai_config('waai_company_website', site_url());
            $headers[] = 'X-Title: WebAssets AI Assistant';
        }
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($result, true);
        $text_response = $data['choices'][0]['message']['content'] ?? '';
    }
    
    // Clean markdown if present
    $text_response = preg_replace('/```json\s*/', '', $text_response);
    $text_response = preg_replace('/```/', '', $text_response);
    $text_response = trim($text_response);
    
    $compressed = json_decode($text_response, true);
    
    if (is_array($compressed)) {
        return ['success' => true, 'compressed_map' => $compressed];
    } else {
        return ['success' => true, 'compressed_map' => $interactables];
    }
}

/* =============================================================================
   HELPER: BACKGROUND SITEMAP COMPRESSION (SECONDARY LLM)
   ============================================================================= */
function waai_compress_sitemap_background($all_pages) {
    if (empty($all_pages)) {
        return ['success' => true, 'compressed_sitemap' => []];
    }
    
    // Use secondary LLM config directly — independent of the long-context fallback toggle.
    // Falls back to slicing only if no API key is configured.
    $provider = waai_config('waai_long_context_provider', 'openrouter');
    $model    = waai_config('waai_long_context_model', 'google/gemini-1.5-flash');
    $api_key  = waai_config('waai_long_context_api_key', '');
    
    if (empty($api_key)) {
        // No secondary LLM configured — return a basic trimmed list
        return ['success' => true, 'compressed_sitemap' => $all_pages];
    }
    
    switch ($provider) {
        case 'openai':
            $endpoint   = 'https://api.openai.com/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'gemini':
            $endpoint   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
            $auth_header = null;
            break;
        case 'anthropic':
            $endpoint   = 'https://api.anthropic.com/v1/messages';
            $auth_header = null;
            break;
        case 'openrouter':
        default:
            $endpoint   = 'https://openrouter.ai/api/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
    }
    
    $system_prompt = "You are a website directory cleaner. You will receive a JSON array containing the titles and URLs of published pages on a WordPress site.
Your goal is to clean this list of junk pages, BUT YOU MUST KEEP ALL VALID NAVIGATIONAL PAGES.
- REMOVE ONLY: Drafts, redundant privacy/terms pages, backend login URLs, or raw checkout/cart endpoints.
- KEEP: EVERYTHING ELSE. You must keep all Services, Products, Home, About, Contact, Blog Index, Core Products, and any other valid content pages.
- DO NOT compress drastically. DO NOT merge pages. Keep them individually.
- OUTPUT FORMAT: Return ONLY a valid JSON array of objects, where each object has 'title' and 'url'. No markdown blocks, no conversational text.
Example output: [{\"title\": \"Contact Us\", \"url\": \"/contact/\"}]";

    $user_message = "Compress this sitemap:\n" . wp_json_encode($all_pages);
    
    if ($provider === 'gemini') {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $system_prompt . "\n\n" . $user_message]]]
            ]
        ];
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($result, true);
        $text_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    } elseif ($provider === 'anthropic') {
        $payload = [
            'model'      => $model,
            'system'     => $system_prompt,
            'messages'   => [['role' => 'user', 'content' => $user_message]],
            'max_tokens' => 1000,
        ];
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "x-api-key: {$api_key}",
            'anthropic-version: 2023-06-01',
        ]);
        $result = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($result, true);
        $text_response = '';
        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') $text_response .= $block['text'];
        }
    } else {
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user', 'content' => $user_message]
        ];
        
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.1
        ];
        
        $headers = ['Content-Type: application/json'];
        if ($auth_header) $headers[] = $auth_header;
        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: ' . waai_config('waai_company_website', site_url());
            $headers[] = 'X-Title: WebAssets AI Assistant';
        }
        
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        curl_close($ch);
        
        $data = json_decode($result, true);
        $text_response = $data['choices'][0]['message']['content'] ?? '';
    }
    
    $text_response = preg_replace('/```json\s*/', '', $text_response);
    $text_response = preg_replace('/```/', '', $text_response);
    $text_response = trim($text_response);
    
    $compressed = json_decode($text_response, true);
    
    if (is_array($compressed)) {
        return ['success' => true, 'compressed_sitemap' => $compressed];
    } else {
        return ['success' => true, 'compressed_sitemap' => $all_pages];
    }
}

/* =============================================================================
   HELPER: BACKGROUND PAGE CONTENT SUMMARIZATION (SECONDARY LLM)
   Sends full page text to secondary LLM, returns a concise ~300 char summary.
   Primary LLM (Groq) then receives only the summary — saves ~1,200 tokens/msg.
   ============================================================================= */
function waai_summarize_page_content_background($page_content, $page_title = '', $page_url = '') {
    $provider = waai_config('waai_long_context_provider', 'openrouter');
    $model    = waai_config('waai_long_context_model', 'google/gemini-1.5-flash');
    $api_key  = waai_config('waai_long_context_api_key', '');

    if (empty($api_key)) {
        waai_log('WARNING', 'Secondary LLM Blocked', ['reason' => 'No secondary LLM API key configured']);
        return ['success' => false, 'error' => 'No secondary LLM API key configured'];
    }

    switch ($provider) {
        case 'openai':
            $endpoint    = 'https://api.openai.com/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
        case 'gemini':
            $endpoint    = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$api_key}";
            $auth_header = null;
            break;
        case 'anthropic':
            $endpoint    = 'https://api.anthropic.com/v1/messages';
            $auth_header = null;
            break;
        case 'openrouter':
        default:
            $endpoint    = 'https://openrouter.ai/api/v1/chat/completions';
            $auth_header = "Authorization: Bearer {$api_key}";
            break;
    }

    $system_prompt = "You are a page context summarizer for an AI chat assistant.
You will receive the full text content of a webpage. Your job is to extract a detailed, section-by-section summary so the AI assistant has a strong understanding of the page's specific content.

OUTPUT FORMAT:
- Page Title & Main Purpose: [brief overview]
- Section Summaries: [Provide a brief paragraph or bullet points summarizing the specific details, services, features, or content presented in each logical section of the page. Do not generalize; include specific names, numbers, and distinct details.]
- Pricing: [specific pricing info, or 'not shown']
- CTAs & Contact: [calls to action and contact info]

Provide enough detail so the primary AI doesn't have to guess. Aim for a comprehensive summary around 1500-2500 characters.";

    $user_message = "Summarize this page:\nTitle: {$page_title}\nURL: {$page_url}\n\n" . substr($page_content, 0, 40000);

    if ($provider === 'gemini') {
        $payload = [
            'contents' => [
                ['role' => 'user', 'parts' => [['text' => $system_prompt . "\n\n" . $user_message]]]
            ]
        ];
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        
        if ($result === false) {
            $curl_err = curl_error($ch);
            curl_close($ch);
            waai_log('ERROR', 'Secondary Gemini cURL Failed', ['error' => $curl_err]);
            return ['success' => false, 'error' => 'cURL failed: ' . $curl_err];
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 400) {
            waai_log('ERROR', 'Secondary Gemini API Error', ['http_code' => $http_code, 'response' => $result]);
            return ['success' => false, 'error' => "HTTP error {$http_code}"];
        }

        $data          = json_decode($result, true);
        $text_response = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
    } elseif ($provider === 'anthropic') {
        $payload = [
            'model'      => $model,
            'system'     => $system_prompt,
            'messages'   => [['role' => 'user', 'content' => $user_message]],
            'max_tokens' => 1000,
            'temperature'=> 0.1,
        ];
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            "x-api-key: {$api_key}",
            'anthropic-version: 2023-06-01',
        ]);
        $result = curl_exec($ch);
        if ($result === false) {
            $curl_err = curl_error($ch);
            curl_close($ch);
            waai_log('ERROR', 'Secondary Anthropic cURL Failed', ['error' => $curl_err]);
            return ['success' => false, 'error' => 'cURL failed: ' . $curl_err];
        }
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($http_code >= 400) {
            waai_log('ERROR', 'Secondary Anthropic API Error', ['http_code' => $http_code, 'response' => $result]);
            return ['success' => false, 'error' => "HTTP error {$http_code}"];
        }
        $data = json_decode($result, true);
        $text_response = '';
        foreach ($data['content'] ?? [] as $block) {
            if (($block['type'] ?? '') === 'text') $text_response .= $block['text'];
        }
    } else {
        $messages = [
            ['role' => 'system', 'content' => $system_prompt],
            ['role' => 'user',   'content' => $user_message]
        ];
        $payload = ['model' => $model, 'messages' => $messages, 'temperature' => 0.1, 'max_tokens' => 1000];
        $headers = ['Content-Type: application/json'];
        if ($auth_header) $headers[] = $auth_header;
        if ($provider === 'openrouter') {
            $headers[] = 'HTTP-Referer: ' . waai_config('waai_company_website', site_url());
            $headers[] = 'X-Title: WebAssets AI Assistant';
        }
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        
        if ($result === false) {
            $curl_err = curl_error($ch);
            curl_close($ch);
            waai_log('ERROR', 'Secondary LLM cURL Failed', ['error' => $curl_err]);
            return ['success' => false, 'error' => 'cURL failed: ' . $curl_err];
        }
        
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code >= 400) {
            waai_log('ERROR', 'Secondary LLM API Error', ['http_code' => $http_code, 'response' => $result]);
            return ['success' => false, 'error' => "HTTP error {$http_code}"];
        }

        $data          = json_decode($result, true);
        $text_response = $data['choices'][0]['message']['content'] ?? '';
    }

    $summary = trim($text_response);

    if (!empty($summary)) {
        waai_log('INFO', 'Secondary LLM Page Summary Generated', ['summary' => $summary, 'page' => $page_url]);
        return ['success' => true, 'page_summary' => $summary];
    } else {
        waai_log('WARNING', 'Secondary LLM Empty Response', ['provider' => $provider, 'raw_response' => $result]);
        return ['success' => false, 'error' => 'Secondary LLM returned empty summary'];
    }
}
