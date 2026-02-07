<?php
// MANDATORY: Suppress all PHP warnings and errors to ensure JSON-only output
error_reporting(0);
ini_set('display_errors', 0);

/**
 * API Endpoint - Evaluate Interview Answers
 * Connects to Google Gemini API (free tier) for intelligent evaluation
 * AI Mock Interview System
 */

header('Content-Type: application/json');
require_once 'config.php';
require_once 'llm_service.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        error_log('[api_evaluate] Invalid method: ' . $_SERVER['REQUEST_METHOD']);
        throw new Exception('Invalid request method. POST required.');
    }

    // Get JSON input
    $input = file_get_contents('php://input');
    error_log('[api_evaluate] Raw input: ' . substr($input, 0, 100));
    $data = json_decode($input, true);
    
    if (!$data) {
        error_log('[api_evaluate] JSON decode error: ' . json_last_error_msg());
        throw new Exception('Invalid JSON input: ' . json_last_error_msg());
    }

    // Validate required fields
    if (!isset($data['answer']) || !isset($data['question'])) {
        error_log('[api_evaluate] Missing fields: ' . implode(', ', array_keys($data)));
        throw new Exception('Missing required fields: answer and question');
    }

    $answer = trim($data['answer']);
    $question = trim($data['question']);

    // Validate answer is not empty
    if (empty($answer)) {
        throw new Exception('Answer cannot be empty');
    }

    // CRITICAL: Use LLM service to evaluate the answer
    // This connects to Google Gemini API (free tier)
    error_log('[api_evaluate] Evaluating answer, Q len: ' . strlen($question) . ', A len: ' . strlen($answer));
    debugLog('Evaluating answer: ' . substr($answer, 0, 100) . '...');
    
    $evaluation = LLMService::evaluateAnswer($question, $answer);
    error_log('[api_evaluate] Evaluation result: ' . json_encode($evaluation));
    
    // Return the evaluation result to frontend
    echo json_encode([
        'success' => true,
        'evaluation' => $evaluation,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (Exception $e) {
    error_log('[api_evaluate] EXCEPTION: ' . $e->getMessage());
    http_response_code(400);
    debugLog('Error: ' . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

?>
