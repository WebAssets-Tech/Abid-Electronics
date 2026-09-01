# WebAssets AI Assistant — Comprehensive Technical Documentation

Welcome to the comprehensive technical documentation for the **WebAssets AI Assistant** (WAAI). This document covers the architecture, backend logic, frontend modules, WP integration, security policies, background processing, speech engines, and lead generation subsystems that make up this premium conversational AI chatbot widget.

---

## Table of Contents
1. [System Architecture & Core Philosophy](#1-system-architecture--core-philosophy)
2. [Dual-Mode Deployment Model](#2-dual-mode-deployment-model)
3. [AI Engine & Multi-Provider Config](#3-ai-engine--multi-provider-config)
4. [Prompt Builder & Dynamic Context System](#4-prompt-builder--dynamic-context-system)
5. [Agentic Website Controls & UI Sandbox](#5-agentic-website-controls--ui-sandbox)
6. [Post-Load Background Processing & Multi-Job Caching](#6-post-load-background-processing--multi-job-caching)
7. [Voice & Speech Integration (STT & TTS)](#7-voice--speech-integration-stt--tts)
8. [CRM Lead Capture & Form Overlay System](#8-crm-lead-capture--form-overlay-system)
9. [Security, Safety, & Rate Limiting Engine](#9-security-safety--rate-limiting-engine)
10. [Admin Settings Panel tabs Map](#10-admin-settings-panel-tabs-map)
11. [Auditing, System Logging, & Maintenance](#11-auditing-system-logging--maintenance)

---

## 1. System Architecture & Core Philosophy

The WebAssets AI Assistant is designed as a modular, decoupled, and secure system that brings agentic capabilities directly into any website. Rather than relying on rigid script loaders or heavy frameworks, WAAI is built using clean **Vanilla PHP** on the backend and **ES Modules (JavaScript)** on the frontend. 

The frontend elements are encapsulated inside a **Shadow DOM Web Component** (`<ai-assistant-widget>`). This guarantees complete stylesheet isolation. Standard page CSS rules, theme overrides, or reset styles cannot leak into the chat panel, preserving the premium glassmorphism layouts, rounded borders, and custom visual cues on any page.

### Decoupled Communication via EventBus
To eliminate tight coupling and race conditions, the frontend features a custom pub/sub system defined in `EventBus.js`. Instead of modules invoking each other's functions directly (which can cause cascading loops), components register handlers and emit events across clean namespaces:
- `api:*` – Controls API request lifecycle (sending query, receiving response, handling errors).
- `voice:*` – Manages Speech-to-Text (STT) and Text-to-Speech (TTS) callbacks (listening, speaking, interrupted, volume changes).
- `state:*` – Tracks internal state machine changes (visualizing state loops).
- `action:*` – Triggers external API intents (WhatsApp, Email, Calendars).
- `ui:*` – Updates panel display, overlay transitions, and visual updates.
- `form:*` – Manages form field inputs, inputs highlighting, and prefill validation.

---

## 2. Dual-Mode Deployment Model

A key feature of the assistant is its **Dual-Mode Adapter Architecture**. While it is deeply integrated into WordPress, it can be deployed on a standalone PHP or static website without any refactoring.

### A. WordPress Integrated Mode
In WordPress, WAAI operates as an enqueued theme module.
- **Bootstrapping**: Loaded via `wordpress-integration.php` which hooks into the `wp_enqueue_scripts` and `wp_footer` hooks.
- **Settings Store**: All settings are saved in the WordPress `wp_options` table using the standard Settings API with custom serialization and sanitization callbacks.
- **Data Model**: Captures leads in a custom SQL table (`wp_waai_leads`) and system logs in `wp_waai_logs` via the global `$wpdb` object.
- **AJAX Endpoints**: Admin actions (like detecting custom post types or scanning site links) are routed through WordPress `admin-ajax.php`.

### B. Standalone PHP Mode
When WordPress is absent, the assistant runs a bootstrapping adapter defined in `ai-proxy.php` and `standalone-implementation-plan.md`.
- **Environment Detection**: Checks for the existence of `ABSPATH` or core WordPress functions (like `get_option()`). If missing, it immediately switches to Standalone Mode.
- **Configuration Fallback**: Parses key values directly from a PHP file (`standalone-config.php`) containing an array structure mirroring the WordPress settings.
- **Database Abstraction (`WaaiDB`)**: Replaces direct `$wpdb` calls with a PDO connection wrapper. It defaults to a local zero-configuration SQLite file (e.g., `waai-database.sqlite`) or connects to an external MySQL server defined in the standalone config.
- **Static Sitemap Indexing**: When WordPress query engines are unavailable, site searches query a static sitemap file (`sitemap.xml` or `standalone-index.json`) placed in the root directory.
- **Standalone Embed Loader**: Employs a lightweight script (`embed.js`) that injects the shadow-root widget tag and fetches configurations via standard fetch parameters.

---

## 3. AI Engine & Multi-Provider Config

The assistant supports multiple LLM providers, allowing you to balance performance, cost, and context limits:
1. **Groq**: Free, ultra-low latency, optimized for Llama 3 models.
2. **OpenRouter**: Access to a broad selection of open models (Gemini Flash, Mistral, Claude).
3. **OpenAI**: Core commercial GPT engines (GPT-4o-mini, GPT-4o).
4. **Google Gemini**: Direct integration with Gemini 1.5 engines.
5. **Anthropic Claude**: Support for Claude 3.5 Sonnet and Opus models.
6. **Custom Endpoint**: Allows connecting to any OpenAI-compatible API (e.g., DeepSeek, Cerebras).

### Dynamic Model Provider Memory
To prevent configuration loss, the WordPress admin settings panel implements a hidden-state sync mechanism. When a administrator switches between AI Providers, the system automatically caches the API key and chosen model name in hidden fields. Saving the form preserves settings for all providers, letting you toggle between models without re-entering keys.

### Smart Long-Context Fallback Routing
Fast models like Llama 3.1 8B on Groq are highly effective for rapid dialogue, but have strict rate limits and small context capacities. When a user visits an extremely long page (such as a detailed portfolio or case study > 5,000 characters), the assistant automatically redirects the request to a secondary **Long-Context Fallback Provider** (e.g., Gemini 1.5 Flash on OpenRouter or Google AI Studio). This prevents token overflow errors while keeping fast interactions on normal pages.

---

## 4. Prompt Builder & Dynamic Context System

WAAI constructs system prompts on the fly during each request. Rather than relying on a static text block, `includes/prompt-builder.php` compiles multiple knowledge blocks, user context variables, and active page parameters.

```
+-------------------------------------------------------------+
|                     SYSTEM PROMPT BUILDER                   |
+-------------------------------------------------------------+
| 1. ROLE IDENTITY                                            |
|    - Name, Location, Description (Cleaned & capped < 800ch) |
+-------------------------------------------------------------+
| 2. SERVICES CATALOG (Up to 8 services with URLs)            |
+-------------------------------------------------------------+
| 3. PRODUCT CATALOG (Up to 5 SaaS/Physical items with URLs)  |
+-------------------------------------------------------------+
| 4. MEDIA CAROUSEL (Images, Title, descriptions)             |
+-------------------------------------------------------------+
| 5. TONE, SECURITY PROTOCOLS, & ANTI-HALLUCINATION RULES     |
+-------------------------------------------------------------+
| 6. USER MEMORY (Saved phone, email, last executed action)   |
+-------------------------------------------------------------+
| 7. VIEWPORT DATA (Current Page Title, URL, Scraped Markdown)|
+-------------------------------------------------------------+
| 8. ACTIVE INTERACTABLES (Links, Inputs, Buttons with IDs)   |
+-------------------------------------------------------------+
| 9. GLOBAL SITEMAP INDEX (WordPress published post URLs)     |
+-------------------------------------------------------------+
```

### Knowledge Extraction Limits
To prevent token bloat (which slows down response times and increases API costs), the prompt builder applies strict limits:
- **About Section**: Stripped of HTML/Markdown markup and truncated to 800 characters.
- **Few-Shot Examples**: Restricted to a maximum of 5 Q&A pairs (truncated to 150 characters each).
- **Page Content Scraper**: Limited to 5,000 characters on standard pages and 40,000 characters on custom post pages (such as blog posts and portfolios).

### Strict Anti-Hallucination Guardrails
The system prompt contains strict instructions that govern the LLM's business boundaries:
- **No Pricing Invention**: The AI is prohibited from inventing or guessing pricing.
- **Product Safety**: Must not declare features or services not defined in the knowledge base.
- **Refusal Protocol**: If requested data is missing, the AI must respond: *"I don't have the exact details on that, but our team would be happy to help. Should I schedule a call or take your contact info?"*
- **Security Sandboxing**: Enforces read-only permissions. If a user tries injection commands (e.g., `"DROP TABLE"` or `"DELETE"`), the AI must immediately refuse, stating it operates inside a frontend sandbox without database write access.

---

## 5. Agentic Website Controls & UI Sandbox

The **Agentic Control Engine** (`modules/AgenticActions.js` and `ai-agent-tools.php`) allows the AI to interact directly with the webpage. When agentic modes are enabled, the LLM receives schema definitions for specific tools.

### Available Tools:
1. `navigate_website`: Takes the user to another page or scrolls to a specific section on the current page.
2. `interact_with_element`: Executes clicks, fills form inputs, or scrolls elements.
3. `search_site_directory`: Searches the database for pages, posts, or projects matching a keyword.
4. `scroll_page`: Scrolls the page in a direction (`up`, `down`, `top`, `bottom`) by a pixel amount.
5. `open_assistant_overlay`: Launches the built-in lead form or calendar scheduling overlay.

### Smart Element Resolution & UX Safety
Executing actions in a web browser requires robust error handling to prevent broken interfaces:
- **Visual Highlight Cues**: Before clicking or filling an element, the widget scrolls it smoothly into view and applies a temporary outline (`outline: 3px solid #5f39ff; box-shadow: 0 0 15px rgba(95,57,255,0.5)`) for 2 seconds to show the user what is happening.
- **Mobile Menu Awareness**: If the target element is hidden inside a mobile navigation header (screen width < 768px), the click runner automatically searches for hamburger buttons (checking classnames like `.menu-toggle`, `.hamburger`, `[aria-label="Menu"]`), clicks to open the mobile drawer, waits 350ms for the animation, and then clicks the link.
- **Tab Panel & Accordion Expansion**: If an element is located inside a hidden tab panel, details card, or collapsed panel, the engine automatically triggers the parent button's click event to open the container before scrolling to the element.
- **Form Submission Verification**: When the AI fills a select dropdown or input field, it dispatches native `input` and `change` events. This ensures that modern JS frameworks (React, Vue, Angular) register the value changes correctly.
- **Action Confirmation Delay**: Because text-to-speech outputs are spoken aloud *before* page redirects occur, the AI must not state *"I have redirected you"* in its reply. Instead, it must state *"Navigating to..."* to avoid confusing the user if an action is delayed.

---

## 6. Post-Load Background Processing & Multi-Job Caching

To keep the main chat interface responsive and conserve LLM tokens, WAAI features a **Post-Load Background worker** (`modules/API.js` and `ai-proxy.php`). Exactly 2 seconds after a page loads, the widget initiates three parallel background operations.

```
                  +-----------------------------------+
                  |        Page Load Complete         |
                  +-----------------------------------+
                                    |
                                    v (2 Seconds Delay)
            +-----------------------+-----------------------+
            |                       |                       |
            v                       v                       v
  +------------------+    +-------------------+    +-------------------+
  |    JOB 1: DOM    |    |   JOB 2: PAGE     |    |   JOB 3: GLOBAL   |
  |   COMPRESSION    |    |     SUMMARY       |    |   SITEMAP SYNC    |
  +------------------+    +-------------------+    +-------------------+
            |                       |                       |
            v                       v                       v
  Extract interactables   Scrape page text        Query WP post types 
  Send to Secondary LLM   Send to Secondary LLM   Send to Secondary LLM
  Cache JSON Map in LS    Cache Summary in LS     Cache Sitemap in LS
  (waai_dom_map_)         (waai_page_summary_)    (waai_global_sitemap)
```

### Job 1: DOM Interactables Compressor
- **Process**: Gathers all link anchors (`<a>`), buttons, inputs, textareas, and select elements on the active page. 
- **Action**: Sends this list to a secondary LLM which filters out noise (like double headers or tracking parameters) and returns a clean, structured JSON list of interactive page links.
- **Storage**: Saved in LocalStorage as `waai_dom_map_[pathname]`. Subsequent user questions pull this cached map directly, saving thousands of prompt tokens.

### Job 2: Page Text Summarizer
- **Process**: Clones the active page content element (using `<main>`, `<article>`, or fallback selectors), strips scripts, styles, sidebars, header/footer elements, and converts the clean content to Markdown.
- **Action**: If the content is longer than 200 characters, it sends it to the secondary LLM for summarization.
- **Storage**: Caches a concise summary in LocalStorage as `waai_page_summary_[pathname]`. This summary is injected into subsequent prompts, allowing the AI to answer questions about long pages without reading the full text again.

### Job 3: Global Sitemap Builder
- **Process**: Queries WordPress for published posts (respecting the custom post type settings), and gathers active products and service pages.
- **Action**: Consolidates the sitemap list and compresses it via the LLM.
- **Storage**: Saved in LocalStorage as `waai_global_sitemap_map`. This provides the AI with a directory of the entire site on every page, enabling seamless redirect navigation.

---

## 7. Voice & Speech Integration (STT & TTS)

WAAI includes support for both text and voice-based conversations, offering an immersive voice mode.

### A. Speech-to-Text (STT) Engines
- **Browser Web Speech API**: Uses the browser's built-in recognition engine. Free and fast, but accuracy varies by browser.
- **Groq Whisper API**: Premium cloud-based voice transcription. Provides highly accurate speech recognition.

### B. Text-to-Speech (TTS) Engines
- **Browser Native Speech**: Free, runs locally, but can sound robotic depending on the user's operating system.
- **Groq Orpheus API**: Fast cloud-based TTS. English voice models support emotional directions (like `[cheerful]` or `[whisper]`).
- **ElevenLabs API**: High-quality, realistic voice synthesis.
- **Sarvam AI API**: Tailored for Indian locales, utilizing the `bulbul:v3` model for Hindi and Indian-accented English.
- **Piper TTS (Self-Hosted Local API)**: A free, high-quality, self-hosted TTS server. By running Piper via Docker on a VPS, the backend handles requests on port `5000` and streams WAV audio to the frontend, eliminating cloud costs.

### C. Advanced Audio Engineering
To ensure a smooth voice experience, the assistant implements several audio handling mechanisms:
- **`TTSQueue` (Watchdog Synthesizer)**: Standard browsers often freeze or drop audio utterances if multiple speech requests are sent close together. The `TTSQueue` module chunks text at sentence boundaries (max 180 characters) and plays them sequentially. A **2500ms Watchdog Timer** monitors playback; if the browser's audio engine stalls, the watchdog resets it and skips to the next chunk.
- **Continuous Voice Mode (Full Duplex)**: Keeps the microphone active while the AI speaks. This allows the user to naturally interrupt the AI. If the AI is interrupted, it instantly stops audio output, logs the interruption, and processes the user's new input.
- **Walkie-Talkie Mode fallback**: For languages where microphone noise can cause transcription errors (hallucinations), this mode disables the mic while the AI is speaking.
- **Phonetics Filters**: Before text is sent to the TTS engines, the pronunciation engine sanitizes the text:
  - Replaces abbreviations with spoken words (e.g., `"UI/UX"` $\rightarrow$ `"U I, U X"`, `"SaaS"` $\rightarrow$ `"Sass"`, `"FB"` $\rightarrow$ `"F B"`).
  - Normalizes currencies (e.g., `"₹"` $\rightarrow$ `"rupees"`).
  - Formats digit sequences (like phone numbers) with spaces (e.g., `"9198..."` $\rightarrow$ `"9 1 9 8..."`) so they are read digit-by-digit rather than as large numbers.

---

## 8. CRM Lead Capture & Form Overlay System

WAAI features a lead collection subsystem that captures visitor information and routes it to your database, email, or third-party CRMs.

```
                      +-----------------------------+
                      |     AI COLLECTS INQUIRY     |
                      +-----------------------------+
                                     |
                                     v
                       Prefills fields in real-time
                       - Name, Email, Phone, Inquiry
                                     |
                                     v
                       Are Email & Phone valid?
                                     |
                    +----------------+----------------+
                    | Yes                             | No
                    v                                 v
         Show countdown overlay             Wait for user to edit
         Auto-submits in 1.2s               manually in overlay
                    |                                 |
                    +----------------+----------------+
                                     |
                                     v
                       +---------------------------+
                       |      SUBMIT LEAD NOW      |
                       +---------------------------+
                                     |
            +------------------------+------------------------+
            |                        |                        |
            v                        v                        v
  +------------------+    +-------------------+    +-------------------+
  |  DESTINATION 1:  |    |  DESTINATION 2:   |    |  DESTINATION 3:   |
  |  WP DATABASE     |    |  ADMIN EMAIL      |    |  GOOGLE SHEETS    |
  +------------------+    +-------------------+    +-------------------+
  Saves to prefix_        Sends copy to       Sends payload data
  waai_leads table        configured email    to Apps Script URL
```

### Lead Capture Destinations
When a lead is submitted, WAAI can send the data to three places simultaneously:
1. **WordPress Database**: Saved to the custom `wp_waai_leads` table, accessible via the WordPress dashboard (with CSV export capability).
2. **Notification Email**: Dispatches a structured summary to the site administrator. WAAI supports native `wp_mail` or custom **SMTP configurations** (Host, Port, TLS/SSL, Username, Password) to bypass host restrictions.
3. **Google Sheets Webhook**: Sends a JSON POST payload to a Google Apps Script web app, appending a row containing name, email, phone, query, and submission page URL.

### Interactive Form Prefilling & Auto-Submit
When the AI detects that a user is sharing contact details in chat, it opens the lead form overlay.
- **Dynamic Prefill**: If the user states their email or phone verbally, WAAI parses the text and prefills the form fields. The updated field flashes with a temporary blue background (`#e0f7fa`) for 1.5 seconds to confirm the update.
- **Auto-Submission Countdown**: Once both email and phone fields are filled with valid values (verified by regex), WAAI initiates a **1.2-second countdown** and submits the form automatically. The AI announces: *"I have received your details. Submitting them to our team now..."* to confirm the submission.

---

## 9. Security, Safety, & Rate Limiting Engine

Operating an AI chatbot on public websites introduces risks, including spam, API cost inflation, and prompt injection. WAAI implements a multi-layered security engine to mitigate these risks.

### Layer 1: CSRF & Request Verification
- All AJAX requests from the widget must include a security token (`X-WAAI-Nonce` header).
- In WordPress, this is verified using `wp_verify_nonce`.
- Custom headers (`X-WAAI-Session-ID` and `X-WAAI-Trace-ID`) track requests and correlate frontend interactions with backend logs.

### Layer 2: API Action Whitelisting
To prevent hackers from abusing the proxy script to trigger arbitrary events, `ai-proxy.php` validates requests against a strict whitelist:
`['chat', 'lead', 'whatsapp', 'email', 'tts', 'sarvam_tts', 'waai_process_dom_background', 'waai_process_sitemap_background', 'waai_process_page_content_background']`
Any unlisted actions are rejected with a 403 response.

### Layer 3: Multi-Tiered Rate Limiting
WAAI applies hourly request limits per IP address to control server load:
- **Chat Queries**: Default limit of 50 requests per hour.
- **Lead Submissions**: Limit of 10 requests per hour.
- **WhatsApp/Email Forwarding**: Limit of 5 requests per hour.
- **Audio TTS Generation**: Limit of 500 requests per hour.
- *In WordPress, rate limits are managed via transient caches. In standalone mode, they fall back to secure PHP session tracking.*

### Layer 4: Content Safety & Validation
All incoming user input and outgoing AI payloads pass through a validation filter (`Validators.js` and `ai-proxy.php`):
- **Character Limits**: Chat inputs are capped at 1,000 characters. WhatsApp payloads are capped at 1,000 characters, and emails are capped at 2,000 characters.
- **XSS & Script Injection Filter**: Blocks requests containing script injection keywords (e.g., `<script`, `javascript:`, `onerror=`, `alert(`, `document.cookie`).
- **Spam & Competitor Protection**: Outgoing message forwards are scanned against forbidden keywords (such as competitor terms, lottery, free money) to prevent the AI from being used for spam.

---

## 10. Admin Settings Panel Tabs Map

The WAAI admin panel (`ai-settings.php`) is organized into six tabs, allowing you to configure the assistant without modifying code.

### Tab 1: AI Config
- **AI Provider**: Choose between Groq, OpenRouter, OpenAI, Gemini, Anthropic, or a Custom Endpoint.
- **Model Name**: The specific LLM model identifier.
- **API Key**: Secure password field for the provider API key.
- **Max Tokens & Temperature**: Control response length and creativity.
- **Long-Context Fallback**: Toggle fallback routing for long content, including fallback provider, model name, and API key.
- **Chat Messages**: Customize the welcome message and voice greeting.

### Tab 2: Knowledge & Rules
- **Company Profile**: Business name, tagline, location, phone, email, website, and description.
- **Services**: Repeater rows (up to 8) to define service titles, descriptions, and page links.
- **Key Products**: Repeater rows (up to 5) to list products and their URLs.
- **AI Media Gallery**: Swipable carousel builder where you can add slide titles, descriptions, and image URLs.
- **AI Tone & Rules**: Custom instructions (e.g., *"Keep answers under two paragraphs"*).
- **Loading Tips & Thinking Steps**: Custom text shown to users while the AI generates responses.
- **Training Examples**: 5 Q&A pairs used to guide the AI's response style.

### Tab 3: Agentic
- **Allowed Actions**: Toggle permissions for scrolling, clicks, navigation, and page reading.
- **Website Sections Mapping**: Map CSS selectors (like `#pricing`) or URLs to section names, enabling the AI to scroll or redirect to them.
- **Homepage Auto-Mapper**: Automatically scans your home page HTML and maps all container IDs.
- **Sitemap Link Auto-Mapper**: Automatically imports sitemap URLs.
- **Sitemap Post Type Filter**: Checkboxes to select which WordPress post types contribute to the sitemap, preventing clutter in the AI's sitemap context.

### Tab 4: Integrations & Leads
- **Lead Capture Settings**: Toggle the lead form, define the notification email, toggle WordPress DB logging, and set the Google Sheets webhook URL.
- **Booking Calendar**: Select Calendly, Google Calendar, or a Custom Booking URL.
- **WhatsApp Send API**: Toggle message forwarding and configure WhatsApp App and Auth Keys.
- **Email Forwarding**: Toggle email sending and configure the delivery method (default `wp_mail` or custom SMTP settings).

### Tab 5: Widget UI
- **Widget Appearance**: Toggle the widget on/off, customize the widget title, subtitle, accent color, and avatar initials.
- **Quick Suggestions**: Configure the three suggestion chips shown when the chat is opened.
- **Voice Settings**: Select the TTS engine (Web Speech, Piper, Sarvam, ElevenLabs, Groq Orpheus) and toggle voice interruptions.
- **Proactive Trigger**: Set a delay (in seconds) before the chat bubble opens automatically.

### Tab 6: Security
- **Rate Limiting**: Toggle rate limits and configure request caps for chat, leads, WhatsApp/email forwarding, and audio synthesis.
- **Maximum Characters**: Set the character cap for user inputs.

---

## 11. Auditing, System Logging, & Maintenance

To help you monitor system health and debug issues, WAAI includes a logging system (`ai-logs.php` and `includes/ai-logger.php`).

### The Logging Database Table
WAAI logs events to a custom table:
- `id`: Auto-incrementing primary key.
- `created_at`: Datetime stamp of the event.
- `level`: Log level (`INFO`, `WARNING`, `ERROR`, `DEBUG`).
- `event`: A short label (e.g., `"API Key Verification"`, `"Rate Limit Exceeded"`).
- `context`: JSON-serialized details of the event.
- `session_id` & `trace_id`: Correlate frontend actions with backend logs.

### Daily Cleanup Maintenance
To prevent logs from consuming excessive database space, WAAI hooks into the WordPress daily cleanup cron (`wp_scheduled_delete`). A daily background task runs:
```sql
DELETE FROM prefix_waai_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
```
This automatically deletes logs older than 30 days.

### Admin Logs Viewer
Navigate to **AI Assistant $\rightarrow$ System Logs** in the WordPress admin to view logs. The interface displays log levels in distinct colors (Red for error, yellow for warning, blue for info) and provides a **"Clear All Logs"** button to clear the logs table manually.

---
*Documentation Version: 3.1.0 — End of File.*
