export const UIMixin = {
    render() {
        const styleLink = document.createElement('link');
        styleLink.setAttribute('rel', 'stylesheet');
        styleLink.setAttribute('href', this.getAttribute('stylesheet-path') || '/webassets-ai-assistant/ai-widget.css');

        const container = document.createElement('div');
        container.setAttribute('id', 'ai-widget-container');

        // Build dynamic header from waaiConfig
        const cfg = this.cfg;
        const avatarInitials = cfg.avatarInitials || 'AI';
        const widgetTitle = cfg.widgetTitle || 'AI Assistant';
        const widgetSubtitle = cfg.widgetSubtitle || 'Online';
        const suggestions = cfg.suggestions || [
            { label: '💻 Our Services', query: 'Tell me about your services' },
            { label: '📅 Book Consultation', query: 'How can I book a free consultation?' },
            { label: '📞 Contact Support', query: 'How to contact support?' },
        ];
        const whatsappNumber = cfg.whatsappNumber || '';
        const voiceLangs = cfg.voiceLangs || ['en-US'];

        // Build suggestion chips HTML
        const chipsHtml = suggestions.map(s =>
            `<button class="suggest-btn" data-query="${s.query}">${s.label}</button>`
        ).join('');

        // Build WhatsApp button HTML
        const whatsappHtml = whatsappNumber
            ? `<button id="whatsapp-btn" title="Continue on WhatsApp" aria-label="Open WhatsApp">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.102.537 4.073 1.477 5.793L.057 23.576l5.916-1.551A11.956 11.956 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.812 9.812 0 01-5.001-1.37l-.359-.214-3.712.974.991-3.604-.233-.371A9.817 9.817 0 012.182 12C2.182 6.58 6.58 2.182 12 2.182c5.42 0 9.818 4.398 9.818 9.818 0 5.42-4.398 9.818-9.818 9.818z"/>
                </svg>
               </button>`
            : '';

        // Build external floating WhatsApp button HTML (above the widget)
        const floatingWhatsappHtml = whatsappNumber
            ? `<a href="${whatsappNumber.startsWith('http') ? whatsappNumber : 'https://wa.me/' + whatsappNumber.replace(/[^0-9]/g, '')}" target="_blank" id="external-whatsapp-btn" class="waai-floating-whatsapp" aria-label="Chat on WhatsApp">
                <svg viewBox="0 0 24 24" fill="currentColor">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 2.102.537 4.073 1.477 5.793L.057 23.576l5.916-1.551A11.956 11.956 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818a9.812 9.812 0 01-5.001-1.37l-.359-.214-3.712.974.991-3.604-.233-.371A9.817 9.817 0 012.182 12C2.182 6.58 6.58 2.182 12 2.182c5.42 0 9.818 4.398 9.818 9.818 0 5.42-4.398 9.818-9.818 9.818z"/>
                </svg>
               </a>`
            : '';

        container.innerHTML = `
            <!-- Chat Trigger Button -->
            <div id="proactive-bubble" class="hidden"></div>
            ${floatingWhatsappHtml}
            <button id="chat-trigger" aria-label="Open AI Assistant">
                <!-- AI Centric Sparkles Icon -->
                <svg class="icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2c0 5.5 4.5 10 10 10-5.5 0-10 4.5-10 10 0-5.5-4.5-10-10-10 5.5 0 10-4.5 10-10Z"></path>
                    <path d="M18 16c0 2.2 1.8 4 4 4-2.2 0-4 1.8-4 4 0-2.2-1.8-4-4-4 2.2 0 4-1.8 4-4Z"></path>
                </svg>
                <!-- Voice Active Pulsing Waveform Icon -->
                <svg class="icon-voice hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"></path>
                    <path d="M19 10v1a7 7 0 0 1-14 0v-1"></path>
                    <line x1="12" y1="18" x2="12" y2="22"></line>
                </svg>
                <svg class="icon-close hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
                <span id="proactive-dot" class="hidden"></span>
            </button>

            <!-- Chat Window Panel -->
            <div id="chat-panel" class="hidden">
                <!-- Panel Header -->
                <div class="chat-header">
                    <div class="agent-info">
                        <div class="avatar">${avatarInitials}</div>
                        <div class="agent-details">
                            <h4>${widgetTitle}</h4>
                            <span class="status"><span class="dot"></span> ${widgetSubtitle}</span>
                        </div>
                    </div>
                    <div class="header-actions">
                        ${whatsappHtml}
                        <button id="clear-chat-btn" title="Clear chat history" aria-label="Clear chat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="1 4 1 10 7 10"></polyline>
                                <path d="M3.51 15a9 9 0 1 0 .49-3.51"></path>
                            </svg>
                        </button>
                        <button id="toggle-speech" title="Toggle voice read-out" aria-label="Toggle voice read-out">
                            <svg class="speaker-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <line x1="23" y1="9" x2="17" y2="15"></line>
                                <line x1="17" y1="9" x2="23" y2="15"></line>
                            </svg>
                            <svg class="speaker-on hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"></polygon>
                                <path d="M19.07 4.93a10 10 0 0 1 0 14.14M15.54 8.46a5 5 0 0 1 0 7.07"></path>
                            </svg>
                        </button>
                        <button id="close-panel" aria-label="Close Chat">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Voice Immersive Overlay (The Centerpiece Orb) -->
                <div id="voice-immersive-overlay" class="waai-voice-immersive">
                    <div class="waai-orb-container">
                        <div id="waai-orb" class="waai-orb state-listening"></div>
                    </div>
                    <div class="waai-voice-transcript">
                        <div id="waai-transcript-you" class="transcript-you">You: "..."</div>
                        <div id="waai-transcript-ai" class="transcript-ai">Listening...</div>
                    </div>
                </div>

                <!-- Messages Panel -->
                <div id="messages-body">
                    <!-- Dynamic Messages Inserted Here -->
                </div>

                <!-- Action Suggestions Overlay -->
                <div id="suggestions-area">
                    ${chipsHtml}
                </div>

                <!-- Embed / Form Panel Overlay (Hidden by Default) -->
                <div id="embed-overlay" class="hidden">
                    <div class="overlay-header">
                        <span id="overlay-title">Action Panel</span>
                        <button id="close-overlay" aria-label="Close panel">&times;</button>
                    </div>
                    <div id="overlay-content">
                        <!-- Dynamic iframe or Form goes here -->
                    </div>
                </div>

                <!-- Live Status Bar (Visible only when isContinuousVoiceMode is true) -->
                <div id="live-status-bar" class="hidden">
                    <div class="live-status-indicator">
                        <span class="live-dot"></span>
                        <span class="live-text">Live Call Active</span>
                    </div>
                    <div class="live-mini-visualizer">
                        <span class="bar bar1"></span>
                        <span class="bar bar2"></span>
                        <span class="bar bar3"></span>
                        <span class="bar bar4"></span>
                    </div>
                    ${this.buildLangToggleHtml()}
                    <button id="toggle-orb-btn" class="waai-orb-toggle" aria-label="Toggle Immersive Orb" title="Hide/Show AI Orb">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;margin-left:8px;cursor:pointer;">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>
                        </svg>
                    </button>
                </div>

                <!-- Chat Input Footer -->
                <div class="chat-footer">
                    <textarea id="chat-input" placeholder="Type a message..." rows="1" max-rows="4"></textarea>
                    <div class="footer-buttons">
                        ${this.recognition ? `
                        <button id="voice-call-toggle" title="Start Live Voice Call" aria-label="Start Live AI voice call" class="waai-voice-btn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 3v18M17 8v8M7 8v8M22 11v2M2 11v2"/>
                            </svg>
                        </button>
                        <button id="mic-btn" title="Speak into microphone" aria-label="Voice input">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 1a3 3 0 0 0-3 3v8a3 3 0 0 0 6 0V4a3 3 0 0 0-3-3z"></path>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"></path>
                                <line x1="12" y1="19" x2="12" y2="23"></line>
                                <line x1="8" y1="23" x2="16" y2="23"></line>
                            </svg>
                        </button>
                        ` : ''}
                        <button id="send-btn" title="Send message" aria-label="Send message">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="22" y1="2" x2="11" y2="13"></line>
                                <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.shadowRoot.appendChild(styleLink);
        this.shadowRoot.appendChild(container);
    },

    setupEventListeners() {
        const shadow = this.shadowRoot;
        const chatTrigger = shadow.getElementById('chat-trigger');
        const closePanel = shadow.getElementById('close-panel');
        const sendBtn = shadow.getElementById('send-btn');
        const chatInput = shadow.getElementById('chat-input');
        const micBtn = shadow.getElementById('mic-btn');
        const toggleSpeech = shadow.getElementById('toggle-speech');
        const closeOverlay = shadow.getElementById('close-overlay');
        const clearChatBtn = shadow.getElementById('clear-chat-btn');
        const whatsappBtn = shadow.getElementById('whatsapp-btn');
        const voiceCallToggleBtn = shadow.getElementById('voice-call-toggle');

        // Toggle Chat Window
        chatTrigger.addEventListener('click', () => {
            this.cancelProactiveTrigger();
            this.toggleChatPanel();
        });
        closePanel.addEventListener('click', () => this.toggleChatPanel(false));

        // Setup Lang Toggle listeners
        this.setupLangToggleListeners();

        // Toggle Orb Immersive Overlay
        const toggleOrbBtn = shadow.getElementById('toggle-orb-btn');
        if (toggleOrbBtn) {
            toggleOrbBtn.addEventListener('click', () => {
                const immersiveOverlay = shadow.getElementById('voice-immersive-overlay');
                if (immersiveOverlay) {
                    const isActive = immersiveOverlay.classList.toggle('active');
                    localStorage.setItem('waai_voice_overlay_hidden', !isActive ? 'true' : 'false');
                }
            });
        }

        // Close Action Overlay
        closeOverlay.addEventListener('click', () => this.toggleOverlay(false));

        // Clear Chat
        if (clearChatBtn) {
            clearChatBtn.addEventListener('click', () => this.clearChat());
        }

        // WhatsApp handoff
        if (whatsappBtn) {
            whatsappBtn.addEventListener('click', () => {
                const num = (this.cfg.whatsappNumber || '').replace(/\D/g, '');
                if (num) window.open(`https://wa.me/${num}?text=Hi%2C+I+was+chatting+with+your+AI+assistant.`, '_blank');
            });
        }

        // Textarea Autogrow and Enter key
        chatInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleUserSend();
            }
        });
        chatInput.addEventListener('input', () => {
            chatInput.style.height = 'auto';
            chatInput.style.height = (chatInput.scrollHeight - 10) + 'px';
        });

        // Click Suggestion Buttons (hide after first use)
        shadow.querySelectorAll('.suggest-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const query = btn.getAttribute('data-query');
                this.hideSuggestions();
                this.addMessage(query, 'user');

                // Intercept local UI intents before calling AI
                if (typeof this.interceptLocalIntents === 'function' && this.interceptLocalIntents(query)) {
                    return;
                }

                this.fetchAIResponse(query);
            });
        });

        // Click Send Button
        sendBtn.addEventListener('click', () => this.handleUserSend());

        // Toggle Speech Read-out
        toggleSpeech.addEventListener('click', () => {
            this.speechSynthesisActive = !this.speechSynthesisActive;
            const speakerOn = toggleSpeech.querySelector('.speaker-on');
            const speakerOff = toggleSpeech.querySelector('.speaker-off');
            if (this.speechSynthesisActive) {
                speakerOn.classList.remove('hidden');
                speakerOff.classList.add('hidden');
            } else {
                speakerOn.classList.add('hidden');
                speakerOff.classList.remove('hidden');
                this.cancelSpeaking();
            }
        });

        // Live Voice Call events
        if (voiceCallToggleBtn) {
            voiceCallToggleBtn.addEventListener('click', () => {
                if (this.isContinuousVoiceMode) {
                    this.endVoiceCall();
                } else {
                    this.startVoiceCall();
                }
            });
        }

        // Microphone Speech Recognition
        if (micBtn && this.recognition) {
            micBtn.addEventListener('click', () => {
                if (this.isContinuousVoiceMode) {
                    this.toggleVoiceMute();
                } else {
                    if (this.isListening) {
                        this.stopListening();
                    } else {
                        this.startListening();
                    }
                }
            });

            this.recognition.onstart = () => {
                if (this._isAndroidChrome) return; // Completely ignore Web Speech API onstart on Android since we use VAD

                this.isListening = true;

                if (this.isContinuousVoiceMode) {
                    if (this.assistantState === 'listening') {
                        this.updateVoiceCallUI('listening');
                    }
                } else {
                    micBtn.classList.add('listening');
                    chatInput.placeholder = "Listening...";
                    if (this.assistantState !== 'listening') {
                        this.transitionTo('listening');
                    }
                }
            };

            // ── Android Chrome Fix ────────────────────────────────────────────────────
            // Android Chrome's Web Speech API fires onend after every short silence
            // even with continuous:true. We detect Android and accumulate fragments
            // into a buffer, flushing only after 1200ms of silence.
            this._isAndroidChrome = (() => {
                const ua = navigator.userAgent || '';
                return /Android/i.test(ua) && /Chrome/i.test(ua) && !/Firefox|OPR|SamsungBrowser|Silk|YaBrowser/i.test(ua);
            })();

            if (this._isAndroidChrome) {
                this.waLog('[Android] Android Chrome detected — enabling speech fragment accumulation mode');
            }

            // Holds accumulated speech fragments on Android
            this._androidSpeechBuffer = '';
            this._androidSilenceTimer = null;
            this._androidBufferFlushing = false; // Prevents double-flush

            this.recognition.onresult = (event) => {
                if (this._isAndroidChrome) return; // Completely ignore Web Speech API onresult on Android since we use VAD

                if (this.isMuted) {
                    return; // Mic is muted, ignore results
                }
                if (this.isContinuousVoiceMode) {
                    // HARD DROP: If Walkie-Talkie mode is enabled, completely ignore any stray audio picked up
                    if (this.assistantState === 'speaking' && this.cfg && this.cfg.allowInterruptions === '0') {
                        return;
                    }

                    // Ignore speech transcriptions if the user is typing/writing in form fields or has text in the chat input
                    const activeEl = this.shadowRoot.activeElement;
                    const isTypingInLeadForm = activeEl && activeEl.id !== 'chat-input' && (activeEl.tagName === 'INPUT' || activeEl.tagName === 'TEXTAREA');
                    const chatInput = this.shadowRoot.getElementById('chat-input');
                    const hasChatInputText = chatInput && chatInput.value.trim() !== '';

                    if (isTypingInLeadForm || hasChatInputText) {
                        return; // User is manually typing, don't interrupt with voice
                    }
                }

                let speechToText = '';
                let isFinal = false;

                for (let i = event.resultIndex; i < event.results.length; i++) {
                    if (event.results[i].isFinal) {
                        isFinal = true;
                    }
                    speechToText += event.results[i][0].transcript;
                }

                if (this.isContinuousVoiceMode) {
                    // Update immersive UI transcript
                    const transcriptYou = this.shadowRoot.getElementById('waai-transcript-you');
                    if (transcriptYou && speechToText.trim()) {
                        transcriptYou.textContent = 'You: "' + speechToText + '"';
                    }

                    // If the AI is speaking (or just finished), check if the transcribed text is just an echo from the speakers
                    const timeSinceSpeech = Date.now() - this.aiSpeechEndTime;
                    const isPiperPhysicallyPlaying = !!this._piperIsPlaying;
                    const isBrowserPhysicallyPlaying = !!(window.speechSynthesis && window.speechSynthesis.speaking);
                    const isSarvamPhysicallyPlaying = !!this._sarvamIsPlaying;
                    // Also treat echoes within 1000ms of a Sarvam chunk ending as potential echoes due to speaker reverberation
                    const isSarvamRecentlyPlayed = (Date.now() - (this._sarvamLastChunkEndTime || 0)) < 1000;
                    const isPhysicallyTalking = isPiperPhysicallyPlaying || isBrowserPhysicallyPlaying || isSarvamPhysicallyPlaying || isSarvamRecentlyPlayed;

                    if (this.assistantState === 'speaking' || isPhysicallyTalking || (timeSinceSpeech < 2000)) {
                        const echoStatusCurrent = this.isSelfFeedback(speechToText, this.currentAISpokenText, isFinal);
                        const echoStatusLast = this.isSelfFeedback(speechToText, this.lastAISpokenText, isFinal);

                        let echoStatus = false;
                        if (echoStatusCurrent === 'confident_echo' || echoStatusLast === 'confident_echo') {
                            echoStatus = 'confident_echo';
                        } else if (echoStatusCurrent === 'too_short' || echoStatusLast === 'too_short') {
                            echoStatus = 'too_short';
                        }

                        if (echoStatus === 'confident_echo') {
                            return; // Ignore this echo completely
                        } else if (echoStatus === 'too_short') {
                            // Instant UI feedback to remove perceived latency
                            if (this.assistantState === 'speaking') {
                                this.updateVoiceCallUI('listening');
                            }
                            // Aggressively buffer interim words so nothing is lost if mic drops
                            if (!this.interruptionBuffer || speechToText.length > this.interruptionBuffer.length) {
                                this.interruptionBuffer = speechToText;
                            }
                            this.lastUserSpeechTime = Date.now();

                            // Don't taint the index, just ignore it for now and let the buffer grow
                            return;
                        }



                        // Real user interruption — stop AI immediately
                        this.lastUserSpeechTime = Date.now();

                        // Removed broken AI word-stripping safety net that prevented interruptions
                        if (!speechToText.trim()) {
                            return;
                        }

                        // Save the captured prefix in case Chrome implicitly aborts the microphone upon cancellation
                        if (!this.interruptionBuffer || speechToText.length > this.interruptionBuffer.length) {
                            this.interruptionBuffer = speechToText;
                        }
                        this.interruptionTime = Date.now();

                        // Emit via EventBus so state machine can react without direct coupling
                        if (this.EventBus) {
                            this.EventBus.emit('voice:interrupted', { speechToText });
                        } else {
                            this.transitionTo('interrupted');
                        }
                    } else {
                        // Not in echo-gate: state is 'listening' or post-cooldown.
                        // But if the state machine de-synced (e.g. between Sarvam chunks) and there
                        // is still physical audio OR the assistant state shows 'speaking', treat it as interruption.
                        if (speechToText.trim()) {
                            this.lastUserSpeechTime = Date.now();
                            if (this.assistantState === 'speaking' || isPhysicallyTalking) {
                                // State machine desync — fire interruption defensively
                                if (!this.interruptionBuffer || speechToText.length > this.interruptionBuffer.length) {
                                    this.interruptionBuffer = speechToText;
                                }
                                this.interruptionTime = Date.now();
                                if (this.EventBus) {
                                    this.EventBus.emit('voice:interrupted', { speechToText });
                                } else {
                                    this.transitionTo('interrupted');
                                }
                            }
                        }
                    }

                    // Only send final transcribed message to AI
                    if (isFinal) {
                        const finalTranscript = speechToText || this.interruptionBuffer;
                        this.interruptionBuffer = ''; // Clear buffer

                        // Check if a form is open and can be updated dynamically
                        const overlayForm = this.shadowRoot.getElementById('lead-overlay-form');
                        if (overlayForm && typeof this.updateOpenLeadForm === 'function') {
                            const details = this.extractPrefillDetails(finalTranscript);
                            if (details.name || details.email || details.phone || details.query) {
                                this.addMessage(finalTranscript, 'user');
                                const updated = this.updateOpenLeadForm(details);
                                if (updated) return; // Stop processing and don't send to AI
                            }
                        }

                        // Check if we are awaiting confirmation for details
                        if (this.awaitingLeadConfirmation) {
                            const lowerInput = finalTranscript.toLowerCase();
                            if (lowerInput.includes('confirm') || lowerInput.includes('yes') || lowerInput.includes('submit')) {
                                this.addMessage(finalTranscript, 'user');
                                this.submitVoiceLead();
                                return;
                            } else if (lowerInput.includes('no') || lowerInput.includes('cancel') || lowerInput.includes('change')) {
                                this.addMessage(finalTranscript, 'user');
                                this.awaitingLeadConfirmation = false;
                                this.pendingLeadPrefill = null;
                                this.accumulatedLeadDetails = { name: '', email: '', phone: '', query: '' };
                                const msg = "No problem. Please edit the details directly in the form on the screen and submit, or continue speaking.";
                                this.addMessage(msg, 'assistant');
                                this.speak(msg);
                                return;
                            }
                        }

                        // Otherwise, detect if the spoken text contains contact details
                        if (this.hasContactDetails(finalTranscript)) {
                            const details = this.extractPrefillDetails(finalTranscript);

                            // Accumulate details across multiple messages
                            if (details.name) this.accumulatedLeadDetails.name = details.name;
                            if (details.email) this.accumulatedLeadDetails.email = details.email;
                            if (details.phone) this.accumulatedLeadDetails.phone = details.phone;
                            if (details.query) this.accumulatedLeadDetails.query = details.query;

                            if (this.accumulatedLeadDetails.email || this.accumulatedLeadDetails.phone) {
                                this.addMessage(finalTranscript, 'user');

                                this.pendingLeadPrefill = {
                                    name: this.accumulatedLeadDetails.name || 'Voice User',
                                    email: this.accumulatedLeadDetails.email,
                                    phone: this.accumulatedLeadDetails.phone,
                                    query: this.accumulatedLeadDetails.query || 'Submitted via voice confirmation.'
                                };
                                this.awaitingLeadConfirmation = true;

                                // Show and prefill lead form
                                this.showLeadForm(this.pendingLeadPrefill);

                                const speakMsg = "I've opened the contact form and pre-filled the details I heard. Please check if the details on the screen are correct, or say 'Confirm details' to submit them.";
                                const chatMsg = `[Lead Form Pre-filled]<br><strong>Name:</strong> ${this.accumulatedLeadDetails.name || 'Not detected'}<br><strong>Email:</strong> ${this.accumulatedLeadDetails.email || 'Not detected'}<br><strong>Phone:</strong> ${this.accumulatedLeadDetails.phone || 'Not detected'}<br><strong>Inquiry:</strong> ${this.accumulatedLeadDetails.query || 'Not detected'}<br><br>Please verify details on the screen, edit if needed, and submit. Or say <strong>"Confirm details"</strong> to submit immediately.`;

                                this.addMessage(chatMsg, 'assistant');
                                this.speak(speakMsg);
                                return;
                            }
                        }

                        this.addMessage(finalTranscript, 'user');

                        // Intercept local UI intents before calling AI
                        if (typeof this.interceptLocalIntents === 'function' && this.interceptLocalIntents(finalTranscript)) {
                            return;
                        }

                        this.fetchAIResponse(finalTranscript);
                    }
                } else {
                    if (isFinal) {
                        chatInput.value = speechToText;
                        this.handleUserSend();
                    }
                }
            };

            // ── Shared transcript processor ──
            // Called by both the normal isFinal path and the Android silence-timer buffer.
            this._processVoiceTranscript = (speechToText, isFinal) => {
                if (!speechToText || !speechToText.trim()) return;
                const chatInput = this.shadowRoot.getElementById('chat-input');

                if (this.isContinuousVoiceMode) {
                    // Update transcript UI
                    const transcriptYou = this.shadowRoot.getElementById('waai-transcript-you');
                    if (transcriptYou && speechToText.trim()) {
                        transcriptYou.textContent = 'You: "' + speechToText + '"';
                    }

                    if (isFinal) {
                        const finalTranscript = speechToText || this.interruptionBuffer;
                        this.interruptionBuffer = '';
                        this._androidBufferFlushing = false;

                        const overlayForm = this.shadowRoot.getElementById('lead-overlay-form');
                        if (overlayForm && typeof this.updateOpenLeadForm === 'function') {
                            const details = this.extractPrefillDetails(finalTranscript);
                            if (details.name || details.email || details.phone || details.query) {
                                this.addMessage(finalTranscript, 'user');
                                const updated = this.updateOpenLeadForm(details);
                                if (updated) return;
                            }
                        }

                        if (this.awaitingLeadConfirmation) {
                            const lowerInput = finalTranscript.toLowerCase();
                            if (lowerInput.includes('confirm') || lowerInput.includes('yes') || lowerInput.includes('submit')) {
                                this.addMessage(finalTranscript, 'user');
                                this.submitVoiceLead();
                                return;
                            } else if (lowerInput.includes('no') || lowerInput.includes('cancel') || lowerInput.includes('change')) {
                                this.addMessage(finalTranscript, 'user');
                                this.awaitingLeadConfirmation = false;
                                this.pendingLeadPrefill = null;
                                this.accumulatedLeadDetails = { name: '', email: '', phone: '', query: '' };
                                const msg = "No problem. Please edit the details directly in the form on the screen and submit, or continue speaking.";
                                this.addMessage(msg, 'assistant');
                                this.speak(msg);
                                return;
                            }
                        }

                        if (this.hasContactDetails(finalTranscript)) {
                            const details = this.extractPrefillDetails(finalTranscript);
                            if (details.name) this.accumulatedLeadDetails.name = details.name;
                            if (details.email) this.accumulatedLeadDetails.email = details.email;
                            if (details.phone) this.accumulatedLeadDetails.phone = details.phone;
                            if (details.query) this.accumulatedLeadDetails.query = details.query;
                            if (this.accumulatedLeadDetails.email || this.accumulatedLeadDetails.phone) {
                                this.addMessage(finalTranscript, 'user');
                                this.pendingLeadPrefill = {
                                    name: this.accumulatedLeadDetails.name || 'Voice User',
                                    email: this.accumulatedLeadDetails.email,
                                    phone: this.accumulatedLeadDetails.phone,
                                    query: this.accumulatedLeadDetails.query || 'Submitted via voice confirmation.'
                                };
                                this.awaitingLeadConfirmation = true;
                                this.showLeadForm(this.pendingLeadPrefill);
                                const speakMsg = "I've opened the contact form and pre-filled the details I heard. Please check if the details on the screen are correct, or say 'Confirm details' to submit them.";
                                const chatMsg = `[Lead Form Pre-filled]<br><strong>Name:</strong> ${this.accumulatedLeadDetails.name || 'Not detected'}<br><strong>Email:</strong> ${this.accumulatedLeadDetails.email || 'Not detected'}<br><strong>Phone:</strong> ${this.accumulatedLeadDetails.phone || 'Not detected'}<br><strong>Inquiry:</strong> ${this.accumulatedLeadDetails.query || 'Not detected'}<br><br>Please verify details on the screen, edit if needed, and submit. Or say <strong>"Confirm details"</strong> to submit immediately.`;
                                this.addMessage(chatMsg, 'assistant');
                                this.speak(speakMsg);
                                return;
                            }
                        }

                        this.addMessage(finalTranscript, 'user');
                        if (typeof this.interceptLocalIntents === 'function' && this.interceptLocalIntents(finalTranscript)) {
                            return;
                        }
                        this.fetchAIResponse(finalTranscript);
                    }
                } else {
                    if (isFinal && chatInput) {
                        chatInput.value = speechToText;
                        this.handleUserSend();
                    }
                }
            };

            this.recognition.onerror = (event) => {
                if (this._isAndroidChrome) return; // Completely ignore Web Speech API errors on Android since we use VAD

                // ── FIX 2: Only clear the silence timer if the buffer is empty ──────────────
                // Error handling
                if (event.error === 'no-speech' || event.error === 'aborted') {
                    // Silent
                } else {
                    console.error("Speech recognition error:", event.error);
                    if (event.error === 'not-allowed') {
                        this.addMessage("⚠️ Microphone access was blocked. Please Refresh the page and allow the microphone access or Please ensure you have granted permission and are using a secure connection (HTTPS). The Web Speech API requires HTTPS to function on mobile devices.", 'assistant');
                        if (this.isContinuousVoiceMode) {
                            this.endVoiceCall();
                        }
                    }
                }
                if (this.isContinuousVoiceMode) {
                    const allowInterruptions = !this.cfg || this.cfg.allowInterruptions !== '0';
                    const shouldListenWhileSpeaking = this.assistantState === 'speaking' && allowInterruptions;
                    if (!this.isMuted && (this.assistantState === 'listening' || shouldListenWhileSpeaking)) {
                        const restartDelay = shouldListenWhileSpeaking ? 0 : 300;
                        setTimeout(() => {
                            if (this.isContinuousVoiceMode && !this.isMuted && (this.assistantState === 'listening' || (this.assistantState === 'speaking' && allowInterruptions))) {
                                try { this.recognition.start(); } catch (e) { }
                            }
                        }, restartDelay);
                    }
                } else {
                    this.transitionTo('idle');
                }
            };

            this.recognition.onend = () => {
                if (this._isAndroidChrome) return; // Completely ignore Web Speech API onend on Android since we use VAD

                // Non-Android (iOS/Desktop) path

                if (this.isContinuousVoiceMode) {
                    const allowInterruptions = !this.cfg || this.cfg.allowInterruptions !== '0';
                    const shouldListenWhileSpeaking = this.assistantState === 'speaking' && allowInterruptions;
                    if (!this.isMuted && (this.assistantState === 'listening' || shouldListenWhileSpeaking)) {
                        try { this.recognition.start(); } catch (e) { }
                    }
                } else {
                    if (this.assistantState === 'listening') {
                        this.transitionTo('idle');
                    }
                }
            };

        }
    },

    toggleChatPanel(forceState = null) {
        const shadow = this.shadowRoot;
        const panel = shadow.getElementById('chat-panel');
        const trigger = shadow.getElementById('chat-trigger');
        const iconChat = trigger.querySelector('.icon-chat');
        const iconClose = trigger.querySelector('.icon-close');
        const iconVoice = trigger.querySelector('.icon-voice');

        this.isOpen = forceState !== null ? forceState : !this.isOpen;
        sessionStorage.setItem('waai_chat_panel_open', this.isOpen ? 'true' : 'false');

        if (this.isOpen) {
            panel.classList.remove('hidden');
            iconChat.classList.add('hidden');
            if (iconVoice) iconVoice.classList.add('hidden');
            iconClose.classList.remove('hidden');
            trigger.classList.add('active');
            trigger.classList.remove('voice-active');

            // Don't auto-focus on mobile — it triggers the keyboard and hides the chat
            if (window.innerWidth > 480) {
                shadow.getElementById('chat-input').focus();
            }

            // Scroll to bottom
            const msgBody = shadow.getElementById('messages-body');
            msgBody.scrollTop = msgBody.scrollHeight;
        } else {
            panel.classList.add('hidden');
            iconClose.classList.add('hidden');
            trigger.classList.remove('active');

            if (this.isContinuousVoiceMode) {
                trigger.classList.add('voice-active');
                iconChat.classList.add('hidden');
                if (iconVoice) iconVoice.classList.remove('hidden');
            } else {
                trigger.classList.remove('voice-active');
                iconChat.classList.remove('hidden');
                if (iconVoice) iconVoice.classList.add('hidden');
            }

            // Stop talking if closed and not in continuous voice call
            if (!this.isContinuousVoiceMode) {
                if (window.speechSynthesis) {
                    window.speechSynthesis.cancel();
                    this.isSpeaking = false;
                }
            }
        }
    },

    toggleOverlay(forceState = null, title = "Action Panel", content = null) {
        const shadow = this.shadowRoot;
        const overlay = shadow.getElementById('embed-overlay');
        const overlayTitle = shadow.getElementById('overlay-title');
        const overlayContent = shadow.getElementById('overlay-content');

        const show = forceState !== null ? forceState : overlay.classList.contains('hidden');

        if (show) {
            overlayTitle.textContent = title;
            if (content) {
                overlayContent.innerHTML = '';
                overlayContent.appendChild(content);
            }
            overlay.classList.remove('hidden');
            // Notify other modules (e.g. stop TTS) via EventBus
            if (this.EventBus) {
                this.EventBus.emit('action:overlay:open', { title });
            }
        } else {
            overlay.classList.add('hidden');
            overlayContent.innerHTML = '';
            if (this.autoSubmitTimeout) {
                clearTimeout(this.autoSubmitTimeout);
                this.autoSubmitTimeout = null;
            }
        }
    },

    addWelcomeMessage() {
        const welcomeText = this.cfg.welcomeMessage ||
            'Hi! I am your AI Assistant. Ask me anything about our services, products, or pricing!';
        // Show typing indicator for 800ms before displaying the welcome message
        this.addWritingIndicator();
        setTimeout(() => {
            this.removeWritingIndicator();
            this.addMessage(welcomeText, 'assistant');
        }, 800);
    },

    hideSuggestions() {
        if (this.suggestionUsed) return;
        this.suggestionUsed = true;
        const sugArea = this.shadowRoot.getElementById('suggestions-area');
        if (sugArea) sugArea.style.display = 'none';
    },

    clearChat() {
        // Clear in-memory history
        this.chatHistory = [];
        this.suggestionUsed = false;
        // Clear localStorage
        try { localStorage.removeItem('waai_chat_history'); } catch (e) { }
        // Clear DOM messages
        const msgBody = this.shadowRoot.getElementById('messages-body');
        if (msgBody) msgBody.innerHTML = '';
        // Re-show suggestions
        const sugArea = this.shadowRoot.getElementById('suggestions-area');
        if (sugArea) sugArea.style.display = '';
        // Show welcome message again
        this.addWelcomeMessage();
    },

    saveHistoryToStorage() {
        try {
            localStorage.setItem('waai_chat_history', JSON.stringify(this.chatHistory.slice(-20)));
        } catch (e) { }
    },

    restoreHistoryFromStorage() {
        try {
            const saved = localStorage.getItem('waai_chat_history');
            if (!saved) return false;
            const parsed = JSON.parse(saved);
            if (!Array.isArray(parsed) || parsed.length === 0) return false;
            // Re-render each stored message (skipping hidden ones)
            parsed.forEach(msg => {
                if (msg.isHidden || (msg.content && msg.content.includes('[SYSTEM INSTRUCTION:'))) {
                    return;
                }
                const role = msg.role === 'user' ? 'user' : 'assistant';
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', role === 'user' ? 'user-message' : 'bot-message');
                const contentDiv = document.createElement('div');
                contentDiv.classList.add('message-content');
                contentDiv.innerHTML = this.formatMessageText(msg.content);
                messageDiv.appendChild(contentDiv);
                this.shadowRoot.getElementById('messages-body').appendChild(messageDiv);
            });
            this.chatHistory = parsed;
            this.suggestionUsed = true;
            const sugArea = this.shadowRoot.getElementById('suggestions-area');
            if (sugArea) sugArea.style.display = 'none';
            return true;
        } catch (e) { return false; }
    },

    startProactiveTrigger() {
        const delay = parseInt(this.cfg.proactiveDelay || 0);
        if (!delay || delay <= 0 || this.isOpen) return;
        this.proactiveTimer = setTimeout(() => {
            if (this.isOpen) return;
            const dot = this.shadowRoot.getElementById('proactive-dot');
            const bubble = this.shadowRoot.getElementById('proactive-bubble');
            if (dot) dot.classList.remove('hidden');
            if (bubble) {
                bubble.textContent = 'Hi! 👋 Need help? Chat with our AI assistant!';
                bubble.classList.remove('hidden');
                setTimeout(() => bubble.classList.add('hidden'), 6000);
            }
        }, delay * 1000);
    },

    cancelProactiveTrigger() {
        if (this.proactiveTimer) {
            clearTimeout(this.proactiveTimer);
            this.proactiveTimer = null;
        }
        const dot = this.shadowRoot.getElementById('proactive-dot');
        const bubble = this.shadowRoot.getElementById('proactive-bubble');
        if (dot) dot.classList.add('hidden');
        if (bubble) bubble.classList.add('hidden');
    },

    formatMessageText(text) {
        if (!text) return '';

        // Safely strip any action tags (SEND_WHATSAPP, SEND_EMAIL, SHOW_LEAD_FORM, SHOW_CALENDAR) as a fallback
        const whatsappRegex = /\[SEND_WHATSAPP:\s*(\+?\d+)\s*\|\s*([\s\S]*?)\]/gi;
        const emailRegex = /\[SEND_EMAIL:\s*([^\s\|]+)\s*\|\s*([^\|]+)\s*\|\s*([\s\S]*?)\]/gi;
        const leadFormRegex = /\[SHOW_LEAD_FORM\]/gi;
        const calendarRegex = /\[SHOW_CALENDAR\]/gi;

        text = text
            .replace(whatsappRegex, '')
            .replace(emailRegex, '')
            .replace(leadFormRegex, '')
            .replace(calendarRegex, '')
            .trim();

        if (!text) return '';

        // 1. Escape HTML to prevent injection
        let html = text
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        // 1a. Parse Custom Carousel Blocks
        html = html.replace(/(?:\n\s*)*\[CAROUSEL\]([\s\S]*?)\[\/CAROUSEL\](?:\n\s*)*/gi, (match, carouselContent) => {
            let cardsHtml = '';
            const cardRegex = /\[CARD\s+title="([^"]*)"\s+image="([^"]*)"\s+desc="([^"]*)"\]/gi;
            let cardMatch;
            while ((cardMatch = cardRegex.exec(carouselContent)) !== null) {
                cardsHtml += `<div class="waai-carousel-card"><img src="${cardMatch[2]}" alt="${cardMatch[1]}" onerror="this.style.display='none'"><div class="waai-carousel-card-body"><h4>${cardMatch[1]}</h4><p>${cardMatch[3]}</p></div></div>`;
            }
            if (!cardsHtml) return '';
            return `<div class="waai-carousel-container"><div class="waai-carousel-track">${cardsHtml}</div></div>`;
        });

        // 1b. Parse horizontal rules (---)
        html = html.replace(/^[ \t]*---[ \t]*$/gm, '<hr class="waai-chat-hr">');

        // 1b. Parse headers (### Header)
        html = html.replace(/^[ \t]*###[ \t]*(.+?)$/gm, '<h4 class="waai-chat-h4">$1</h4>');
        html = html.replace(/^[ \t]*###[ \t]*$/gm, ''); // Remove empty headings

        // 1c. Parse tables (| Column 1 | Column 2 |)
        const lines = html.split('\n');
        let inTable = false;
        let tableHtml = '';
        const newLines = [];

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i].trim();
            if (line.startsWith('|') && line.endsWith('|')) {
                if (!inTable) {
                    inTable = true;
                    tableHtml = '<div class="waai-table-wrapper"><table class="waai-chat-table">';
                }
                const cells = line.split('|').map(c => c.trim()).filter((c, idx, arr) => idx > 0 && idx < arr.length - 1);
                if (line.match(/^\|[\s\-:|]+$/)) {
                    continue; // Skip separator line
                }
                const isHeader = !tableHtml.includes('</th>') && !tableHtml.includes('</td>');
                let trContent = '';
                cells.forEach(cell => {
                    const cellTag = isHeader ? 'th' : 'td';
                    trContent += `<${cellTag}>${cell}</${cellTag}>`;
                });
                tableHtml += '<tr>' + trContent + '</tr>';
            } else {
                if (inTable) {
                    inTable = false;
                    tableHtml += '</table></div>';
                    newLines.push(tableHtml);
                    tableHtml = '';
                }
                newLines.push(lines[i]);
            }
        }
        if (inTable) {
            tableHtml += '</table></div>';
            newLines.push(tableHtml);
        }
        html = newLines.join('\n');

        // 2. Split inline numbered lists (e.g., " 1. ", " 2. ")
        html = html.replace(/(?:\s+|\n|^)(\d+)\.\s/g, (match, num, offset) => {
            return (offset === 0 ? '' : '<br>') + '<strong>' + num + '.</strong> ';
        });

        // 3. Split inline bulleted lists (e.g., " - ", " • ", " * ")
        html = html.replace(/(?:\s+|\n|^)([•\-\*])\s+/g, (match, bullet, offset) => {
            return (offset === 0 ? '' : '<br>') + '&bull; ';
        });

        // 4. Split call-to-action transition / questions (e.g., "Would you...", "Do you...")
        html = html.replace(/(\.|\))\s+(Would you|Do you|Let me|Please|If you|Can I)\b/g, '$1<br><br>$2');

        // 5. Basic markdown bold & italics
        html = html
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/__(.*?)__/g, '<strong>$1</strong>')
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/_(.*?)_/g, '<em>$1</em>');

        // 6. Markdown links [Text](url)
        html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>');

        // 7. Convert remaining literal newlines to <br>
        html = html.replace(/\n/g, '<br>');

        // 8. Auto-link raw URLs without double-wrapping existing ones
        const htmlTagOrUrlRegex = /(<a\s+[^>]*>[\s\S]*?<\/a>|<[^>]+>)|(\bhttps?:\/\/[^\s<>\(\)]+(?:\([^\s<>\(\)]+\))?[^\s<>\(\)\.,!\?;:]*)/gi;
        html = html.replace(htmlTagOrUrlRegex, (match, g1, g2) => {
            if (g1) return g1;
            return `<a href="${g2}" target="_blank" rel="noopener noreferrer">${g2}</a>`;
        });

        // 9. Clean up excess consecutive line breaks
        html = html.replace(/(?:<br>\s*){3,}/g, '<br><br>');

        return html;
    },

    addMessage(text, sender, rawText = null) {
        const shadow = this.shadowRoot;
        const messagesBody = shadow.getElementById('messages-body');

        // Hide suggestion chips after first real user message
        if (sender === 'user') this.hideSuggestions();

        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');

        const contentDiv = document.createElement('div');
        contentDiv.classList.add('message-content');

        // Apply advanced formatting
        let formattedText = this.formatMessageText(text);

        contentDiv.innerHTML = formattedText;
        messageDiv.appendChild(contentDiv);

        messagesBody.appendChild(messageDiv);

        // Auto-hide immersive overlay if the bot sends a carousel
        if (sender !== 'user' && text && text.includes('[CAROUSEL]')) {
            const immersiveOverlay = shadow.getElementById('voice-immersive-overlay');
            if (immersiveOverlay && immersiveOverlay.classList.contains('active')) {
                immersiveOverlay.classList.remove('active');
            }
        }

        // Save to chat history state
        this.chatHistory.push({ role: sender === 'user' ? 'user' : 'assistant', content: rawText || text });

        // Limit history to last 20 messages for token efficiency
        if (this.chatHistory.length > 20) {
            this.chatHistory.shift();
        }

        // Persist to localStorage
        this.saveHistoryToStorage();

        // Scroll to bottom
        messagesBody.scrollTop = messagesBody.scrollHeight;
    },

    addWritingIndicator() {
        const shadow = this.shadowRoot;
        const messagesBody = shadow.getElementById('messages-body');

        const funFacts = this.cfg.loadingTips && this.cfg.loadingTips.length > 0
            ? this.cfg.loadingTips
            : [
                "A WhatsApp button on your site can increase customer inquiries by up to 300%.",
                "Our custom websites include tailored UI/UX design and AI automated workflows.",
                "Dynamic websites with editable dashboards make content updates quick and code-free.",
                "Did you know? Page load speed is one of Google's main search ranking factors.",
                "WebAssets offers premium cloud web hosting from just ₹1,500/year.",
                "We build local SEO strategies specifically tailored for Kashmir businesses.",
                "All our custom packages come with 3 months of free dedicated support."
            ];

        let stepIdx = 0;
        let factIdx = Math.floor(Math.random() * funFacts.length);
        const initialFact = funFacts[factIdx];

        const indicator = document.createElement('div');
        indicator.setAttribute('id', 'writing-indicator');
        indicator.classList.add('message', 'bot-message');
        indicator.innerHTML = `
            <div class="message-content waai-dynamic-indicator">
                <div class="waai-thinking-header">
                    <div class="indicator-bubbles">
                        <span class="bubble"></span>
                        <span class="bubble"></span>
                        <span class="bubble"></span>
                    </div>
                    <span class="waai-thinking-status" id="waai-thinking-status">Analyzing your request...</span>
                </div>
                <div class="waai-thinking-tip" id="waai-thinking-tip">
                    <span class="waai-tip-emoji">💡</span>
                    <span class="waai-tip-text" id="waai-tip-text">WebAssets Tip: ${initialFact}</span>
                </div>
            </div>
        `;
        messagesBody.appendChild(indicator);
        messagesBody.scrollTop = messagesBody.scrollHeight;

        const thinkingSteps = (this.cfg.thinkingSteps && this.cfg.thinkingSteps.length > 0)
            ? this.cfg.thinkingSteps
            : [
                "🔍 Analyzing your request...",
                "🧠 Querying knowledge base...",
                "📂 Checking company services...",
                "⚙️ Formulating response...",
                "✍️ Finalizing reply..."
            ];

        if (this.writingIndicatorInterval) {
            clearInterval(this.writingIndicatorInterval);
        }

        const updateIndicator = () => {
            const statusEl = shadow.getElementById('waai-thinking-status');
            const tipTextEl = shadow.getElementById('waai-tip-text');
            const tipEl = shadow.getElementById('waai-thinking-tip');

            if (!statusEl) return;

            stepIdx = (stepIdx + 1) % thinkingSteps.length;
            statusEl.textContent = thinkingSteps[stepIdx];

            if (stepIdx % 2 === 0 && tipTextEl && tipEl) {
                tipEl.style.opacity = '0';
                setTimeout(() => {
                    const statusCheck = shadow.getElementById('waai-thinking-status');
                    if (!statusCheck) return; // indicator was removed in the meantime
                    factIdx = (factIdx + 1) % funFacts.length;
                    tipTextEl.textContent = "WebAssets Tip: " + funFacts[factIdx];
                    tipEl.style.opacity = '1';
                }, 200);
            }
        };

        this.writingIndicatorInterval = setInterval(updateIndicator, 1500);
    },

    removeWritingIndicator() {
        if (this.writingIndicatorInterval) {
            clearInterval(this.writingIndicatorInterval);
            this.writingIndicatorInterval = null;
        }
        const shadow = this.shadowRoot;
        const indicator = shadow.getElementById('writing-indicator');
        if (indicator) {
            indicator.remove();
        }
    },

    handleUserSend() {
        const shadow = this.shadowRoot;
        const chatInput = shadow.getElementById('chat-input');
        const query = chatInput.value.trim();

        if (query === '') return;

        // Display user message
        this.addMessage(query, 'user');

        // Clear input field
        chatInput.value = '';
        chatInput.style.height = 'auto';

        // Blur input so voice recognition can resume correctly without being ignored
        if (this.isContinuousVoiceMode) {
            chatInput.blur();
        }

        // Check if a form is open and can be updated
        const overlayForm = this.shadowRoot.getElementById('lead-overlay-form');
        if (overlayForm && typeof this.updateOpenLeadForm === 'function') {
            const details = this.extractPrefillDetails(query);
            if (details.name || details.email || details.phone || details.query) {
                const updated = this.updateOpenLeadForm(details);
                if (updated) return; // Stop processing and don't send to AI
            }
        }

        // Stop speech
        this.cancelSpeaking();

        // Intercept local UI intents (calendar, lead form) before calling AI
        if (typeof this.interceptLocalIntents === 'function' && this.interceptLocalIntents(query)) {
            return;
        }

        // Fetch response
        this.fetchAIResponse(query);
    },

};
