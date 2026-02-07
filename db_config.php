<?php
// MANDATORY: Suppress all PHP warnings and errors to ensure JSON-only output
error_reporting(0);
ini_set('display_errors', 0);

/**
 * Database Configuration
 * AI Mock Interview System
 */

// Database credentials
define('DB_SERVER', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ai_mock_interview');

// Create connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);

// Check connection
if ($conn->connect_error) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Set charset to UTF-8
$conn->set_charset("utf8");

// Error handling for development
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

?>
