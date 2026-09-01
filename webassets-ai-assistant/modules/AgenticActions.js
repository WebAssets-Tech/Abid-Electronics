/**
 * Agentic Website Navigation
 * This isolated module handles DOM manipulations sent by the AI backend.
 * Refactored for Phase 1: Action Verification & Observability
 */
export const AgenticActions = {
    async handleAction(actionData, widget) {
        if (!actionData) return { success: false, reason: "no_action_data" };

        window.WAAI_DEBUG = window.WAAI_DEBUG || {};
        window.WAAI_DEBUG.lastToolCall = actionData;

        try {
            if (actionData.type === 'agentic_interaction') {
                const { action, target_text, element_id, value } = actionData.params;
                if (action === 'click') {
                    return await this.clickElementByIdOrText(element_id, target_text);
                } else if (action === 'fill') {
                    return await this.fillElementByIdOrText(element_id, target_text, value);
                } else if (action === 'scroll_to') {
                    return await this.scrollElementByIdOrText(element_id, target_text);
                }
                return { success: false, reason: "unknown_interaction_action" };
            }

            if (actionData.type === 'scroll_page') {
                const { direction, amount_pixels } = actionData.params;
                await this.handleScrollPage(direction, amount_pixels);
                return { success: true };
            }

            if (actionData.type === 'open_assistant_overlay') {
                const { overlay_type } = actionData.params;
                if (overlay_type === 'lead_form' && widget && typeof widget.showLeadForm === 'function') {
                    widget.showLeadForm();
                    return { success: true };
                } else if (overlay_type === 'calendar' && widget && typeof widget.showCalendarEmbed === 'function') {
                    widget.showCalendarEmbed();
                    return { success: true };
                }
                return { success: false, reason: "overlay_not_found" };
            }

            if (actionData.type !== 'agentic_navigation') {
                 return { success: false, reason: "unknown_action_type" };
            }

            const params = actionData.params;
            if (!params) return { success: false, reason: "missing_params" };

            // Force redirect action if the selector is a URL or absolute path
            if (params.selector && (params.selector.startsWith('http') || params.selector.startsWith('/'))) {
                params.action = 'redirect';
            }

            if (params.action === 'scroll') {
                let res = { success: false, reason: "route_not_found" };
                if (params.selector) {
                    res = await this.scrollTo(params.selector);
                }
                if (res && res.success) {
                    return res;
                }
                // Fallback 1: Fallback to text/ID/class dynamic scrolling if selector wasn't found or scroll failed
                if (params.target_name) {
                    const fallbackRes = await this.scrollElementByIdOrText(null, params.target_name);
                    if (fallbackRes && fallbackRes.success) {
                        return fallbackRes;
                    }
                }
                // Fallback 2: If the section doesn't exist on the current page, check if there is a separate page for it, and redirect!
                if (params.target_name) {
                    const resolved = this.resolveIntent(params.target_name);
                    if (resolved && resolved.url) {
                        sessionStorage.setItem('waai_agentic_redirect', 'true');
                        window.location.href = resolved.url;
                        return { success: true, redirecting: true, details: "scroll_fallback_redirect" };
                    }
                }
                return res;
            } else if (params.action === 'redirect') {
                let urlToNavigate = params.selector;
                
                // Intent Resolver: If selector is missing or not a URL, try fuzzy semantic search
                if (!urlToNavigate || (!urlToNavigate.startsWith('http') && !urlToNavigate.startsWith('/'))) {
                    const resolved = this.resolveIntent(params.target_name);
                    if (resolved && resolved.url) {
                        urlToNavigate = resolved.url;
                        window.WAAI_DEBUG.intentResolution = resolved;
                    }
                }

                if (urlToNavigate) {
                    if (urlToNavigate.startsWith('#')) {
                        // It's a hash anchor. Check if it exists on the current page.
                        if (document.querySelector(urlToNavigate)) {
                            // Convert this to a scroll action instead of redirecting!
                            return await this.scrollTo(urlToNavigate);
                        } else {
                            // Fallback to dynamic scroll on current page before failing
                            if (params.target_name) {
                                const fallbackRes = await this.scrollElementByIdOrText(null, params.target_name);
                                if (fallbackRes && fallbackRes.success) {
                                    return fallbackRes;
                                }
                            }
                            return { success: false, reason: "route_not_found", details: "Section not found on this page." };
                        }
                    }

                    // Save voice state before redirecting to maintain continuous Agentic environment
                    sessionStorage.setItem('waai_agentic_redirect', 'true');
                    window.location.href = urlToNavigate;
                    return { success: true, redirecting: true };
                } else {
                    return { success: false, reason: "route_not_found" };
                }
            }

            return { success: false, reason: "unhandled_navigation" };
        } catch (err) {
            window.WAAI_DEBUG.lastFailure = err;
            console.error("[AgenticActions] Execution error:", err);
            return { success: false, reason: "execution_error", details: err.message };
        }
    },

    resolveIntent(targetName) {
        if (!window.waaiConfig || !window.waaiConfig.siteIndex) return null;
        
        const index = window.waaiConfig.siteIndex;
        const targetLower = (targetName || '').toLowerCase().trim();
        if (!targetLower) return null;

        let bestMatch = null;
        let highestConfidence = 0;

        const checkMatch = (item, type) => {
            const title = (item.title || item.name || '').toLowerCase();
            const url = item.url || item.link || '';
            
            if (title === targetLower) {
                return { confidence: 1.0, url, type, item };
            }
            if (title.includes(targetLower) || targetLower.includes(title)) {
                return { confidence: 0.8, url, type, item };
            }
            return null;
        };

        // Check Services
        if (Array.isArray(index.services)) {
            for (const s of index.services) {
                const match = checkMatch(s, 'service');
                if (match && match.confidence > highestConfidence) {
                    highestConfidence = match.confidence;
                    bestMatch = match;
                }
            }
        }
        
        // Check Products
        if (Array.isArray(index.products)) {
            for (const p of index.products) {
                const match = checkMatch(p, 'product');
                if (match && match.confidence > highestConfidence) {
                    highestConfidence = match.confidence;
                    bestMatch = match;
                }
            }
        }

        // Check Sections / Pages
        if (Array.isArray(index.sections)) {
            for (const s of index.sections) {
                const match = checkMatch(s, 'section');
                if (match && match.confidence > highestConfidence) {
                    highestConfidence = match.confidence;
                    bestMatch = match;
                }
            }
        }

        return bestMatch;
    },

    async handleScrollPage(direction, amount) {
        return new Promise((resolve) => {
            const scrollAmount = amount ? parseInt(amount, 10) : window.innerHeight * 0.8;
            
            switch(direction) {
                case 'up':
                    window.scrollBy({ top: -scrollAmount, behavior: 'smooth' });
                    break;
                case 'down':
                    window.scrollBy({ top: scrollAmount, behavior: 'smooth' });
                    break;
                case 'top':
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    break;
                case 'bottom':
                    window.scrollTo({ top: document.body.scrollHeight, behavior: 'smooth' });
                    break;
            }
            setTimeout(() => resolve({ success: true }), 300); // Wait for smooth scroll
        });
    },

    async ensureElementRevealed(el) {
        if (!el) return;

        const isHidden = (target) => {
            const style = window.getComputedStyle(target);
            return style.display === 'none' || style.visibility === 'hidden' || parseFloat(style.opacity) === 0 || target.offsetWidth === 0 || target.offsetHeight === 0;
        };

        // 1. Mobile Menu Auto-opening
        if (window.innerWidth < 768 && isHidden(el)) {
            const insideHeaderOrNav = el.closest('header, nav, #header, #navigation, .nav, .menu, .main-navigation');
            if (insideHeaderOrNav) {
                const hamburgerSelectors = [
                    '.menu-toggle', '.hamburger', '.nav-toggle', '.mobile-menu-btn', 
                    '[aria-label="Menu"]', '[aria-label="Toggle navigation"]', 
                    '.menu-btn', '.icon-menu', '.menu-icon', '#menu-icon',
                    'a[href="#menu"]', 'button[class*="menu"]', 'button[class*="nav"]',
                    'a[class*="menu"]', 'a[class*="nav"]', '[class*="hamburger"]'
                ];
                
                let trigger = null;
                for (const sel of hamburgerSelectors) {
                    const found = document.querySelector(sel);
                    if (found && !isHidden(found)) {
                        trigger = found;
                        break;
                    }
                }

                if (trigger) {
                    trigger.click();
                    await new Promise(r => setTimeout(r, 350));
                }
            }
        }

        // 2. Tab Panel Unlocking
        const tabpanel = el.closest('[role="tabpanel"], .tab-pane, .tab-content > div');
        if (tabpanel && (tabpanel.hasAttribute('hidden') || isHidden(tabpanel) || !tabpanel.classList.contains('active'))) {
            const id = tabpanel.id;
            const ariaLabelledby = tabpanel.getAttribute('aria-labelledby');
            let tabCtrl = null;
            if (ariaLabelledby) {
                tabCtrl = document.getElementById(ariaLabelledby);
            }
            if (!tabCtrl && id) {
                tabCtrl = document.querySelector(`[aria-controls="${id}"], [href="#${id}"]`);
            }
            if (tabCtrl) {
                tabCtrl.click();
                await new Promise(r => setTimeout(r, 200));
            }
        }

        // 3. Accordion/Details Panel Expansion
        const details = el.closest('details');
        if (details && !details.open) {
            details.open = true;
            await new Promise(r => setTimeout(r, 200));
        }

        const collapsedParent = el.closest('.collapse, .panel-collapse');
        if (collapsedParent && !collapsedParent.classList.contains('show') && !collapsedParent.classList.contains('in')) {
            const id = collapsedParent.id;
            let trigger = null;
            if (id) {
                trigger = document.querySelector(`[data-target="#${id}"], [href="#${id}"], [aria-controls="${id}"]`);
            }
            if (!trigger) {
                const header = collapsedParent.previousElementSibling;
                if (header) {
                    trigger = header.querySelector('a, button, [role="button"]') || header;
                }
            }
            if (trigger) {
                trigger.click();
                await new Promise(r => setTimeout(r, 300));
            }
        }
    },

    highlightElement(el) {
        const originalOutline = el.style.outline;
        const originalTransition = el.style.transition;
        el.style.transition = 'outline 0.3s ease-in-out, box-shadow 0.3s ease-in-out';
        el.style.outline = '3px solid #5f39ff';
        el.style.boxShadow = '0 0 15px rgba(95, 57, 255, 0.5)';
        setTimeout(() => {
            el.style.outline = '3px solid transparent';
            el.style.boxShadow = 'none';
            setTimeout(() => {
                el.style.outline = originalOutline;
                el.style.transition = originalTransition;
            }, 300);
        }, 2000);
    },

    async clickElementByIdOrText(elementId, text) {
        return new Promise(async (resolve) => {
            let el = null;
            if (elementId) {
                el = document.querySelector(`[data-waai-id="${elementId}"]`);
            }
            
            if (!el && text) {
                const lowerText = text.toLowerCase().trim();
                const interactables = Array.from(document.querySelectorAll('a, button, input[type="button"], input[type="submit"], [role="button"], h1, h2, h3, h4, .post-title'));
                
                // Exact match first
                el = interactables.find(e => e.textContent.toLowerCase().trim() === lowerText || (e.value && e.value.toLowerCase().trim() === lowerText));
                
                // Fuzzy match fallback
                if (!el) {
                    el = interactables.find(e => e.textContent.toLowerCase().includes(lowerText) || (e.value && e.value.toLowerCase().includes(lowerText)));
                }
            }
            
            if (el) {
                await this.ensureElementRevealed(el);
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.highlightElement(el);
                setTimeout(() => {
                    // If the element itself is a link
                    if (el.tagName === 'A' && el.href) {
                        sessionStorage.setItem('waai_agentic_redirect', 'true');
                        window.location.href = el.href;
                        setTimeout(() => {
                            resolve({ success: false, reason: "redirect_failed", details: "Navigation to " + el.href + " failed" });
                        }, 1500);
                        return;
                    }
                    
                    // If the element contains a link
                    const childLink = el.querySelector('a[href]');
                    if (childLink && childLink.href) {
                        sessionStorage.setItem('waai_agentic_redirect', 'true');
                        window.location.href = childLink.href;
                        setTimeout(() => {
                            resolve({ success: false, reason: "redirect_failed", details: "Navigation to " + childLink.href + " failed" });
                        }, 1500);
                        return;
                    }

                    // If the element is wrapped inside a link
                    const parentLink = el.closest('a[href]');
                    if (parentLink && parentLink.href) {
                        sessionStorage.setItem('waai_agentic_redirect', 'true');
                        window.location.href = parentLink.href;
                        setTimeout(() => {
                            resolve({ success: false, reason: "redirect_failed", details: "Navigation to " + parentLink.href + " failed" });
                        }, 1500);
                        return;
                    }

                    // Fallback: try standard click for buttons
                    let redirectOccurred = false;
                    const handleUnload = () => { 
                        redirectOccurred = true; 
                        sessionStorage.setItem('waai_agentic_redirect', 'true');
                    };
                    window.addEventListener('beforeunload', handleUnload);

                    el.click();

                    // Verification Loop: Wait for DOM modifications or redirect
                    setTimeout(() => {
                        window.removeEventListener('beforeunload', handleUnload);
                        
                        // Check if a modal or popup was opened
                        const modalOpened = Array.from(document.querySelectorAll('.modal, .show, .open, dialog, .active')).some(m => {
                            const style = window.getComputedStyle(m);
                            return (m.tagName === 'DIALOG' && m.open) || (style.display !== 'none' && style.visibility !== 'hidden' && parseFloat(style.opacity) > 0);
                        });

                        window.WAAI_DEBUG = window.WAAI_DEBUG || {};
                        window.WAAI_DEBUG.lastDOMScan = Date.now();

                        if (modalOpened) {
                            resolve({ success: true, details: "click_executed_modal_verified" });
                        } else if (!redirectOccurred && (el.tagName === 'A' || el.closest('a') || el.type === 'submit')) {
                            // Link/Submit click failed to navigate
                            resolve({ success: false, reason: "redirect_failed", details: "Click did not cause navigation" });
                        } else {
                            resolve({ success: true, details: "click_executed" });
                        }
                    }, 800);

                }, 600);
            } else {
                console.warn(`[AgenticActions] Could not find clickable element for ID: ${elementId} / text: ${text}`);
                window.WAAI_DEBUG = window.WAAI_DEBUG || {};
                window.WAAI_DEBUG.lastFailure = { reason: "dom_element_missing", elementId, text };
                resolve({ success: false, reason: "dom_element_missing" });
            }
        });
    },

    async fillElementByIdOrText(elementId, text, value) {
        return new Promise(async (resolve) => {
            let el = null;
            if (elementId) {
                el = document.querySelector(`[data-waai-id="${elementId}"]`);
            }
            
            if (!el && text) {
                const lowerText = text.toLowerCase().trim();
                const inputs = Array.from(document.querySelectorAll('input:not([type="hidden"]), textarea, select'));
                
                el = inputs.find(e => {
                    const placeholder = (e.getAttribute('placeholder') || '').toLowerCase();
                    const name = (e.getAttribute('name') || '').toLowerCase();
                    const aria = (e.getAttribute('aria-label') || '').toLowerCase();
                    return placeholder.includes(lowerText) || name.includes(lowerText) || aria.includes(lowerText);
                });
                
                if (!el) {
                    const labels = Array.from(document.querySelectorAll('label'));
                    const label = labels.find(l => l.textContent.toLowerCase().includes(lowerText));
                    if (label && label.htmlFor) {
                        el = document.getElementById(label.htmlFor);
                    } else if (label) {
                        el = label.querySelector('input, textarea, select');
                    }
                }
            }
            
            if (el) {
                await this.ensureElementRevealed(el);
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.highlightElement(el);
                setTimeout(() => {
                    if (el.tagName === 'SELECT') {
                        const lowerValue = (value || '').toLowerCase().trim();
                        const options = Array.from(el.options);
                        const matchedOption = options.find(opt => opt.text.toLowerCase().includes(lowerValue) || opt.value.toLowerCase().includes(lowerValue));
                        if (matchedOption) {
                            el.value = matchedOption.value;
                        } else {
                            el.value = value || '';
                        }
                    } else {
                        el.value = value || '';
                    }
                    el.dispatchEvent(new Event('input', { bubbles: true }));
                    el.dispatchEvent(new Event('change', { bubbles: true }));
                    
                    resolve({ success: true, details: "form_filled" });
                }, 400);
            } else {
                console.warn(`[AgenticActions] Could not find input element for ID: ${elementId} / text: ${text}`);
                window.WAAI_DEBUG = window.WAAI_DEBUG || {};
                window.WAAI_DEBUG.lastFailure = { reason: "form_field_missing", elementId, text };
                resolve({ success: false, reason: "form_field_missing" });
            }
        });
    },

    async scrollElementByIdOrText(elementId, text) {
        return new Promise(async (resolve) => {
            let el = null;
            if (elementId) {
                el = document.querySelector(`[data-waai-id="${elementId}"]`);
            }
            
            if (!el && text) {
                const lowerText = text.toLowerCase().trim();
                
                // 1. Special handling for form requests
                if (lowerText.includes('form')) {
                    const forms = Array.from(document.querySelectorAll('form'));
                    if (forms.length > 0) {
                        el = forms.find(f => {
                            const id = (f.id || '').toLowerCase();
                            const cls = (f.className || '').toLowerCase();
                            return id.includes('contact') || cls.includes('contact') || id.includes('lead') || cls.includes('lead') || id.includes('form') || cls.includes('form');
                        });
                        if (!el) el = forms[0];
                    }
                }
                
                // 2. Try ID or Class matching for sections/elements (e.g. #about, .about-section)
                if (!el) {
                    const sectionsAndDivs = Array.from(document.querySelectorAll('section, article, div, header, footer'));
                    el = sectionsAndDivs.find(e => {
                        const id = (e.id || '').toLowerCase();
                        return id === lowerText || id === lowerText.replace(/\s+/g, '-') || id === lowerText.replace(/\s+/g, '_');
                    });
                }
                
                // 3. Fallback to direct text content matching
                if (!el) {
                    const interactables = Array.from(document.querySelectorAll('h1, h2, h3, h4, h5, h6, p, span, div, a, button, section, article, form'));
                    
                    const textNodes = interactables.filter(e => {
                        if (e.children.length > 3 && !['H1','H2','H3','H4','H5','H6','FORM','SECTION'].includes(e.tagName)) return false;
                        return e.textContent.toLowerCase().includes(lowerText);
                    });

                    textNodes.sort((a, b) => a.textContent.length - b.textContent.length);
                    
                    if (textNodes.length > 0) {
                        el = textNodes[0];
                    }
                }

                // 4. Fallback to fuzzy ID/Class inclusion
                if (!el) {
                    const sectionsAndDivs = Array.from(document.querySelectorAll('section, article, div'));
                    el = sectionsAndDivs.find(e => {
                        const id = (e.id || '').toLowerCase();
                        const cls = (e.className || '').toLowerCase();
                        return id.includes(lowerText) || cls.includes(lowerText);
                    });
                }
            }
            
            if (el) {
                await this.ensureElementRevealed(el);
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.highlightElement(el);
                setTimeout(() => resolve({ success: true, details: "scrolled_to_text" }), 800);
            } else {
                console.warn(`[AgenticActions] Could not find text to scroll to: ${text}`);
                window.WAAI_DEBUG = window.WAAI_DEBUG || {};
                window.WAAI_DEBUG.lastFailure = { reason: "dom_element_missing", elementId, text };
                resolve({ success: false, reason: "dom_element_missing" });
            }
        });
    },

    async scrollTo(selector) {
        return new Promise((resolve) => {
            try {
                const el = document.querySelector(selector);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });

                    const originalOutline = el.style.outline;
                    const originalTransition = el.style.transition;
                    el.style.transition = 'outline 0.3s ease-in-out, box-shadow 0.3s ease-in-out';
                    el.style.outline = '3px solid #5f39ff';
                    el.style.boxShadow = '0 0 15px rgba(95, 57, 255, 0.5)';

                    setTimeout(() => {
                        el.style.outline = '3px solid transparent';
                        el.style.boxShadow = 'none';
                        setTimeout(() => {
                            el.style.outline = originalOutline;
                            el.style.transition = originalTransition;
                        }, 300);
                    }, 2000);

                    setTimeout(() => resolve({ success: true }), 800);
                } else {
                    console.warn(`[AgenticActions] Element not found for selector: ${selector}`);
                    window.WAAI_DEBUG = window.WAAI_DEBUG || {};
                    window.WAAI_DEBUG.lastFailure = { reason: "route_not_found", selector };
                    resolve({ success: false, reason: "route_not_found" });
                }
            } catch (e) {
                console.error('[AgenticActions] Error executing scroll:', e);
                resolve({ success: false, reason: "execution_error", details: e.message });
            }
        });
    }
};
