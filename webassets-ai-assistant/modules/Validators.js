/**
 * Validators Module
 * Provides helper functions for validating inputs like email addresses and phone numbers.
 */

export const ValidatorsMixin = {
    isValidEmail(email) {
        if (!email) return false;
        // Basic RFC 5322 regex
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    },

    isValidPhone(phone) {
        if (!phone) return false;
        // Basic check: must contain mostly digits, optional +, dashes, spaces, or parentheses
        const re = /^\+?[\d\s\-\(\)]{7,20}$/;
        return re.test(String(phone));
    },

    isEmptyString(str) {
        return !str || String(str).trim().length === 0;
    },

    isValidURL(url) {
        if (!url) return false;
        try {
            new URL(url);
            return true;
        } catch (err) {
            return false;
        }
    },

    isWithinMaxLength(str, maxLength) {
        if (!str) return true;
        return String(str).length <= maxLength;
    },

    containsBlockedWords(str, extraBlockedWords = []) {
        if (!str) return false;
        const lowerStr = String(str).toLowerCase();
        
        // Base list of dangerous or spam words
        const defaultBlocked = [
            '<script', 'javascript:', 'onerror=', 'onload=', 'eval(', 
            'document.cookie', 'iframe', 'alert('
        ];
        
        const allBlocked = [...defaultBlocked, ...extraBlockedWords];
        return allBlocked.some(word => lowerStr.includes(word.toLowerCase()));
    },

    isCompanyAllowedContent(str) {
        if (!str) return false;
        
        // Company-only restrictions for sending actions.
        // We block competitor names or completely unrelated/unsafe topics.
        const forbiddenTopics = [
            'competitor', 'scam', 'phishing', 'click here to win',
            'free money', 'lottery'
        ];
        
        return !this.containsBlockedWords(str, forbiddenTopics);
    },
    
    sanitizeHTML(str) {
        if (!str) return '';
        const temp = document.createElement('div');
        temp.textContent = str;
        return temp.innerHTML;
    }
};
