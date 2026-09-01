export const APIMixin = {
    fetchAIResponse(query) {
        if (!query || (this.isEmptyString && this.isEmptyString(query))) return;
        
        if (this.containsBlockedWords && this.containsBlockedWords(query)) {
            this.waWarn(`[API Layer] Query failed safety validation.`);
            this.addMessage(`⚠️ Your message contains restricted content and cannot be processed.`, 'assistant');
            if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            } else {
                this.transitionTo('idle');
            }
            return;
        }

        if (this.isWithinMaxLength && !this.isWithinMaxLength(query, 1000)) {
            this.waWarn(`[API Layer] Query exceeds maximum length.`);
            this.addMessage(`⚠️ Your message is too long to process. Please keep it under 1000 characters.`, 'assistant');
            if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            } else {
                this.transitionTo('idle');
            }
            return;
        }

        this.addWritingIndicator();
        this.transitionTo('processing');

        // Generate a fresh Trace ID for this request turn
        const traceId = this.newTraceId ? this.newTraceId() : '';

        // Prepare request body for standalone vs WordPress
        let body = {};
        if (this.isWordPressMode) {
            body = { message: query, history: this.chatHistory.slice(0, -1) };
        } else {
            body = { action: 'chat', message: query, history: this.chatHistory.slice(0, -1) };
        }
        if (this.capturedPhoneNumber) {
            body.user_phone = this.capturedPhoneNumber;
        }
        if (this.capturedUserEmail) {
            body.user_email = this.capturedUserEmail;
        }

        // Scan and inject active DOM page context
        const pageContext = this.getPageContext ? this.getPageContext() : null;
        if (pageContext) {
            body.page_context = pageContext;
        }

        // Inject Conversational Memory (Phase 3)
        const lastAction = sessionStorage.getItem('waai_last_action');
        if (lastAction) {
            try {
                body.last_action = JSON.parse(lastAction);
            } catch(e){}
        }

        fetch(this.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type':      'application/json',
                'X-WAAI-Nonce':      this.csrfToken || '',
                'X-WAAI-Session-ID': this.sessionId  || '',
                'X-WAAI-Trace-ID':   traceId         || '',
            },
            body: JSON.stringify(body)
        })
        .then(res => res.json())
        .then(data => {
            this.removeWritingIndicator();
            if (data.success && data.reply) {
                const cleanReply = data.reply;
                this.addMessage(cleanReply, 'assistant');
                
                // Capture and persist context if action contains user contact info
                if (data.action && data.action.params) {
                    if (data.action.type === 'whatsapp' && data.action.params.to) {
                        this.capturedPhoneNumber = data.action.params.to;
                        if (this.setStoredItem) this.setStoredItem('waai_user_phone', data.action.params.to);
                    }
                    if (data.action.type === 'email' && data.action.params.to) {
                        this.capturedUserEmail = data.action.params.to;
                        if (this.setStoredItem) this.setStoredItem('waai_user_email', data.action.params.to);
                    }
                }

                // Emit structured response event — Actions.js picks this up via EventBus
                // instead of being called directly, eliminating the tight coupling.
                if (this.EventBus) {
                    this.EventBus.emit('api:response', {
                        reply: cleanReply,
                        action: data.action || null,
                        actions: data.actions || null,  // Support multiple tool calls
                    });
                } else if (data.action) {
                    // Fallback: direct call if EventBus not yet initialized (should not happen)
                    this.processActionRequest(data.action);
                } else if (data.actions && Array.isArray(data.actions)) {
                    data.actions.forEach(act => this.processActionRequest(act));
                }
                
                if (this.speechSynthesisActive) {
                    // Start the speaking pipeline. The TTS engine (Piper, Groq, or Browser TTSQueue)
                    // will manage its own state transitions (e.g. going back to listening when finished).
                    this.transitionTo('speaking', { text: cleanReply });
                } else if (this.isContinuousVoiceMode) {
                    this.transitionTo('cooldown', { nextState: 'listening' });
                }
            } else {
                const serverError = data.error || '';
                const errMsg = serverError
                    ? `⚠️ ${serverError}`
                    : "Sorry, I encountered an error. Please try again or contact support directly.";
                this.addMessage(errMsg, 'assistant');
                if (this.EventBus) {
                    this.EventBus.emit('api:error', { message: errMsg, data });
                }
                if (this.speechSynthesisActive) {
                    this.transitionTo('speaking', { text: errMsg });
                } else if (this.isContinuousVoiceMode) {
                    this.transitionTo('cooldown', { nextState: 'listening' });
                }
            }
        })
        .catch(err => {
            this.removeWritingIndicator();
            if (this.clearTraceId) this.clearTraceId();
            if(this.waError) this.waError('AI assistant communication error:', err);
            const errMsg = "Failed to reach server. Please check your connection.";
            this.addMessage(errMsg, 'assistant');
            if (this.EventBus) {
                this.EventBus.emit('api:error', { message: errMsg, err });
            }
            if (this.speechSynthesisActive) {
                this.transitionTo('speaking', { text: errMsg });
            } else if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            }
        });
    },

    initBackgroundProcessing() {
        setTimeout(() => {
            if (window.waaiConfig && window.waaiConfig.enableAgentic === '0') return;

            const rawContext = this.getPageContext(true); // bypass cache — scan DOM fresh

            const bgEndpoint = this.apiEndpoint; // use same endpoint as chat (has nonce support)
            const bgHeaders = {
                'Content-Type': 'application/json',
                'X-WAAI-Nonce': this.csrfToken || '',
                'X-WAAI-Session-ID': this.sessionId || '',
            };

            // 1. DOM Interactables Compression — only run if enabled and not already cached for this page
            const domCacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('dom_map_' + window.location.pathname) : ('waai_abid_dom_map_' + window.location.pathname);
            if (window.waaiConfig && window.waaiConfig.enablePageLinks === '0') {
                localStorage.removeItem(domCacheKey);
            } else if ((!window.waaiConfig || window.waaiConfig.enablePageLinks !== '0') && !localStorage.getItem(domCacheKey) && rawContext.interactables && rawContext.interactables.length > 0) {
                fetch(bgEndpoint, {
                    method: 'POST',
                    headers: bgHeaders,
                    body: JSON.stringify({
                        action: 'waai_process_dom_background',
                        interactables: rawContext.interactables
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.compressed_map) {
                        localStorage.setItem(domCacheKey, JSON.stringify(data.compressed_map));
                        console.log('[WAAI BG] DOM map cached:', data.compressed_map.length, 'elements');
                    } else {
                        console.warn('[WAAI BG] DOM compression failed:', data);
                    }
                })
                .catch(err => console.error('[WAAI BG] DOM background fetch error:', err));
            }

            // 2. Page Content Summary — only run if enabled and not already cached for this page
            const pageSummaryCacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('page_summary_' + window.location.pathname) : ('waai_abid_page_summary_' + window.location.pathname);
            if (window.waaiConfig && window.waaiConfig.enablePageText === '0') {
                localStorage.removeItem(pageSummaryCacheKey);
            } else if ((!window.waaiConfig || window.waaiConfig.enablePageText !== '0') && !localStorage.getItem(pageSummaryCacheKey) && rawContext.page_content && rawContext.page_content.length > 200) {
                console.log('[WAAI BG] Starting page summary for:', rawContext.url, '(', rawContext.page_content.length, 'chars)');
                fetch(bgEndpoint, {
                    method: 'POST',
                    headers: bgHeaders,
                    body: JSON.stringify({
                        action: 'waai_process_page_content_background',
                        page_content: rawContext.page_content,
                        page_title: rawContext.title,
                        page_url: rawContext.url
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.page_summary) {
                        localStorage.setItem(pageSummaryCacheKey, data.page_summary);
                        console.log('[WAAI BG] Page summary cached for:', rawContext.url);
                    } else {
                        console.warn('[WAAI BG] Page summary failed:', data);
                        localStorage.setItem(pageSummaryCacheKey, '[WAAI_SUMMARY_FAILED]');
                    }
                })
                .catch(err => {
                    console.error('[WAAI BG] Page summary fetch error:', err);
                    localStorage.setItem(pageSummaryCacheKey, '[WAAI_SUMMARY_FAILED]');
                });
            }

            // 3. Global Sitemap Background Sync — only run if enabled and not already cached
            const sitemapCacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('global_sitemap_map') : 'waai_abid_global_sitemap_map';
            if (window.waaiConfig && !window.waaiConfig.siteIndex) {
                localStorage.removeItem(sitemapCacheKey);
            } else if (window.waaiConfig && window.waaiConfig.siteIndex && !localStorage.getItem(sitemapCacheKey)) {
                fetch(bgEndpoint, {
                    method: 'POST',
                    headers: bgHeaders,
                    body: JSON.stringify({
                        action: 'waai_process_sitemap_background'
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success && data.compressed_sitemap) {
                        localStorage.setItem(sitemapCacheKey, JSON.stringify(data.compressed_sitemap));
                        console.log('[WAAI BG] Sitemap cached:', data.compressed_sitemap.length, 'pages');
                    } else {
                        console.warn('[WAAI BG] Sitemap compression failed:', data);
                    }
                })
                .catch(err => console.error('[WAAI BG] Sitemap background fetch error:', err));
            }
        }, 2000);
    },

    getPageContext(bypassCache = false) {
        const context = {
            url: window.location.pathname + window.location.search,
            title: document.title,
            interactables: [],
            global_sitemap: []
        };
        
        // Attach global sitemap if cached and enabled
        if (window.waaiConfig && window.waaiConfig.siteIndex) {
            const sitemapCacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('global_sitemap_map') : 'waai_abid_global_sitemap_map';
            const cachedSitemap = localStorage.getItem(sitemapCacheKey);
            if (cachedSitemap) {
                try {
                    context.global_sitemap = JSON.parse(cachedSitemap);
                } catch (e) {}
            }
        }

        const selector = 'a, button, input:not([type="hidden"]), textarea, select, [role="button"]';
        let elements = [];
        if (!window.waaiConfig || window.waaiConfig.enablePageLinks !== '0') {
            elements = Array.from(document.querySelectorAll(selector));
        }
        
        let idCounter = 1;
        
        // Clear previous attributes
        document.querySelectorAll('[data-waai-id]').forEach(el => {
            el.removeAttribute('data-waai-id');
        });

        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const style = window.getComputedStyle(el);
            
            if (style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0) {
                return;
            }
            if (rect.width === 0 && rect.height === 0) {
                return;
            }

            // Skip assistant widget itself
            if (el.closest('ai-assistant-widget') || el.closest('#chat-panel') || el.id === 'chat-trigger') {
                return;
            }

            let text = '';
            let type = el.tagName.toLowerCase();
            
            if (type === 'input') {
                const inputType = el.getAttribute('type') || 'text';
                type = `${inputType}_input`;
                text = el.getAttribute('placeholder') || el.getAttribute('name') || el.getAttribute('aria-label') || '';
            } else if (type === 'textarea') {
                text = el.getAttribute('placeholder') || el.getAttribute('name') || el.getAttribute('aria-label') || '';
            } else if (type === 'select') {
                text = el.getAttribute('name') || el.getAttribute('aria-label') || '';
            } else {
                text = el.textContent.trim().replace(/\s+/g, ' ');
            }

            if (text.length > 80) {
                text = text.substring(0, 77) + '...';
            }

            if (!text && !el.getAttribute('href')) {
                return;
            }

            const waaiId = idCounter++;
            el.setAttribute('data-waai-id', waaiId);

            const item = {
                waai_id: waaiId,
                type: type,
                text: text || '(No Label)'
            };

            if (el.tagName === 'A' && el.getAttribute('href')) {
                item.href = el.getAttribute('href');
            }

            context.interactables.push(item);
        });

        if (context.interactables.length > 80) {
            context.interactables = context.interactables.slice(0, 80);
        }
        
        if (!bypassCache && (!window.waaiConfig || window.waaiConfig.enablePageLinks !== '0')) {
            const cacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('dom_map_' + window.location.pathname) : ('waai_abid_dom_map_' + window.location.pathname);
            const cachedMap = localStorage.getItem(cacheKey);
            if (cachedMap) {
                try {
                    context.interactables = JSON.parse(cachedMap);
                } catch (e) {
                    console.error('[WAAI] Error parsing cached DOM map', e);
                }
            }
        }

        // Extract page content text (for summary and context)
        let pageContentText = '';
        if (!window.waaiConfig || window.waaiConfig.enablePageText !== '0') {
            try {
                // Detect any single post/page type (blog post, portfolio, case study, service page, etc.)
                // document.body on single WP posts gets class like: single-post, single-portfolio, single-services
                const isSinglePost = window.location.pathname.includes('/blog/') || 
                               window.location.pathname.includes('/post/') || 
                               document.body.classList.contains('single-post') ||
                               document.body.classList.contains('single-portfolio') ||
                               document.body.classList.contains('single-services') ||
                               document.body.classList.contains('single-project') ||
                               document.body.classList.contains('single-case-study') ||
                               Array.from(document.body.classList).some(c => c.startsWith('single-') && c !== 'single-format-standard');
                               
                // Base node
                const mainEl = document.querySelector('main, article, #content, .content, .main') || document.body;
                const clone = mainEl.cloneNode(true);
                
                // 1. Semantic Filtering: Remove noise
                clone.querySelectorAll('script, style, nav, footer, header, aside, .sidebar, #sidebar, svg, noscript, iframe, ai-assistant-widget, #chat-panel, #chat-trigger').forEach(el => el.remove());
                
                // 2. Markdown Conversion
                pageContentText = this.waai_domToMarkdown(clone);
                
                // Clean up excess whitespace and format line-by-line
                pageContentText = pageContentText.split('\n')
                    .map(line => line.trim().replace(/\s+/g, ' '))
                    .filter((line, index, arr) => {
                        if (line === '') {
                            return index > 0 && arr[index - 1] !== '';
                        }
                        return true;
                    })
                    .join('\n').trim();
                
            } catch (err) {
                console.error('[WAAI] Error extracting page content text:', err);
            }
        }

        // Use cached page summary from secondary LLM if available (saves tokens vs raw text)
        const pageSummaryCacheKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('page_summary_' + window.location.pathname) : ('waai_abid_page_summary_' + window.location.pathname);
        const cachedSummary = (!bypassCache && (!window.waaiConfig || window.waaiConfig.enablePageText !== '0')) ? localStorage.getItem(pageSummaryCacheKey) : null;

        if (cachedSummary && cachedSummary !== '[WAAI_SUMMARY_FAILED]') {
            // Use the pre-processed summary — very short, very efficient
            context.page_content = cachedSummary;
            console.log('[WAAI] Using cached page summary (' + cachedSummary.length + ' chars)');
        } else if (!window.waaiConfig || window.waaiConfig.enablePageText !== '0') {
            // No cache yet — fall back to raw text.
            // For rich single-post pages (portfolio, blog, services) allow up to 40k.
            // For generic pages (home, archive, shop listing) cap at 5k to save tokens.
            const isSinglePage = window.location.pathname.includes('/blog/') || 
                               window.location.pathname.includes('/post/') || 
                               document.body.classList.contains('single-post') ||
                               document.body.classList.contains('single-portfolio') ||
                               document.body.classList.contains('single-services') ||
                               document.body.classList.contains('single-project') ||
                               document.body.classList.contains('single-case-study') ||
                               Array.from(document.body.classList).some(c => c.startsWith('single-') && c !== 'single-format-standard');
            const maxChars = isSinglePage ? 40000 : 5000;

            if (pageContentText.length > maxChars) {
                pageContentText = pageContentText.substring(0, maxChars) + '\n... [CONTENT TRUNCATED]';
            }
            context.page_content = pageContentText;
        }

        return context;
    },

    waai_domToMarkdown(node) {
        if (!node) return '';
        if (node.nodeType === 3) {
            // Collapse all whitespace runs (spaces, tabs, newlines) to a single space
            return node.textContent.replace(/\s+/g, ' ');
        }
        
        let md = '';
        const tag = node.nodeName.toLowerCase();
        
        // Block elements
        if (tag === 'h1') md += '\n# ';
        else if (tag === 'h2') md += '\n## ';
        else if (tag === 'h3') md += '\n### ';
        else if (tag === 'h4') md += '\n#### ';
        else if (tag === 'h5') md += '\n##### ';
        else if (tag === 'h6') md += '\n###### ';
        else if (tag === 'li') md += '\n- ';
        else if (tag === 'p' || tag === 'div' || tag === 'section') md += '\n\n';
        else if (tag === 'br') md += '\n';
        
        // Inline elements
        else if (tag === 'strong' || tag === 'b') md += '**';
        else if (tag === 'em' || tag === 'i') md += '*';

        for (let i = 0; i < node.childNodes.length; i++) {
            md += this.waai_domToMarkdown(node.childNodes[i]);
        }
        
        if (tag === 'strong' || tag === 'b') md += '**';
        else if (tag === 'em' || tag === 'i') md += '*';
        
        return md;
    }
};
