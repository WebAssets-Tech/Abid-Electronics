<?php
/**
 * Prompt Builder Module
 * Contains modular helper functions to construct the AI system prompt securely on the backend.
 */

if (!defined('ABSPATH')) exit; // Prevent direct access

function waai_build_identity_prompt($name, $tagline, $location, $contact_text, $desc) {
    $prompt  = "[ROLE]\n";
    $prompt .= "You are the friendly, knowledgeable AI assistant for {$name}. Your goal is to help website visitors learn about the company, its services, and products, and guide interested visitors to book a free consultation or share their contact details.\n\n";

    // Cap the About/Description field to prevent token bloat.
    // Users sometimes paste a full AI system prompt here which duplicates other sections.
    $desc_clean = strip_tags((string)$desc);                    // strip HTML
    $desc_clean = preg_replace('/^#{1,6}\s+/m', '', $desc_clean); // strip markdown headers
    $desc_clean = preg_replace('/\*{1,2}([^*]+)\*{1,2}/', '$1', $desc_clean); // strip bold/italic
    $desc_clean = preg_replace('/\n{3,}/', "\n\n", trim($desc_clean)); // collapse whitespace
    if (strlen($desc_clean) > 800) {
        $desc_clean = substr($desc_clean, 0, 797) . '...';
    }

    $prompt .= "[COMPANY]\n";
    $prompt .= "Name: {$name}\n";
    $prompt .= "Tagline: {$tagline}\n";
    $prompt .= "Location: {$location}\n";
    $prompt .= "Contact: {$contact_text}\n";
    $prompt .= "About: {$desc_clean}\n\n";
    
    return $prompt;
}

function waai_build_services_prompt($services) {
    if (empty($services)) return "";
    $text = "";
    foreach ($services as $index => $service) {
        $num = $index + 1;
        $name = $service['name'] ?? '';
        $desc = $service['description'] ?? '';
        $url  = $service['url'] ?? '';
        $text .= "{$num}. {$name}: {$desc}" . ($url ? " (Link: {$url})" : "") . "\n";
    }
    return "[SERVICES]\n{$text}\n";
}

function waai_build_products_prompt($products) {
    if (empty($products)) return "";
    $text = "";
    foreach ($products as $index => $prod) {
        $num = $index + 1;
        $name = $prod['name'] ?? '';
        $desc = $prod['description'] ?? '';
        $url  = $prod['url'] ?? '';
        $text .= "{$num}. {$name}: {$desc}" . ($url ? " (Link: {$url})" : "") . "\n";
    }
    return "[SAAS PRODUCTS]\n{$text}\n";
}

function waai_build_gallery_prompt($gallery) {
    if (empty($gallery)) return "";
    $text = "";
    foreach ($gallery as $item) {
        $title = $item['title'] ?? '';
        $desc = $item['description'] ?? '';
        $img  = $item['image'] ?? '';
        $text .= "[CARD title=\"{$title}\" image=\"{$img}\" desc=\"{$desc}\"]\n";
    }
    $prompt  = "[RICH MEDIA GALLERY / CAROUSEL ITEMS]\n";
    $prompt .= "You have access to a visual media gallery. When a user asks to see examples, products, dishes, or portfolio items, you MUST output them exactly using the custom carousel format below.\n";
    $prompt .= "CRITICAL RULES:\n";
    $prompt .= "1. FILTER THE ITEMS: If the user asks for a specific product, ONLY include the card for that exact product. Do NOT show the entire gallery unless the user explicitly asks to see 'all'.\n";
    $prompt .= "2. Do not invent image URLs. Only use the predefined items provided here:\n\n";
    $prompt .= "Available Items:\n" . $text . "\n";
    $prompt .= "To display the selected items to the user, output exactly this syntax (include only the relevant cards):\n";
    $prompt .= "[CAROUSEL]\n";
    $prompt .= "[CARD title=\"Exact Title\" image=\"Exact URL\" desc=\"Exact Description\"]\n";
    $prompt .= "[/CAROUSEL]\n\n";
    return $prompt;
}

function waai_build_rules_prompt($tone_rules) {
    $prompt  = "[TONE & RULES]\n";
    $prompt .= "1. Keep replies brief, engaging, and professional.\n";
    $prompt .= "2. PROACTIVE GUIDANCE: Most users do not know what is available on the website. If a user says 'hello' or asks a vague question, ALWAYS proactively ask if they want to learn about the company's Services, Products, or specific pages (e.g., 'How can I help you today? Would you like to know about our Social Media Management or see our Pricing?').\n";
    $prompt .= "3. Always end with a clear call-to-action (e.g., 'Would you like to book a consultation?').\n";
    $prompt .= "4. If the user asks something completely unrelated to WebAssets or web development/SaaS, politely steer the conversation back to our services.\n";
    $prompt .= "5. Do NOT use overly complex jargon unless the user asks for technical details.\n";
    $prompt .= "6. SECURITY PROTOCOL: You operate in a strict read-only/navigation sandbox. You CANNOT execute SQL queries, delete data, modify configurations, or perform destructive server actions. If a user asks you to DROP TABLE, DELETE, UPDATE, or modify the system, you must REFUSE immediately and state that you are a frontend assistant without backend access.\n";
    
    if ($tone_rules) {
        $prompt .= "7. {$tone_rules}\n";
    }
    return $prompt . "\n";
}

function waai_build_anti_hallucination_prompt() {
    $prompt  = "[ANTI-HALLUCINATION & BUSINESS ACCURACY]\n";
    $prompt .= "You must remain strictly grounded in the configured knowledge base provided to you.\n";
    $prompt .= "- NEVER invent or guess pricing.\n";
    $prompt .= "- NEVER invent or guess product names.\n";
    $prompt .= "- NEVER invent or guess company policies.\n";
    $prompt .= "- NEVER guess service availability.\n";
    $prompt .= "- If a user asks a question and you do not have the exact details in your prompt context, ask for clarification or strictly say: 'I don't have the exact details on that, but our team would be happy to help. Should I schedule a call or take your contact info?'\n\n";
    return $prompt;
}

function waai_build_tool_rules_prompt() {
    $agentic_enabled = function_exists('waai_config') ? waai_config('waai_agentic_enabled', '0') : '0';
    $lead_enabled = function_exists('waai_config') ? waai_config('waai_lead_form_enabled', '1') : '1';
    $calendar_type = function_exists('waai_config') ? waai_config('waai_calendar_type', 'disabled') : 'disabled';
    $calendar_enabled = ($calendar_type !== 'disabled');
    $whatsapp_enabled = function_exists('waai_config') ? waai_config('waai_whatsapp_api_enabled', '1') : '1';
    $email_enabled = function_exists('waai_config') ? waai_config('waai_email_api_enabled', '0') : '0';

    $allowed_actions = [];
    if ($whatsapp_enabled === '1') $allowed_actions[] = 'whatsapp';
    if ($email_enabled === '1') $allowed_actions[] = 'email';
    if ($lead_enabled === '1') $allowed_actions[] = 'lead_form';
    if ($calendar_enabled) $allowed_actions[] = 'calendar';
    $allowed_str = implode(', ', $allowed_actions);

    $prompt  = "[TOOL USAGE RULES]\n";
    if (!empty($allowed_actions)) {
        $prompt .= "When you need to trigger an action (" . $allowed_str . "), you MUST emit a STRUCTURED JSON ACTION BLOCK at the absolute end of your response.\n";
        $prompt .= "CRITICAL FORMAT — always wrap the JSON block with these exact sentinel tokens:\n";
        $prompt .= "  __ACTION__ {\"type\":\"<action_type>\",\"params\":{...}} __END_ACTION__\n";
        $prompt .= "Allowed action types: " . $allowed_str . ".\n";
        if ($calendar_enabled) {
            $prompt .= "- Calendar: __ACTION__ {\"type\":\"calendar\",\"params\":{}} __END_ACTION__\n";
            $prompt .= "  (ONLY trigger Calendar if the user explicitly asks to schedule a call, book a meeting, or see availability.)\n";
        }
        if ($lead_enabled === '1') {
            $prompt .= "- Lead Form: __ACTION__ {\"type\":\"lead_form\",\"params\":{}} __END_ACTION__\n";
            $prompt .= "  (ONLY trigger the Lead Form if the user explicitly asks to book a consultation, get a custom quote, contact sales, or agrees to provide their details. Do NOT trigger proactively just because they asked about services or pricing.)\n";
        }
        $prompt .= "DO NOT embed action blocks in the middle of a sentence. Place them only at the very end.\n";
        $prompt .= "DO NOT emit any bracket-style tags like [SHOW_CALENDAR] or [SHOW_LEAD_FORM] — use the JSON format only.\n\n";
    }

    if ($agentic_enabled === '1') {
        $prompt .= "CRITICAL RULE FOR ALL ACTIONS: When using ANY tool call (like navigate_website, interact_with_element), your text message MUST NOT confirm that the action was successful. The text response is spoken aloud BEFORE the action completes on the user's screen. If you say 'I have taken you there' or 'I opened the page', and the action fails, you will look broken and give fake feedback. Just acknowledge the intent and state you are trying to do it (e.g. 'Let me pull up the AI Automation Service page for you...' or 'Navigating to our products...'). You will receive a SYSTEM INSTRUCTION on the next turn if the navigation succeeded or failed.\n\n";

        $prompt .= "[PAGE VS SECTION NAVIGATION STRATEGY]\n";
        $prompt .= "1. Understand the difference between a PAGE and a SECTION:\n";
        $prompt .= "   - A PAGE is a distinct URL or document (e.g. About Us, Contact Us, Services overview, dynamic blog posts).\n";
        $prompt .= "   - A SECTION or FORM is a block, header, form container, or element within a page (e.g. contact form, footer, portfolio grid, specific product feature list).\n";
        $prompt .= "2. MULTI-STEP CROSS-PAGE NAVIGATION:\n";
        $prompt .= "   - If the user asks you to go to page X and scroll to section/form Y (e.g. 'go to contact page and scroll to contact form' or 'navigate to About and scroll to about section'):\n";
        $prompt .= "     - First, call `navigate_website` with action='redirect' and target_name of that page to redirect the browser there.\n";
        $prompt .= "     - Do NOT call `scroll_to` or scroll actions on the active/current page for that section in the same turn, as it doesn't exist yet on the current page.\n";
        $prompt .= "     - Once the browser loads the new page, you will automatically receive a new Current Page Viewport prompt context on the next turn. Inspect the newly loaded page's context (text, headings, inputs, buttons) and immediately issue a scroll/interaction action (e.g., calling `interact_with_element` with action='scroll_to' and target_text set to the form label or heading name) to position the user at the correct section.\n";
        $prompt .= "3. PATH PLANNING & NESTED CATEGORIES:\n";
        $prompt .= "   - Review target page parent-child routing structures. If a page or product is nested under a parent section (e.g., App Development resides under Services), plan a sequential route: first navigate to the parent page or toggle its parent category, then click/select the child target.\n";
        $prompt .= "   - Note: On mobile viewports or smaller screens, standard nav links are hidden. You must assume links inside navbars are collapsed under the mobile hamburger menu drawer. The system automatically handles mobile triggers, but you should prioritize directing navigations clearly.\n";
        $prompt .= "4. DYNAMIC SCROLLING TARGETS:\n";
        $prompt .= "   - When scrolling to a section or form using `interact_with_element` (action='scroll_to'), set `target_text` to a specific, unique text segment or label found inside or near that block (e.g. 'contact form', 'your name', 'send us a message', 'about section').\n";
        $prompt .= "5. INTELLIGENT PAGE VS SECTION RESOLUTION:\n";
        $prompt .= "   - If the user asks to see 'Pricing', 'Services', or 'Portfolio', check the page text first.\n";
        $prompt .= "   - If the active page already contains the detailed section contents (e.g. detailed pricing plans on the current screen), ALWAYS choose local scrolling (`action='scroll'`) or `scroll_to` to focus the view on the section.\n";
        $prompt .= "   - If the active page does not contain the detailed content (or only contains a small summary hyperlink with no text contents), use `action='redirect'` to navigate the browser to the dedicated page instead.\n\n";

        $prompt .= "[CONTACT FORM SMART ROUTING]\n";
        $prompt .= "CRITICAL: When the user says anything like 'take me to the contact form', 'show me contact', 'I want to contact you', 'contact us', or similar, you MUST apply the following context-aware logic BEFORE deciding what action to take:\n\n";
        $prompt .= "STEP 1 — Identify where the user is (check [CURRENT PAGE VIEWPORT] URL field):\n";
        $prompt .= "  CASE A — User is on the HOMEPAGE (URL is '/', '/home', or the root domain):\n";
        $prompt .= "    → Scroll to the contact section on the homepage using `interact_with_element` with action='scroll_to' and target_text='contact'.\n";
        $prompt .= "    → After scrolling, confirm: 'I've brought you to the contact section on the homepage. Is there anything else I can help you with?'\n";
        $prompt .= "  CASE B — User is already ON the contact page (URL contains '/contact' or the page title contains 'Contact'):\n";
        $prompt .= "    → Scroll directly to the contact form on this page using `interact_with_element` with action='scroll_to' and target_text='contact form'.\n";
        $prompt .= "    → After scrolling, confirm: 'I've scrolled to the contact form. Feel free to fill it out! Need anything else?'\n";
        $prompt .= "  CASE C — User is on ANY OTHER page (not homepage, not contact page):\n";
        $prompt .= "    → You must CLARIFY the user's intent BEFORE taking action. Ask:\n";
        $prompt .= "      'Would you like to go to the contact section on the homepage, or the dedicated contact page form? 😊'\n";
        $prompt .= "    → Wait for the user to respond, then execute the appropriate action (Case A or Case B routing above).\n\n";
        $prompt .= "STEP 2 — After completing any contact navigation action:\n";
        $prompt .= "  - Always CONFIRM the action taken in a friendly, conversational tone.\n";
        $prompt .= "  - Always ASK if the user needs any further help.\n";
        $prompt .= "  - NEVER navigate or scroll without first determining which path is correct (homepage section vs contact page form).\n\n";
    } else {
        $prompt .= "[AGENTIC ACTIONS DISABLED]\n";
        $prompt .= "CRITICAL: Agentic website navigation, scrolling, page redirecting, and page interaction features are currently DISABLED in the settings.\n";
        $prompt .= "If the user asks you to navigate to another page, take them to a page or section, redirect them, scroll the page, or click on an element, you MUST politely refuse and explain that this feature is currently turned off in the settings (so you cannot perform this action right now). You can guide them verbally on where to go, but you cannot redirect or interact with the page for them.\n\n";
    }

    return $prompt;
}

function waai_build_whatsapp_prompt() {
    $prompt  = "[WHATSAPP FORWARDING]\n";
    $prompt .= "You can forward summaries of services, products, pricing, or info directly to the user's WhatsApp. Follow these instructions strictly:\n";
    $prompt .= "1. WHEN TO TRIGGER: ONLY emit the action block when the user explicitly asks you to send, forward, or share something to their WhatsApp. Do NOT trigger proactively for casual conversation.\n";
    $prompt .= "2. STRICT NUMBER ENFORCEMENT:\n";
    $prompt .= "   - Only send to the user's own number. Never to third-party numbers.\n";
    $prompt .= "   - If you already know the number (from User Context), use it. Otherwise ask first.\n";
    $prompt .= "3. STRICT CONTENT RULES:\n";
    $prompt .= "   - ONLY send company-related information (services, pricing, official details). NO personal messages, jokes, or arbitrary text.\n";
    $prompt .= "4. ACTION BLOCK FORMAT (use ONLY this format):\n";
    $prompt .= '   __ACTION__ {"type":"whatsapp","params":{"to":"<digits_only>","message":"<under_150_words_whatsapp_markdown>"}} __END_ACTION__' . "\n";
    $prompt .= "   - `to` must be digits only (e.g. 918899144592).\n";
    $prompt .= "   - `message` must be under 150 words, WhatsApp markdown ('*' for bold, '•' for bullets).\n";
    $prompt .= "5. CHAT RESPONSE: When triggering WhatsApp, your chat reply MUST say something like 'I have prepared the summary, please confirm on screen.' Do NOT say you already sent it. Do NOT repeat the message content in chat.\n";
    $prompt .= "6. RESEND: If the user asks to resend, emit the action block again.\n\n";
    return $prompt;
}

function waai_build_email_prompt() {
    $prompt  = "[EMAIL FORWARDING]\n";
    $prompt .= "You can forward summaries of services, products, pricing, or info directly to the user's Email address. Follow these instructions strictly:\n";
    $prompt .= "1. WHEN TO TRIGGER: ONLY emit the action block when the user explicitly asks you to email something to them. Do NOT trigger proactively.\n";
    $prompt .= "2. STRICT EMAIL ENFORCEMENT:\n";
    $prompt .= "   - Only send to the user's own email. Never to third-party addresses.\n";
    $prompt .= "   - If you already know the email (from User Context), use it. Otherwise ask first.\n";
    $prompt .= "3. STRICT CONTENT RULES:\n";
    $prompt .= "   - ONLY send company-related information. NO personal messages or arbitrary text.\n";
    $prompt .= "4. ACTION BLOCK FORMAT (use ONLY this format):\n";
    $prompt .= '   __ACTION__ {"type":"email","params":{"to":"<email>","subject":"<short subject>","message":"<html under 150 words>"}} __END_ACTION__' . "\n";
    $prompt .= "   - `message` should use simple HTML (<b>, <ul><li>, <br>).\n";
    $prompt .= "5. CHAT RESPONSE: When triggering email, your chat reply MUST say the email is ready to confirm. Do NOT say you already sent it. Do NOT repeat the content in chat.\n\n";
    return $prompt;
}


function waai_build_context_prompt($user_phone, $user_email, $last_action = null) {
    $prompt = "";
    if ($user_phone || $user_email || $last_action) {
        $prompt .= "[USER CONTEXT & MEMORY]\n";
        if ($user_phone) {
            $prompt .= "The user's registered WhatsApp/Phone number is: {$user_phone}\n";
            $prompt .= "You MUST strictly use this number for forwarding information via WhatsApp.\n";
        }
        if ($user_email) {
            $prompt .= "The user's registered Email address is: {$user_email}\n";
            $prompt .= "You MUST strictly use this email for forwarding information via Email.\n";
        }
        if ($last_action) {
            $action_json = json_encode($last_action);
            $prompt .= "CONVERSATIONAL MEMORY - The last action you executed was: {$action_json}\n";
            $prompt .= "Use this to maintain context. If the user asks 'open the form', and you just navigated to a product page, assume they mean the form on that page.\n";
        }
        $prompt .= "\n";
    }
    return $prompt;
}



function waai_build_examples_prompt($examples) {
    if (empty($examples)) return "";
    $text = "";
    $count = 0;
    foreach ($examples as $qa) {
        if ($count >= 5) break; // Cap at 5 examples to prevent token bloat
        $q = substr(trim($qa['q'] ?? ''), 0, 150);
        $a = substr(trim($qa['a'] ?? ''), 0, 150);
        if (!$q || !$a) continue;
        $text .= "User: {$q}\nAssistant: {$a}\n\n";
        $count++;
    }
    if (!$text) return "";
    return "[EXAMPLE CONVERSATIONS]\nUse these as a guide for tone and style:\n\n{$text}";
}

function waai_build_language_prompt($lang_code) {
    $prompt = "[LANGUAGE]\n";
    $prompt .= "The user's interface is configured for language code: {$lang_code}. Please reply in this language if appropriate, unless the user speaks to you in a different language first.\n\n";
    return $prompt;
}

function waai_build_page_context_prompt($page_context) {
    if (empty($page_context) || !is_array($page_context)) return "";
    
    $url   = $page_context['url'] ?? '/';
    $title = $page_context['title'] ?? 'Active Page';
    
    $prompt  = "[CURRENT PAGE VIEWPORT]\n";
    $prompt .= "The user is currently browsing the page: \"{$title}\" (URL: {$url}).\n";
    
    $page_content = $page_context['page_content'] ?? '';
    if (!empty($page_content)) {
        $sanitized_content = strip_tags($page_content);
        $prompt .= "Visible page text content (for context, summarization, and answering questions about this page):\n";
        $prompt .= "\"\"\"\n" . $sanitized_content . "\n\"\"\"\n\n";
    }
    
    $interactables = $page_context['interactables'] ?? [];
    if (!empty($interactables) && is_array($interactables)) {
        $agentic_enabled = function_exists('waai_config') ? waai_config('waai_agentic_enabled', '0') : '0';
        if ($agentic_enabled === '1') {
            $prompt .= "Visible interactable elements on this page (if the user asks to click, fill, or open one of these, USE `interact_with_element` and PASS its corresponding element_id):\n";
        } else {
            $prompt .= "Visible interactable elements on this page (since browser interaction is currently disabled, you cannot click or interact with these automatically. If the user asks, explain that the feature is disabled, but guide them where the element is on the page):\n";
        }
        foreach ($interactables as $item) {
            $id = $item['waai_id'] ?? '';
            $type = $item['type'] ?? 'element';
            $text = $item['text'] ?? '';
            $href = $item['href'] ?? '';
            
            $prompt .= "- Element [ID {$id}]: type='{$type}' | text='{$text}'" . ($href ? " | leads to='{$href}'" : "") . "\n";
        }
    } else {
        $prompt .= "There are no direct form inputs or custom links detected in the viewport.\n";
    }
    $prompt .= "\n";
    return $prompt;
}

function waai_build_global_sitemap_prompt($page_context) {
    if (empty($page_context) || !isset($page_context['global_sitemap']) || empty($page_context['global_sitemap'])) return "";
    
    $sitemap = $page_context['global_sitemap'];
    if (!is_array($sitemap)) return "";

    $agentic_enabled = function_exists('waai_config') ? waai_config('waai_agentic_enabled', '0') : '0';

    $prompt  = "[GLOBAL WEBSITE DIRECTORY]\n";
    if ($agentic_enabled === '1') {
        $prompt .= "This is the complete, optimized sitemap of the website. If the user asks to navigate to a specific page or section that is not on the current screen, USE `navigate_website` and PASS the raw URL as the `target_name`.\n";
    } else {
        $prompt .= "This is the complete, optimized sitemap of the website. Since browser navigation is currently disabled, you cannot navigate the user to these pages automatically. If the user asks to navigate, explain that the feature is disabled, but you can share the link/URL verbally so they can click/visit it themselves.\n";
    }
    
    foreach ($sitemap as $item) {
        $title = $item['title'] ?? $item['name'] ?? '';
        $url   = $item['url'] ?? '';
        if ($title && $url) {
            $prompt .= "- {$title} -> {$url}\n";
        }
    }
    
    $prompt .= "\n";
    return $prompt;
}
