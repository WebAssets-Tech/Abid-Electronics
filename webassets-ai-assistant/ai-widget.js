/**
 * Framework-Agnostic Conversational AI Assistant Widget
 * Package: webassets-ai-assistant
 * Version: 3.0.9
 *
 * Uses HTML5 Web Component, Shadow DOM, Web Speech API, and relative endpoints.
 */

import { ConfigMixin } from './modules/Config.js?v=3.0.25';
import { StateMixin } from './modules/State.js?v=3.0.25';
import { LoggerMixin } from './modules/Logger.js?v=3.0.25';
import { ValidatorsMixin } from './modules/Validators.js?v=3.0.25';
import { EventBusMixin } from './modules/EventBus.js?v=3.0.25';
import { ConversationStateMixin } from './modules/ConversationState.js?v=3.0.25';
import { ActionsMixin } from './modules/Actions.js?v=3.0.25';
import { UIMixin } from './modules/UI.js?v=3.0.25';
import { APIMixin } from './modules/API.js?v=3.0.25';
import { FormsMixin } from './modules/Forms.js?v=3.0.25';
import { VoiceMixin } from './modules/Voice.js?v=3.0.25';
import { TTSQueue } from './modules/TTSQueue.js?v=3.0.25';
import { AgenticActions } from './modules/AgenticActions.js?v=3.0.25';

class AIAssistantWidget extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });

        this.initConfig();
        this.initState();
        this.initLogger();
        this.initEventBus();          // Must be after initLogger
        this.initConversationState(); // Track high-level journey state

        // Sentence-aware TTS queue (browser SpeechSynthesis path)
        this.ttsQueue = new TTSQueue(this);

        // Speech Recognition Setup
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SpeechRecognition) {
            this.recognition = new SpeechRecognition();
            this.recognition.continuous = true;
            this.recognition.interimResults = true;
            this.recognition.lang = 'en-IN'; // Universal mode for English + Hinglish
        } else {
            this.recognition = null;
        }
    }

    connectedCallback() {
        // Move to body to prevent transform stacking context bugs on fixed elements
        if (this.parentNode !== document.body && this.parentNode !== document.documentElement) {
            document.body.appendChild(this);
            return;
        }

        this.render();
        this.setupEventListeners();
        this._bindEventBusListeners(); // Wire cross-module EventBus subscriptions
        // Restore chat history from localStorage; show welcome only if no history
        const hadHistory = this.restoreHistoryFromStorage();
        if (!hadHistory) {
            this.addWelcomeMessage();
        }
        // Start proactive trigger timer
        if (this.startProactiveTrigger) this.startProactiveTrigger();

        // Initiate background LLM processing for interactables + sitemap cache
        if (typeof this.initBackgroundProcessing === 'function') {
            this.initBackgroundProcessing();
        }

        // Resume Continuous Agentic Environment after a cross-page redirect or during active voice call
        const isVoiceCallActive = sessionStorage.getItem('waai_voice_call_active') === 'true';
        const isAgenticRedirect = sessionStorage.getItem('waai_agentic_redirect') === 'true';
        if (isVoiceCallActive || isAgenticRedirect) {
            sessionStorage.removeItem('waai_agentic_redirect');
            const wasOpen = sessionStorage.getItem('waai_chat_panel_open') !== 'false';
            setTimeout(() => {
                this.toggleChatPanel(wasOpen); // Restore open/closed state
                if (isVoiceCallActive && this.startVoiceCall) this.startVoiceCall(true); // Start mic (skip default greeting)

                // Post-Navigation Awareness (Phase 3)
                // Determine clean page name from title (e.g. "Contact Us - WebAssets" -> "Contact Us")
                let pageName = document.title ? document.title.split('-')[0].split('|')[0].trim() : "this page";
                if (!pageName || pageName.toLowerCase() === 'home') pageName = "this page";

                if (typeof this.getPageContext === 'function' && typeof this.fetchAIResponse === 'function') {
                    if (window.waaiConfig && window.waaiConfig.enablePostNav === '1') {
                        const ctx = this.getPageContext();
                        const intentMsg = `[SYSTEM INSTRUCTION: Navigation successful. The user is now on the ${pageName} page (URL: ${window.location.href}). There are ${ctx.interactables ? ctx.interactables.length : 0} interactive elements visible. Provide a brief, contextual greeting. Acknowledge the new page and optionally suggest a relevant action based on the visible CTAs. Keep it under 2 sentences. DO NOT say 'System Instruction' or narrate that you navigated here.]`;

                        // Push silently to history without showing in UI
                        this.chatHistory.push({ role: 'user', content: intentMsg, isHidden: true });
                        this.saveHistoryToStorage();
                        this.fetchAIResponse(intentMsg);
                    }

                    // Multi-Step Planner: Resume pending tasks
                    if (window.waaiConfig && window.waaiConfig.enableTaskQueue === '1') {
                        const pendingTasksJson = sessionStorage.getItem('waai_pending_task_queue');
                        if (pendingTasksJson) {
                            sessionStorage.removeItem('waai_pending_task_queue');
                            try {
                                const pendingTasks = JSON.parse(pendingTasksJson);
                                if (pendingTasks && pendingTasks.length > 0) {
                                    setTimeout(() => {
                                        if (this.EventBus) {
                                            this.EventBus.emit('api:response', { actions: pendingTasks });
                                        }
                                    }, 2000); // Wait for new page DOM to fully settle
                                }
                            } catch (e) { console.error('Failed to resume tasks', e); }
                        }
                    }

                } else {
                    const msg = `Here we are at the ${pageName}! What would you like to explore?`;
                    this.addMessage(msg, 'assistant');
                    if (this.speak) {
                        setTimeout(() => { this.speak(msg); }, 350);
                    }
                }
            }, 800); // Slight delay for UI stabilization
        }
    }

    disconnectedCallback() {
        // Clean up TTS queue on widget removal
        if (this.ttsQueue) this.ttsQueue.cancel();
        this.destroyEventBus();
    }

    /**
     * Wire all cross-module EventBus subscriptions here after all mixins are composed.
     * This replaces direct method calls between modules.
     */
    _bindEventBusListeners() {
        // When the API receives a full response, process any action intent.
        // If a conversation journey is already active, requestConversationAction()
        // will queue or deduplicate the action safely.
        this.EventBus.on('api:response', async ({ action, actions }) => {
            const processAction = async (act) => {
                if (!act) return;

                // Intent Confidence Interception (Phase 2)
                if (act.params && act.params.confidence !== undefined && act.params.confidence < 0.7) {
                    const fallbackMsg = "I'm not entirely sure which page or section you meant. Could you clarify?";
                    this.addMessage(`⚠️ Action aborted (Low Confidence): ${fallbackMsg}`, 'assistant');
                    if (this.speechSynthesisActive) {
                        this.transitionTo('speaking', { text: fallbackMsg });
                    }
                    return { success: false, reason: "low_confidence" }; // Abort execution
                }

                if (act.type === 'agentic_navigation' || act.type === 'agentic_interaction' || act.type === 'scroll_page' || act.type === 'open_assistant_overlay') {
                    if (window.waaiConfig && window.waaiConfig.enableAgentic === '0') {
                        const disabledMsg = "I'm sorry, but browser navigation and page interaction features are currently disabled in the settings, so I cannot perform this action right now.";
                        this.addMessage(`⚠️ Action blocked: ${disabledMsg}`, 'assistant');
                        if (this.speechSynthesisActive) {
                            this.transitionTo('speaking', { text: disabledMsg });
                        }
                        return { success: false, reason: "agentic_disabled" };
                    }
                    // Agentic actions happen immediately and don't open blocking UI overlays
                    const result = await AgenticActions.handleAction(act, this);
                    if (result && !result.success && !result.redirecting) {
                        // Global Failure Recovery
                        let failureMsg = "I couldn't complete that action right now.";
                        if (result.reason === 'route_not_found') failureMsg = "I couldn't find the requested section on this page.";
                        if (result.reason === 'dom_element_missing') failureMsg = "I couldn't find that specific element to click.";
                        if (result.reason === 'form_field_missing') failureMsg = "I couldn't find the form field to fill.";

                        // Instead of a hard UI error, trigger a hidden feedback loop so the AI can handle it conversationally
                        if (typeof this.fetchAIResponse === 'function') {
                            const hiddenInstruction = `[SYSTEM INSTRUCTION: Action failed. Reason: ${failureMsg}. Clarify the user's intent. If there are multiple options, ask them which one they meant. DO NOT say 'System Instruction' or explicitly say 'Action failed'. Keep it conversational.]`;
                            this.chatHistory.push({ role: 'user', content: hiddenInstruction, isHidden: true });
                            this.saveHistoryToStorage();
                            this.transitionTo('processing');
                            this.fetchAIResponse(hiddenInstruction);
                        } else {
                            this.addMessage(`⚠️ Action failed: ${failureMsg}`, 'assistant');
                            if (this.speechSynthesisActive) {
                                this.transitionTo('speaking', { text: failureMsg });
                            }
                        }
                    }
                    return result;
                } else {
                    // Let ConversationState decide whether to execute or queue standard actions
                    const accepted = this.requestConversationAction(act.type, act.params);
                    if (accepted) {
                        this.processActionRequest(act);
                    }
                    return { success: true };
                }
            };

            if (actions && Array.isArray(actions)) {
                // If multiple tool calls were returned (e.g. filling multiple form fields)
                for (let i = 0; i < actions.length; i++) {
                    const act = actions[i];

                    // Check if this action is explicitly a navigation redirect beforehand
                    const isExplicitRedirect = (act.type === 'agentic_navigation' && act.params && act.params.action === 'redirect');

                    if (window.waaiConfig && window.waaiConfig.enableConversationalMemory === '1') {
                        sessionStorage.setItem('waai_last_action', JSON.stringify(act));
                    }

                    if (isExplicitRedirect) {
                        const remaining = actions.slice(i + 1);
                        if (remaining.length > 0 && window.waaiConfig && window.waaiConfig.enableTaskQueue === '1') {
                            sessionStorage.setItem('waai_pending_task_queue', JSON.stringify(remaining));
                        }
                    }

                    const result = await processAction(act);

                    if (isExplicitRedirect || (result && result.redirecting === true)) {
                        // If it redirected dynamically (e.g. click element resolved to a redirect), save remaining tasks if not already done
                        if (!isExplicitRedirect) {
                            const remaining = actions.slice(i + 1);
                            if (remaining.length > 0 && window.waaiConfig && window.waaiConfig.enableTaskQueue === '1') {
                                sessionStorage.setItem('waai_pending_task_queue', JSON.stringify(remaining));
                            }
                        }
                        break; // STOP executing further actions on the current unloading page
                    }
                }
            } else if (action) {
                if (window.waaiConfig && window.waaiConfig.enableConversationalMemory === '1') {
                    sessionStorage.setItem('waai_last_action', JSON.stringify(action));
                }
                // Legacy single action support
                await processAction(action);
            }
        });

        // When voice is interrupted, cancel TTS and transition state
        this.EventBus.on('voice:interrupted', () => {
            // Always check physical audio state — the state machine may have de-synced
            // (e.g. Piper fallback to Browser TTS caused early 'cooldown' transition)
            const isBrowserTalking = window.speechSynthesis && window.speechSynthesis.speaking;
            const isPiperPlaying = !!this._piperIsPlaying;
            const isSarvamPlaying = !!this._sarvamIsPlaying;
            const isPhysicallyPlaying = isBrowserTalking || isPiperPlaying || isSarvamPlaying;

            this.waLog(`[WAAI-INTERRUPT] voice:interrupted received | state: ${this.assistantState} | browser: ${isBrowserTalking} | piper: ${isPiperPlaying} | sarvam: ${isSarvamPlaying}`);

            if (this.assistantState === 'speaking' || isPhysicallyPlaying) {
                // Kill ALL audio engines immediately
                this.cancelSpeaking();
                // Only do a formal state transition if state machine is in speaking/cooldown
                // (if already 'listening', just killing audio is enough — don't double-transition)
                if (this.assistantState === 'speaking' || this.assistantState === 'cooldown') {
                    this.transitionTo('interrupted');
                } else {
                    // State is already 'listening' but audio was still playing — just reset speech tracking
                    this.aiSpeechEndTime = Date.now();
                    if (this.currentAISpokenText) {
                        this.lastAISpokenText = this.currentAISpokenText;
                    }
                    this.currentAISpokenText = '';
                }
            }
        });

        // When TTS finishes speaking, advance state
        this.EventBus.on('voice:speak:end', (evtData) => {
            this.waLog('[WAAI-SPEAK-END] voice:speak:end received | state:', this.assistantState, '| isContinuous:', this.isContinuousVoiceMode, '| data:', evtData);
            if (this.assistantState === 'speaking') {
                if (this.isContinuousVoiceMode) {
                    const timeSinceUserSpeech = this.lastUserSpeechTime ? (Date.now() - this.lastUserSpeechTime) : 999999;
                    if (timeSinceUserSpeech < 2000) {
                        this.waLog(`[TTS] Speech ended but user is actively speaking (${timeSinceUserSpeech}ms ago). Transitioning directly to listening.`);
                        this.transitionTo('listening');
                    } else {
                        this.transitionTo('cooldown', { nextState: 'listening' });
                    }
                } else {
                    this.transitionTo('idle');
                }
            }
        });

        // When a state transition occurs, update UI indicators
        this.EventBus.on('state:changed', ({ from, to }) => {
            if (typeof this.updateVoiceCallUI === 'function' && this.isContinuousVoiceMode) {
                const uiStateMap = {
                    listening: 'listening',
                    processing: 'thinking',
                    speaking: 'speaking',
                    interrupted: 'listening',
                    cooldown: 'thinking',
                    idle: 'listening',
                };
                this.updateVoiceCallUI(uiStateMap[to] || 'listening');
            }
        });

        // When an action overlay opens:
        // 1. Pause voice so TTS doesn't interfere with UI
        // 2. Set conversation state so subsequent actions queue properly
        this.EventBus.on('action:overlay:open', ({ title }) => {
            if (this.assistantState === 'speaking') {
                this.cancelSpeaking();
            }
            // Map overlay title back to action type for conversation state tracking
            // (ConversationState was already set by requestConversationAction above,
            //  but this handles cases where overlays are opened by other paths)
        });

        // When overlay is closed (close-overlay button), reset conversation state
        // so queued actions can be flushed
        const shadow = this.shadowRoot;
        if (shadow) {
            // We defer this binding until after render()
            setTimeout(() => {
                const closeOverlayBtn = shadow.getElementById('close-overlay');
                if (closeOverlayBtn) {
                    closeOverlayBtn.addEventListener('click', () => {
                        this.resetConversationState();
                    });
                }
            }, 0);
        }
    }
}

Object.assign(
    AIAssistantWidget.prototype,
    ConfigMixin,
    StateMixin,
    LoggerMixin,
    ValidatorsMixin,
    EventBusMixin,
    ConversationStateMixin,
    ActionsMixin,
    UIMixin,
    APIMixin,
    FormsMixin,
    VoiceMixin
);

// Register Custom Web Component Element
customElements.define('ai-assistant-widget', AIAssistantWidget);
