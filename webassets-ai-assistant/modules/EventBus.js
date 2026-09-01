/**
 * EventBus Module — Centralized Pub/Sub Event System
 *
 * Decouples modules from direct method calls. Modules emit named events and
 * subscribe to events they care about, eliminating tight coupling and the
 * race conditions that arise from synchronous cross-module method calls.
 *
 * Usage (within any mixin that has `this` bound to the widget):
 *   this.EventBus.emit('api:response', { reply, action });
 *   this.EventBus.on('voice:interrupted', handler);
 *   this.EventBus.off('voice:interrupted', handler);
 *
 * Event Namespaces (convention):
 *   api:*       — API request lifecycle
 *   voice:*     — Voice recognition / TTS lifecycle
 *   state:*     — State machine transitions
 *   action:*    — Action intents (WhatsApp, Email, etc.)
 *   ui:*        — UI visibility / overlay events
 *   form:*      — Form overlay events
 */

export const EventBusMixin = {
    /**
     * Called once during initState to create the shared bus on this instance.
     * Keeps the bus per-widget rather than a module-level singleton, which is
     * safer when multiple widget instances exist on the same page.
     */
    initEventBus() {
        // Map<eventName, Set<handler>>
        this._busListeners = new Map();

        this.EventBus = {
            /**
             * Subscribe to an event.
             * @param {string} event  - namespaced event name e.g. 'api:response'
             * @param {Function} handler - callback receiving (payload)
             * @returns {Function} unsubscribe helper — call it to remove the listener
             */
            on: (event, handler) => {
                if (typeof handler !== 'function') return () => {};
                if (!this._busListeners.has(event)) {
                    this._busListeners.set(event, new Set());
                }
                this._busListeners.get(event).add(handler);
                // Return unsubscribe helper
                return () => this.EventBus.off(event, handler);
            },

            /**
             * Subscribe to an event exactly once — auto-removed after first fire.
             * @param {string} event
             * @param {Function} handler
             */
            once: (event, handler) => {
                const wrapper = (payload) => {
                    handler(payload);
                    this.EventBus.off(event, wrapper);
                };
                this.EventBus.on(event, wrapper);
            },

            /**
             * Unsubscribe a specific handler from an event.
             * @param {string} event
             * @param {Function} handler
             */
            off: (event, handler) => {
                const listeners = this._busListeners.get(event);
                if (listeners) {
                    listeners.delete(handler);
                    if (listeners.size === 0) {
                        this._busListeners.delete(event);
                    }
                }
            },

            /**
             * Emit an event to all registered listeners.
             * Errors in individual handlers are caught so one bad handler
             * cannot block others from receiving the event.
             * @param {string} event
             * @param {*} payload
             */
            emit: (event, payload) => {
                if (this.waLog) {
                    this.waLog(`[EventBus] emit: ${event}`, payload ?? '');
                }
                const listeners = this._busListeners.get(event);
                if (!listeners || listeners.size === 0) return;
                listeners.forEach(handler => {
                    try {
                        handler(payload);
                    } catch (err) {
                        if (this.waError) {
                            this.waError(`[EventBus] Handler error on event "${event}":`, err);
                        } else {
                            console.error(`[EventBus] Handler error on "${event}":`, err);
                        }
                    }
                });
            },

            /**
             * Remove ALL listeners for an event, or all listeners if no event given.
             * Call during widget teardown to prevent memory leaks.
             * @param {string} [event]
             */
            clear: (event) => {
                if (event) {
                    this._busListeners.delete(event);
                } else {
                    this._busListeners.clear();
                }
            },
        };
    },

    /**
     * Teardown helper — clears all listeners when the widget is disconnected.
     * Call from disconnectedCallback() in the main widget class.
     */
    destroyEventBus() {
        if (this.EventBus) {
            this.EventBus.clear();
        }
    },
};
