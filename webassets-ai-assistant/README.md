# WebAssets AI Assistant

A powerful, modular, and deeply integrated conversational AI assistant chatbot widget for WordPress. It offers a fully-fledged WordPress admin panel, multi-provider LLM support, voice capabilities (Speech-to-Text and Text-to-Speech), and integrated lead capture.

---

## Features

- **No-Code WordPress Admin Panel**: Configure everything (AI Provider, API keys, system prompts, colors, avatars) directly from the WordPress dashboard (`Settings > AI Assistant`).
- **Multiple AI Providers**: Supports Groq (Llama 3), OpenAI, OpenRouter, and Gemini.
- **Advanced Voice Interaction (Full Duplex)**: Talk to the AI using your microphone. It uses Web Speech API for recognition and supports multiple TTS engines (Web Speech, Groq Orpheus, ElevenLabs, and Local Piper API).
- **Integrated Lead Capture & CRM**: The AI can autonomously collect user details (name, email, phone) and save them to the WordPress database (`Settings > AI Assistant > Leads`) or forward them to Google Sheets/Email.
- **Calendar Booking**: Embeds appointment calendars (like Google Calendar or Calendly) inside the chat flow.
- **Shadow DOM Isolation**: The frontend chat widget is isolated from your theme's CSS, ensuring perfectly preserved premium glassmorphism layouts across all sites.

---

## Folder Structure

- `ai-settings.php`: The comprehensive WordPress admin settings interface.
- `ai-leads.php`: The WordPress admin page to view captured leads.
- `ai-proxy.php`: The secure backend proxy that handles API requests, rate limiting, and TTS streaming.
- `wordpress-integration.php`: Handles hooking the plugin into WordPress, enqueuing assets, and rendering the widget.
- `ai-widget.js` & `ai-widget.css`: The frontend web component and its styling.
- `modules/`: Contains modular frontend logic (e.g., `Voice.js` for audio handling).

---

## 1. Installation & Setup (WordPress)

1. **Upload**: Place the `webassets-ai-assistant` folder into your active WordPress theme directory (`wp-content/themes/your-theme-name/webassets-ai-assistant/`).
2. **Include the Integration File**: Open your theme's `functions.php` file and add the following line at the bottom:
   ```php
   require_once get_template_directory() . '/webassets-ai-assistant/wordpress-integration.php';
   ```
3. **Configure**: Log in to your WordPress Admin dashboard. Navigate to **Settings > AI Assistant**.
4. **API Keys**: Under the "AI Engine" tab, select your preferred provider (e.g., Groq) and paste your API key.
5. **Save Changes**: Click "Save Settings" at the bottom of the page. The widget will now appear on your website!

---

## 2. Voice & Text-to-Speech (TTS) Setup

The AI Assistant supports multiple Text-to-Speech engines, configurable via the **Widget UI** tab in the WordPress settings.

### Option A: Browser Default (Free)
Uses the `window.speechSynthesis` API built into the user's browser. It requires no setup and has zero latency, but voices can sound robotic depending on the user's OS.

### Option B: Groq Orpheus / ElevenLabs (Cloud Premium)
1. Select the provider in the WordPress settings.
2. Enter your API key and preferred Voice ID.
3. The proxy will securely stream the audio to the frontend.

### Option C: Piper Local HTTP API (Self-Hosted, Fast, Free)
If you are hosting your website on a VPS (like aaPanel, Ubuntu, etc.), you can run a local text-to-speech server using Piper. This offers high-quality voices with near-zero latency, completely free of cloud costs.

#### Option 1: Docker on aaPanel (Recommended)

The cleanest and easiest way to run Piper on an aaPanel VPS is by using Docker.

1. **Install Docker**: Open your aaPanel, go to the **App Store**, and install **Docker Manager** if you haven't already.
2. **Download the Voice Model**: Connect to your server via SSH and download the voice files to a dedicated folder.
   ```bash
   mkdir -p /opt/piper/models
   cd /opt/piper/models
   curl -L -O "https://huggingface.co/rhasspy/piper-voices/resolve/main/en/en_US/libritts_r/medium/en_US-libritts_r-medium.onnx"
   curl -L -O "https://huggingface.co/rhasspy/piper-voices/resolve/main/en/en_US/libritts_r/medium/en_US-libritts_r-medium.onnx.json"
   ```
3. **Run the Docker Container**:
   ```bash
   docker run -d --name piper-tts --restart always \
     -p 5000:5000 \
     -v /opt/piper/models:/models \
     rhasspy/piper \
     --model /models/en_US-libritts_r-medium.onnx \
     --http-server 5000
   ```
   *This command runs the Piper server in the background and automatically restarts it if your VPS reboots.*

#### Option 2: Native Python Setup (Ubuntu/Debian)

1. **Install Python and Pip**: Ensure your VPS has Python 3 installed.
2. **Install Piper HTTP**:
   ```bash
   python3 -m pip install piper-tts[http]
   ```
3. **Download a Voice Model**: 
   ```bash
   mkdir -p /root/piper_models
   cd /root/piper_models
   python3 -m piper.download_voices en_US-libritts_r-medium
   ```
4. **Run the Piper HTTP Server**:
   ```bash
   python3 -m piper.http_server -m /root/piper_models/en_US-libritts_r-medium.onnx --port 5000
   ```

#### 3. Connect the Assistant
Regardless of which method you chose above:
- Go to your WordPress **Settings > AI Assistant > Widget UI**.
- Select **"Piper Local API"** as the Speech Engine.
- Set the Piper API URL to `http://127.0.0.1:5000`.
- The WordPress PHP backend (`ai-proxy.php`) will automatically route text to this local port and stream the generated audio back to the user securely.

---

## 3. Google Sheets Integration (Optional)

To automatically export leads captured by the AI to Google Sheets:
1. Create a new Google Sheet. Add headers: `Date`, `Name`, `Email`, `Phone`, `Query`, `Website`.
2. Go to **Extensions > Apps Script**.
3. Paste the following webhook code and save:
   ```javascript
   function doPost(e) {
     var data = JSON.parse(e.postData.contents);
     var sheet = SpreadsheetApp.getActiveSpreadsheet().getActiveSheet();
     sheet.appendRow([new Date(), data.name, data.email, data.phone, data.query, data.website]);
     return ContentService.createTextOutput(JSON.stringify({success: true})).setMimeType(ContentService.MimeType.JSON);
   }
   ```
4. Click **Deploy > New Deployment** (Web App, Execute as: Me, Access: Anyone).
5. Copy the **Web App URL**.
6. In WordPress, go to **Settings > AI Assistant > Leads** and paste the URL into the "Google Sheets Webhook URL" field.

---

## Troubleshooting

- **No AI Response?** Check your API Keys in the WordPress settings. Ensure your hosting provider allows external `cURL` requests.
- **Audio Clipping / Static?** If the Piper voice clips at the end of sentences, ensure you are using the latest `Voice.js` which forces natural sentence drop-offs.
- **Widget not showing?** Ensure `wp_footer()` is being called in your theme's `footer.php` file, as the widget hooks into the WordPress footer.
