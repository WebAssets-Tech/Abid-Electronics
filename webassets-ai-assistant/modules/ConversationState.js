/**
 * ConversationState Module — Phase 1 Tier 2
 *
 * Tracks the HIGH-LEVEL user journey state, separate from the low-level
 * voice/audio state machine in State.js.
 *
 * Voice states (State.js) answer:  "What is the audio hardware doing?"
 *   → idle | listening | processing | speaking | interrupted | cooldown
 *
 * Conversation states (here) answer: "What user journey step are we on?"
 *   → idle | collecting_lead | awaiting_whatsapp_confirm |
 *      awaiting_email_confirm | awaiting_calendar
 *
 * Rules enforced:
 *  - Only ONE conversation journey can be active at a time.
 *  - If a NEW action arrives while a journey is in-progress, it is queued.
 *  - The queue is flushed when the current journey completes (overlay closes).
 *  - Chat/text replies NEVER close an active overlay — they are threaded in.
 */

export const ConversationStateMixin = {

    initConversationState() {
        /** @type {'idle'|'collecting_lead'|'awaiting_whatsapp_confirm'|'awaiting_email_confirm'|'awaiting_calendar'} */
        this.conversationState = 'idle';

        /** Queue of pending actions while a journey is active */
        this._pendingActionQueue = [];

        this.waLog('[ConvState] Initialized — state: idle');
    },

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Called by the overlay system when a new action is about to be displayed.
     * Returns true if the action was accepted immediately, false if queued.
     *
     * @param {'whatsapp'|'email'|'lead_form'|'calendar'} actionType
     * @param {object} actionParams
     * @returns {boolean}
     */
    requestConversationAction(actionType, actionParams = {}) {
        const targetState = this._actionTypeToConvState(actionType);

        if (this.conversationState === 'idle') {
            // No active journey — execute immediately
            this._setConversationState(targetState);
            return true;
        }

        if (this.conversationState === targetState) {
            // Same journey type already active — ignore duplicate (double-click guard)
            this.waLog(`[ConvState] Duplicate action ignored — already in state: ${targetState}`);
            return false;
        }

        // Different journey is active — queue for later
        this.waWarn(`[ConvState] Action queued (busy: ${this.conversationState})`, {
            queued: actionType,
            params: actionParams,
        });
        this._pendingActionQueue.push({ type: actionType, params: actionParams });
        return false;
    },

    /**
     * Called when the overlay is closed (by user or after success/error).
     * Resets conversation state to idle and flushes any queued action.
     */
    resetConversationState() {
        const prev = this.conversationState;
        this._setConversationState('idle');
        this.waLog(`[ConvState] Reset to idle (was: ${prev})`);

        // Flush the queue — process the next pending action (if any)
        if (this._pendingActionQueue.length > 0) {
            const next = this._pendingActionQueue.shift();
            this.waLog(`[ConvState] Flushing queued action: ${next.type}`);
            // Small delay to let DOM settle after overlay close
            setTimeout(() => {
                if (typeof this.processActionRequest === 'function') {
                    this.processActionRequest({ type: next.type, params: next.params });
                }
            }, 200);
        }
    },

    /**
     * Returns true if the user is in the middle of a multi-step journey
     * (e.g. a form or overlay is open). Used to prevent overlay-closing
     * side-effects from API responses that arrive while a journey is active.
     *
     * @returns {boolean}
     */
    isConversationBusy() {
        return this.conversationState !== 'idle';
    },

    /**
     * Returns the current conversation state label for logging/UI.
     * @returns {string}
     */
    getConversationState() {
        return this.conversationState;
    },

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    _setConversationState(newState) {
        const old = this.conversationState;
        if (old === newState) return;
        this.conversationState = newState;
        this.waLog(`[ConvState] Transition: ${old} → ${newState}`);

        // Emit for any interested listeners (e.g. UI badges, analytics)
        if (this.EventBus) {
            this.EventBus.emit('conversation:state:changed', { from: old, to: newState });
        }
    },

    /**
     * Maps an action type string to the matching conversation state label.
     * @param {string} actionType
     * @returns {string}
     */
    _actionTypeToConvState(actionType) {
        const map = {
            whatsapp:  'awaiting_whatsapp_confirm',
            email:     'awaiting_email_confirm',
            lead_form: 'collecting_lead',
            calendar:  'awaiting_calendar',
        };
        return map[actionType] || 'idle';
    },
};
