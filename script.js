/**
 * AI Mock Interview System - JavaScript Logic
 * Handles Web Speech API, Interview Flow, and API Integration
 */

// ==================== CONFIGURATION ====================
const CONFIG = {
    userId: 1, // Guest user ID (can be changed for multi-user support)
    questions: [
        "Tell me about your professional background and experience.",
        "What are your key strengths in your field?",
        "Describe a challenging project you've worked on and how you overcame the obstacles.",
        "How do you stay updated with the latest trends in your industry?",
        "Where do you see yourself in the next five years?"
    ],
    apiBaseUrl: '' // Empty for same origin requests
};

// ==================== STATE MANAGEMENT ====================
const state = {
    currentQuestionIndex: 0,
    isListening: false,
    isVoiceMode: true,
    currentAnswer: '',
    allResults: [],
    interviewStarted: false,
    recognition: null,
    synthesis: null,
    recognitionInitialized: false, // FIX: Prevent multiple initialization
    voiceAvailable: true, // FIX: Track if voice mode is available
    lastErrorTime: 0, // FIX: Prevent alert spam
    errorAlertTimeout: 3000 // FIX: Minimum time between alerts (milliseconds)
};

// ==================== WEB SPEECH API SETUP ====================
/**
 * CRITICAL FIX FOR VOICE INPUT:
 * This function properly initializes Web Speech API with error handling.
 * Voice input now works by:
 * 1. Starting recognition only on button click
 * 2. Capturing speech as final transcript
 * 3. Populating both the textarea and display with recognized text
 * 4. Gracefully handling errors
 */
function initializeSpeechAPI() {
    // Only initialize once
    if (state.recognitionInitialized && state.recognition) {
        return true;
    }

    // Get the right API for this browser
    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    const SpeechSynthesis = window.speechSynthesis;

    if (!SpeechRecognition) {
        console.warn('Speech Recognition not supported in this browser.');
        state.voiceAvailable = false;
        return false;
    }

    state.recognition = new SpeechRecognition();
    state.synthesis = SpeechSynthesis;

    // CRITICAL: Configure for reliable speech capture
    state.recognition.continuous = false;        // Stop after one phrase
    state.recognition.interimResults = false;    // Only process final results (FIX: was true, now false)
    state.recognition.language = 'en-US';        // English language

    // CRITICAL: Handle speech recognition start
    state.recognition.onstart = function() {
        state.isListening = true;
        updateListeningUI(true);
        console.log('🎤 Microphone is listening...');
    };

    // CRITICAL: This is where voice → text conversion happens
    state.recognition.onresult = function(event) {
        let transcript = '';

        // Process all results
        for (let i = event.resultIndex; i < event.results.length; i++) {
            const current = event.results[i][0].transcript;
            transcript += current;
        }

        // CRITICAL FIX: Populate BOTH the textarea and the display
        if (transcript.trim()) {
            state.currentAnswer = transcript.trim();
            
            // Update textarea (CRITICAL: this was missing before)
            const answerInput = document.getElementById('answerInput');
            if (answerInput) {
                answerInput.value = state.currentAnswer;
            }
            
            // Update display
            updateAnswerDisplay();
            
            console.log('✓ Transcript captured:', state.currentAnswer);
        }

        // Auto-stop after final result captured
        if (event.isFinal) {
            setTimeout(function() {
                safeStopRecognition();
            }, 300);
        }
    };

    // CRITICAL: Error handling prevents silent failures
    state.recognition.onerror = function(event) {
        console.error('🔴 Speech Recognition Error:', event.error);
        
        // Prevent error spam
        const now = Date.now();
        if (now - state.lastErrorTime < state.errorAlertTimeout) {
            return;
        }
        state.lastErrorTime = now;

        switch (event.error) {
            case 'network':
                console.warn('Network error detected. Switching to text mode.');
                handleVoiceModeFailure('Network issue with speech recognition. Using text input instead.');
                break;

            case 'not-allowed':
                console.warn('Microphone access denied by user.');
                handleVoiceModeFailure('Microphone access denied. Please enable microphone permissions.');
                break;

            case 'no-speech':
                console.log('No speech detected. Try speaking again.');
                state.isListening = false;
                updateListeningUI(false);
                break;

            case 'audio-capture':
                console.warn('No microphone or audio input device found.');
                handleVoiceModeFailure('No microphone detected. Please connect a microphone.');
                break;

            default:
                console.error('Unexpected error:', event.error);
                handleVoiceModeFailure(`Speech recognition error: ${event.error}`);
        }

        state.isListening = false;
        updateListeningUI(false);
    };

    // Handle recognition end - CRITICAL: Reset state for next use
    state.recognition.onend = function() {
        state.isListening = false;
        updateListeningUI(false);
        console.log('🎤 Microphone stopped, ready for next input');
        
        // CRITICAL FIX: Browser can now start a new recognition cycle
        // The recognition object can be reused by calling .start() again
    };

    state.recognitionInitialized = true;
    console.log('✓ Speech API initialized successfully');
    return true;
}

// FIX: Safe way to stop recognition without errors
function safeStopRecognition() {
    if (state.recognition && state.isListening) {
        try {
            state.recognition.stop();
        } catch (error) {
            console.warn('Error stopping recognition:', error);
        }
    }
}

// FIX: Handle voice mode failures gracefully
function handleVoiceModeFailure(message) {
    state.voiceAvailable = false;
    
    // Auto-switch to text mode
    if (state.isVoiceMode) {
        state.isVoiceMode = false;
        document.getElementById('textToggleBtn').classList.add('active');
        document.getElementById('voiceToggleBtn').classList.remove('active');
        document.getElementById('voiceSection').classList.add('hidden');
        document.getElementById('textSection').classList.remove('hidden');
        
        // Focus text input
        const textarea = document.getElementById('answerInput');
        textarea.focus();
    }
    
    // Disable voice mode button
    document.getElementById('voiceToggleBtn').disabled = true;
    document.getElementById('startListeningBtn').disabled = true;
    document.getElementById('stopListeningBtn').disabled = true;
    
    // Show user message (not alert spam)
    showUserMessage(message, 'error');
}

// FIX: Show user messages without spam
function showUserMessage(message, type = 'info') {
    console.log(`[${type.toUpperCase()}] ${message}`);
    // Create a non-blocking notification (optional DOM element)
    // For now, log to console - can be enhanced with toast/notification UI
}

// ==================== UI UPDATE FUNCTIONS ====================
function updateListeningUI(listening) {
    const listeningStatus = document.getElementById('listeningStatus');
    const startBtn = document.getElementById('startListeningBtn');
    const stopBtn = document.getElementById('stopListeningBtn');

    if (listening) {
        listeningStatus.classList.remove('hidden');
        startBtn.disabled = true;
        stopBtn.disabled = false;
    } else {
        listeningStatus.classList.add('hidden');
        startBtn.disabled = false;
        stopBtn.disabled = true;
    }
}

function updateAnswerDisplay() {
    const answerDisplay = document.getElementById('answerDisplay');
    const answerInput = document.getElementById('answerInput');
    const submitBtn = document.getElementById('submitBtn');

    // CRITICAL FIX: Update BOTH the display and the textarea
    if (state.currentAnswer) {
        // Update the display area
        answerDisplay.innerHTML = state.currentAnswer;
        
        // Update the hidden textarea (for submission)
        if (answerInput) {
            answerInput.value = state.currentAnswer;
        }
        
        // Enable submit button
        submitBtn.disabled = false;
    } else {
        // Show placeholder
        answerDisplay.innerHTML = '<span class="placeholder">Your answer will appear here...</span>';
        
        // Clear textarea
        if (answerInput) {
            answerInput.value = '';
        }
        
        // Disable submit button
        submitBtn.disabled = true;
    }
}

function updateQuestionCounter() {
    const counter = document.getElementById('questionCounter');
    counter.textContent = `Question ${state.currentQuestionIndex + 1}/${CONFIG.questions.length}`;
}

function displayQuestion(index) {
    const questionDisplay = document.getElementById('questionDisplay');
    const question = CONFIG.questions[index];
    
    questionDisplay.innerHTML = `<p>${question}</p>`;
    updateQuestionCounter();
}

function showInterviewSection() {
    document.getElementById('interviewSection').classList.remove('hidden');
    document.getElementById('resultsSection').classList.add('hidden');
    document.getElementById('endSection').classList.add('hidden');
}

function showResultsSection() {
    document.getElementById('interviewSection').classList.add('hidden');
    document.getElementById('resultsSection').classList.remove('hidden');
    document.getElementById('endSection').classList.add('hidden');
}

function showEndSection() {
    document.getElementById('interviewSection').classList.add('hidden');
    document.getElementById('resultsSection').classList.add('hidden');
    document.getElementById('endSection').classList.remove('hidden');
    populateEndSection();
}

// ==================== TEXT TO SPEECH ====================
function speakQuestion(question) {
    if (!state.synthesis) return;

    // Cancel any ongoing speech
    state.synthesis.cancel();

    const utterance = new SpeechSynthesisUtterance(question);
    utterance.rate = 0.95;
    utterance.pitch = 1;
    utterance.volume = 1;

    state.synthesis.speak(utterance);
}

// ==================== VOICE INTERACTION HANDLERS ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('📍 Page loaded, initializing interview system...');
    
    // Initialize Speech API - CRITICAL for voice input
    const speechInitialized = initializeSpeechAPI();
    
    if (!speechInitialized) {
        // Speech API not supported - disable voice mode
        console.warn('Speech API not available, using text mode only');
        state.voiceAvailable = false;
        document.getElementById('voiceToggleBtn').disabled = true;
        document.getElementById('startListeningBtn').disabled = true;
        document.getElementById('stopListeningBtn').disabled = true;
        
        // Auto-switch to text mode
        state.isVoiceMode = false;
        document.getElementById('textToggleBtn').classList.add('active');
        document.getElementById('voiceToggleBtn').classList.remove('active');
        document.getElementById('voiceSection').classList.add('hidden');
        document.getElementById('textSection').classList.remove('hidden');
    } else {
        // Speech API is available - ensure voice mode is active
        console.log('✓ Speech API ready, voice mode enabled');
        state.voiceAvailable = true;
        state.isVoiceMode = true;
        document.getElementById('voiceToggleBtn').classList.add('active');
        document.getElementById('textToggleBtn').classList.remove('active');
        document.getElementById('voiceSection').classList.remove('hidden');
        document.getElementById('textSection').classList.add('hidden');
    }

    // Voice Mode Button
    document.getElementById('voiceToggleBtn').addEventListener('click', () => {
        // FIX: Check if voice mode is actually available
        if (!state.voiceAvailable) {
            showUserMessage('Voice mode is not available on this device.', 'warning');
            return;
        }

        state.isVoiceMode = true;
        document.getElementById('voiceToggleBtn').classList.add('active');
        document.getElementById('textToggleBtn').classList.remove('active');
        document.getElementById('voiceSection').classList.remove('hidden');
        document.getElementById('textSection').classList.add('hidden');
        if (state.currentAnswer) {
            document.getElementById('answerInput').value = '';
        }
    });

    // Text Mode Button
    document.getElementById('textToggleBtn').addEventListener('click', () => {
        state.isVoiceMode = false;
        document.getElementById('textToggleBtn').classList.add('active');
        document.getElementById('voiceToggleBtn').classList.remove('active');
        document.getElementById('voiceSection').classList.add('hidden');
        document.getElementById('textSection').classList.remove('hidden');
        
        // FIX: Stop any active recognition when switching to text mode
        if (state.isListening) {
            safeStopRecognition();
        }
        
        // Pre-fill textarea with current answer
        const textarea = document.getElementById('answerInput');
        textarea.value = state.currentAnswer;
        textarea.focus();
    });

    // Start Listening Button - CRITICAL: This must work correctly
    document.getElementById('startListeningBtn').addEventListener('click', function() {
        // Check if voice is available
        if (!state.voiceAvailable) {
            console.warn('Voice mode not available');
            return;
        }

        // Prevent double-start
        if (state.isListening) {
            console.log('Already listening, ignoring start request');
            return;
        }

        // Check if recognition object exists
        if (!state.recognition) {
            console.error('Speech Recognition not initialized!');
            return;
        }

        // Clear previous answer
        state.currentAnswer = '';
        updateAnswerDisplay();

        try {
            console.log('🎤 Starting speech recognition...');
            state.recognition.start();
        } catch (error) {
            console.error('Error starting recognition:', error);
            console.warn('Falling back to text mode');
            handleVoiceModeFailure('Failed to start microphone. Using text mode instead.');
        }
    });

    // Stop Listening Button - CRITICAL: Must stop cleanly
    document.getElementById('stopListeningBtn').addEventListener('click', function() {
        console.log('⏹ Stopping speech recognition...');
        safeStopRecognition();
    });

    // Submit Answer Button
    document.getElementById('submitBtn').addEventListener('click', submitAnswer);

    // Clear Button
    document.getElementById('clearBtn').addEventListener('click', () => {
        state.currentAnswer = '';
        document.getElementById('answerInput').value = '';
        updateAnswerDisplay();
        if (state.isVoiceMode) {
            // FIX: Safe cleanup of recognition state
            safeStopRecognition();
            updateListeningUI(false);
        }
    });

    // Next Question Button
    document.getElementById('nextBtn').addEventListener('click', nextQuestion);

    // Restart Interview Button
    document.getElementById('restartBtn').addEventListener('click', restartInterview);

    // Home Button
    document.getElementById('homeBtn').addEventListener('click', () => {
        // FIX: Stop recognition before navigating away
        if (state.recognition) {
            safeStopRecognition();
        }
        window.location.href = 'index.html';
    });

    // Textarea input handling - CRITICAL: For text mode input
    document.getElementById('answerInput').addEventListener('input', function(e) {
        state.currentAnswer = e.target.value.trim();
        updateAnswerDisplay();
        console.log('📝 Text input updated:', state.currentAnswer.substring(0, 50) + '...');
    });

    // Start the interview
    console.log('✓ All event listeners attached, starting interview...');
    startInterview();
});

// ==================== INTERVIEW FLOW ====================
function startInterview() {
    state.interviewStarted = true;
    state.currentQuestionIndex = 0;
    state.allResults = [];
    showInterviewSection();
    loadNextQuestion();
}

function loadNextQuestion() {
    if (state.currentQuestionIndex < CONFIG.questions.length) {
        // FIX: Stop any active recognition before loading new question
        if (state.isListening) {
            safeStopRecognition();
        }

        state.currentAnswer = '';
        document.getElementById('answerInput').value = '';
        updateAnswerDisplay();

        displayQuestion(state.currentQuestionIndex);
        
        // Speak the question
        const question = CONFIG.questions[state.currentQuestionIndex];
        speakQuestion(question);

        // Focus on listening if in voice mode
        if (state.isVoiceMode && state.voiceAvailable) {
            setTimeout(() => {
                document.getElementById('startListeningBtn').focus();
            }, 2000);
        } else {
            document.getElementById('answerInput').focus();
        }
    }
}

async function submitAnswer() {
    console.log('🎯 SUBMIT BUTTON CLICKED');
    
    // CRITICAL FIX: Always sync from textarea to ensure we have the latest answer
    const textarea = document.getElementById('answerInput');
    if (textarea) {
        const textareaValue = textarea.value.trim();
        if (textareaValue) {
            state.currentAnswer = textareaValue;
            console.log('📝 Synced answer from textarea');
        }
    }
    
    if (!state.currentAnswer || !state.currentAnswer.trim()) {
        showUserMessage('Please provide an answer first', 'warning');
        console.warn('⚠️  Submission blocked: No answer provided');
        return;
    }

    console.log('✓ Answer text captured:', state.currentAnswer.substring(0, 100) + '...');

    // FIX: Stop listening when submitting answer
    if (state.isListening) {
        console.log('🛑 Stopping microphone before submission');
        safeStopRecognition();
    }

    showLoadingSpinner(true);
    console.log('⏳ Showing loading spinner, beginning submission process...');

    try {
        const question = CONFIG.questions[state.currentQuestionIndex];
        console.log('📋 Current question:', question);

        // Step 1: Evaluate answer with LLM API
        console.log('📤 Sending answer to LLM evaluation endpoint...');
        const evaluationResponse = await evaluateAnswer(question, state.currentAnswer);
        console.log('📥 Evaluation API response:', evaluationResponse);

        if (!evaluationResponse || !evaluationResponse.success) {
            const errorMsg = evaluationResponse?.error || 'Evaluation failed';
            throw new Error(errorMsg);
        }

        const scores = evaluationResponse.evaluation;
        console.log('✓ Evaluation scores received:', scores);
        console.log('  - Accuracy:', scores.accuracy + '%');
        console.log('  - Clarity:', scores.clarity + '%');
        console.log('  - Completeness:', scores.completeness + '%');
        console.log('  - Confidence:', scores.confidence + '%');

        // Step 2: Store result in database
        console.log('💾 Sending results to database...');
        const storeResponse = await storeResult(question, state.currentAnswer, scores);
        console.log('📥 Database response:', storeResponse);

        if (!storeResponse || !storeResponse.success) {
            const errorMsg = storeResponse?.error || 'Failed to store result';
            throw new Error(errorMsg);
        }

        console.log('✓ Result stored successfully with ID:', storeResponse.resultId);

        // Step 3: Save to local state
        state.allResults.push({
            question: question,
            answer: state.currentAnswer,
            scores: scores
        });
        console.log('✓ Result saved to local state. Total results:', state.allResults.length);

        // Step 4: Display results
        displayResults(question, state.currentAnswer, scores);
        showResultsSection();
        console.log('✓ Results displayed and section shown');

    } catch (error) {
        console.error('❌ ERROR DURING SUBMISSION:', error);
        console.error('   Error message:', error.message);
        console.error('   Error stack:', error.stack);
        
        const errorMessage = 'Error processing answer: ' + error.message;
        showUserMessage(errorMessage, 'error');
        alert('⚠️  Submission failed! Check browser console (F12) for details.\n\n' + error.message);
    } finally {
        showLoadingSpinner(false);
        console.log('✓ Loading spinner hidden');
    }
}

function nextQuestion() {
    state.currentQuestionIndex++;

    if (state.currentQuestionIndex < CONFIG.questions.length) {
        loadNextQuestion();
        showInterviewSection();
    } else {
        showEndSection();
    }
}

function restartInterview() {
    startInterview();
}

// ==================== RESULTS DISPLAY ====================
function displayResults(question, answer, scores) {
    document.getElementById('resultQuestion').textContent = question;
    document.getElementById('resultAccuracy').textContent = scores.accuracy.toFixed(0);
    document.getElementById('resultClarity').textContent = scores.clarity.toFixed(0);
    document.getElementById('resultCompleteness').textContent = scores.completeness.toFixed(0);
    document.getElementById('resultConfidence').textContent = scores.confidence.toFixed(0);

    document.getElementById('reviewAnswer').textContent = answer;

    // Update progress bars
    document.getElementById('accuracyBar').style.width = scores.accuracy + '%';
    document.getElementById('clarityBar').style.width = scores.clarity + '%';
    document.getElementById('completenessBar').style.width = scores.completeness + '%';
    document.getElementById('confidenceBar').style.width = scores.confidence + '%';
}

function populateEndSection() {
    if (state.allResults.length === 0) return;

    // Calculate averages
    const avgAccuracy = state.allResults.reduce((sum, r) => sum + r.scores.accuracy, 0) / state.allResults.length;
    const avgClarity = state.allResults.reduce((sum, r) => sum + r.scores.clarity, 0) / state.allResults.length;
    const avgCompleteness = state.allResults.reduce((sum, r) => sum + r.scores.completeness, 0) / state.allResults.length;
    const avgConfidence = state.allResults.reduce((sum, r) => sum + r.scores.confidence, 0) / state.allResults.length;

    document.getElementById('summaryAccuracy').textContent = avgAccuracy.toFixed(0);
    document.getElementById('summaryClarity').textContent = avgClarity.toFixed(0);
    document.getElementById('summaryCompleteness').textContent = avgCompleteness.toFixed(0);
    document.getElementById('summaryConfidence').textContent = avgConfidence.toFixed(0);

    // Build history table
    const historyTable = document.getElementById('historyTable');
    const table = document.createElement('table');
    table.innerHTML = `
        <thead>
            <tr>
                <th>Question #</th>
                <th>Accuracy</th>
                <th>Clarity</th>
                <th>Completeness</th>
                <th>Confidence</th>
            </tr>
        </thead>
        <tbody>
            ${state.allResults.map((result, index) => `
                <tr>
                    <td>Question ${index + 1}</td>
                    <td>${result.scores.accuracy.toFixed(0)}%</td>
                    <td>${result.scores.clarity.toFixed(0)}%</td>
                    <td>${result.scores.completeness.toFixed(0)}%</td>
                    <td>${result.scores.confidence.toFixed(0)}%</td>
                </tr>
            `).join('')}
        </tbody>
    `;
    historyTable.innerHTML = '';
    historyTable.appendChild(table);
}

// ==================== API CALLS ====================
async function evaluateAnswer(question, answer) {
    const endpoint = `${CONFIG.apiBaseUrl}api_evaluate_answer.php`;
    console.log('  [evaluateAnswer] Sending to:', endpoint);
    console.log('  [evaluateAnswer] Question:', question.substring(0, 50) + '...');
    console.log('  [evaluateAnswer] Answer:', answer.substring(0, 50) + '...');
    
    try {
        const requestBody = {
            question: question,
            answer: answer
        };
        console.log('  [evaluateAnswer] Request payload:', JSON.stringify(requestBody).substring(0, 100) + '...');
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestBody)
        });

        console.log('  [evaluateAnswer] Response status:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('  [evaluateAnswer] Response body:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('  [evaluateAnswer] Response JSON:', data);
        return data;
    } catch (error) {
        console.error('  [evaluateAnswer] FETCH ERROR:', error.message);
        console.error('  [evaluateAnswer] Full error:', error);
        throw error;
    }
}

async function storeResult(question, answer, scores) {
    const endpoint = `${CONFIG.apiBaseUrl}store_result.php`;
    console.log('  [storeResult] Sending to:', endpoint);
    console.log('  [storeResult] User ID:', CONFIG.userId);
    console.log('  [storeResult] Scores object:', scores);
    
    try {
        const requestBody = {
            userId: CONFIG.userId,
            question: question,
            answer: answer,
            scores: scores
        };
        console.log('  [storeResult] Request payload keys:', Object.keys(requestBody).join(', '));
        
        const response = await fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(requestBody)
        });

        console.log('  [storeResult] Response status:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('  [storeResult] Response body:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('  [storeResult] Response JSON:', data);
        return data;
    } catch (error) {
        console.error('  [storeResult] FETCH ERROR:', error.message);
        console.error('  [storeResult] Full error:', error);
        throw error;
    }
}

// ==================== UTILITY FUNCTIONS ====================
function showLoadingSpinner(show) {
    const spinner = document.getElementById('loadingSpinner');
    if (show) {
        spinner.classList.remove('hidden');
    } else {
        spinner.classList.add('hidden');
    }
}

// Add smooth scroll behavior
window.addEventListener('load', () => {
    document.documentElement.style.scrollBehavior = 'smooth';
});

// FIX: Clean up recognition when page unloads
window.addEventListener('beforeunload', () => {
    if (state.recognition && state.isListening) {
        safeStopRecognition();
    }
});

// ==================== VOICE INPUT DIAGNOSTICS ====================
// VOICE INPUT DEBUG FUNCTION - Call from browser console to test
// Usage: testVoiceInput()
window.testVoiceInput = function() {
    console.clear();
    console.log('🎤 === VOICE INPUT DIAGNOSTIC TEST ===');
    console.log('');
    console.log('Status Check:');
    console.log('  - Speech API Available:', state.voiceAvailable);
    console.log('  - Recognition Initialized:', state.recognitionInitialized);
    console.log('  - Currently Listening:', state.isListening);
    console.log('  - Voice Mode Active:', state.isVoiceMode);
    console.log('  - Recognition Object:', state.recognition ? '✓ Exists' : '✗ Missing');
    console.log('');
    console.log('Current Answer:', state.currentAnswer || '(empty)');
    console.log('');
    console.log('🎯 QUICK TEST:');
    console.log('  1. Make sure browser has microphone permission');
    console.log('  2. Click "Start Listening" button');
    console.log('  3. Speak into your microphone');
    console.log('  4. Check if text appears in the answer box');
    console.log('  5. If it works, your voice input is FIXED ✓');
    console.log('');
    console.log('If voice input is NOT working:');
    console.log('  - Check browser console for error messages');
    console.log('  - Verify microphone permission is granted');
    console.log('  - Try text mode as fallback');
};

console.log('✓ Voice input system ready. Type testVoiceInput() to diagnose.');
