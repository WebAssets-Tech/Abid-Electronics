/**
 * Actions Module
 * Handles API fetches for specific third-party actions (WhatsApp, Email).
 */

export const ActionsMixin = {
    processActionRequest(action) {
        if (!action || !action.type) return;

        this.waLog(`[Action Layer] Processing action request:`, action);

        // Validate the action type
        const allowedTypes = ['whatsapp', 'email', 'lead_form', 'calendar'];
        if (!allowedTypes.includes(action.type)) {
            this.waError(`[Action Layer] Invalid action type: ${action.type}`);
            return;
        }

        switch (action.type) {
            case 'whatsapp':
                this.handleWhatsAppAction(action.params);
                break;
            case 'email':
                this.handleEmailAction(action.params);
                break;
            case 'lead_form':
                this.handleLeadFormAction(action.params);
                break;
            case 'calendar':
                this.handleCalendarAction(action.params);
                break;
        }
    },

    handleWhatsAppAction(params) {
        const to = params.to || '';
        const message = params.message || '';

        this.waLog(`[Action Layer] Validating WhatsApp request: to=${to}`);

        if (!this.isValidPhone(to)) {
            this.waWarn(`[Action Layer] WhatsApp failed validation: Invalid phone number '${to}'`);
            this.addMessage(`⚠️ Tried to prepare WhatsApp forward, but the phone number format is invalid (+${to}). Please provide your country code and phone number.`, 'assistant');
            return;
        }

        if (this.isEmptyString(message)) {
            this.waWarn(`[Action Layer] WhatsApp failed validation: Empty message content`);
            return;
        }

        if (!this.isWithinMaxLength(message, 1000)) {
            this.waWarn(`[Action Layer] WhatsApp failed validation: Message exceeds max length`);
            this.addMessage(`⚠️ Tried to prepare WhatsApp forward, but the message is too long.`, 'assistant');
            return;
        }

        if (this.containsBlockedWords(message) || !this.isCompanyAllowedContent(message)) {
            this.waWarn(`[Action Layer] WhatsApp failed validation: Unsafe or disallowed content detected`);
            this.addMessage(`⚠️ Tried to prepare WhatsApp forward, but the content failed safety checks.`, 'assistant');
            return;
        }

        this.waLog(`[Action Layer] Executing WhatsApp action confirmation overlay`);
        this.sendWhatsAppMessage(to, message);
    },

    handleEmailAction(params) {
        const to = params.to || '';
        const subject = params.subject || 'Information from WebAssets';
        const message = params.message || '';

        this.waLog(`[Action Layer] Validating Email request: to=${to}`);

        if (!this.isValidEmail(to)) {
            this.waWarn(`[Action Layer] Email failed validation: Invalid email address '${to}'`);
            this.addMessage(`⚠️ Tried to prepare Email forward, but the email address is invalid (${to}). Please provide a valid email address.`, 'assistant');
            return;
        }

        if (this.isEmptyString(message)) {
            this.waWarn(`[Action Layer] Email failed validation: Empty message content`);
            return;
        }

        if (!this.isWithinMaxLength(message, 2000)) {
            this.waWarn(`[Action Layer] Email failed validation: Message exceeds max length`);
            this.addMessage(`⚠️ Tried to prepare Email forward, but the message is too long.`, 'assistant');
            return;
        }

        if (this.containsBlockedWords(message) || !this.isCompanyAllowedContent(message)) {
            this.waWarn(`[Action Layer] Email failed validation: Unsafe or disallowed content detected`);
            this.addMessage(`⚠️ Tried to prepare Email forward, but the content failed safety checks.`, 'assistant');
            return;
        }

        if (!this.isWithinMaxLength(subject, 150) || this.containsBlockedWords(subject)) {
            this.waWarn(`[Action Layer] Email failed validation: Subject unsafe or too long`);
            this.addMessage(`⚠️ Tried to prepare Email forward, but the subject failed safety checks.`, 'assistant');
            return;
        }

        this.waLog(`[Action Layer] Executing Email action confirmation overlay`);
        this.sendEmailMessage(to, subject, message);
    },

    handleLeadFormAction(params) {
        this.waLog(`[Action Layer] Executing Lead Form action`);
        if (typeof this.showLeadForm === 'function') {
            this.showLeadForm();
        } else {
            this.waError(`[Action Layer] showLeadForm method is missing or not bound`);
        }
    },

    handleCalendarAction(params) {
        this.waLog(`[Action Layer] Executing Calendar action`);
        if (typeof this.showCalendarEmbed === 'function') {
            this.showCalendarEmbed();
        } else {
            this.waError(`[Action Layer] showCalendarEmbed method is missing or not bound`);
        }
    },

    sendWhatsAppMessage(phoneNumber, messageContent) {
        const container = this.showWhatsAppOverlay(phoneNumber, messageContent);

        const loaderWrapper = container.querySelector('.wa-loader-wrapper');
        const statusText = container.querySelector('.wa-status-text');
        const actionsContainer = container.querySelector('.wa-actions');
        const cancelBtn = container.querySelector('.wa-cancel-btn');
        const confirmBtn = container.querySelector('.wa-confirm-btn');

        if(cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.toggleOverlay(false);
            });
        }

        if(confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                // Transition to loading/sending UI
                loaderWrapper.innerHTML = `<div class="wa-spinner"></div>`;
                statusText.className = 'wa-status-text';
                statusText.textContent = 'Crafting and sending message...';
                actionsContainer.innerHTML = ''; // Clear action buttons

                const body = { action: 'whatsapp', to: phoneNumber, message: messageContent };

                fetch(this.whatsappEndpoint, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-WAAI-Nonce': this.csrfToken || ''
                    },
                    body: JSON.stringify(body)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loaderWrapper.innerHTML = `
                            <div class="wa-icon-success">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#20d9a1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        `;
                        statusText.className = 'wa-status-text success';
                        statusText.textContent = 'Message has been sent successfully!';
                        
                        actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                        actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                            this.toggleOverlay(false);
                        });

                        const successMsg = `✅ Sent details to WhatsApp successfully!`;
                        this.addMessage(successMsg, 'assistant');
                        if (this.speechSynthesisActive || this.isContinuousVoiceMode) {
                            if(this.speak) this.speak("I have successfully sent the details to your WhatsApp.");
                        }
                    } else {
                        if(this.waError) this.waError("Server reported failure:", data.error);
                        loaderWrapper.innerHTML = `
                            <div class="wa-icon-error">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#ff453a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </div>
                        `;
                        statusText.className = 'wa-status-text error';
                        statusText.textContent = `Failed to send: ${data.error || 'Unknown error'}`;

                        actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                        actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                            this.toggleOverlay(false);
                        });

                        const errMsg = `❌ Failed to send WhatsApp: ${data.error || 'Unknown error'}`;
                        this.addMessage(errMsg, 'assistant');
                    }
                })
                .catch(err => {
                    if(this.waError) this.waError("Network-level error during fetch request:", err);
                    loaderWrapper.innerHTML = `
                        <div class="wa-icon-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ff453a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </div>
                    `;
                    statusText.className = 'wa-status-text error';
                    statusText.textContent = 'Connection failed. Could not send message.';

                    actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                    actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                        this.toggleOverlay(false);
                    });

                    const errMsg = "❌ Connection failed. Could not send WhatsApp message.";
                    this.addMessage(errMsg, 'assistant');
                });
            });
        }
    },

    sendEmailMessage(emailAddress, subject, messageContent) {
        const container = this.showEmailOverlay(emailAddress, subject, messageContent);

        const loaderWrapper = container.querySelector('.wa-loader-wrapper');
        const statusText = container.querySelector('.wa-status-text');
        const actionsContainer = container.querySelector('.wa-actions');
        const cancelBtn = container.querySelector('.wa-cancel-btn');
        const confirmBtn = container.querySelector('.wa-confirm-btn');

        if(cancelBtn) {
            cancelBtn.addEventListener('click', () => {
                this.toggleOverlay(false);
            });
        }

        if(confirmBtn) {
            confirmBtn.addEventListener('click', () => {
                // Transition to loading/sending UI
                loaderWrapper.innerHTML = `<div class="wa-spinner"></div>`;
                statusText.className = 'wa-status-text';
                statusText.textContent = 'Crafting and sending email...';
                actionsContainer.innerHTML = '';

                const body = { action: 'email', to: emailAddress, subject: subject, message: messageContent };

                fetch(this.proxyEndpoint, {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-WAAI-Nonce': this.csrfToken || ''
                    },
                    body: JSON.stringify(body)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        loaderWrapper.innerHTML = `
                            <div class="wa-icon-success">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#20d9a1" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                        `;
                        statusText.className = 'wa-status-text success';
                        statusText.textContent = 'Email has been sent successfully!';
                        
                        actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                        actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                            this.toggleOverlay(false);
                        });

                        const successMsg = `✅ Sent details to Email successfully!`;
                        this.addMessage(successMsg, 'assistant');
                        if (this.speechSynthesisActive || this.isContinuousVoiceMode) {
                            if(this.speak) this.speak("I have successfully sent the details to your Email.");
                        }
                    } else {
                        if(this.waError) this.waError("Server reported failure:", data.error);
                        loaderWrapper.innerHTML = `
                            <div class="wa-icon-error">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#ff453a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </div>
                        `;
                        statusText.className = 'wa-status-text error';
                        statusText.textContent = `Failed to send: ${data.error || 'Unknown error'}`;

                        actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                        actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                            this.toggleOverlay(false);
                        });

                        const errMsg = `❌ Failed to send Email: ${data.error || 'Unknown error'}`;
                        this.addMessage(errMsg, 'assistant');
                    }
                })
                .catch(err => {
                    if(this.waError) this.waError("Network-level error during fetch request:", err);
                    loaderWrapper.innerHTML = `
                        <div class="wa-icon-error">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#ff453a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="wa-icon-svg">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </div>
                    `;
                    statusText.className = 'wa-status-text error';
                    statusText.textContent = 'Connection failed. Could not send email.';

                    actionsContainer.innerHTML = `<button class="wa-close-btn">Close Panel</button>`;
                    actionsContainer.querySelector('.wa-close-btn').addEventListener('click', () => {
                        this.toggleOverlay(false);
                    });

                    const errMsg = "❌ Connection failed. Could not send Email.";
                    this.addMessage(errMsg, 'assistant');
                });
            });
        }
    }
};
