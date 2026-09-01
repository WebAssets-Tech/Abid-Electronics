/**
 * State Module
 * Centralizes all state tracking and local storage persistence for the AI Assistant.
 */

export const StateMixin = {
    initState() {
        this.isOpen = false;
        this.chatHistory = [];
        this.suggestionUsed = false;
        this.proactiveTimer = null;
        this.speechSynthesisActive = true;
        this.prevSpeechSynthesisActive = true;
        this.isSpeaking = false;
        this.isListening = false;
        this.isContinuousVoiceMode = false;
        this.isMuted = false;
        this.activeLangIndex = 0;
        this.currentAISpokenText = '';
        this.lastAISpokenText = '';
        this.aiSpeechEndTime = 0;
        this.lastUserSpeechTime = 0;
        this.isFetchingResponse = false;
        
        // Assistant state machine state
        this.assistantState = 'idle'; // idle, listening, processing, speaking, interrupted, cooldown
        this.cooldownTimeout = null;
        
        // Lead confirmation state
        this.awaitingLeadConfirmation = false;
        this.pendingLeadPrefill = null;
        this.accumulatedLeadDetails = { name: '', email: '', phone: '', query: '' };
        
        // Load persisted context
        this.capturedPhoneNumber = this.getStoredItem('waai_user_phone', '');
        this.capturedUserEmail = this.getStoredItem('waai_user_email', '');
    },

    transitionTo(newState, data = {}) {
        const oldState = this.assistantState || 'idle';
        if (oldState === newState) return;

        this.waLog(`[State Machine] Transition: ${oldState} -> ${newState}`, data);


        // 1. Exit actions for the current/old state
        switch (oldState) {
            case 'listening':
                // Stop the microphone when leaving listening state, unless we are interrupted or going to processing
                if (newState !== 'processing' && newState !== 'interrupted') {
                    this.stopListening();
                }
                break;
            case 'speaking':
                // Cancel speech when leaving speaking state, unless moving to interrupted
                if (newState !== 'interrupted') {
                    this.cancelSpeaking();
                }
                break;
            case 'cooldown':
                if (this.cooldownTimeout) {
                    clearTimeout(this.cooldownTimeout);
                    this.cooldownTimeout = null;
                }
                break;
        }

        // 2. Set new state
        this.assistantState = newState;

        // Sync old boolean variables for compatibility with visual render/indicators
        this.isSpeaking = (newState === 'speaking');
        this.isListening = (newState === 'listening');
        this.isFetchingResponse = (newState === 'processing');

        // 3. Entry actions for the new state
        switch (newState) {
            case 'idle':
                this.stopListening();
                this.cancelSpeaking();
                if (this.isContinuousVoiceMode) {
                    this.updateVoiceCallUI('listening', 'Live Call: Idle');
                }
                break;

            case 'listening':
                if (this.isContinuousVoiceMode) {
                    if (!this.isMuted) {
                        this.updateVoiceCallUI('listening');
                        this.startListening();
                    } else {
                        this.updateVoiceCallUI('muted');
                    }
                } else {
                    this.startListening();
                }
                break;

            case 'processing':
                this.abortListening();
                if (this.isContinuousVoiceMode) {
                    this.updateVoiceCallUI('thinking');
                }
                break;

            case 'speaking':
                if (this.isContinuousVoiceMode) {
                    if (this.cfg && this.cfg.allowInterruptions === '0') {
                        this.updateVoiceCallUI('speaking', 'Speaking (Mic Muted)');
                        this.stopListening();
                    } else {
                        this.updateVoiceCallUI('speaking');
                    }
                }
                if (data.text) {
                    this.speak(data.text);
                }
                break;

            case 'interrupted':
                this.cancelSpeaking();
                this.aiSpeechEndTime = Date.now();
                if (this.isContinuousVoiceMode) {
                    this.updateVoiceCallUI('listening');
                }
                // Transition immediately to listening state to capture remaining user voice,
                // but keep the mic active (do NOT stop listening)
                this.assistantState = 'listening';
                this.isListening = true;
                break;

            case 'cooldown':
                this.stopListening();
                const duration = data.duration !== undefined ? data.duration : 600;
                this.cooldownTimeout = setTimeout(() => {
                    this.cooldownTimeout = null;
                    const next = data.nextState || (this.isContinuousVoiceMode ? 'listening' : 'idle');
                    this.transitionTo(next);
                }, duration);
                break;
        }
    },

    getStoredItem(key, fallback = null) {
        try {
            const actualKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey(key) : key;
            return localStorage.getItem(actualKey) || fallback;
        } catch (e) {
            return fallback;
        }
    },

    setStoredItem(key, value) {
        try {
            const actualKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey(key) : key;
            localStorage.setItem(actualKey, value);
            return true;
        } catch (e) {
            return false;
        }
    },

    restoreHistoryFromStorage() {
        try {
            const storageKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('waai_chat_history') : 'waai_abid_chat_history';
            const saved = localStorage.getItem(storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                if (Array.isArray(parsed) && parsed.length > 0) {
                    this.chatHistory = parsed.filter(m => m.role === 'user' || m.role === 'assistant');
                    
                    // Render history visually
                    this.chatHistory.forEach(msg => {
                        if (msg.isHidden || (msg.content && msg.content.includes('[SYSTEM INSTRUCTION:'))) {
                            return;
                        }
                        this.addMessage(msg.visualContent || msg.content, msg.role, msg.content, true);
                    });
                    return true;
                }
            }
        } catch (e) {
            console.error('Failed to parse saved chat history:', e);
        }
        return false;
    },

    saveHistoryToStorage() {
        try {
            const storageKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('waai_chat_history') : 'waai_abid_chat_history';
            localStorage.setItem(storageKey, JSON.stringify(this.chatHistory));
        } catch (e) {
            console.error('Failed to save chat history:', e);
        }
    },

    clearHistory() {
        this.chatHistory = [];
        try {
            const storageKey = (typeof this.getStorageKey === 'function') ? this.getStorageKey('waai_chat_history') : 'waai_abid_chat_history';
            localStorage.removeItem(storageKey);
        } catch (e) {}
    }
};
