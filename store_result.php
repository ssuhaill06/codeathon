<?php
// MANDATORY: Suppress all PHP warnings and errors to ensure JSON-only output
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Store Interview Result to Database
 * AI Mock Interview System
 */

header('Content-Type: application/json');
require_once 'db_config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Get input data
    $rawInput = file_get_contents('php://input');
    error_log('[store_result] Input: ' . substr($rawInput, 0, 100));
    $data = json_decode($rawInput, true);
    
    if (!$data) {
        error_log('[store_result] JSON error: ' . json_last_error_msg());
        throw new Exception('Invalid JSON: ' . json_last_error_msg());
    }
    
    if (!isset($data['userId']) || !isset($data['question']) || !isset($data['answer']) || !isset($data['scores'])) {
        error_log('[store_result] Missing: ' . implode(', ', array_keys($data)));\n        throw new Exception('Missing required fields: userId, question, answer, scores');
    }

    $userId = (int)$data['userId'];
    $question = trim($data['question']);
    $answer = trim($data['answer']);
    $scores = $data['scores'];

    if (empty($question) || empty($answer)) {
        throw new Exception('Question and answer cannot be empty');
    }

    // Validate scores structure
    if (!isset($scores['accuracy']) || !isset($scores['clarity']) || !isset($scores['completeness']) || !isset($scores['confidence'])) {
        error_log('[store_result] Invalid scores: ' . json_encode($scores));
        throw new Exception('Invalid scores structure');
    }

    // Validate scores
    $accuracy = (float)$scores['accuracy'];
    $clarity = (float)$scores['clarity'];
    $completeness = (float)$scores['completeness'];
    $confidence = (float)$scores['confidence'];
    
    error_log('[store_result] Scores: A=' . $accuracy . ', C=' . $clarity . ', Co=' . $completeness . ', Cf=' . $confidence);

    // Prepare SQL statement
    $stmt = $conn->prepare("INSERT INTO interview_results (user_id, question, answer_text, accuracy, clarity, completeness, confidence) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        error_log('[store_result] Prepare failed: ' . $conn->error);
        throw new Exception('Statement preparation failed: ' . $conn->error);
    }

    // Bind parameters
    $bindResult = $stmt->bind_param('isddddd', $userId, $question, $answer, $accuracy, $clarity, $completeness, $confidence);
    if (!$bindResult) {
        error_log('[store_result] Bind failed: ' . $stmt->error);
        throw new Exception('Bind parameter failed: ' . $stmt->error);
    }

    // Execute statement
    if ($stmt->execute()) {
        $insertId = $stmt->insert_id;
        error_log('[store_result] INSERT successful, ID: ' . $insertId);
        echo json_encode([
            'success' => true,
            'message' => 'Result stored successfully',
            'resultId' => $insertId
        ]);
    } else {
        error_log('[store_result] Execute failed: ' . $stmt->error);
        throw new Exception('Failed to store result: ' . $stmt->error);
    }

    $stmt->close();

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

$conn->close();

?>
