/**
 * TTSQueue — Sentence-Aware Browser TTS Queue Manager — Phase 1 Tier 2
 *
 * Problems solved:
 *  1. Browser speechSynthesis silently drops utterances if speak() is called
 *     before the previous one drains → causes speech cutoffs mid-sentence.
 *  2. No way to detect a stalled synthesis queue without a watchdog.
 *  3. Cancellation mid-chunk leaves the queue inconsistent.
 *
 * Architecture:
 *  - Accepts full AI reply text, chunks it at sentence boundaries (≤180 chars)
 *  - Plays chunks sequentially: the next chunk only starts after the previous
 *    utterance's `onend` fires.
 *  - A 2500ms watchdog per chunk detects a stalled browser TTS engine and
 *    forces advance to the next chunk (handles Chrome's known stall bug).
 *  - cancel() drains the queue cleanly and fires the onEnd callback if needed.
 *
 * Usage (from Voice.js):
 *   this.ttsQueue = new TTSQueue(widget);
 *   this.ttsQueue.enqueue(text, { onEnd, onError });
 *   this.ttsQueue.cancel();
 */

export class TTSQueue {

    /**
     * @param {object} widget — The AIAssistantWidget instance (provides waLog/waError/EventBus)
     */
    constructor(widget) {
        this._widget     = widget;
        this._queue      = [];      // Array of sentence-chunk strings
        this._active     = false;   // True when a chunk is currently being spoken
        this._cancelled  = false;   // Set on cancel() to prevent onEnd callbacks
        this._watchdog   = null;    // setTimeout handle for stall detection
        this._onEnd      = null;    // Called when all chunks drain naturally
        this._onError    = null;    // Called on utterance error
        this._WATCHDOG_MS = 2500;   // How long to wait before declaring synthesis stalled
        this._MAX_CHUNK  = 180;     // Max characters per TTS chunk
        this._lang       = 'en-US'; // Detected language for current enqueue batch
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Enqueue text for TTS playback. Cancels any in-progress speech first.
     *
     * @param {string} text
     * @param {{ onEnd?: Function, onError?: Function }} callbacks
     */
    enqueue(text, { onEnd = null, onError = null } = {}) {
        // Cancel whatever is currently playing
        this._hardCancel();

        this._cancelled = false;
        this._onEnd     = onEnd;
        this._onError   = onError;
        this._queue     = this._chunkText(text, this._MAX_CHUNK);

        // Auto-detect language from script characters
        // Devanagari Unicode block: U+0900–U+097F (covers Hindi, Marathi, Sanskrit, Nepali)
        // Threshold: if >10% of chars are Devanagari, treat as Hindi
        const devanagariCount = (text.match(/[\u0900-\u097F]/g) || []).length;
        const arabicCount     = (text.match(/[\u0600-\u06FF]/g) || []).length;
        if (devanagariCount / text.length > 0.05) {
            this._lang = 'hi-IN';
        } else if (arabicCount / text.length > 0.05) {
            this._lang = 'ar-SA';
        } else {
            this._lang = 'en-US';
        }

        if (this._queue.length === 0) {
            this._log('TTSQueue: empty text, nothing to enqueue');
            if (onEnd) onEnd();
            return;
        }

        this._log(`TTSQueue: enqueued ${this._queue.length} chunk(s) for playback [lang=${this._lang}]`);
        this._scheduleNext();
    }

    /**
     * Cancel all pending speech immediately.
     * Safe to call multiple times.
     */
    cancel() {
        this._log('TTSQueue: cancel() called');
        this._cancelled = true;
        this._hardCancel();
        this._queue   = [];
        this._active  = false;
        this._onEnd   = null;
        this._onError = null;
    }

    /**
     * True if speech is currently playing or chunks remain in the queue.
     * @returns {boolean}
     */
    get isActive() {
        return this._active || this._queue.length > 0;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: scheduling
    // ─────────────────────────────────────────────────────────────────────────

    _scheduleNext() {
        if (this._cancelled) return;

        if (this._queue.length === 0) {
            // All chunks drained naturally
            this._active = false;
            this._log('TTSQueue: all chunks complete — firing onEnd');
            if (this._onEnd) this._onEnd();
            return;
        }

        if (!window.speechSynthesis) {
            this._active = false;
            this._log('TTSQueue: speechSynthesis not available');
            if (this._onError) this._onError(new Error('speechSynthesis unavailable'));
            return;
        }

        const chunk   = this._queue.shift();
        this._active  = true;

        const utterance      = new SpeechSynthesisUtterance(chunk);
        utterance.lang       = this._lang;
        utterance.rate       = 1.0;
        utterance.pitch      = 1.0;

        // Pick the best available voice for the detected language.
        // Falls back to whatever the browser chooses if no match found.
        const voices = window.speechSynthesis.getVoices();
        if (voices.length > 0 && this._lang !== 'en-US') {
            // Prefer an exact locale match, then a language-prefix match
            const exactMatch  = voices.find(v => v.lang === this._lang);
            const prefixMatch = voices.find(v => v.lang.startsWith(this._lang.split('-')[0]));
            if (exactMatch)       utterance.voice = exactMatch;
            else if (prefixMatch) utterance.voice = prefixMatch;
        }

        utterance.onstart = () => {
            this._clearWatchdog();
            this._log(`TTSQueue: chunk started (${chunk.length} chars)`);
            if (this._widget && typeof this._widget.updateImmersiveTranscript === 'function') {
                this._widget.updateImmersiveTranscript(chunk);
            }
        };

        utterance.onend = () => {
            if (this._cancelled) return;
            this._clearWatchdog();
            this._active = false;
            this._scheduleNext();
        };

        utterance.onerror = (evt) => {
            if (this._cancelled) return;
            this._clearWatchdog();
            this._active = false;
            // 'interrupted' is normal (user spoke) — not a real error
            if (evt.error === 'interrupted') {
                this._log('TTSQueue: utterance interrupted by user — draining queue');
                this._queue = [];
                if (this._onEnd) this._onEnd();
                return;
            }
            this._logError(`TTSQueue: utterance error — ${evt.error}`);
            if (this._onError) this._onError(evt);
        };

        // Watchdog: if onstart never fires within _WATCHDOG_MS, advance to next chunk.
        // This handles Chrome's known bug where it stalls after ~15 utterances.
        this._watchdog = setTimeout(() => {
            this._logError('TTSQueue: watchdog triggered — synthesis stalled, advancing');
            window.speechSynthesis.cancel();
            this._active = false;
            // Give the synthesis engine a brief moment to reset
            setTimeout(() => this._scheduleNext(), 100);
        }, this._WATCHDOG_MS);

        window.speechSynthesis.speak(utterance);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: text chunking
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Split text into chunks of at most maxLen chars, preferring sentence
     * boundaries, then clause boundaries, then word boundaries.
     *
     * @param {string} text
     * @param {number} maxLen
     * @returns {string[]}
     */
    _chunkText(text, maxLen) {
        text = text.trim();
        if (!text) return [];
        if (text.length <= maxLen) return [text];

        const chunks    = [];
        let   remaining = text;

        // Priority: sentence end → clause pause → word boundary → hard cut
        const sentenceEnds = ['. ', '! ', '? ', '.\n', '!\n', '?\n'];
        const clauseEnds   = [', ', '; ', ':\n'];

        while (remaining.length > 0) {
            if (remaining.length <= maxLen) {
                chunks.push(remaining.trim());
                break;
            }

            let splitIdx = -1;

            // 1. Sentence boundary
            for (const sep of sentenceEnds) {
                const idx = remaining.lastIndexOf(sep, maxLen);
                if (idx > 0 && idx + sep.length - 1 > splitIdx) {
                    splitIdx = idx + sep.length - 1;
                }
            }

            // 2. Clause boundary
            if (splitIdx <= 0) {
                for (const sep of clauseEnds) {
                    const idx = remaining.lastIndexOf(sep, maxLen);
                    if (idx > 0 && idx + sep.length - 1 > splitIdx) {
                        splitIdx = idx + sep.length - 1;
                    }
                }
            }

            // 3. Word boundary
            if (splitIdx <= 0) {
                splitIdx = remaining.lastIndexOf(' ', maxLen);
            }

            // 4. Hard cut
            if (splitIdx <= 0) {
                splitIdx = maxLen;
            }

            const chunk = remaining.substring(0, splitIdx).trim();
            if (chunk) chunks.push(chunk);
            remaining = remaining.substring(splitIdx).trim();
        }

        return chunks.filter(c => c.length > 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: helpers
    // ─────────────────────────────────────────────────────────────────────────

    _hardCancel() {
        this._clearWatchdog();
        if (window.speechSynthesis) {
            // Workaround for Chrome/Android where cancel() is delayed or ignored.
            window.speechSynthesis.pause();
            window.speechSynthesis.cancel();
            
            // Forcing a flush using an empty utterance
            const flushUtterance = new SpeechSynthesisUtterance('');
            flushUtterance.volume = 0; // mute it just in case
            window.speechSynthesis.speak(flushUtterance);

            // Resume just in case pause locked it
            if (window.speechSynthesis.paused) {
                window.speechSynthesis.resume();
            }
        }
    }

    _clearWatchdog() {
        if (this._watchdog !== null) {
            clearTimeout(this._watchdog);
            this._watchdog = null;
        }
    }

    _log(msg) {
        if (this._widget && this._widget.waLog) {
            this._widget.waLog(msg);
        }
    }

    _logError(msg) {
        if (this._widget && this._widget.waError) {
            this._widget.waError(msg);
        } else {
            console.error('[TTSQueue]', msg);
        }
    }
}
