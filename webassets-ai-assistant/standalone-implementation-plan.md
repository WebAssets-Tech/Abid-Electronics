# Dual-Mode Architecture: WebAssets AI Assistant (WordPress + Standalone)

This document outlines a plan to upgrade the `webassets-ai-assistant` into a **Dual-Mode** system. Rather than stripping out WordPress functionality, we will add graceful fallbacks on top of the existing codebase. 
When deployed in WordPress, it will continue using the WP Admin UI, `$wpdb`, and `get_posts()`. When integrated into a vanilla PHP or custom CMS site, it will seamlessly fall back to a standalone configuration file, PDO SQLite/MySQL, and static indexing.

## User Review Required

> [!TIP]
> This approach minimizes risk and avoids a major refactor. The core logic remains identical; we simply wrap environment-specific operations (like fetching config or saving to the DB) in conditional checks. Please review the strategy below.

## Proposed Architecture Shifts

### 1. Robust Environment Detection
- **Strategy:** Create a central bootstrapper that checks if `ABSPATH` or core WP functions (like `get_option`) exist. 
- **Execution:** All internal logic will run a quick check. If WP is active, use the WP API. If not, trigger the Standalone Adapter.

### 2. Standalone Configuration Adapter (`waai_config`)
- **Current State:** `waai_config()` uses `get_option()` and falls back to a hardcoded array in `ai-proxy.php`.
- **Strategy:** Move the standalone fallback into a dedicated `standalone-config.php` file.
- **Execution:** Non-WP users will copy a `standalone-config.sample.php`, rename it, and populate their API keys, prompt settings, and email addresses. `waai_config()` will parse this file dynamically.

### 3. Dual-Mode Database Abstraction (Logs & Leads)
- **Current State:** Direct use of global `$wpdb` in `ai-logger.php` and `ai-leads.php`.
- **Strategy:** Create a `WaaiDB` adapter class. 
- **Execution:** 
  - If WP is present, `WaaiDB` routes queries to `$wpdb`.
  - If WP is absent, `WaaiDB` initializes a standard PHP PDO connection (defaulting to a local SQLite file for zero-configuration, or MySQL if specified in the standalone config). 
  - We will provide a simple `standalone-setup.php` script for non-WP users to initialize the SQLite database tables.

### 4. Dual-Mode Site Indexing & Navigation
- **Current State:** `waai_wp_search_site()` relies on `get_posts()`.
- **Strategy:** Implement a Sitemap/JSON fallback.
- **Execution:** If `get_posts()` is unavailable, the search function will read a `sitemap.xml` (or a `site-index.json` file) from the host site's root directory. It will parse the URLs and titles into an array and perform a local text search to power the Agentic Navigation.

### 5. Dual-Mode Mail Delivery
- **Current State:** Uses `wp_mail()`.
- **Strategy:** Native PHP mail fallback.
- **Execution:** If `wp_mail()` doesn't exist, we will use native PHP `mail()` configured via SMTP settings defined in the `standalone-config.php`, or bundle a lightweight PHPMailer script.

### 6. Universal Frontend Loader
- **Current State:** `wordpress-integration.php` injects the widget via `wp_footer`.
- **Strategy:** Provide a standalone JS loader.
- **Execution:** Create a lightweight `embed.js` script that vanilla PHP sites can include in their footer: `<script src="/path/to/embed.js"></script>`. It will automatically fetch settings from the standalone API and render the Web Component.

## Implementation Steps

---

### Step 1: Centralize Configuration
#### [MODIFY] [ai-proxy.php](file:///c:/xampp/htdocs/webassets.tech/wp-content/themes/WebAssets/webassets-ai-assistant/ai-proxy.php)
- Update `waai_config()` to load from `standalone-config.php` if `get_option` is missing.
#### [NEW] `standalone-config.sample.php`
- Create a documented array of all settings that normally live in the WP Admin.

### Step 2: Database Adapter
#### [NEW] `includes/ai-db-adapter.php`
- Build the `WaaiDB` class that wraps `$wpdb` or PDO SQLite/MySQL.
#### [MODIFY] `includes/ai-logger.php` & `ai-leads.php`
- Replace raw `$wpdb` calls with `WaaiDB::query()` and `WaaiDB::insert()`.

### Step 3: Navigation Fallback
#### [MODIFY] [ai-proxy.php](file:///c:/xampp/htdocs/webassets.tech/wp-content/themes/WebAssets/webassets-ai-assistant/ai-proxy.php)
- Update `waai_wp_search_site()` to parse `sitemap.xml` or `standalone-index.json` if `get_posts()` is not defined.

### Step 4: Standalone Embed
#### [NEW] `embed.js`
- Write a universal injection script that creates the `<webassets-ai-assistant>` DOM element and loads its assets, mimicking what the WordPress `wp_footer` hook does.
