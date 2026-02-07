<?php
// MANDATORY: Suppress all PHP warnings and errors to ensure JSON-only output
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Fetch Interview Results from Database
 * AI Mock Interview System
 */

header('Content-Type: application/json');
require_once 'db_config.php';

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        throw new Exception('Invalid request method');
    }

    // Get user_id from query parameters
    if (!isset($_GET['user_id'])) {
        throw new Exception('user_id is required');
    }

    $userId = (int)$_GET['user_id'];
    
    if ($userId <= 0) {
        throw new Exception('Invalid user_id');
    }

    // Fetch results from database
    $query = "SELECT id, question, answer_text, accuracy, clarity, completeness, confidence, created_at 
              FROM interview_results 
              WHERE user_id = ? 
              ORDER BY created_at DESC";
    
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception('Statement preparation failed: ' . $conn->error);
    }

    $stmt->bind_param('i', $userId);

    if (!$stmt->execute()) {
        throw new Exception('Query execution failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $results = [];

    while ($row = $result->fetch_assoc()) {
        $results[] = [
            'id' => (int)$row['id'],
            'question' => $row['question'],
            'answer_text' => $row['answer_text'],
            'accuracy' => (float)$row['accuracy'],
            'clarity' => (float)$row['clarity'],
            'completeness' => (float)$row['completeness'],
            'confidence' => (float)$row['confidence'],
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'count' => count($results),
        'results' => $results
    ]);

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
