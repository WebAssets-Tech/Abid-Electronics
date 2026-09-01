function levenshtein(a, b) {
    const matrix = [];
    for (let i = 0; i <= b.length; i++) matrix[i] = [i];
    for (let j = 0; j <= a.length; j++) matrix[0][j] = j;
    for (let i = 1; i <= b.length; i++) {
        for (let j = 1; j <= a.length; j++) {
            if (b.charAt(i - 1) === a.charAt(j - 1)) {
                matrix[i][j] = matrix[i - 1][j - 1];
            } else {
                matrix[i][j] = Math.min(
                    matrix[i - 1][j - 1] + 1, // substitution
                    matrix[i][j - 1] + 1,     // insertion
                    matrix[i - 1][j] + 1      // deletion
                );
            }
        }
    }
    return matrix[b.length][a.length];
}

function getFuzzyPrefixMatch(userSolid, aiSolid) {
    if (userSolid.length > 10 && aiSolid.includes(userSolid)) return true;

    const len = userSolid.length;
    if (len < 4) return false;

    for (let i = Math.max(4, len - 2); i <= Math.min(aiSolid.length, len + 2); i++) {
        const prefix = aiSolid.substring(0, i);
        const dist = levenshtein(userSolid, prefix);
        const maxLen = Math.max(userSolid.length, prefix.length);
        const similarity = 1 - (dist / maxLen);
        if (similarity >= 0.80) {
            return true;
        }
    }
    return false;
}

class VADManager {
    constructor(assistant) {
        this.assistant = assistant;
        this.stream = null;
        this.audioContext = null;
        this.analyser = null;
        this.microphone = null;
        this.mediaRecorder = null;
        this.audioChunks = [];
        this.isRecording = false;
        this.isSpeaking = false;
        
        this.silenceTimer = null;
        this.animationFrame = null;
        
        // Configuration
        this.threshold = 0.08; // Adjust based on mic sensitivity (0.0 to 1.0)
        this.silenceDelay = 1500; // ms of silence before sending chunk
    }

    async start() {
        if (this.stream) return;

        try {
            this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
            this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            this.analyser = this.audioContext.createAnalyser();
            this.analyser.fftSize = 512;
            this.analyser.smoothingTimeConstant = 0.5;

            this.microphone = this.audioContext.createMediaStreamSource(this.stream);
            this.microphone.connect(this.analyser);

            this.monitorAudio();
        } catch (err) {
            this.assistant.waLog('[VAD] Failed to start microphone:', err);
        }
    }

    stop() {
        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
        this.animationFrame = null;
        if (this.silenceTimer) clearTimeout(this.silenceTimer);
        this.silenceTimer = null;
        
        if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
            try { this.mediaRecorder.stop(); } catch(e) {}
        }

        if (this.stream) {
            this.stream.getTracks().forEach(track => track.stop());
            this.stream = null;
        }

        if (this.audioContext) {
            try { this.audioContext.close(); } catch(e) {}
            this.audioContext = null;
        }

        this.isRecording = false;
        this.isSpeaking = false;
        this.isPaused = false;
    }

    // Pause monitoring without destroying the mic stream.
    // Call this when the state machine moves away from listening temporarily.
    pause() {
        if (this.isPaused) return;
        this.isPaused = true;
        if (this.animationFrame) cancelAnimationFrame(this.animationFrame);
        this.animationFrame = null;
        if (this.silenceTimer) clearTimeout(this.silenceTimer);
        this.silenceTimer = null;
        if (this.mediaRecorder && this.mediaRecorder.state === 'recording') {
            try { this.mediaRecorder.stop(); } catch(e) {}
        }
        this.isRecording = false;
        this.isSpeaking = false;
    }

    // Resume monitoring using the existing mic stream (no new getUserMedia).
    resume() {
        if (!this.isPaused || !this.stream) {
            // Stream was stopped — full restart required
            this.start();
            return;
        }
        this.isPaused = false;
        this.monitorAudio();
    }

    monitorAudio() {
        if (!this.analyser) return;

        const pcmData = new Float32Array(this.analyser.fftSize);
        
        const checkAudio = () => {
            this.analyser.getFloatTimeDomainData(pcmData);
            
            // Calculate RMS (Root Mean Square) for volume
            let sumSquares = 0.0;
            for (let i = 0; i < pcmData.length; i++) {
                sumSquares += pcmData[i] * pcmData[i];
            }
            const rms = Math.sqrt(sumSquares / pcmData.length);

            // Is the assistant speaking? Allow recording to capture interruptions,
            // but only start a recording chunk if interruptions are allowed.
            const isAssistantSpeaking = this.assistant.assistantState === 'speaking';
            const allowInterruptions = !this.assistant.cfg || this.assistant.cfg.allowInterruptions !== '0';
            // If muted entirely, ignore all audio
            if (this.assistant.isMuted) {
                this.animationFrame = requestAnimationFrame(checkAudio);
                return;
            }

            if (rms > this.threshold) {
                if (isAssistantSpeaking && allowInterruptions) {
                    // User is interrupting — signal the EventBus and start recording
                    if (!this.isRecording) {
                        this.startRecording();
                    }
                    // Cancel the silence timer while the user is speaking
                    if (this.silenceTimer) {
                        clearTimeout(this.silenceTimer);
                        this.silenceTimer = null;
                    }
                    if (!this.isSpeaking) {
                        this.isSpeaking = true;
                        // Signal interruption
                        if (this.assistant.EventBus) {
                            this.assistant.EventBus.emit('voice:interrupted', { speechToText: '' });
                        } else {
                            this.assistant.transitionTo('interrupted');
                        }
                    }
                } else if (!isAssistantSpeaking || !allowInterruptions) {
                    // Normal listening path
                    if (!this.isSpeaking) {
                        this.isSpeaking = true;
                        this.startRecording();
                    }
                    if (this.silenceTimer) {
                        clearTimeout(this.silenceTimer);
                        this.silenceTimer = null;
                    }
                }
            } else {
                // Silence detected
                if (this.isSpeaking && !this.silenceTimer) {
                    // Start counting silence
                    this.silenceTimer = setTimeout(() => {
                        this.isSpeaking = false;
                        this.stopRecordingAndSend();
                        this.silenceTimer = null;
                    }, this.silenceDelay);
                }
            }

            this.animationFrame = requestAnimationFrame(checkAudio);
        };

        checkAudio();
    }

    startRecording() {
        if (this.isRecording || !this.stream) return;
        
        this.audioChunks = [];
        try {
            // Prefer webm/opus for fast STT, fallback to mp4
            let mimeType = 'audio/webm;codecs=opus';
            if (!MediaRecorder.isTypeSupported(mimeType)) {
                mimeType = 'audio/mp4';
            }
            this.mediaRecorder = new MediaRecorder(this.stream, { mimeType });
        } catch (e) {
            this.mediaRecorder = new MediaRecorder(this.stream); // default
        }

        this.mediaRecorder.ondataavailable = (event) => {
            if (event.data.size > 0) {
                this.audioChunks.push(event.data);
            }
        };

        this.mediaRecorder.onstop = () => {
            if (this.audioChunks.length > 0) {
                const blob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType });
                this.assistant._transcribeAudioBlob(blob);
                this.audioChunks = [];
            }
        };

        this.mediaRecorder.start();
        this.isRecording = true;
        
        // Update UI
        if (this.assistant.assistantState !== 'listening') {
            this.assistant.transitionTo('listening');
        }
    }

    stopRecordingAndSend() {
        if (!this.isRecording || !this.mediaRecorder) return;
        if (this.mediaRecorder.state !== 'inactive') {
            this.mediaRecorder.stop(); // Triggers onstop which sends the blob
        }
        this.isRecording = false;
    }
}

export const VoiceMixin = {
    cancelSpeaking() {
        this._speechGenerationId = (this._speechGenerationId || 0) + 1;
        // Only cancel browser TTS if it's actually speaking to avoid implicitly aborting the microphone on Chrome
        if (window.speechSynthesis && window.speechSynthesis.speaking) {
            window.speechSynthesis.cancel();
        }
        // Cancel the TTS queue to prevent pending chunks from playing
        if (this.ttsQueue) {
            this.ttsQueue.cancel();
        }
        if (this.elevenLabsAudio) {
            try { this.elevenLabsAudio.pause(); } catch (e) { }
            this.elevenLabsAudio = null;
        }
        this.cancelGroqTts();
        this.cancelPiperTts();
        this.cancelSarvamTts();
        if (this.currentAISpokenText) {
            this.lastAISpokenText = this.currentAISpokenText;
        }
        this.currentAISpokenText = '';
        this.aiSpeechEndTime = Date.now();
        this.isSpeaking = false;
    },

    async _transcribeAudioBlob(blob) {
        this.waLog('[VAD] Sending audio blob for STT...', blob.size, 'bytes');
        this.updateVoiceCallUI('processing');

        const formData = new FormData();
        formData.append('action', 'stt');
        formData.append('audio', blob, 'recording.webm');

        try {
            const url = (this.cfg && this.cfg.proxyUrl) ? this.cfg.proxyUrl : '/wp-content/themes/WebAssets/webassets-ai-assistant/ai-proxy.php';
            const headers = {};
            if (this.cfg && this.cfg.nonce) {
                headers['X-WAAI-Nonce'] = this.cfg.nonce;
            }

            const response = await fetch(url, {
                method: 'POST',
                headers: headers,
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                if (data.text) {
                    this.waLog('[VAD] STT Result:', data.text);
                    // Inject the transcribed text directly into the chat flow
                    this._processVoiceTranscript(data.text, true);
                } else {
                    // Filtered out as hallucination/silence
                    this.waLog('[VAD] STT returned empty/silence. Resuming listening.');
                    this.transitionTo('listening');
                }
            } else {
                this.waLog('ERROR', 'STT Failed:', data.error);
                this.transitionTo('listening'); // Fallback to listening
            }
        } catch (err) {
            this.waLog('ERROR', 'STT Network Error:', err);
            this.transitionTo('listening');
        }
    },

    startListening() {
        if (this.isMuted && this.isContinuousVoiceMode) {
            return;
        }

        // --- Android: Use VADManager ---
        if (this._isAndroidChrome) {
            if (!this._vadManager) {
                this._vadManager = new VADManager(this);
                this._vadManager.start();
            } else {
                // Resume existing VAD (no new getUserMedia, no beep)
                this._vadManager.resume();
            }
            return;
        }

        // --- Non-Android: Use Web Speech API ---
        if (this.recognition) {
            if (!this.isContinuousVoiceMode) {
                this.cancelSpeaking();
            }
            try {
                this.recognition.start();
            } catch (e) {
                // already running
            }
        }
    },

    stopListening() {
        const shadow = this.shadowRoot;
        const micBtn = shadow.getElementById('mic-btn');
        const chatInput = shadow.getElementById('chat-input');

        this.isListening = false;
        if (micBtn) micBtn.classList.remove('listening');
        if (chatInput) {
            chatInput.placeholder = "Type a message...";
            // Don't auto-focus on mobile — prevents keyboard from popping up
            if (window.innerWidth > 480) {
                chatInput.focus();
            }
        }

        // --- Android: Pause VADManager (don't destroy stream) ---
        if (this._isAndroidChrome && this._vadManager) {
            this._vadManager.pause();
            return;
        }

        // --- Non-Android: Stop Web Speech API ---
        if (this.recognition) {
            try { this.recognition.stop(); } catch (e) { }
        }
    },

    abortListening() {
        const shadow = this.shadowRoot;
        const micBtn = shadow.getElementById('mic-btn');

        this.isListening = false;
        if (micBtn) micBtn.classList.remove('listening');



        if (this.recognition) {
            try { this.recognition.abort(); } catch (e) { }
        }
    },



    cleanTextForSpeech(text) {

        // Clean speech text (strip markdown syntax, parenthesized URLs, raw URLs, HTML tags, and markdown formatting characters)
        let cleanText = text
            .replace(/\[CAROUSEL\]([\s\S]*?)\[\/CAROUSEL\]/gi, '') // Completely strip carousel markdown from speech
            .replace(/^[ \t]*[-*+•]\s+/gm, '') // Remove bullet points (including unicode •) entirely to avoid weird intonations
            .replace(/^(\s*\d+)\.\s+/gm, '$1, ') // Replace list numbers with a comma for a pause instead of removing the period
            
            // --- Markdown Table Formatting ---
            .replace(/^[\s|:\-]+$/gm, '') // Remove markdown table delimiter rows (e.g. |---|---|)
            .replace(/\|/g, ', ') // Replace table pipes with commas for natural pauses between cells
            
            .replace(/\[([^\]]+)\]\([^)]+\)/g, '$1') // [text](url) -> text
            .replace(/\(\s*https?:\/\/[^\s\)]+\s*\)/gi, '') // (https://...) -> empty
            
            // Format Emails to be spoken naturally
            .replace(/([a-zA-Z0-9._-]+)@([a-zA-Z0-9._-]+)\.([a-zA-Z]{2,})/gi, '$1 at $2 dot $3')
            
            // Format URLs to be spoken naturally (strip https:// and format domain and paths)
            .replace(/\bhttps?:\/\/(?:www\.)?/gi, '')
            .replace(/\b([a-zA-Z0-9.-]+)\.([a-zA-Z]{2,})(\/[a-zA-Z0-9.\-_/]*[a-zA-Z0-9.\-_])?\/?/gi, (match, domain, tld, path) => {
                let res = `${domain} dot ${tld}`;
                if (path) {
                    res += ` slash ${path.replace(/^\//, '').replace(/[\/]/g, ' slash ').replace(/[-_]/g, ' ')}`;
                }
                return res;
            })
            .replace(/<[^>]*>/g, '') // HTML tags -> empty
            .replace(/[\*_#~`]+/g, '') // bold/italic/code symbols -> empty
            // Ensure every line ends with a period before collapsing, to force a natural sentence drop-off and prevent audio clipping
            .replace(/([^\.,?!;:])\s*\n+/g, '$1. ')
            .replace(/\n+/g, ' ') // collapse remaining newlines
            .replace(/!+/g, '.') // Exclamation marks -> period (prevents "factorial"/"exclamation" being spoken aloud)
            .replace(/\s+/g, ' ') // collapse spacing
            .trim();

        // Advanced TTS pronunciation fixes for Piper & Sarvam
        cleanText = cleanText
            .replace(/\bWebAssets\b/gi, 'Web Assets')
            .replace(/\bSEO\b/g, 'S E O')
            .replace(/\bSaaS\b/gi, 'Sass')
            .replace(/\bFB\b/g, 'F B')
            .replace(/\bIG\b/g, 'I G')
            .replace(/\bGMB\b/g, 'G M B')
            .replace(/\bUI\/UX\b/gi, 'U I, U X')
            .replace(/\biOS\/Android\b/gi, 'iOS or Android')
            .replace(/\bmo\b/gi, 'month')
            .replace(/\bmsgs\b/gi, 'messages')
            
            // Expand common contractions to fix TTS mispronunciations (e.g., "I'll" -> "I will")
            .replace(/\bI['’]m\b/gi, 'I am')
            .replace(/\bI['’]ll\b/gi, 'I will')
            .replace(/\bI['’]ve\b/gi, 'I have')
            .replace(/\bI['’]d\b/gi, 'I would')
            .replace(/\byou['’]re\b/gi, 'you are')
            .replace(/\byou['’]ll\b/gi, 'you will')
            .replace(/\byou['’]ve\b/gi, 'you have')
            .replace(/\byou['’]d\b/gi, 'you would')
            .replace(/\bhe['’]s\b/gi, 'he is')
            .replace(/\bhe['’]ll\b/gi, 'he will')
            .replace(/\bshe['’]s\b/gi, 'she is')
            .replace(/\bshe['’]ll\b/gi, 'she will')
            .replace(/\bit['’]s\b/gi, 'it is')
            .replace(/\bwe['’]re\b/gi, 'we are')
            .replace(/\bwe['’]ll\b/gi, 'we will')
            .replace(/\bwe['’]ve\b/gi, 'we have')
            .replace(/\bthey['’]re\b/gi, 'they are')
            .replace(/\bthey['’]ll\b/gi, 'they will')
            .replace(/\bthey['’]ve\b/gi, 'they have')
            .replace(/\bthat['’]s\b/gi, 'that is')
            .replace(/\bwho['’]s\b/gi, 'who is')
            .replace(/\bwhat['’]s\b/gi, 'what is')
            .replace(/\bwhere['’]s\b/gi, 'where is')
            .replace(/\bthere['’]s\b/gi, 'there is')
            .replace(/\bhere['’]s\b/gi, 'here is')
            .replace(/\blet['’]s\b/gi, 'let us')
            .replace(/\bcan['’]t\b/gi, 'cannot')
            .replace(/\bwon['’]t\b/gi, 'will not')
            .replace(/\b([a-zA-Z]+)n['’]t\b/gi, '$1 not') // matches don't, doesn't, didn't, isn't, aren't, etc.

            .replace(/\bpayment gateway\b/gi, 'pay-ment gateway') // Fix for "pin gateway" mispronunciation
            .replace(/\(one-time\)/gi, 'one time')
            .replace(/\(fee only\)/gi, 'fee only')
            .replace(/\s*&\s*/g, ' and ') // Replace ampersands with 'and'
            .replace(/[•·▪]/g, '') // Strip remaining stray bullet characters
            
            // Currency fixes (prevents "Rupees rupees")
            .replace(/Rupees\s*₹/gi, 'Rupees')
            .replace(/₹\s*/g, 'rupees ')
            
            // Number ranges with optional commas (e.g., 100-150, 700 - rupees 1,999)
            .replace(/(\d+(?:,\d+)?)\s*[-–—‑]\s*(rupees\s*)?(\d+(?:,\d+)?)/g, '$1 to $2$3')
            // Numbers attached to words with dashes (e.g., 15-day -> 15 day)
            .replace(/(\d+)\s*[-–—‑]\s*([a-zA-Z]+)/g, '$1 $2')
            // Standalone hyphens / dashes with spaces around them become a pause
            .replace(/\s+[-–—‑]\s+/g, ', ')
            // Slashes for rates (e.g., / mo, / year)
            .replace(/(\d+)\s*\/\s*([a-zA-Z]+)/g, '$1 per $2')
            // Remaining slashes (e.g., Razorpay/Stripe -> Razorpay or Stripe)
            .replace(/\s*\/\s*/g, ' or ')
            // Plus signs for addition/features (Meta + Google)
            .replace(/\s*\+\s*/g, ' plus ')
            // Trailing plus signs (50,000+)
            .replace(/(\d+)\+/g, '$1 plus')
            
            // Cleanup duplicate commas caused by table formatting
            .replace(/,\s*,+/g, ',')
            .replace(/^,\s*/, '') // Remove leading comma
            .replace(/\s+/g, ' ');

        // Format continuous or dashed/spaced long digits (7+ digits) so they are spoken one digit at a time
        // This prevents the AI from reading phone numbers like "Seven billion..."
        cleanText = cleanText.replace(/(?:(?:\+?\d[\s-]*){7,})/g, match => match.replace(/[^\d+]/g, '').split('').join(' '));

        return cleanText;
    },

    updateImmersiveTranscript(text, reset = false) {
        if (!text || !text.trim()) return;
        const transcriptAi = this.shadowRoot.getElementById('waai-transcript-ai');
        if (transcriptAi) {
            let clean = text
                .replace(/\[CAROUSEL\]([\s\S]*?)\[\/CAROUSEL\]/gi, '') // Completely strip carousel markdown from immersive text
                .replace(/\*\*(.*?)\*\*/g, '$1') // bold
                .replace(/\*(.*?)\*/g, '$1') // italic
                .replace(/_(.*?)_/g, '$1') // italic
                .replace(/#(.*?)#/g, '$1') // headings
                .replace(/\[(.*?)\]\(.*?\)/g, '$1') // links
                .replace(/`([^`]+)`/g, '$1') // inline code
                .replace(/```[\s\S]*?```/g, '') // code blocks
                .replace(/>\s?/g, '') // blockquotes
                .replace(/[-*+]\s+/g, '') // lists
                .replace(/\n+/g, ' ') // remove newlines
                .trim();

            if (this._transcriptInterval) {
                clearInterval(this._transcriptInterval);
                this._transcriptInterval = null;
            }

            if (reset || typeof this._fullImmersiveText === 'undefined') {
                this._fullImmersiveText = '';
                transcriptAi.textContent = '';
            }

            let baseText = this._fullImmersiveText;
            if (baseText.length > 0 && !baseText.endsWith(' ')) {
                baseText += ' ';
            }

            let i = 0;
            // 45ms per character gives a realistic reading speed that roughly matches TTS
            this._transcriptInterval = setInterval(() => {
                transcriptAi.textContent = baseText + clean.substring(0, i + 1);
                // Auto-scroll to show the latest lines
                transcriptAi.scrollTop = transcriptAi.scrollHeight;
                i++;
                if (i >= clean.length) {
                    clearInterval(this._transcriptInterval);
                    this._transcriptInterval = null;
                    this._fullImmersiveText = transcriptAi.textContent;
                }
            }, 45);
        }
    },

    speak(text, forceBrowserTTS = false) {
        if (!this.speechSynthesisActive && !this.isContinuousVoiceMode) {
            return;
        }

        // Cancel previous speech if any
        this.cancelSpeaking();

        // Check if text is completely empty or just whitespace
        if (!text || !text.trim()) {
            return;
        }

        const cleanText = this.cleanTextForSpeech(text);
        if (!cleanText) {
            return;
        }

        // Save current AI spoken text
        this.currentAISpokenText = cleanText;
        this.lastAISpokenText = cleanText;

        // Reset the immersive transcript accumulator for the new response
        this._fullImmersiveText = '';
        const transcriptAi = this.shadowRoot.getElementById('waai-transcript-ai');
        if (transcriptAi) {
            transcriptAi.textContent = '';
        }

        // Set speaking state synchronously to prevent race conditions in recognition callbacks
        this.isSpeaking = true;

        const devCount = (cleanText.match(/[\u0900-\u097F]/g) || []).length;
        const isHindi = devCount / cleanText.length > 0.05;

        const urduCount = (cleanText.match(/[\u0600-\u06FF]/g) || []).length;
        const isUrdu = urduCount / cleanText.length > 0.05;

        let detectedLang = 'en-IN'; // Default to Indian English for Sarvam
        if (isHindi) detectedLang = 'hi-IN';
        if (isUrdu) detectedLang = 'hi-IN'; // Map Urdu script to Hindi voice (Bulbul understands phonetics)

        if (!forceBrowserTTS && this.cfg.speechEngine === 'sarvam') {
            this.speakSarvam(cleanText, detectedLang);
            return;
        }

        // In full duplex mode, we DO NOT abort listening. 
        // We leave the microphone ON to catch user interruptions.
        if (!forceBrowserTTS && this.cfg.speechEngine === 'piper') {
            this.speakPiper(cleanText);
            return;
        }

        if (!forceBrowserTTS && this.cfg.speechEngine === 'groq_orpheus' && this.cfg.groqApiKey) {
            this.speakGroqOrpheus(cleanText);
            return;
        }

        if (!forceBrowserTTS && this.cfg.speechEngine === 'elevenlabs' && this.cfg.elevenLabsKey && this.cfg.elevenLabsVoiceId) {
            this.speakElevenLabs(cleanText);
            return;
        }

        if (!window.speechSynthesis) {
            this.isSpeaking = false;
            return;
        }

        // Delegate to TTSQueue: handles sequential chunk playback,
        // stall watchdog, and clean cancellation.
        if (this.ttsQueue) {
            this.ttsQueue.enqueue(cleanText, {
                onEnd: () => {
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                    this.aiSpeechEndTime = Date.now();
                    if (this.EventBus) {
                        this.EventBus.emit('voice:speak:end', { source: 'ttsQueue:onEnd' });
                    } else if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                            if (timeSinceUserSpeech < 2000) {
                                this.waLog(`[Browser TTS] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                                this.transitionTo('listening');
                            } else {
                                this.transitionTo('cooldown', { nextState: 'listening' });
                            }
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                },
                onError: () => {
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                    this.aiSpeechEndTime = Date.now();
                    if (this.EventBus) {
                        this.EventBus.emit('voice:speak:end', { source: 'ttsQueue:onError' });
                    } else if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                            if (timeSinceUserSpeech < 2000) {
                                this.waLog(`[Browser TTS Error] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                                this.transitionTo('listening');
                            } else {
                                this.transitionTo('cooldown', { nextState: 'listening' });
                            }
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                },
            });

            // Ensure visualizer shows speaking state immediately
            if (this.assistantState !== 'speaking') {
                this.transitionTo('speaking');
            }
            if (this.isContinuousVoiceMode) {
                this.startListening();
            }
            return;
        }

        // Fallback: direct utterance if TTSQueue is not yet initialized
        const _fallbackLang = (() => {
            const devCount = (cleanText.match(/[\u0900-\u097F]/g) || []).length;
            if (devCount / cleanText.length > 0.05) return 'hi-IN';
            return 'en-US';
        })();
        const utterance = new SpeechSynthesisUtterance(cleanText);
        utterance.lang = _fallbackLang;
        utterance.rate = 1.0;

        utterance.onstart = () => {
            if (this.assistantState !== 'speaking') {
                this.transitionTo('speaking');
            }
            if (this.isContinuousVoiceMode) {
                this.startListening();
            }
        };

        utterance.onend = () => {
            if (this.currentAISpokenText) {
                this.lastAISpokenText = this.currentAISpokenText;
            }
            this.currentAISpokenText = '';
            this.aiSpeechEndTime = Date.now();
            if (this.EventBus) {
                this.EventBus.emit('voice:speak:end', { source: 'utterance:onend' });
            } else if (this.isContinuousVoiceMode) {
                if (this.assistantState === 'speaking') {
                    const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                    if (timeSinceUserSpeech < 2000) {
                        this.transitionTo('listening');
                    } else {
                        this.transitionTo('cooldown', { nextState: 'listening' });
                    }
                }
            } else {
                this.transitionTo('idle');
            }
        };

        utterance.onerror = () => {
            if (this.currentAISpokenText) {
                this.lastAISpokenText = this.currentAISpokenText;
            }
            this.currentAISpokenText = '';
            this.aiSpeechEndTime = Date.now();
            if (this.EventBus) {
                this.EventBus.emit('voice:speak:end', { source: 'utterance:onerror' });
            } else if (this.isContinuousVoiceMode) {
                if (this.assistantState === 'speaking') {
                    const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                    if (timeSinceUserSpeech < 2000) {
                        this.transitionTo('listening');
                    } else {
                        this.transitionTo('cooldown', { nextState: 'listening' });
                    }
                }
            } else {
                this.transitionTo('idle');
            }
        };

        window.speechSynthesis.speak(utterance);
    },


    /**
     * Groq Orpheus TTS — handles 200-char limit by chunking text at sentence
     * boundaries, fetching audio for each chunk sequentially, and playing
     * them back-to-back for seamless speech.
     */
    speakGroqOrpheus(text) {
        this._speechGenerationId = (this._speechGenerationId || 0) + 1;
        this.cancelGroqTts();

        const chunks = this.chunkTextForTTS(text, 200);
        if (!chunks.length) {
            if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            } else {
                this.transitionTo('idle');
            }
            return;
        }

        this._groqChunkQueue = chunks.slice();
        this._groqAborted = false;

        if (this.isContinuousVoiceMode) {
            this.updateVoiceCallUI('speaking');
            this.startListening();
        }

        this._playNextGroqChunk();
    },

    _playNextGroqChunk() {
        if (this._groqAborted || !this._groqChunkQueue || !this._groqChunkQueue.length) {
            // Entire queue finished or was cancelled
            if (!this._groqAborted) {
                if (this.currentAISpokenText) {
                    this.lastAISpokenText = this.currentAISpokenText;
                }
                this.currentAISpokenText = '';
                this.aiSpeechEndTime = Date.now();
                if (this.isContinuousVoiceMode) {
                    if (this.assistantState === 'speaking') {
                        const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                        if (timeSinceUserSpeech < 2000) {
                            this.waLog(`[Groq TTS] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                            this.transitionTo('listening');
                        } else {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    }
                } else {
                    this.transitionTo('idle');
                }
            }
            return;
        }

        const chunk = this._groqChunkQueue.shift();
        const currentGenId = this._speechGenerationId;
        const url = 'https://api.groq.com/openai/v1/audio/speech';

        if (!this._groqAbortController) {
            this._groqAbortController = new AbortController();
        }

        fetch(url, {
            method: 'POST',
            signal: this._groqAbortController.signal,
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${this.cfg.groqApiKey}`
            },
            body: JSON.stringify({
                model: this.cfg.groqTtsModel || 'canopylabs/orpheus-v1-english',
                input: chunk,
                voice: this.cfg.groqTtsVoice || 'troy',
                response_format: 'wav'
            })
        })
            .then(response => {
                if (!response.ok) throw new Error('Groq TTS API error: ' + response.status);
                return response.blob();
            })
            .then(blob => {
                if (this._groqAborted || this._speechGenerationId !== currentGenId) return;

                const audioUrl = URL.createObjectURL(blob);
                this.groqTtsAudio = new Audio(audioUrl);

                this.groqTtsAudio.onended = () => {
                    URL.revokeObjectURL(audioUrl);
                    this.groqTtsAudio = null;
                    // Play next chunk in queue
                    this._playNextGroqChunk();
                };

                this.groqTtsAudio.onerror = () => {
                    URL.revokeObjectURL(audioUrl);
                    this.groqTtsAudio = null;
                    this._groqChunkQueue = [];
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                    this.aiSpeechEndTime = Date.now();
                    if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                };

                this.updateImmersiveTranscript(chunk);
                this.groqTtsAudio.play().catch(e => {
                    console.error('Groq TTS Audio play failed:', e);
                    this.groqTtsAudio = null;
                    this._groqChunkQueue = [];
                    if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                });
            })
            .catch(err => {
                console.error('Groq TTS fetch error:', err);
                this._groqChunkQueue = [];
                if (this.isContinuousVoiceMode) {
                    if (this.assistantState === 'speaking') {
                        this.transitionTo('cooldown', { nextState: 'listening' });
                    }
                } else {
                    this.transitionTo('idle');
                }
            });
    },

    /**
     * Split text into chunks of at most maxLen characters, breaking at
     * sentence boundaries (. ! ?) first, then at word boundaries.
     */
    chunkTextForTTS(text, maxLen, fastFirstChunk = false) {
        if (text.length <= maxLen && !fastFirstChunk) return [text];

        const chunks = [];
        let remaining = text;

        const sentenceEnds = ['. ', '! ', '? ', '.\n', '!\n', '?\n'];
        const clauseEnds = [', ', '; ', ': ', ',\n', ';\n', ':\n'];

        let isFirstChunk = true;

        while (remaining.length > 0) {
            let currentMaxLen = (isFirstChunk && fastFirstChunk) ? Math.min(35, maxLen) : maxLen;

            if (remaining.length <= currentMaxLen) {
                chunks.push(remaining.trim());
                break;
            }

            let splitIdx = -1;

            // 1. Try to split at sentence boundary within maxLen
            for (const sep of sentenceEnds) {
                const idx = remaining.lastIndexOf(sep, maxLen);
                if (idx > 0 && idx + sep.length - 1 > splitIdx) {
                    splitIdx = idx + sep.length - 1;
                }
            }

            // 2. Try to split at clause boundary within maxLen (keeps phrasing natural)
            if (splitIdx <= 0) {
                for (const sep of clauseEnds) {
                    const idx = remaining.lastIndexOf(sep, maxLen);
                    if (idx > 0 && idx + sep.length - 1 > splitIdx) {
                        splitIdx = idx + sep.length - 1;
                    }
                }
            }

            // 3. Fall back to word boundary
            if (splitIdx <= 0) {
                splitIdx = remaining.lastIndexOf(' ', maxLen);
            }

            // 4. Worst case: hard cut
            if (splitIdx <= 0) {
                splitIdx = currentMaxLen;
            }

            chunks.push(remaining.substring(0, splitIdx + 1).trim());
            remaining = remaining.substring(splitIdx + 1).trimStart();
            isFirstChunk = false;
        }

        return chunks.filter(c => c.length > 0);
    },

    cancelGroqTts() {
        this._groqAborted = true;
        this._groqChunkQueue = [];

        // Phase 4: Abort pending fetch requests immediately
        if (this._groqAbortController) {
            try { this._groqAbortController.abort(); } catch (e) { }
            this._groqAbortController = null;
        }

        if (this.groqTtsAudio) {
            this.groqTtsAudio.pause();
            this.groqTtsAudio = null;
        }
    },

    stopElevenLabsTts() {
        if (this.elevenLabsAudio) {
            this.elevenLabsAudio.pause();
            this.elevenLabsAudio = null;
        }
    },


    speakElevenLabs(text) {
        this._speechGenerationId = (this._speechGenerationId || 0) + 1;
        const currentGenId = this._speechGenerationId;
        if (this.elevenLabsAudio) {
            this.elevenLabsAudio.pause();
            this.elevenLabsAudio = null;
        }

        const url = `https://api.elevenlabs.io/v1/text-to-speech/${this.cfg.elevenLabsVoiceId}`;

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'xi-api-key': this.cfg.elevenLabsKey
            },
            body: JSON.stringify({
                text: text,
                model_id: "eleven_multilingual_v1",
                voice_settings: { stability: 0.5, similarity_boost: 0.5 }
            })
        })
            .then(response => {
                if (!response.ok) throw new Error("ElevenLabs API error");
                return response.blob();
            })
            .then(blob => {
                if (this.assistantState !== 'speaking' || this._speechGenerationId !== currentGenId) return; // cancelled while fetching

                if (this.elevenLabsAudio) {
                    this.elevenLabsAudio.pause();
                }

                if (this.isContinuousVoiceMode) {
                    this.updateVoiceCallUI('speaking');
                    this.startListening();
                }

                const audioUrl = URL.createObjectURL(blob);
                this.elevenLabsAudio = new Audio(audioUrl);

                this.elevenLabsAudio.onended = () => {
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                    this.aiSpeechEndTime = Date.now();
                    if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                            if (timeSinceUserSpeech < 2000) {
                                this.waLog(`[ElevenLabs] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                                this.transitionTo('listening');
                            } else {
                                this.transitionTo('cooldown', { nextState: 'listening' });
                            }
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                };

                this.elevenLabsAudio.onerror = () => {
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                    this.aiSpeechEndTime = Date.now();
                    if (this.isContinuousVoiceMode) {
                        if (this.assistantState === 'speaking') {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    } else {
                        this.transitionTo('idle');
                    }
                };

                this.updateImmersiveTranscript(text);
                this.elevenLabsAudio.play().catch(e => {
                    console.error("ElevenLabs Audio play failed:", e);
                    if (this.isContinuousVoiceMode) {
                        this.transitionTo('cooldown', { nextState: 'listening' });
                    } else {
                        this.transitionTo('idle');
                    }
                });
            })
            .catch(err => {
                console.error(err);
                if (this.isContinuousVoiceMode) {
                    this.transitionTo('cooldown', { nextState: 'listening' });
                } else {
                    this.transitionTo('idle');
                }
            });
    },

    cancelSarvamTts() {
        this._sarvamAborted = true;
        this._sarvamIsPlaying = false;

        if (this._sarvamAbortController) {
            try { this._sarvamAbortController.abort(); } catch (e) { }
            this._sarvamAbortController = null;
        }

        if (this._sarvamChunkTimeout) {
            clearTimeout(this._sarvamChunkTimeout);
            this._sarvamChunkTimeout = null;
        }

        if (this._sarvamLoopTimeout) {
            clearTimeout(this._sarvamLoopTimeout);
            this._sarvamLoopTimeout = null;
        }

        if (this._sarvamCurrentSource) {
            try { this._sarvamCurrentSource.onended = null; this._sarvamCurrentSource.stop(); } catch (e) { }
            this._sarvamCurrentSource = null;
        }

        if (this.sarvamAudio) {
            if (typeof this.sarvamAudio.pause === 'function') {
                try { this.sarvamAudio.pause(); } catch (e) { }
            }
            if (typeof this.sarvamAudio === 'object') {
                this.sarvamAudio.onended = null;
                this.sarvamAudio.onerror = null;
                this.sarvamAudio.src = '';
            }
            this.sarvamAudio = null;
        }

        if (this._sarvamPlayQueue) {
            this._sarvamPlayQueue.forEach(item => {
                if (item.audioUrl && item.audioUrl.startsWith('blob:')) {
                    try { URL.revokeObjectURL(item.audioUrl); } catch (e) { }
                }
            });
        }
        this._sarvamPlayQueue = [];
    },

    speakSarvam(text, langCode = 'hi-IN') {
        this.cancelSarvamTts();

        // Shorter chunk size for rapid initial generation
        // Shorter chunk size (150 chars) but use Fast-First-Chunk (35 chars) for near 0-latency start
        const chunks = this.chunkTextForTTS(text, 150, true);
        if (!chunks.length) {
            if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            } else {
                this.transitionTo('idle');
            }
            return;
        }

        this._sarvamAborted = false;
        this._sarvamSessionId = Date.now();

        this._sarvamPlayQueue = chunks.map((chunk) => ({
            text: chunk,
            langCode: langCode,
            audioUrl: null,
            fetched: false,
            fetching: false,
            error: false
        }));

        if (this.isContinuousVoiceMode) {
            if (this.cfg && this.cfg.allowInterruptions === '0') {
                this.updateVoiceCallUI('speaking', 'Speaking (Mic Muted)');
                this.stopListening();
            } else {
                this.updateVoiceCallUI('speaking');
                this.startListening();
            }
        }

        this._sarvamLastFetchTime = 0;

        // Mark as speaking so the Hardware VAD barge-in can detect us
        this.isSpeaking = true;

        // Start prefetching first two chunks in parallel to avoid network starvation
        if (this._sarvamPlayQueue.length > 0) this._prefetchSarvamChunk(this._sarvamPlayQueue[0]);
        if (this._sarvamPlayQueue.length > 1) this._prefetchSarvamChunk(this._sarvamPlayQueue[1]);

        this._playNextSarvamQueueItem();
    },

    _prefetchSarvamChunk(item, _retryCount = 0) {
        if (item.fetching || item.fetched) return;
        item.fetching = true;
        const mySessionId = this._sarvamSessionId;

        if (!this._sarvamAbortController) {
            this._sarvamAbortController = new AbortController();
        }

        const doFetch = () => {
            if (this._sarvamAborted || this._sarvamSessionId !== mySessionId) {
                item.fetching = false;
                return;
            }
            fetch(this.cfg.chatUrl, {
                method: 'POST',
                signal: this._sarvamAbortController ? this._sarvamAbortController.signal : undefined,
                headers: {
                    'Content-Type': 'application/json',
                    'X-WAAI-Nonce': this.cfg.nonce || ''
                },
                body: JSON.stringify({
                    action: 'sarvam_tts',
                    text: item.text,
                    language_code: item.langCode
                })
            })
                .then(response => {
                    if (!response.ok) throw new Error('Sarvam proxy error: ' + response.status);
                    return response.json();
                })
                .then(data => {
                    if (this._sarvamAborted || this._sarvamSessionId !== mySessionId) return;

                    // Detect rate-limit errors returned as JSON { error: "Too many..." }
                    // and retry with exponential backoff (1s → 2s → 4s → give up)
                    if (data.error && /too many/i.test(data.error)) {
                        if (_retryCount < 3) {
                            const backoff = Math.pow(2, _retryCount) * 1000; // 1s, 2s, 4s
                            console.warn(`[Sarvam TTS] Rate limited. Retrying in ${backoff}ms (attempt ${_retryCount + 1}/3)…`);
                            item.fetching = false; // allow retry to re-enter
                            this._sarvamLastFetchTime = Date.now() + backoff;
                            setTimeout(() => {
                                if (!this._sarvamAborted && this._sarvamSessionId === mySessionId) {
                                    this._prefetchSarvamChunk(item, _retryCount + 1);
                                }
                            }, backoff);
                            return;
                        }
                        // 3 retries exhausted — skip chunk gracefully
                        console.error('[Sarvam TTS] Rate limit retries exhausted. Skipping chunk.');
                        throw new Error(data.error);
                    }

                    if (data.error) throw new Error(data.error);

                    let base64Audio = '';
                    if (data.audios && Array.isArray(data.audios) && data.audios.length > 0) {
                        base64Audio = data.audios[0];
                    } else {
                        base64Audio = data.audio || data.data?.audio || '';
                    }
                    if (!base64Audio) throw new Error('No audio returned from Sarvam');

                    // Decode base64 to ArrayBuffer for Web Audio API
                    const binaryString = window.atob(base64Audio);
                    const len = binaryString.length;
                    const bytes = new Uint8Array(len);
                    for (let i = 0; i < len; i++) {
                        bytes[i] = binaryString.charCodeAt(i);
                    }
                    const arrayBuffer = bytes.buffer;

                    const ctx = this._piperAudioContext; // Reuse the shared AudioContext
                    if (ctx) {
                        return ctx.decodeAudioData(arrayBuffer).then(decoded => {
                            if (this._sarvamAborted || this._sarvamSessionId !== mySessionId) return;
                            item.audioBuffer = decoded;
                            item.fetched = true;
                            item.fetching = false;
                            this._playNextSarvamQueueItem();
                        });
                    } else {
                        // Fallback to HTML Audio
                        item.audioUrl = 'data:audio/wav;base64,' + base64Audio;
                        item.fetched = true;
                        item.fetching = false;
                        this._playNextSarvamQueueItem();
                    }
                })
                .catch(err => {
                    if (err.name === 'AbortError') return;
                    console.error('Sarvam TTS chunk fetch/decode error:', err);
                    item.error = true;
                    item.fetched = true; // Mark as fetched so the queue can shift past it
                    item.fetching = false;
                    this._playNextSarvamQueueItem();
                });
        };

        doFetch();
    },

    _playNextSarvamQueueItem() {
        if (this._sarvamAborted || !this._sarvamPlayQueue || !this._sarvamPlayQueue.length) {
            return;
        }
        if (this._sarvamIsPlaying) return; // Wait for current chunk to finish

        const nextItem = this._sarvamPlayQueue[0];

        if (!nextItem.fetched) {
            return; // Waiting for fetch to finish, will resume automatically when fetched
        }

        // Remove from queue
        this._sarvamPlayQueue.shift();

        // Keep the prefetch buffer rolling (always try to keep 1 chunk ahead)
        if (this._sarvamPlayQueue.length > 0) {
            this._prefetchSarvamChunk(this._sarvamPlayQueue[0]);
        }

        if (nextItem.error) {
            this._playNextSarvamQueueItem();
            return;
        }

        const mySessionId = this._sarvamSessionId;

        const onChunkEnded = () => {
            if (this._sarvamSessionId !== mySessionId) return;
            this.sarvamAudio = null;
            this._sarvamIsPlaying = false;
            this._sarvamLastChunkEndTime = Date.now();
            if (this._sarvamCurrentSource) {
                this._sarvamCurrentSource.onended = null;
                this._sarvamCurrentSource = null;
            }

            if (this._sarvamChunkTimeout) {
                clearTimeout(this._sarvamChunkTimeout);
                this._sarvamChunkTimeout = null;
            }

            if (this._sarvamPlayQueue.length === 0) {
                // All chunks done — transition state
                if (this.currentAISpokenText) {
                    this.lastAISpokenText = this.currentAISpokenText;
                }
                this.currentAISpokenText = '';
                this.aiSpeechEndTime = Date.now();
                if (this.isContinuousVoiceMode) {
                    if (this.assistantState === 'speaking') {
                        const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                        if (timeSinceUserSpeech < 2000) {
                            this.transitionTo('listening');
                        } else {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    }
                } else {
                    this.transitionTo('idle');
                }
            } else {
                this._playNextSarvamQueueItem();
            }
        };

        const onChunkError = () => {
            if (this._sarvamSessionId !== mySessionId) return;
            this.sarvamAudio = null;
            this._sarvamIsPlaying = false;
            this._sarvamLastChunkEndTime = Date.now();
            if (this._sarvamCurrentSource) {
                this._sarvamCurrentSource.onended = null;
                this._sarvamCurrentSource = null;
            }

            if (this._sarvamChunkTimeout) {
                clearTimeout(this._sarvamChunkTimeout);
                this._sarvamChunkTimeout = null;
            }

            this._sarvamPlayQueue = [];
            if (this.currentAISpokenText) {
                this.lastAISpokenText = this.currentAISpokenText;
            }
            this.currentAISpokenText = '';
            this.aiSpeechEndTime = Date.now();
            if (this.isContinuousVoiceMode) {
                if (this.assistantState === 'speaking') {
                    this.transitionTo('cooldown', { nextState: 'listening' });
                }
            } else {
                this.transitionTo('idle');
            }
        };

        const ctx = this._piperAudioContext;

        if (ctx && nextItem.audioBuffer) {
            // ── Web Audio API path (mobile-compatible full-duplex) ──────────────
            this._sarvamIsPlaying = true;
            this.sarvamAudio = true; // Truthy sentinel

            const source = ctx.createBufferSource();
            source.buffer = nextItem.audioBuffer;
            source.connect(ctx.destination);
            this._sarvamCurrentSource = source;

            source.onended = () => {
                if (this._sarvamSessionId !== mySessionId) return;
                onChunkEnded();
            };
            this.updateImmersiveTranscript(nextItem.text);
            source.start(0);
        } else if (nextItem.audioUrl) {
            // ── HTML Audio fallback path ──
            this._sarvamIsPlaying = true;
            this.sarvamAudio = new Audio(nextItem.audioUrl);

            this.sarvamAudio.onended = () => {
                if (this._sarvamSessionId !== mySessionId) return;
                onChunkEnded();
            };

            this.sarvamAudio.onerror = (e) => {
                console.error("Sarvam Audio playback error:", e);
                this.sarvamAudio = null;
                this._sarvamIsPlaying = false;
                onChunkError();
            };

            this.updateImmersiveTranscript(nextItem.text);
            this.sarvamAudio.play().catch(e => {
                if (this._sarvamSessionId !== mySessionId) return;
                console.error("Sarvam Audio play failed:", e);
                this.sarvamAudio = null;
                this._sarvamIsPlaying = false;
                this._playNextSarvamQueueItem();
            });
        } else {
            this._playNextSarvamQueueItem();
        }
    },

    speakPiper(text) {
        this.cancelPiperTts();

        // Shorter chunk size (150 chars) for rapid initial generation and natural clause pauses
        // Shorter chunk size (150 chars) but use Fast-First-Chunk (35 chars) for near 0-latency start
        const chunks = this.chunkTextForTTS(text, 150, true);
        if (!chunks.length) {
            if (this.isContinuousVoiceMode) {
                this.transitionTo('cooldown', { nextState: 'listening' });
            } else {
                this.transitionTo('idle');
            }
            return;
        }

        this._piperAborted = false;
        this._piperSessionId = Date.now(); // Generate a new session ID to scope asynchronous callbacks

        // Map chunks to a prefetching queue structure.
        // We store an audioBuffer (Web Audio API) instead of an Audio element
        // so that playback can coexist with the microphone on mobile devices.
        this._piperPlayQueue = chunks.map((chunk) => ({
            text: chunk,
            audioBuffer: null, // Web Audio API decoded buffer
            fetched: false,
            fetching: false,
            error: false,
        }));

        if (this.isContinuousVoiceMode) {
            if (this.cfg && this.cfg.allowInterruptions === '0') {
                this.updateVoiceCallUI('speaking', 'Speaking (Mic Muted)');
                this.stopListening();
            } else {
                this.updateVoiceCallUI('speaking');
                this.startListening();
            }
        }

        // Start prefetching first two chunks to avoid network starvation
        if (this._piperPlayQueue.length > 0) this._prefetchPiperChunk(this._piperPlayQueue[0], 0);
        if (this._piperPlayQueue.length > 1) this._prefetchPiperChunk(this._piperPlayQueue[1], 1);

        // Start playback sequence scheduler
        this._playNextPiperQueueItem();
    },

    _prefetchPiperChunk(item, index) {
        if (item.fetching || item.fetched) return;
        item.fetching = true;
        const url = this.apiEndpoint;
        const mySessionId = this._piperSessionId;

        if (!this._piperAbortController) {
            this._piperAbortController = new AbortController();
        }

        fetch(url, {
            method: 'POST',
            signal: this._piperAbortController.signal,
            headers: {
                'Content-Type': 'application/json',
                'X-WAAI-Nonce': this.csrfToken || '',
                'X-WAAI-Session-ID': this.sessionId || '',
                'X-WAAI-Trace-ID': this.currentTraceId || '',
            },
            body: JSON.stringify({ action: 'tts', text: item.text })
        })
            .then(response => {
                if (!response.ok) throw new Error('Piper TTS proxy error: ' + response.status);
                // Fetch as ArrayBuffer for Web Audio API decoding
                return response.arrayBuffer();
            })
            .then(arrayBuffer => {
                if (this._piperAborted || this._piperSessionId !== mySessionId) return;

                // Catch empty or invalid audio responses from the server before decodeAudioData throws
                if (arrayBuffer.byteLength < 44) {
                    throw new Error(`Server returned empty or invalid audio data (${arrayBuffer.byteLength} bytes). The text chunk may contain unpronounceable symbols or the Piper backend failed.`);
                }

                // Use Web Audio API (AudioContext) to decode the audio data.
                // This is CRITICAL for mobile: the HTML <Audio> element acquires an exclusive
                // OS audio output session that blocks the microphone (Web Speech API).
                // AudioContext runs in a shared session that can coexist with the mic.
                const ctx = this._piperAudioContext;
                if (ctx) {
                    return ctx.decodeAudioData(arrayBuffer).then(decoded => {
                        if (this._piperAborted || this._piperSessionId !== mySessionId) return;
                        item.audioBuffer = decoded;
                        item.fetched = true;
                        item.fetching = false;
                        this._playNextPiperQueueItem();
                    });
                } else {
                    if (this._piperAborted || this._piperSessionId !== mySessionId) return;
                    // Fallback for browsers without AudioContext (very rare)
                    const blob = new Blob([arrayBuffer], { type: 'audio/wav' });
                    const audioUrl = URL.createObjectURL(blob);
                    item.audioFallback = new Audio(audioUrl);
                    item.audioFallbackUrl = audioUrl;
                    item.fetched = true;
                    item.fetching = false;
                    this._playNextPiperQueueItem();
                }
            })
            .catch(err => {
                if (this._piperSessionId !== mySessionId) return;
                console.warn(`[Piper Prefetch] Chunk ${index} failed:`, err.message || err);

                // If the VERY FIRST chunk fails, Piper backend is probably down.
                // Abort piper queue and fallback to Browser TTS seamlessly!
                if (index === 0 && !this._piperIsPlaying) {
                    console.warn('[Piper TTS] First chunk failed, falling back to Browser TTS');
                    this.cancelPiperTts();
                    this.speak(this.currentAISpokenText, true); // true = forceBrowserTTS
                    return;
                }

                item.error = true;
                item.fetched = true;
                item.fetching = false;
                this._playNextPiperQueueItem();
            });
    },

    _playNextPiperQueueItem() {
        if (this._piperAborted || !this._piperPlayQueue || !this._piperPlayQueue.length) {
            return;
        }

        // If a chunk is already playing, do nothing — onended will call us again
        if (this._piperIsPlaying) {
            return;
        }

        const nextItem = this._piperPlayQueue[0];

        // If the next item is still downloading, wait for the prefetch to trigger us
        if (!nextItem.fetched) {
            return;
        }

        // Remove from queue
        this._piperPlayQueue.shift();

        // Keep the prefetch buffer rolling (always try to keep 1 chunk ahead)
        if (this._piperPlayQueue.length > 0) {
            this._prefetchPiperChunk(this._piperPlayQueue[0], 0);
        }

        // Skip errored chunks silently
        if (nextItem.error) {
            this._playNextPiperQueueItem();
            return;
        }

        const mySessionId = this._piperSessionId;

        const onChunkEnded = () => {
            if (this._piperSessionId !== mySessionId) {
                return;
            }
            this._piperIsPlaying = false;
            this._piperCurrentSource = null;

            if (this._piperChunkTimeout) {
                clearTimeout(this._piperChunkTimeout);
                this._piperChunkTimeout = null;
            }

            if (this._piperPlayQueue.length === 0) {
                // All chunks done — transition state
                this.piperTtsAudio = null;
                if (this.currentAISpokenText) {
                    this.lastAISpokenText = this.currentAISpokenText;
                }
                this.currentAISpokenText = '';
                this.aiSpeechEndTime = Date.now();
                if (this.isContinuousVoiceMode) {
                    if (this.assistantState === 'speaking') {
                        const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                        if (timeSinceUserSpeech < 2000) {
                            this.waLog(`[Piper] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                            this.transitionTo('listening');
                        } else {
                            this.transitionTo('cooldown', { nextState: 'listening' });
                        }
                    }
                } else {
                    this.transitionTo('idle');
                }
            } else {
                this._playNextPiperQueueItem();
            }
        };

        const onChunkError = () => {
            if (this._piperSessionId !== mySessionId) {
                return;
            }
            this._piperIsPlaying = false;
            this._piperCurrentSource = null;
            this.piperTtsAudio = null;

            if (this._piperChunkTimeout) {
                clearTimeout(this._piperChunkTimeout);
                this._piperChunkTimeout = null;
            }

            this._piperPlayQueue = [];
            if (this.currentAISpokenText) {
                this.lastAISpokenText = this.currentAISpokenText;
            }
            this.currentAISpokenText = '';
            this.aiSpeechEndTime = Date.now();
            if (this.isContinuousVoiceMode) {
                if (this.assistantState === 'speaking') {
                    this.transitionTo('cooldown', { nextState: 'listening' });
                }
            } else {
                this.transitionTo('idle');
            }
        };

        const ctx = this._piperAudioContext;

        if (nextItem.audioBuffer && ctx) {
            // ── Web Audio API path (mobile-compatible full-duplex) ──────────────
            // AudioContext does NOT take an exclusive audio session, so it can
            // play audio and the Web Speech API mic can run simultaneously.
            this._piperIsPlaying = true;
            this.piperTtsAudio = true; // truthy sentinel so cancelPiperTts() knows we're active

            const source = ctx.createBufferSource();
            source.buffer = nextItem.audioBuffer;
            source.connect(ctx.destination);
            this._piperCurrentSource = source;

            // Add a natural 250ms pause between chunks because Piper trims trailing silence
            source.onended = () => {
                if (this._piperSessionId !== mySessionId) return;
                this._piperChunkTimeout = setTimeout(onChunkEnded, 250);
            };

            this.updateImmersiveTranscript(nextItem.text);
            source.start(0);

        } else if (nextItem.audioFallback) {
            // ── HTML Audio fallback path (desktop / non-AudioContext browsers) ──
            this._piperIsPlaying = true;
            this.piperTtsAudio = nextItem.audioFallback;

            nextItem.audioFallback.onended = () => {
                if (this._piperSessionId !== mySessionId) return;
                if (nextItem.audioFallbackUrl) URL.revokeObjectURL(nextItem.audioFallbackUrl);
                this.piperTtsAudio = null;
                this._piperChunkTimeout = setTimeout(onChunkEnded, 250);
            };
            nextItem.audioFallback.onerror = () => {
                if (this._piperSessionId !== mySessionId) return;
                if (nextItem.audioFallbackUrl) URL.revokeObjectURL(nextItem.audioFallbackUrl);
                this.piperTtsAudio = null;
                onChunkError();
            };

            this.updateImmersiveTranscript(nextItem.text);
            nextItem.audioFallback.play().catch(e => {
                if (this._piperSessionId !== mySessionId) return;
                console.error('Piper Audio fallback play failed:', e);
                if (nextItem.audioFallbackUrl) URL.revokeObjectURL(nextItem.audioFallbackUrl);
                this.piperTtsAudio = null;
                this._piperIsPlaying = false;
                this._playNextPiperQueueItem();
            });

        } else {
            // No audio data at all — skip this chunk
            this._playNextPiperQueueItem();
        }
    },

    cancelPiperTts() {
        this._piperAborted = true;
        this._piperSessionId = null; // Invalidate active session ID
        this._piperIsPlaying = false;
        this._micRestartPending = false;

        // Phase 4: Abort pending fetch requests immediately
        if (this._piperAbortController) {
            try { this._piperAbortController.abort(); } catch (e) { }
            this._piperAbortController = null;
        }

        if (this._piperChunkTimeout) {
            clearTimeout(this._piperChunkTimeout);
            this._piperChunkTimeout = null;
        }

        // Stop Web Audio API source if playing
        if (this._piperCurrentSource) {
            try { this._piperCurrentSource.onended = null; this._piperCurrentSource.stop(); } catch (e) { }
            this._piperCurrentSource = null;
        }

        // Stop and clean up fallback HTML Audio if playing
        if (this.piperTtsAudio) {
            if (typeof this.piperTtsAudio.pause === 'function') {
                try { this.piperTtsAudio.pause(); } catch (e) { }
            }
            // Explicitly clear handlers to prevent late-firing of onended/onerror closures (only if it is an Audio element object)
            if (typeof this.piperTtsAudio === 'object') {
                this.piperTtsAudio.onended = null;
                this.piperTtsAudio.onerror = null;
            }
        }

        // Clean up any fallback Audio elements in the queue
        if (this._piperPlayQueue) {
            this._piperPlayQueue.forEach(item => {
                if (item.audioFallbackUrl) {
                    try { URL.revokeObjectURL(item.audioFallbackUrl); } catch (e) { }
                }
                if (item.audioFallback) {
                    try {
                        item.audioFallback.pause();
                        item.audioFallback.onended = null;
                        item.audioFallback.onerror = null;
                    } catch (e) { }
                }
            });
        }
        this._piperPlayQueue = [];
        this.piperTtsAudio = null;
    },

    hasContactDetails(text) {
        const details = this.extractPrefillDetails(text);
        return !!(details.name || details.email || details.phone || details.query);
    },

    extractPrefillDetails(text) {
        const numberWords = {
            'zero': '0', 'one': '1', 'two': '2', 'three': '3', 'four': '4',
            'five': '5', 'six': '6', 'seven': '7', 'eight': '8', 'nine': '9'
        };

        const stopWords = new Set([
            'is', 'was', 'are', 'am', 'be', 'have', 'has', 'had', 'been',
            'which', 'who', 'whom', 'whose', 'that', 'this', 'these', 'those',
            'my', 'your', 'his', 'her', 'its', 'our', 'their',
            'the', 'a', 'an',
            'here', 'there', 'me', 'us', 'you', 'him', 'them', 'it',
            'and', 'but', 'or', 'so', 'yet',
            'to', 'for', 'with', 'about', 'of', 'at', 'by', 'from', 'in', 'on',
            'name', 'number', 'phone', 'mobile', 'contact',
            'listen', 'hear', 'say', 'speak', 'tell', 'i', 'im', 'i\'m',
            'want', 'need', 'please', 'would', 'like'
        ]);

        const tlds = new Set([
            'com', 'net', 'org', 'edu', 'gov', 'mil', 'co', 'info', 'biz', 'io', 'in', 'pk', 'uk', 'us', 'ca', 'au', 'de', 'fr', 'jp', 'cn', 'tech', 'agency', 'me', 'xyz', 'online', 'site', 'website', 'dev', 'app', 'ai'
        ]);

        const queryTruncateWords = new Set([
            'my', 'email', 'phone', 'number', 'contact', 'name', 'and', 'is', 'was', 'are', 'am', 'be', 'have', 'has', 'here', 'this', 'with', 'i', 'im', 'i\'m'
        ]);

        // Strip common non-email punctuation
        let cleanInput = text.replace(/[,?!;:]/g, ' ');
        const lower = cleanInput.toLowerCase();

        // Normalize number words to digits
        let normalized = lower;
        for (const [word, digit] of Object.entries(numberWords)) {
            const regex = new RegExp('\\b' + word + '\\b', 'g');
            normalized = normalized.replace(regex, digit);
        }

        // Normalize email tokens to make @ and . stand alone
        let emailText = normalized
            .replace(/\s+at(\s+the\s+rate(\s+of)?)?\s+/g, ' @ ')
            .replace(/\s+dot\s+/g, ' . ')
            .replace(/\s+dash\s+|\s+hyphen\s+/g, ' - ')
            .replace(/@/g, ' @ ')
            .replace(/\./g, ' . ');

        // Tokenize
        const tokens = emailText.split(/\s+/).filter(t => t.length > 0);
        const atIndex = tokens.indexOf('@');

        let email = '';
        let emailStartIndex = -1;
        let emailEndIndex = -1;

        if (atIndex !== -1) {
            // Scan backward for local part
            const localTokens = [];
            for (let i = atIndex - 1; i >= 0; i--) {
                const token = tokens[i];
                if (stopWords.has(token) && token.length > 1) {
                    break;
                }
                if (!/^[a-z0-9._%+-]+$/i.test(token)) {
                    break;
                }
                localTokens.unshift(token);
                emailStartIndex = i;
            }

            // Scan forward for domain part
            const domainTokens = [];
            for (let i = atIndex + 1; i < tokens.length; i++) {
                const token = tokens[i];
                if (stopWords.has(token) && token.length > 1) {
                    break;
                }
                if (!/^[a-z0-9.-]+$/i.test(token)) {
                    break;
                }
                domainTokens.push(token);
                emailEndIndex = i;
                if (tlds.has(token)) {
                    // Check if followed by dot and another TLD (e.g. .co.uk)
                    let isPrefixTld = false;
                    if (i + 2 < tokens.length) {
                        if (tokens[i + 1] === '.' && tlds.has(tokens[i + 2])) {
                            isPrefixTld = true;
                        }
                    }
                    if (!isPrefixTld) {
                        break;
                    }
                }
            }

            if (localTokens.length > 0 && domainTokens.length > 0) {
                const candidateEmail = (localTokens.join('') + '@' + domainTokens.join('')).replace(/\.+/g, '.');
                if (/^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/i.test(candidateEmail)) {
                    email = candidateEmail;
                }
            }
        }

        // Create remaining tokens list excluding email parts
        const remainingTokens = [];
        for (let i = 0; i < tokens.length; i++) {
            if (email && i >= emailStartIndex && i <= emailEndIndex) {
                continue;
            }
            remainingTokens.push(tokens[i]);
        }

        // Extract phone
        let phone = '';
        let currentDigitGroup = [];
        let phoneStartIndex = -1;
        let phoneEndIndex = -1;

        for (let i = 0; i < remainingTokens.length; i++) {
            const token = remainingTokens[i];
            if (/^\+?\d+$/.test(token)) {
                if (currentDigitGroup.length === 0) phoneStartIndex = i;
                let cleaned = token.replace(/[^\d+]/g, '');
                if (currentDigitGroup.length > 0) {
                    cleaned = cleaned.replace(/\+/g, '');
                }
                currentDigitGroup.push(cleaned);
                phoneEndIndex = i;
            } else {
                if (currentDigitGroup.length > 0) {
                    const potentialPhone = currentDigitGroup.join('');
                    const digitsCount = potentialPhone.replace(/\D/g, '').length;
                    if (digitsCount >= 10 && digitsCount <= 12) {
                        phone = potentialPhone;
                        break;
                    }
                    currentDigitGroup = [];
                }
            }
        }
        if (currentDigitGroup.length > 0 && !phone) {
            const potentialPhone = currentDigitGroup.join('');
            const digitsCount = potentialPhone.replace(/\D/g, '').length;
            if (digitsCount >= 10 && digitsCount <= 12) {
                phone = potentialPhone;
            } else {
                phoneStartIndex = -1;
                phoneEndIndex = -1;
            }
        }

        // Reconstruct cleaned text without email and phone to match name & query
        const cleanTokens = [];
        for (let i = 0; i < remainingTokens.length; i++) {
            if (phone && i >= phoneStartIndex && i <= phoneEndIndex) {
                continue;
            }
            cleanTokens.push(remainingTokens[i]);
        }
        const cleanText = cleanTokens.join(' ');

        // Extract Name
        let name = '';
        const nameRegex = /(?:my name is|i am|this is|i'm|name is)\s+([a-z0-9'-]+(?:\s+[a-z0-9'-]+){0,4})/i;
        const nameMatch = cleanText.match(nameRegex);
        if (nameMatch) {
            const nameCandidateWords = nameMatch[1].split(/\s+/);
            const nameWords = [];
            for (let word of nameCandidateWords) {
                if (stopWords.has(word)) {
                    break;
                }
                nameWords.push(word.charAt(0).toUpperCase() + word.slice(1));
            }
            if (nameWords.length > 0) {
                name = nameWords.join(' ');
            }
        }

        // Extract Query
        let query = '';
        const queryRegex = /(?:want to know about|interested in|looking for|query is|message is|details about|question about|ask about|know about|regarding|about)\s+([^.]+)/i;
        const queryMatch = cleanText.match(queryRegex);
        if (queryMatch) {
            const queryCandidateWords = queryMatch[1].trim().split(/\s+/);
            const queryWords = [];
            for (let word of queryCandidateWords) {
                if (queryTruncateWords.has(word)) {
                    break;
                }
                queryWords.push(word);
            }

            let q = queryWords.join(' ').trim();
            // Strip leading "the ", "a ", "an " case-insensitively
            q = q.replace(/^(?:the|a|an)\s+/i, '');
            // Strip trailing polite words
            q = q.replace(/\s+(?:please|thank\s*you|thanks)$/i, '');
            if (q) {
                query = q.charAt(0).toUpperCase() + q.slice(1);
            }
        }

        return { name, email, phone, query };
    },

    submitVoiceLead() {
        const shadow = this.shadowRoot;
        const form = shadow.getElementById('lead-overlay-form');

        let name = 'Voice User';
        let email = '';
        let phone = '';
        let query = 'Submitted via voice confirmation.';

        let submitBtn = null;
        if (form) {
            submitBtn = form.querySelector('.submit-lead-btn');
            if (submitBtn) {
                submitBtn.textContent = 'Submitting...';
                submitBtn.disabled = true;
            }

            const nameEl = form.querySelector('#lead-name');
            const emailEl = form.querySelector('#lead-email');
            const phoneEl = form.querySelector('#lead-phone');
            const queryEl = form.querySelector('#lead-query');
            if (nameEl) name = nameEl.value;
            if (emailEl) email = emailEl.value;
            if (phoneEl) phone = phoneEl.value;
            if (queryEl) query = queryEl.value;
        } else if (this.pendingLeadPrefill) {
            name = this.pendingLeadPrefill.name || name;
            email = this.pendingLeadPrefill.email || email;
            phone = this.pendingLeadPrefill.phone || phone;
            query = this.pendingLeadPrefill.query || query;
        } else {
            return;
        }

        let body = {};
        if (this.isWordPressMode) {
            body = { lead_data: { name, email, phone, query } };
        } else {
            body = { action: 'lead', lead_data: { name, email, phone, query } };
        }

        fetch(this.leadEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type':      'application/json',
                'X-WAAI-Nonce':      this.csrfToken  || '',
                'X-WAAI-Session-ID': this.sessionId  || '',
                'X-WAAI-Trace-ID':   this.newTraceId ? this.newTraceId() : '',
            },
            body: JSON.stringify(body)
        })
            .then(res => res.json())
            .then(data => {
                this.awaitingLeadConfirmation = false;
                this.pendingLeadPrefill = null;
                this.accumulatedLeadDetails = { name: '', email: '', phone: '', query: '' };
                this.toggleOverlay(false);

                if (data.success) {
                    // Capture and persist the phone number from the lead form
                    this.capturedPhoneNumber = phone;
                    try {
                        localStorage.setItem('waai_user_phone', phone);
                    } catch (e) { }

                    const msg = "Thank you! Your details have been submitted and confirmed successfully. I have sent your contact information to our team and they will reach out to you shortly.";
                    this.addMessage(msg, "assistant");
                    this.speak(msg);
                } else {
                    if (submitBtn) {
                        submitBtn.textContent = 'Submit Information';
                        submitBtn.disabled = false;
                    }
                    const msg = "Submission failed. Please check the form details and click submit.";
                    this.addMessage(msg, "assistant");
                    this.speak(msg);
                }
            })
            .catch(err => {
                console.error(err);
                this.awaitingLeadConfirmation = false;
                this.pendingLeadPrefill = null;
                this.accumulatedLeadDetails = { name: '', email: '', phone: '', query: '' };
                if (submitBtn) {
                    submitBtn.textContent = 'Submit Information';
                    submitBtn.disabled = false;
                }
                const msg = "Connection failed. Please submit the form directly.";
                this.addMessage(msg, "assistant");
                this.speak(msg);
            });
    },

    isSelfFeedback(userText, aiText, isFinal = false) {
        if (!aiText) return false;

        // Normalizer to align TTS pronunciation with written text
        const normalizeForEcho = (text) => {
            let s = text.toLowerCase();

            // Map common spoken numbers to digits
            const numMap = {
                'zero': '0', 'one': '1', 'two': '2', 'three': '3', 'four': '4',
                'five': '5', 'six': '6', 'seven': '7', 'eight': '8', 'nine': '9',
                'ten': '10', 'eleven': '11', 'twelve': '12', 'thirteen': '13', 'fourteen': '14',
                'fifteen': '15', 'sixteen': '16', 'seventeen': '17', 'eighteen': '18', 'nineteen': '19',
                'twenty': '20', 'thirty': '30', 'forty': '40', 'fifty': '50', 'sixty': '60',
                'seventy': '70', 'eighty': '80', 'ninety': '90', 'hundred': '00', 'thousand': '000',
                // Hindi spoken number normalization
                'शून्य': '0', 'एक': '1', 'दो': '2', 'तीन': '3', 'चार': '4',
                'पाँच': '5', 'पांच': '5', 'छह': '6', 'छः': '6', 'सात': '7',
                'आठ': '8', 'नौ': '9', 'दस': '10', 'सौ': '00', 'हज़ार': '000',
                'हजार': '000', 'लाख': '00000'
            };

            for (const [word, digit] of Object.entries(numMap)) {
                s = s.replace(new RegExp('\\b' + word + '\\b', 'g'), digit);
            }

            // Normalize "k" suffix for thousands (e.g. "18 k" -> "18000", "8k" -> "8000")
            s = s.replace(/(\d)\s*k\b/g, '$1000');

            // Normalize common industry terms that might be transcribed weirdly
            s = s.replace(/\be[-\s]?commerce\b/g, 'ecommerce');

            // Strip hallucinated "www." or "www " which often prepends "web"
            s = s.replace(/\bwww\.?\s*/g, '');

            // Replace non-alphanumeric with spaces to avoid mashing words together (e.g. "web.development" -> "web development")
            // Preserve Hindi (Devanagari) and Urdu (Arabic) characters for echo matching
            s = s.replace(/[^a-z0-9\u0900-\u097F\u0600-\u06FF]/g, ' ');

            return s.replace(/\s+/g, ' ').trim();
        };

        const cleanUser = normalizeForEcho(userText);
        const cleanAi = normalizeForEcho(aiText);

        if (!cleanUser) return 'too_short';

        const solidUser = cleanUser.replace(/\s+/g, '');
        const solidAi = cleanAi.replace(/\s+/g, '');

        // 1. DEFINITIVE ECHO: The user text is a verbatim substring of AI text (no spaces).
        //    This catches the most common echo pattern — microphone picking up speaker output.
        //    Only trigger if the match is substantial (>15 chars to avoid common words).
        if (solidUser.length > 15 && solidAi.includes(solidUser)) {
            return 'confident_echo';
        }

        // 2. DEFINITIVE ECHO: Fuzzy substring match (handles slight pronunciation variations ANYWHERE in the text)
        if (solidUser.length > 12 && this.getFuzzySubstringMatch(solidUser, solidAi)) {
            return 'confident_echo';
        }

        // 3. DEFINITIVE INTERRUPTION: Check for strong interruption keywords.
        //    If the user says "no", "stop", "wait", "actually", etc., it's always real.
        const hardInterruptKeywords = ['stop', 'wait', 'no', 'halt', 'pause', 'cancel', 'actually',
            'enough', 'hold', 'quiet', 'nevermind', 'incorrect', 'wrong', 'listen', 'excuse',
            // Hindi (Devanagari)
            'नहीं', 'रुको', 'रुकिए', 'सुनो', 'सुनिए', 'बस', 'गलत', 'चुप', 'हटाओ', 'ठहरो',
            // Hinglish (Romanized Hindi)
            'ruko', 'nahi', 'nahin', 'suno', 'bas', 'galat', 'theek', 'hato', 'chup', 'thehro', 'acha'];

        const userWords = cleanUser.split(/\s+/).filter(w => w.length > 0);
        const aiWords = new Set(cleanAi.split(/\s+/));
        for (const word of userWords) {
            if (hardInterruptKeywords.includes(word) && !aiWords.has(word)) {
                return false; // Definite real user speech
            }
        }

        // 4. TOO SHORT: If the transcript is too short (< 3 words), wait for more speech before deciding
        if (!isFinal && userWords.length < 3) {
            return 'too_short';
        }

        // 5. SEQUENTIAL CHUNK MATCH: Check if a consecutive run of 4+ user words appear
        //    in the same order in the AI text. This catches echoes while allowing topic overlap.
        //    Hindi words are shorter/more particles, so use a larger window to avoid false positives.
        const aiText_clean = cleanAi;
        const hasDevanagari = /[\u0900-\u097F]/.test(cleanUser);
        const chunkSize = Math.min(hasDevanagari ? 6 : 4, userWords.length);
        for (let i = 0; i <= userWords.length - chunkSize; i++) {
            const chunk = userWords.slice(i, i + chunkSize).join(' ');
            if (aiText_clean.includes(chunk)) {
                // If the user's text is almost entirely this matched chunk, it's an echo.
                // But if they spoke significantly more words, it's a real interruption + overlap.
                if (userWords.length <= chunkSize + 2) {
                    return 'confident_echo';
                }
            }
        }

        // 6. BAG OF WORDS OVERLAP: Catch heavily mangled speech where sequences break but core words match.
        // Extremely important for Sarvam API (Indian accent) which speech recognition often mistranscribes!
        // We filter out common stop/filler words because speech recognition often hallucinates them around an echo.
        const stopWords = new Set(['and', 'or', 'but', 'the', 'a', 'an', 'some', 'any', 'in', 'of', 'to', 'for', 'with', 'on', 'at', 'by', 'this', 'that', 'these', 'those', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'it', 'its', 'they', 'them', 'their', 'he', 'him', 'his', 'she', 'her', 'we', 'us', 'our', 'you', 'your', 'my', 'mine', 'words', 'word', 'things', 'thing', 'stuff', 'like', 'just', 'so', 'then', 'than', 'there', 'here', 'where', 'when', 'why', 'how', 'what', 'which', 'who', 'whom', 'whose', 'can', 'could', 'will', 'would', 'shall', 'should', 'may', 'might', 'must', 'ought', 'need', 'dare', 'used']);
        
        const significantUserWords = userWords.filter(w => w.length >= 3 && !stopWords.has(w));
        
        // If the entire transcript was just filler words, or there is only 1 significant word, we fall back to a simple check
        if (significantUserWords.length === 0) {
            // The transcript consists entirely of stop words (e.g., "there is a"). 
            // Since it passed step 3 (no hard interrupt keywords), this is just conversational noise or mic bleed.
            return 'confident_echo';
        } else if (significantUserWords.length === 1) {
            const singleWord = significantUserWords[0];
            if (aiWords.has(singleWord) || Array.from(aiWords).some(aw => aw.length >= 4 && this.getFuzzySubstringMatch(singleWord, aw))) {
                return 'confident_echo';
            }
        } else if (significantUserWords.length >= 2) {
            let matchCount = 0;
            for (const word of significantUserWords) {
                if (aiWords.has(word)) {
                    matchCount++;
                } else {
                    for (const aiWord of aiWords) {
                        if (aiWord.length >= 3 && this.getFuzzySubstringMatch(word, aiWord)) {
                            matchCount++;
                            break;
                        }
                    }
                }
            }
            // If >= 60% of significant words match the AI's recent speech, it's a mangled echo
            if ((matchCount / significantUserWords.length) >= 0.6) {
                return 'confident_echo';
            }
        }

        return false;
    },

    getFuzzySubstringMatch(shortStr, longStr) {
        if (shortStr.length > longStr.length + 5) return false;

        // Allow up to 15% error margin
        const maxErrors = Math.floor(shortStr.length * 0.15) + 1;

        // Find possible starting points in longStr
        for (let i = 0; i <= longStr.length - shortStr.length + maxErrors; i++) {
            let errors = 0;
            let shortIdx = 0;
            let longIdx = i;

            while (shortIdx < shortStr.length && longIdx < longStr.length) {
                if (shortStr[shortIdx] === longStr[longIdx]) {
                    shortIdx++;
                    longIdx++;
                } else {
                    errors++;
                    if (errors > maxErrors) break;

                    if (shortStr[shortIdx] === longStr[longIdx + 1]) {
                        longIdx++;
                    } else if (shortStr[shortIdx + 1] === longStr[longIdx]) {
                        shortIdx++;
                    } else {
                        shortIdx++;
                        longIdx++;
                    }
                }
            }

            if (shortIdx >= shortStr.length - maxErrors) {
                return true;
            }
        }
        return false;
    },

    startVoiceCall(skipGreeting = false) {
        if (!this.recognition) return;

        this.isContinuousVoiceMode = true;
        this.isMuted = false;
        sessionStorage.setItem('waai_voice_call_active', 'true');

        // Save speech synthesis setting and force it ON for the call
        this.prevSpeechSynthesisActive = this.speechSynthesisActive;
        this.speechSynthesisActive = true;

        const chatPanel = this.shadowRoot.getElementById('chat-panel');
        if (chatPanel) {
            chatPanel.classList.add('waai-mobile-transparent');
        }

        // Update header readout controls visually
        const toggleSpeech = this.shadowRoot.getElementById('toggle-speech');
        if (toggleSpeech) {
            const speakerOn = toggleSpeech.querySelector('.speaker-on');
            const speakerOff = toggleSpeech.querySelector('.speaker-off');
            if (speakerOn && speakerOff) {
                speakerOn.classList.remove('hidden');
                speakerOff.classList.add('hidden');
            }
        }

        // Toggle footer call button state
        const toggleBtn = this.shadowRoot.getElementById('voice-call-toggle');
        if (toggleBtn) {
            toggleBtn.classList.add('active');
            toggleBtn.title = "End Live Voice Call";
        }

        // Show Live Status Bar
        const statusBar = this.shadowRoot.getElementById('live-status-bar');
        if (statusBar) statusBar.classList.remove('hidden');

        // Show Voice Immersive Overlay
        const immersiveOverlay = this.shadowRoot.getElementById('voice-immersive-overlay');
        if (immersiveOverlay) {
            if (localStorage.getItem('waai_voice_overlay_hidden') !== 'true') {
                immersiveOverlay.classList.add('active');
            }
        }

        // Initialize UI State
        this.transitionTo('speaking');

        // Mute button reset
        const micBtn = this.shadowRoot.getElementById('mic-btn');
        if (micBtn) {
            micBtn.classList.remove('muted');
            micBtn.title = "Mute Live Call Mic";
        }

        // ── MOBILE CRITICAL: Create AudioContext from the user gesture ─────────
        if (!this._piperAudioContext) {
            try {
                this._piperAudioContext = new (window.AudioContext || window.webkitAudioContext)();
            } catch (e) {
                console.warn('[Piper] AudioContext not available, using Audio element fallback:', e);
                this._piperAudioContext = null;
            }
        }
        if (this._piperAudioContext && this._piperAudioContext.state === 'suspended') {
            this._piperAudioContext.resume().catch(() => { });
        }

        // ── MOBILE CRITICAL: Start mic synchronously from the user gesture ────
        this.startListening();

        // Start conversation — use voice greeting from admin config
        if (!skipGreeting) {
            const greeting = this.cfg.voiceGreeting || 'Welcome! How can I help you today?';
            setTimeout(() => {
                if (this.assistantState === 'speaking') {
                    this.speak(greeting);
                }
            }, 300);
        }
    },

    endVoiceCall() {
        this.isContinuousVoiceMode = false;
        this.isMuted = false;
        sessionStorage.removeItem('waai_voice_call_active');

        // Toggle footer call button state
        const toggleBtn = this.shadowRoot.getElementById('voice-call-toggle');
        if (toggleBtn) {
            toggleBtn.classList.remove('active');
            toggleBtn.title = "Start Live Voice Call";
        }

        // Hide Live Status Bar
        const statusBar = this.shadowRoot.getElementById('live-status-bar');
        if (statusBar) statusBar.classList.add('hidden');

        // Hide Voice Immersive Overlay
        const immersiveOverlay = this.shadowRoot.getElementById('voice-immersive-overlay');
        if (immersiveOverlay) immersiveOverlay.classList.remove('active');

        // Stop microphone and speech via state transition
        this.transitionTo('idle');
        this.currentAISpokenText = '';
        this.lastAISpokenText = '';
        this.aiSpeechEndTime = 0;
        this.lastUserSpeechTime = 0;

        // Fully stop the VAD on Android (release mic hardware entirely)
        if (this._vadManager) {
            this._vadManager.stop();
            this._vadManager = null;
        }

        // Restore previous speech readout active state
        this.speechSynthesisActive = this.prevSpeechSynthesisActive;

        const chatPanel = this.shadowRoot.getElementById('chat-panel');
        if (chatPanel) {
            chatPanel.classList.remove('waai-mobile-transparent');
        }

        // Update header readout controls visually to match restored state
        const toggleSpeech = this.shadowRoot.getElementById('toggle-speech');
        if (toggleSpeech) {
            const speakerOn = toggleSpeech.querySelector('.speaker-on');
            const speakerOff = toggleSpeech.querySelector('.speaker-off');
            if (speakerOn && speakerOff) {
                if (this.speechSynthesisActive) {
                    speakerOn.classList.remove('hidden');
                    speakerOff.classList.add('hidden');
                } else {
                    speakerOn.classList.add('hidden');
                    speakerOff.classList.remove('hidden');
                }
            }
        }

        // Reset mic button styling if it was muted
        const micBtn = this.shadowRoot.getElementById('mic-btn');
        if (micBtn) {
            micBtn.classList.remove('muted');
            micBtn.title = "Speak into microphone";
        }

        // Reset trigger styling when ending call
        const trigger = this.shadowRoot.getElementById('chat-trigger');
        if (trigger) {
            trigger.classList.remove('voice-active');
            const iconChat = trigger.querySelector('.icon-chat');
            const iconClose = trigger.querySelector('.icon-close');
            const iconVoice = trigger.querySelector('.icon-voice');
            if (this.isOpen) {
                if (iconChat) iconChat.classList.add('hidden');
                if (iconVoice) iconVoice.classList.add('hidden');
                if (iconClose) iconClose.classList.remove('hidden');
            } else {
                if (iconChat) iconChat.classList.remove('hidden');
                if (iconVoice) iconVoice.classList.add('hidden');
                if (iconClose) iconClose.classList.add('hidden');
            }
        }
    },

    toggleVoiceMute() {
        const shadow = this.shadowRoot;
        const micBtn = shadow.getElementById('mic-btn');

        this.isMuted = !this.isMuted;

        if (this.isMuted) {
            if (micBtn) {
                micBtn.classList.add('muted');
                micBtn.title = "Unmute Live Call Mic";
            }
            this.stopListening();
            this.updateVoiceCallUI('muted');
        } else {
            if (micBtn) {
                micBtn.classList.remove('muted');
                micBtn.title = "Mute Live Call Mic";
            }
            if (this.assistantState === 'speaking') {
                this.updateVoiceCallUI('speaking');
                this.startListening();
            } else if (this.assistantState === 'processing') {
                this.updateVoiceCallUI('thinking');
            } else {
                if (this.assistantState === 'listening') {
                    this.updateVoiceCallUI('listening');
                    this.startListening();
                } else {
                    this.transitionTo('listening');
                }
            }
        }
    },

    buildLangToggleHtml() {
        // Option 1: Automatic universal language ('en-IN'). 
        // We no longer render manual toggles.
        return '';
    },

    setupLangToggleListeners() {
        const statusBar = this.shadowRoot.getElementById('live-status-bar');
        if (!statusBar) return;
        statusBar.querySelectorAll('.waai-lang-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const lang = btn.getAttribute('data-lang');
                const index = parseInt(btn.getAttribute('data-index'));
                this.activeLangIndex = index;
                if (this.recognition) {
                    const wasListening = this.isListening;
                    try { this.recognition.stop(); } catch (e) { }
                    this.recognition.lang = lang;
                    if (wasListening && this.isContinuousVoiceMode && !this.isMuted) {
                        setTimeout(() => { try { this.startListening(); } catch (e) { } }, 200);
                    }
                }
                statusBar.querySelectorAll('.waai-lang-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
            });
        });
    },

    updateVoiceCallUI(state, customCaption = null) {
        const shadow = this.shadowRoot;
        const statusBar = shadow.getElementById('live-status-bar');
        if (!statusBar) return;

        const statusText = statusBar.querySelector('.live-text');
        const visualizer = statusBar.querySelector('.live-mini-visualizer');

        if (!statusText || !visualizer) return;

        // Reset classes
        visualizer.classList.remove('live-status-listening', 'live-status-speaking', 'live-status-thinking');

        const orb = this.shadowRoot.getElementById('waai-orb');
        if (orb) orb.className = 'waai-orb';

        if (state === 'muted' || (this.isMuted && state === 'listening')) {
            statusText.textContent = 'Live Call: Mic Muted';
            return;
        }

        switch (state) {
            case 'listening':
                visualizer.classList.add('live-status-listening');
                if (orb) orb.classList.add('state-listening');
                statusText.textContent = customCaption || 'Live Call: Listening...';
                break;
            case 'thinking':
                visualizer.classList.add('live-status-thinking');
                if (orb) orb.classList.add('state-thinking');
                statusText.textContent = customCaption || 'Live Call: Thinking...';
                break;
            case 'speaking':
                visualizer.classList.add('live-status-speaking');
                if (orb) orb.classList.add('state-speaking');
                statusText.textContent = customCaption || 'Live Call: Speaking...';
                break;
        }
    },

};
