<?php
/**
 * Agentic Website Navigation Tools
 * This isolated module generates the JSON schema for tools the LLM can use
 * to manipulate the frontend DOM (e.g. scrolling to sections).
 */

class WebAssetsAIAgent {

    /**
     * Get the tool schema for the LLM
     */
    public static function get_tools($sections = [], $toggles = []) {
        $valid_sections = self::get_valid_sections($sections);
        
        if (empty($valid_sections)) {
            return []; // No tools if no sections defined
        }

        $enum_names = [];
        $section_descriptions = [];
        foreach ($valid_sections as $sec) {
            $enum_names[] = $sec['name'];
            $section_descriptions[] = "- " . $sec['name'];
        }

        $desc_string = implode("\n", $section_descriptions);

        $tools = [];

        // 1. Navigation Tool
        if (isset($toggles['navigate']) && $toggles['navigate'] === '1') {
            $tools[] = [
                "type" => "function",
                "function" => [
                    "name" => "navigate_website",
                    "description" => "CRITICAL: Use this tool to navigate the user to a different page or section of the website. The list of valid target_names is provided in the available_targets list. If the user asks for a page that is NOT in the available_targets list, DO NOT use this tool immediately. Instead, first use search_site_directory to find the correct URL, and then use this tool passing the raw URL as the target_name. Available sections/pages:\n" . $desc_string,
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "action" => [
                                "type" => "string",
                                "enum" => ["scroll", "redirect"],
                                "description" => "The action to perform. Use 'scroll' to scroll to a section on the current page. Use 'redirect' if the target is a different page or URL."
                            ],
                            "target_name" => [
                                "type" => "string",
                                "description" => "The exact name of the target page/section, or a raw URL/relative path (e.g. '/services/ai-automation/') discovered via search_site_directory. Pre-mapped targets:\n" . $desc_string
                            ],
                            "confidence" => [
                                "type" => "number",
                                "description" => "Confidence score between 0.0 and 1.0 that this action matches the user's intent based on the available tools and context."
                            ]
                        ],
                        "required" => ["action", "target_name", "confidence"]
                    ]
                ]
            ];
        }

        // 2. Interaction Tool
        if (isset($toggles['interact']) && $toggles['interact'] === '1') {
            $tools[] = [
                "type" => "function",
                "function" => [
                    "name" => "interact_with_element",
                    "description" => "CRITICAL: Use this tool to CLICK a specific button/link (e.g. a blog post title, 'Submit', 'Read More') OR to FILL out a form field on the CURRENT PAGE. Do NOT use this to navigate to major pages like About/Contact—use navigate_website for that.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "action" => [
                                "type" => "string",
                                "enum" => ["click", "fill", "scroll_to"],
                                "description" => "The action to perform. 'click' for buttons/links, 'fill' for text inputs, 'scroll_to' to scroll directly to the target_text."
                            ],
                            "element_id" => [
                                "type" => "integer",
                                "description" => "Optional. The dynamic ID (waai_id) of the interactable element from the [CURRENT PAGE VIEWPORT] prompt section. If an ID is available, pass it here to guarantee a 100% accurate interaction."
                            ],
                            "target_text" => [
                                "type" => "string",
                                "description" => "Optional. The visible text of the button or link (e.g., 'Hello World', 'Submit'), or the placeholder/label of the input field (e.g., 'Your Name')."
                            ],
                            "value" => [
                                "type" => "string",
                                "description" => "If action is 'fill', the exact text to type into the field."
                            ],
                            "confidence" => [
                                "type" => "number",
                                "description" => "Confidence score between 0.0 and 1.0 that this element matches the user's intent."
                            ]
                        ],
                        "required" => ["action", "confidence"]
                    ]
                ]
            ];
        }

        // 3. Search / Scraping (Read Page Content Equivalent)
        if (isset($toggles['read']) && $toggles['read'] === '1') {
            $tools[] = [
                "type" => "function",
                "function" => [
                    "name" => "search_site_directory",
                    "description" => "CRITICAL: Use this tool to SEARCH the website database for pages, posts, portfolio items, or services by a search query. Use this when the user asks for a page, project, or service detail page that is not currently visible on the screen or in the mapped pages list.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "query" => [
                                "type" => "string",
                                "description" => "The search query/keyword (e.g., 'agriculture', 'about us', 'contact', 'e-commerce project')."
                            ],
                            "confidence" => [
                                "type" => "number",
                                "description" => "Confidence score between 0.0 and 1.0 that this search matches the user's intent."
                            ]
                        ],
                        "required" => ["query", "confidence"]
                    ]
                ]
            ];
        }

        // 4. Scrolling Tool
        if (isset($toggles['scroll']) && $toggles['scroll'] === '1') {
            $tools[] = [
                "type" => "function",
                "function" => [
                    "name" => "scroll_page",
                    "description" => "CRITICAL: Use this tool when the user explicitly asks to scroll up, down, top, bottom, or by a specific amount. Do NOT use this to navigate to specific sections or pages.",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "direction" => [
                                "type" => "string",
                                "enum" => ["up", "down", "top", "bottom"],
                                "description" => "The direction to scroll."
                            ],
                            "amount_pixels" => [
                                "type" => "integer",
                                "description" => "Optional. The number of pixels to scroll. Leave blank to scroll by a standard viewport amount."
                            ]
                        ],
                        "required" => ["direction"]
                    ]
                ]
            ];
        }

        // 5. Dynamic Overlay tool registration based on settings
        $overlay_enum = [];
        if (function_exists('waai_config')) {
            if (waai_config('waai_lead_form_enabled', '1') === '1') {
                $overlay_enum[] = 'lead_form';
            }
            if (waai_config('waai_calendar_type', 'disabled') !== 'disabled') {
                $overlay_enum[] = 'calendar';
            }
        } else {
            $overlay_enum = ['lead_form', 'calendar']; // default fallback
        }

        if (!empty($overlay_enum)) {
            $desc_parts = [];
            foreach ($overlay_enum as $o_type) {
                $desc_parts[] = $o_type === 'lead_form' ? "Lead Generation Contact Form" : "Calendar Appointment";
            }
            $tools[] = [
                "type" => "function",
                "function" => [
                    "name" => "open_assistant_overlay",
                    "description" => "CRITICAL: Use this tool to open the assistant's built-in " . implode(' or ', $desc_parts) . " overlay. Only use this if the user wants to leave their contact details or book a call via the assistant directly. Do NOT use this if the user wants to fill a form on the website (use interact_with_element for that).",
                    "parameters" => [
                        "type" => "object",
                        "properties" => [
                            "overlay_type" => [
                                "type" => "string",
                                "enum" => $overlay_enum,
                                "description" => "Which overlay to open."
                            ]
                        ],
                        "required" => ["overlay_type"]
                    ]
                ]
            ];
        }

        return $tools;
    }

    /**
     * Maps the target name returned by the LLM back to its CSS selector
     */
    public static function get_selector_for_target($target_name, $sections, $action = 'redirect') {
        $valid_sections = self::get_valid_sections($sections);
        $target_lower = strtolower(trim($target_name));
        
        // If the target is already a valid path or URL (from search_site_directory fallback), return it directly
        if (strpos($target_lower, '/') === 0 || strpos($target_lower, 'http') === 0) {
            return $target_name; // Preserve original case for URLs
        }
        
        // Hardcode fallback for Home Page since auto-mapper excludes it by default
        if (in_array($target_lower, ['home', 'homepage', 'home page', 'main page', 'front page'])) {
            return '/';
        }
        
        $best_match = '';
        
        // Exact match with action preference
        foreach ($valid_sections as $sec) {
            if (strtolower(trim($sec['name'])) === $target_lower) {
                if ($action === 'redirect' && (strpos($sec['selector'], '/') === 0 || strpos($sec['selector'], 'http') === 0)) {
                    return $sec['selector']; // Perfect match for redirect
                } else if ($action === 'scroll' && strpos($sec['selector'], '#') === 0) {
                    return $sec['selector']; // Perfect match for scroll
                }
                if (empty($best_match)) $best_match = $sec['selector'];
            }
        }
        if (!empty($best_match)) return $best_match;
        
        // Fallback: Check if the LLM hallucinated the target name slightly (e.g. "product" instead of "products")
        foreach ($valid_sections as $sec) {
            $sec_lower = strtolower(trim($sec['name']));
            if (strpos($sec_lower, $target_lower) !== false || strpos($target_lower, $sec_lower) !== false) {
                if ($action === 'redirect' && (strpos($sec['selector'], '/') === 0 || strpos($sec['selector'], 'http') === 0)) {
                    return $sec['selector']; // Perfect match for redirect
                } else if ($action === 'scroll' && strpos($sec['selector'], '#') === 0) {
                    return $sec['selector']; // Perfect match for scroll
                }
                if (empty($best_match)) $best_match = $sec['selector'];
            }
        }
        
        return $best_match;
    }

    private static function get_valid_sections($sections) {
        $valid = [];
        if (is_array($sections)) {
            foreach ($sections as $sec) {
                if (!empty($sec['name']) && !empty($sec['selector'])) {
                    $valid[] = $sec;
                }
            }
        }
        return $valid;
    }
}
