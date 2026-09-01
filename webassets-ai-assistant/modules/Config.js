/**
 * Config Module
 * Handles loading and parsing of configuration data from WordPress or data attributes.
 */

export const ConfigMixin = {
    initConfig() {
        // Parse global config from WordPress localized script or window
        this.cfg = window.waaiConfig || {};
        this.isWordPressMode = !!window.waaiConfig;
        this.csrfToken = this.cfg.nonce || '';

        // Endpoints
        this.apiEndpoint = this.isWordPressMode 
            ? (this.cfg.chatUrl || '/wp-json/webassets-ai/v1/chat')
            : (this.getAttribute('proxy-endpoint') || '/webassets-ai-assistant/ai-proxy.php');
            
        this.proxyEndpoint = this.apiEndpoint;
        
        this.leadEndpoint = this.isWordPressMode 
            ? (this.cfg.leadUrl || '/wp-json/webassets-ai/v1/lead')
            : (this.getAttribute('lead-endpoint') || '/webassets-ai-assistant/lead-proxy.php');

        this.whatsappEndpoint = this.isWordPressMode 
            ? (this.cfg.whatsappUrl || '/wp-json/webassets-ai/v1/whatsapp')
            : (this.getAttribute('whatsapp-endpoint') || '/webassets-ai-assistant/whatsapp-proxy.php');
            
        // Setup initial config values with fallbacks
        this.cfg.voiceLangs = this.cfg.voiceLangs || ['en-US'];
        this.storagePrefix = this.cfg.storagePrefix || 'waai_abid_';
    },
    
    getConfig(key, fallback = null) {
        return this.cfg[key] !== undefined ? this.cfg[key] : fallback;
    },

    getStorageKey(key) {
        const prefix = (this.cfg && this.cfg.storagePrefix) ? this.cfg.storagePrefix : 'waai_abid_';
        if (typeof key === 'string' && key.startsWith('waai_')) {
            return prefix + key.substring(5);
        }
        return prefix + key;
    }
};
