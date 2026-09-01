export const FormsMixin = {

    interceptLocalIntents(text) {
        // Disabled local intercepts.
        // We now rely on the LLM's 'open_assistant_overlay' or 'interact_with_element' tools
        // to contextually decide whether to open the built-in overlays or interact with website forms.
        return false;
    },

showCalendarEmbed() {
        const calType  = this.cfg.calendarType  || 'disabled';
        const calUrl   = calType === 'calendly' ? (this.cfg.calendlyUrl   || '')
                       : calType === 'google'   ? (this.cfg.googleCalUrl  || '')
                       : calType === 'custom'   ? (this.cfg.customCalUrl  || '')
                       : '';

        const container = document.createElement('div');
        container.style.cssText = 'width:100%;height:100%;display:flex;flex-direction:column;';

        if (calType === 'disabled' || !calUrl) {
            container.innerHTML = `
                <div style="text-align:center;padding:30px 20px;color:#555">
                    <p style="font-size:28px;margin:0 0 12px">📞</p>
                    <p style="font-size:15px;font-weight:600">Book a Free Consultation</p>
                    <p style="font-size:13px;color:#777;margin:8px 0 20px">Contact us directly and we'll schedule a call at your convenience.</p>
                    <a href="mailto:${this.cfg.companyEmail || ''}" style="display:${this.cfg.companyEmail ? 'inline-block' : 'none'};padding:10px 24px;background:#5f39ff;color:#fff;border-radius:8px;text-decoration:none;font-weight:600">Send Email</a>
                </div>`;
        } else if (calType === 'calendly') {
            // Calendly inline embed
            container.innerHTML = `
                <div id="waai-calendly-embed" style="flex:1;min-height:400px"></div>`;
            // Load Calendly widget script
            const script = document.createElement('script');
            script.src = 'https://assets.calendly.com/assets/external/widget.js';
            script.onload = () => {
                if (window.Calendly) {
                    window.Calendly.initInlineWidget({
                        url: calUrl,
                        parentElement: container.querySelector('#waai-calendly-embed'),
                    });
                }
            };
            document.head.appendChild(script);
        } else {
            // Google Calendar or custom URL — iframe embed
            container.innerHTML = `
                <iframe src="${calUrl}"
                    style="border:0;width:100%;flex:1;min-height:350px;border-radius:10px"
                    frameborder="0" scrolling="no"></iframe>`;
        }

        this.toggleOverlay(true, 'Book a Consultation', container);
    },

showLeadForm(prefill = {}) {
        const escapeHTML = (str) => {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const form = document.createElement('form');
        form.setAttribute('id', 'lead-overlay-form');
        form.innerHTML = `
            <div class="form-group">
                <label for="lead-name">Full Name</label>
                <input type="text" id="lead-name" required placeholder="John Doe" value="${escapeHTML(prefill.name)}">
            </div>
            <div class="form-group">
                <label for="lead-email">Email Address</label>
                <input type="email" id="lead-email" required title="Please enter a valid email address (e.g., name@domain.com)." placeholder="john@example.com" value="${escapeHTML(prefill.email)}">
            </div>
            <div class="form-group">
                <label for="lead-phone">Phone Number</label>
                <input type="tel" id="lead-phone" required title="Phone number must be between 10 and 12 digits (numbers only)." placeholder="10-12 digit phone number" value="${escapeHTML(prefill.phone)}">
            </div>
            <div class="form-group">
                <label for="lead-query">Describe Inquiry</label>
                <textarea id="lead-query" placeholder="Tell us what you are looking for..." rows="3">${escapeHTML(prefill.query)}</textarea>
            </div>
            <button type="submit" class="submit-lead-btn">Submit Information</button>
        `;

        form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitVoiceLead();
        });

        this.toggleOverlay(true, "Submit Inquiry Details", form);

        // If the form is opened and we already have complete, valid prefill details, auto-submit
        const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
        const phoneRegex = /^\+?\d{10,12}$/;
        if (prefill.email && emailRegex.test(prefill.email) && prefill.phone && phoneRegex.test(prefill.phone)) {
            this.pendingLeadPrefill = Object.assign({
                name: 'Voice User',
                query: 'Submitted via voice confirmation.'
            }, prefill);

            const autoMsg = "I have received your email and phone number. Submitting them to our team now...";
            if (this.autoSubmitTimeout) clearTimeout(this.autoSubmitTimeout);
            this.autoSubmitTimeout = setTimeout(() => {
                this.addMessage(autoMsg, 'assistant');
                if (this.speechSynthesisActive || this.isContinuousVoiceMode) {
                    this.speak(autoMsg);
                }
                this.autoSubmitTimeout = setTimeout(() => {
                    this.submitVoiceLead();
                    this.autoSubmitTimeout = null;
                }, 1200);
            }, 500);
        }
    },

    updateOpenLeadForm(details) {
        const shadow = this.shadowRoot;
        const form = shadow.getElementById('lead-overlay-form');
        if (!form) return false;

        let updated = false;
        let message = "I've updated the form with: ";
        let updates = [];

        if (!this.pendingLeadPrefill) {
            this.pendingLeadPrefill = { name: '', email: '', phone: '', query: '' };
        }

        if (details.name) {
            const nameInput = form.querySelector('#lead-name');
            if (nameInput && nameInput.value !== details.name) {
                nameInput.value = details.name;
                this.pendingLeadPrefill.name = details.name;
                updates.push(`Name: ${details.name}`);
                updated = true;
            }
        }
        if (details.email) {
            const emailInput = form.querySelector('#lead-email');
            if (emailInput && emailInput.value !== details.email) {
                emailInput.value = details.email;
                this.pendingLeadPrefill.email = details.email;
                updates.push(`Email: ${details.email}`);
                updated = true;
            }
        }
        if (details.phone) {
            const phoneInput = form.querySelector('#lead-phone');
            if (phoneInput && phoneInput.value !== details.phone) {
                phoneInput.value = details.phone;
                this.pendingLeadPrefill.phone = details.phone;
                updates.push(`Phone: ${details.phone}`);
                updated = true;
            }
        }
        if (details.query) {
            const queryInput = form.querySelector('#lead-query');
            if (queryInput && queryInput.value !== details.query) {
                queryInput.value = details.query;
                this.pendingLeadPrefill.query = details.query;
                updates.push(`Inquiry: ${details.query}`);
                updated = true;
            }
        }

        if (updated) {
            // Flash the inputs that were actually updated
            const nameUpdated = updates.some(u => u.startsWith('Name:'));
            const emailUpdated = updates.some(u => u.startsWith('Email:'));
            const phoneUpdated = updates.some(u => u.startsWith('Phone:'));
            const queryUpdated = updates.some(u => u.startsWith('Inquiry:'));

            if (nameUpdated) form.querySelector('#lead-name').style.backgroundColor = '#e0f7fa';
            if (emailUpdated) form.querySelector('#lead-email').style.backgroundColor = '#e0f7fa';
            if (phoneUpdated) form.querySelector('#lead-phone').style.backgroundColor = '#e0f7fa';
            if (queryUpdated) form.querySelector('#lead-query').style.backgroundColor = '#e0f7fa';

            setTimeout(() => {
                const ni = form.querySelector('#lead-name');
                const ei = form.querySelector('#lead-email');
                const pi = form.querySelector('#lead-phone');
                const qi = form.querySelector('#lead-query');
                if (ni && nameUpdated) ni.style.backgroundColor = '';
                if (ei && emailUpdated) ei.style.backgroundColor = '';
                if (pi && phoneUpdated) pi.style.backgroundColor = '';
                if (qi && queryUpdated) qi.style.backgroundColor = '';
            }, 1500);

            // Check if both fields are now fully valid for auto-submission
            const emailVal = (form.querySelector('#lead-email')?.value || '').trim();
            const phoneVal = (form.querySelector('#lead-phone')?.value || '').trim();
            
            const emailRegex = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i;
            const phoneRegex = /^\+?\d{10,12}$/;

            if (emailRegex.test(emailVal) && phoneRegex.test(phoneVal)) {
                if (!this.autoSubmitTimeout) {
                    const autoMsg = "I have received all your details. Submitting them to our team now...";
                    this.addMessage(autoMsg, 'assistant');
                    if (this.speechSynthesisActive || this.isContinuousVoiceMode) {
                        this.speak(autoMsg);
                    }
                    this.autoSubmitTimeout = setTimeout(() => {
                        this.submitVoiceLead();
                        this.autoSubmitTimeout = null;
                    }, 1200);
                }
            } else {
                if (this.autoSubmitTimeout) {
                    clearTimeout(this.autoSubmitTimeout);
                    this.autoSubmitTimeout = null;
                }
                const reply = message + updates.join(", ") + ". Please confirm the details on the screen.";
                this.addMessage(reply, 'assistant');
                if (this.speechSynthesisActive || this.isContinuousVoiceMode) {
                    this.speak(reply);
                }
            }
        }
        return updated;
    },

    showWhatsAppOverlay(phoneNumber, messageContent) {
        const escapeHTML = (str) => {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const container = document.createElement('div');
        container.className = 'wa-overlay-container';
        
        container.innerHTML = `
            <div class="wa-header">
                <div class="wa-title">Forwarding to WhatsApp</div>
                <div class="wa-recipient">Sending to: <strong>+${phoneNumber}</strong></div>
            </div>
            <div class="wa-content">
                <div class="wa-status-box">
                    <div class="wa-loader-wrapper">
                        <div class="wa-icon-prompt">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#5f39ff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                <line x1="12" y1="17" x2="12.01" y2="17"></line>
                            </svg>
                        </div>
                    </div>
                    <div class="wa-status-text">Would you like to send this message?</div>
                </div>
                <div class="wa-message-preview">
                    <div class="wa-preview-label">Message Preview</div>
                    <div class="wa-preview-box">${escapeHTML(messageContent).replace(/\n/g, '<br>')}</div>
                </div>
                <div class="wa-actions">
                    <div class="wa-actions-row">
                        <button class="wa-cancel-btn">Cancel</button>
                        <button class="wa-confirm-btn">Send Now</button>
                    </div>
                </div>
            </div>
        `;

        this.toggleOverlay(true, "WhatsApp Delivery", container);
        return container;
    },

    showEmailOverlay(emailAddress, subject, messageContent) {
        const escapeHTML = (str) => {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        const container = document.createElement('div');
        container.className = 'wa-overlay-container waai-email-overlay';
        
        container.innerHTML = `
            <div class="wa-header">
                <div class="wa-title">Email Forwarding</div>
                <div class="wa-recipient">Sending to: <strong>${escapeHTML(emailAddress)}</strong></div>
            </div>
            <div class="wa-content">
                <div class="wa-status-box">
                    <div class="wa-loader-wrapper">
                        <div class="wa-icon-prompt">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#5f39ff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                    </div>
                    <div class="wa-status-text">Would you like to send this email?</div>
                </div>
                <div class="wa-message-preview">
                    <div class="wa-preview-label">Subject: ${escapeHTML(subject)}</div>
                    <div class="wa-preview-box">${messageContent}</div>
                </div>
                <div class="wa-actions">
                    <div class="wa-actions-row">
                        <button class="wa-cancel-btn">Cancel</button>
                        <button class="wa-confirm-btn">Send Email</button>
                    </div>
                </div>
            </div>
        `;

        this.toggleOverlay(true, "Email Delivery", container);
        return container;
    }

};
