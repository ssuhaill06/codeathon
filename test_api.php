<?php
/**
 * Diagnostic Tool - Test Gemini API Configuration
 * AI Mock Interview System
 * 
 * Usage:
 * - From browser: http://localhost/ai-mock-interview/test_api.php
 * - From terminal: php test_api.php
 * 
 * This script verifies:
 * - API key is configured
 * - Network connection works
 * - API returns valid responses
 * - JSON parsing works correctly
 */

// Set execution time limit for testing
set_time_limit(30);

require_once 'config.php';
require_once 'llm_service.php';

// Check if running from CLI or browser
$isCLI = php_sapi_name() === 'cli';

if (!$isCLI) {
    header('Content-Type: text/html; charset=UTF-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>API Diagnostic Test</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 1000px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                border-radius: 8px;
                padding: 30px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 {
                color: #333;
                border-bottom: 3px solid #4f46e5;
                padding-bottom: 10px;
            }
            .status {
                display: flex;
                align-items: center;
                margin: 15px 0;
                padding: 10px;
                background: #f9f9f9;
                border-radius: 4px;
                border-left: 4px solid #ddd;
            }
            .status.success {
                border-left-color: #10b981;
                background: #ecfdf5;
            }
            .status.error {
                border-left-color: #ef4444;
                background: #fef2f2;
            }
            .status.warning {
                border-left-color: #f59e0b;
                background: #fffbeb;
            }
            .status-icon {
                font-size: 24px;
                margin-right: 15px;
                width: 30px;
            }
            .status-text {
                flex: 1;
            }
            .status-text strong {
                display: block;
                color: #333;
            }
            .status-text small {
                color: #666;
                display: block;
                margin-top: 5px;
            }
            code {
                background: #f0f0f0;
                padding: 2px 6px;
                border-radius: 3px;
                font-family: 'Courier New', monospace;
            }
            .test-result {
                margin: 20px 0;
                padding: 15px;
                background: #f9f9f9;
                border-radius: 4px;
                border-left: 4px solid #4f46e5;
            }
            .test-result h3 {
                margin-top: 0;
                color: #333;
            }
            .test-result pre {
                background: white;
                padding: 10px;
                border-radius: 3px;
                overflow-x: auto;
                font-size: 12px;
                max-height: 300px;
            }
            button {
                background: #4f46e5;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 4px;
                cursor: pointer;
                font-size: 16px;
                margin-top: 20px;
            }
            button:hover {
                background: #4338ca;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>🔧 Gemini API Diagnostic Test</h1>
            <p>This tool tests your Google Gemini API configuration and connectivity.</p>
    <?php
}

// ==================== DIAGNOSTIC CHECKS ====================

echo $isCLI ? "\n🔧 GEMINI API DIAGNOSTIC TEST\n\n" : "";

// ==================== Check 1: Configuration ====================
echo $isCLI ? "1. Configuration Check\n" : "<h2>1. Configuration Check</h2>";

$apiConfigured = isGeminiConfigured();

if ($apiConfigured) {
    $status = "✓";
    $message = "Gemini API key is configured";
    $detail = "API key found: " . substr(GEMINI_API_KEY, 0, 10) . "...";
    $class = "success";
} else {
    $status = "✗";
    $message = "Gemini API key is NOT configured";
    $detail = "See GEMINI_API_SETUP.md for instructions on getting a free API key";
    $class = "error";
}

if ($isCLI) {
    echo "   $status $message\n   $detail\n\n";
} else {
    echo "<div class='status $class'><div class='status-icon'>$status</div><div class='status-text'><strong>$message</strong><small>$detail</small></div></div>\n";
}

// ==================== Check 2: Network Connectivity ====================
echo $isCLI ? "2. Network Connectivity Check\n" : "<h2>2. Network Connectivity Check</h2>";

$useragent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36";
$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => 'https://www.google.com',
    CURLOPT_NOBODY => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_USERAGENT => $useragent,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 400) {
    $status = "✓";
    $message = "Internet connection is working";
    $detail = "Successfully reached Google servers";
    $class = "success";
} else {
    $status = "✗";
    $message = "Internet connectivity issue";
    $detail = "Could not reach Google servers (HTTP $httpCode)";
    $class = "error";
}

if ($isCLI) {
    echo "   $status $message\n   $detail\n\n";
} else {
    echo "<div class='status $class'><div class='status-icon'>$status</div><div class='status-text'><strong>$message</strong><small>$detail</small></div></div>\n";
}

// ==================== Check 3: API Functionality Test ====================
if ($apiConfigured) {
    echo $isCLI ? "3. API Functionality Test\n" : "<h2>3. API Functionality Test</h2>";
    
    // Test with a simple answer
    $testQuestion = "What is your experience with teamwork?";
    $testAnswer = "I have successfully collaborated with diverse teams in multiple projects, showing strong communication and problem-solving skills.";
    
    if ($isCLI) {
        echo "   Testing with sample answer...\n";
    } else {
        echo "<p>Testing with sample answer...</p>";
        echo "<div class='test-result'><h3>Test Input</h3>";
        echo "<p><strong>Question:</strong> $testQuestion</p>";
        echo "<p><strong>Answer:</strong> $testAnswer</p></div>";
    }
    
    $startTime = microtime(true);
    $evaluation = LLMService::evaluateAnswer($testQuestion, $testAnswer);
    $duration = microtime(true) - $startTime;
    
    if ($isCLI) {
        echo "   Response time: " . round($duration, 2) . " seconds\n";
    } else {
        echo "<p><strong>Response time:</strong> " . round($duration, 2) . " seconds</p>";
    }
    
    if (isset($evaluation['fallback']) && $evaluation['fallback']) {
        $status = "⚠";
        $message = "API using fallback scores";
        $detail = "Reason: " . ($evaluation['fallback_reason'] ?? 'Unknown');
        $class = "warning";
        
        if ($isCLI) {
            echo "   $status $message\n   $detail\n\n";
        } else {
            echo "<div class='status $class'><div class='status-icon'>$status</div><div class='status-text'><strong>$message</strong><small>$detail</small></div></div>\n";
        }
    } else {
        $status = "✓";
        $message = "API evaluation successful";
        $detail = "Real scores from Gemini API received";
        $class = "success";
        
        if ($isCLI) {
            echo "   $status $message\n   $detail\n\n";
        } else {
            echo "<div class='status $class'><div class='status-icon'>$status</div><div class='status-text'><strong>$message</strong><small>$detail</small></div></div>\n";
        }
    }
    
    // Display the evaluation results
    if ($isCLI) {
        echo "   Evaluation Results:\n";
        echo "   - Accuracy: " . $evaluation['accuracy'] . "%\n";
        echo "   - Clarity: " . $evaluation['clarity'] . "%\n";
        echo "   - Completeness: " . $evaluation['completeness'] . "%\n";
        echo "   - Confidence: " . $evaluation['confidence'] . "%\n\n";
    } else {
        echo "<div class='test-result'><h3>Evaluation Scores</h3>";
        echo "<pre>" . json_encode($evaluation, JSON_PRETTY_PRINT) . "</pre></div>";
    }
}

// ==================== Summary ====================
echo $isCLI ? "\n" . str_repeat("=", 50) . "\n" : "<h2>Summary</h2>";
echo $isCLI ? "DIAGNOSTIC COMPLETE\n" : "<p>";

if ($apiConfigured) {
    echo $isCLI ? "Status: ✓ All systems operational\n" : "✓ <strong>All systems operational</strong>";
    if (!isset($evaluation['fallback']) || !$evaluation['fallback']) {
        echo $isCLI ? "API Status: Real LLM evaluation working\n" : " - Real LLM evaluation working";
    }
} else {
    echo $isCLI ? "Status: ✗ Configuration required\n" : "✗ <strong>Configuration required</strong>";
    echo $isCLI ? "Next steps:\n  1. Get free Gemini API key from https://ai.google.dev/\n  2. Set GEMINI_API_KEY environment variable\n  3. Restart XAMPP\n  4. Run this test again\n" : " - See GEMINI_API_SETUP.md for instructions";
}

if (!$isCLI) {
    echo "</p><button onclick='location.reload()'>Run Test Again</button></div></body></html>";
}

echo $isCLI ? "\n" : "";

?>
