/**
 * Logger Module — v2.4.0
 * Provides unified console logging plus Session ID / Trace ID generation
 * for correlation of backend logs with client-side events.
 *
 * Session ID: generated once per widget lifecycle (persists across messages).
 * Trace ID:   generated fresh per fetchAIResponse() call (one turn = one trace).
 */

export const LoggerMixin = {
    initLogger() {
        // Enable or disable debug logs via config (default to true for dev)
        this.debugMode = this.getConfig ? this.getConfig('debugMode', false) : false;

        // ── Session ID ──────────────────────────────────────────────────────
        // Unique per widget load. Reused across all messages in this session.
        this.sessionId = this._generateUUID();

        // ── Active Trace ID ─────────────────────────────────────────────────
        // Set to a new UUID at the start of each fetchAIResponse() call.
        // Cleared after the response is processed.
        this._activeTraceId = null;

        this.waLog(`[Logger] Session started. sid=${this.sessionId}`);
    },

    // ─────────────────────────────────────────────────────────────────────────
    // Trace ID helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Generate a fresh Trace ID and set it as active.
     * Call this at the start of every API request turn.
     * @returns {string} The new trace ID
     */
    newTraceId() {
        this._activeTraceId = this._generateUUID();
        return this._activeTraceId;
    },

    /**
     * Clear the active trace ID (call after request/response cycle ends).
     */
    clearTraceId() {
        this._activeTraceId = null;
    },

    /**
     * Get the currently active trace ID (may be null between turns).
     * @returns {string|null}
     */
    getTraceId() {
        return this._activeTraceId;
    },

    // ─────────────────────────────────────────────────────────────────────────
    // Console logging helpers
    // ─────────────────────────────────────────────────────────────────────────

    waLog(message, data = null) {
        if (!this.debugMode) return;
        const prefix = this._logPrefix();
        if (data) {
            console.log(`${prefix} ${message}`, data);
        } else {
            console.log(`${prefix} ${message}`);
        }
    },

    waWarn(message, data = null) {
        const prefix = this._logPrefix();
        if (data) {
            console.warn(`${prefix} WARNING: ${message}`, data);
        } else {
            console.warn(`${prefix} WARNING: ${message}`);
        }
    },

    waError(message, data = null) {
        const prefix = this._logPrefix();
        if (data) {
            console.error(`${prefix} ERROR: ${message}`, data);
        } else {
            console.error(`${prefix} ERROR: ${message}`);
        }
    },

    // ─────────────────────────────────────────────────────────────────────────
    // Internal helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Build a log prefix that includes session/trace IDs when available.
     * e.g. "[AIAssistant][sid:a1b2][tid:c3d4]"
     */
    _logPrefix() {
        let prefix = '[AIAssistant]';
        if (this.sessionId) {
            prefix += `[sid:${this.sessionId.slice(0, 8)}]`;
        }
        if (this._activeTraceId) {
            prefix += `[tid:${this._activeTraceId.slice(0, 8)}]`;
        }
        return prefix;
    },

    /**
     * Generate a UUID v4. Uses crypto.randomUUID() when available
     * (all modern browsers), with a Date.now() + Math.random() fallback.
     * @returns {string}
     */
    _generateUUID() {
        if (typeof crypto !== 'undefined' && crypto.randomUUID) {
            return crypto.randomUUID();
        }
        // Fallback for older browsers
        return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, c => {
            const r = (Math.random() * 16) | 0;
            const v = c === 'x' ? r : (r & 0x3) | 0x8;
            return v.toString(16);
        });
    },
};
