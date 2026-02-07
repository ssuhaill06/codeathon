<?php
/**
 * Configuration File - API Keys and Settings
 * AI Mock Interview System
 * 
 * SECURITY: Store sensitive data as environment variables in production
 * For development, you can set these directly here.
 */

// ==================== DATABASE CONFIGURATION ====================
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ai_mock_interview');

// ==================== LLM API CONFIGURATION ====================
/**
 * GOOGLE GEMINI API - FREE TIER
 * 
 * Setup Instructions:
 * 1. Go to: https://ai.google.dev/
 * 2. Click "Get API Key" 
 * 3. Create new API key for free (no credit card needed)
 * 4. Copy your API key below
 * 
 * Free tier includes:
 * - 60 requests per minute
 * - 1,500 requests per day
 * - FREE forever
 */

// Get API key from environment variable first (recommended)
// If not set, use the constant below (for local development only)
$GEMINI_API_KEY = getenv('GEMINI_API_KEY');

// If environment variable not set, you can paste your key here (development only)
if (!$GEMINI_API_KEY) {
    // TODO: Replace with your actual Gemini API key
    // Get free key from: https://ai.google.dev/
    $GEMINI_API_KEY = '';  // Leave empty initially
}

define('GEMINI_API_KEY', $GEMINI_API_KEY);
define('GEMINI_API_URL', 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent');

// ==================== LLM SETTINGS ====================
// Fallback scores if API is unavailable
define('FALLBACK_ACCURACY', 70);
define('FALLBACK_CLARITY', 70);
define('FALLBACK_COMPLETENESS', 70);
define('FALLBACK_CONFIDENCE', 70);

// API timeout (seconds)
define('API_TIMEOUT', 15);

// ==================== DEBUG MODE ====================
// Set to true to see debug messages in logs
define('DEBUG_MODE', false);

// ==================== HELPER FUNCTIONS ====================

/**
 * Write to debug log
 * @param string $message Message to log
 */
function debugLog($message) {
    if (DEBUG_MODE) {
        error_log('[DEBUG] ' . $message);
    }
}

/**
 * Check if Gemini API key is configured
 * @return bool True if API key is set, false otherwise
 */
function isGeminiConfigured() {
    return !empty(GEMINI_API_KEY) && GEMINI_API_KEY !== '';
}

?>
