<?php
/**
 * LLM Service - Handles API calls to Google Gemini
 * AI Mock Interview System
 * 
 * This service:
 * - Connects to Google Gemini API (free tier)
 * - Evaluates interview answers
 * - Returns structured JSON scores
 * - Provides fallback if API unavailable
 */

require_once 'config.php';

class LLMService {
    
    /**
     * Evaluate an interview answer using Google Gemini API
     * 
     * @param string $question The interview question
     * @param string $answer The user's answer
     * @return array Structured evaluation scores
     */
    public static function evaluateAnswer($question, $answer) {
        // Check if API is configured
        if (!isGeminiConfigured()) {
            debugLog('Gemini API not configured, using fallback scores');
            return self::getFallbackScores('API not configured');
        }

        // Build the prompt for the LLM
        $prompt = self::buildEvaluationPrompt($question, $answer);
        
        // Call the Gemini API
        $response = self::callGeminiAPI($prompt);
        
        // Parse and validate the response
        if ($response === null) {
            debugLog('Gemini API call failed, using fallback scores');
            return self::getFallbackScores('API call failed');
        }
        
        // Extract JSON from response
        $scores = self::extractJSONFromResponse($response);
        
        // Validate the scores
        if ($scores === null || !self::validateScores($scores)) {
            debugLog('Invalid scores returned from API, using fallback');
            return self::getFallbackScores('Invalid response format');
        }
        
        debugLog('Successfully evaluated answer: ' . json_encode($scores));
        return $scores;
    }

    /**
     * Build the prompt sent to the LLM
     * MANDATORY: Use exact format for consistent results
     * 
     * @param string $question The interview question
     * @param string $answer The user's answer
     * @return string The complete prompt
     */
    private static function buildEvaluationPrompt($question, $answer) {
        return "Evaluate the following interview answer and score it in PERCENTAGES.\n\n" .
               "Question: " . trim($question) . "\n\n" .
               "Answer: " . trim($answer) . "\n\n" .
               "Return ONLY valid JSON in this exact format:\n" .
               "{\n" .
               "  \"accuracy\": number,\n" .
               "  \"clarity\": number,\n" .
               "  \"completeness\": number,\n" .
               "  \"confidence\": number\n" .
               "}\n\n" .
               "Do NOT include any text outside the JSON.\n" .
               "All values must be between 0 and 100.";
    }

    /**
     * Call Google Gemini API with cURL
     * 
     * @param string $prompt The evaluation prompt
     * @return string|null The API response or null on failure
     */
    private static function callGeminiAPI($prompt) {
        $url = GEMINI_API_URL . '?key=' . urlencode(GEMINI_API_KEY);
        
        // Prepare the request body
        $requestBody = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt
                        ]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.3,  // Low temperature for consistent responses
                'maxOutputTokens' => 200,  // Keep response short
            ]
        ];
        
        debugLog('Calling Gemini API: ' . $url);
        debugLog('Request body: ' . json_encode($requestBody));
        
        // Initialize cURL
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => API_TIMEOUT,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($requestBody),
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        
        // Execute request
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        
        curl_close($curl);
        
        // Log the API response
        debugLog('HTTP Code: ' . $httpCode);
        debugLog('Response: ' . substr($response, 0, 500));
        
        // Handle network errors
        if ($error) {
            debugLog('cURL Error: ' . $error);
            return null;
        }
        
        // Handle HTTP errors
        if ($httpCode !== 200) {
            debugLog('API Error (HTTP ' . $httpCode . '): ' . $response);
            return null;
        }
        
        return $response;
    }

    /**
     * Extract JSON from the API response
     * The response may contain text before/after JSON, so we parse carefully
     * 
     * @param string $response The API response
     * @return array|null Parsed JSON data or null on failure
     */
    private static function extractJSONFromResponse($response) {
        try {
            // Decode the API response wrapper
            $data = json_decode($response, true);
            
            if (!$data) {
                debugLog('Failed to decode API response as JSON');
                return null;
            }
            
            // Extract the text content from Gemini response
            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $data['candidates'][0]['content']['parts'][0]['text'];
                
                // Extract JSON from the text (it might have surrounding text)
                $jsonStart = strpos($text, '{');
                $jsonEnd = strrpos($text, '}');
                
                if ($jsonStart === false || $jsonEnd === false) {
                    debugLog('No JSON found in response: ' . $text);
                    return null;
                }
                
                // Extract just the JSON part
                $jsonString = substr($text, $jsonStart, $jsonEnd - $jsonStart + 1);
                
                debugLog('Extracted JSON: ' . $jsonString);
                
                // Decode the JSON
                $scores = json_decode($jsonString, true);
                
                return $scores;
            }
            
            debugLog('Unexpected API response structure: ' . json_encode($data));
            return null;
            
        } catch (Exception $e) {
            debugLog('Exception parsing response: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Validate that all required fields exist and are valid numbers
     * 
     * @param array $scores The scores array to validate
     * @return bool True if valid, false otherwise
     */
    private static function validateScores($scores) {
        // Check if $scores is an array
        if (!is_array($scores)) {
            debugLog('Scores is not an array');
            return false;
        }
        
        // Required fields
        $requiredFields = ['accuracy', 'clarity', 'completeness', 'confidence'];
        
        // Validate each field
        foreach ($requiredFields as $field) {
            // Check if field exists
            if (!isset($scores[$field])) {
                debugLog('Missing field: ' . $field);
                return false;
            }
            
            // Convert to number
            $value = floatval($scores[$field]);
            
            // Check if valid number between 0-100
            if ($value < 0 || $value > 100 || !is_numeric($scores[$field])) {
                debugLog('Invalid value for ' . $field . ': ' . $scores[$field]);
                return false;
            }
        }
        
        return true;
    }

    /**
     * Get fallback scores when API is unavailable
     * This ensures the interview flow never breaks
     * 
     * @param string $reason Reason for fallback (for logging)
     * @return array Default scores
     */
    private static function getFallbackScores($reason = '') {
        debugLog('Using fallback scores - Reason: ' . $reason);
        
        return [
            'accuracy' => FALLBACK_ACCURACY,
            'clarity' => FALLBACK_CLARITY,
            'completeness' => FALLBACK_COMPLETENESS,
            'confidence' => FALLBACK_CONFIDENCE,
            'fallback' => true,
            'fallback_reason' => $reason
        ];
    }

    /**
     * Get LLM status information
     * Useful for debugging and monitoring
     * 
     * @return array Status information
     */
    public static function getStatus() {
        return [
            'configured' => isGeminiConfigured(),
            'api_key_set' => !empty(GEMINI_API_KEY),
            'api_url' => GEMINI_API_URL,
            'api_timeout' => API_TIMEOUT
        ];
    }
}

?>
