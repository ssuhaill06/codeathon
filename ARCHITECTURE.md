# Technical Architecture Documentation

## System Overview

The AI Mock Interview System is a full-stack web application designed for conducting realistic mock interviews with AI-powered evaluation.

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENT BROWSER                        │
│  ┌──────────────┬──────────────────────┬──────────────────┐ │
│  │  Landing     │  Interview Page      │  Results Page    │ │
│  │  (index.html)│  (interview.html)    │  (Same HTML)     │ │
│  └──────────────┴──────────────────────┴──────────────────┘ │
│         ↓              ↓                      ↓              │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         Frontend Logic (script.js)                  │   │
│  │  - Web Speech API (voice recognition)              │   │
│  │  - Speech Synthesis (question audio)               │   │
│  │  - Interview flow management                       │   │
│  │  - API calls to backend                            │   │
│  │  - Results visualization                           │   │
│  └──────────────────────────────────────────────────────┘   │
│         ↓              ↓              ↓                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │           Styling (style.css)                       │   │
│  │  - Responsive design                               │   │
│  │  - Dark/Light themed interface                     │   │
│  │  - Animation & transitions                         │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                           ↓
                      HTTP/HTTPS
                    (AJAX Requests)
                           ↓
┌─────────────────────────────────────────────────────────────┐
│                    SERVER (Apache/PHP)                       │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         PHP Backend Scripts                         │   │
│  │  • db_config.php - DB connection                   │   │
│  │  • api_evaluate_answer.php - LLM evaluation        │   │
│  │  • store_result.php - Save to database             │   │
│  │  • get_results.php - Fetch results                 │   │
│  └──────────────────────────────────────────────────────┘   │
│         ↓                                                    │
│  ┌──────────────────────────────────────────────────────┐   │
│  │         Database Access Layer                       │   │
│  │  • MySQLi prepared statements                      │   │
│  │  • Input sanitization & validation                 │   │
│  │  • Error handling & logging                        │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
            ↓                            ↓
    ┌─────────────────┐        ┌──────────────────┐
    │  MySQL Database │        │  LLM API (Optional)
    │                 │        │  OpenAI GPT API  │
    │ • users table   │        │  + Fallback Mock │
    │ • results table │        │  Evaluation      │
    └─────────────────┘        └──────────────────┘
```

---

## Component Details

### 1. Frontend Components

#### index.html (Landing Page)
- **Purpose**: Welcome screen and introduction
- **Elements**:
  - Hero section with app description
  - Feature cards (voice, AI, tracking)
  - Call-to-action button
- **Size**: ~3 KB
- **Dependencies**: style.css

#### interview.html (Interview Page)
- **Purpose**: Main interview interface
- **Sections**:
  - Interview section (questions & answers)
  - Results section (evaluation display)
  - End section (summary & history)
- **Size**: ~6 KB
- **Dependencies**: style.css, script.js

#### style.css (Styling)
- **Purpose**: Complete responsive styling
- **Includes**:
  - CSS variables for theming
  - Flexbox & Grid layouts
  - Animations & transitions
  - Mobile responsiveness
  - Dark gradient backgrounds
- **Size**: ~12 KB
- **Responsive**: Mobile-first, tested on 480px+

#### script.js (Frontend Logic)
- **Purpose**: Core interview logic and Web Speech API
- **Key Functions**:
  ```
  initializeSpeechAPI()    - Setup Web Speech
  startInterview()         - Begin interview
  loadNextQuestion()       - Display next question
  submitAnswer()          - Process answer
  evaluateAnswer()        - Call LLM API
  storeResult()           - Save to database
  displayResults()        - Show scores
  speakQuestion()         - Text-to-speech
  updateUI()              - Refresh interface
  ```
- **Size**: ~15 KB
- **Dependencies**: None (vanilla JS)

---

### 2. Backend Components

#### db_config.php (Database Configuration)
```php
- MySQLi connection
- Error handling
- UTF-8 charset
- Connection pooling ready
```

#### api_evaluate_answer.php (LLM Evaluation)
```
Input: {question, answer}
↓
Process:
  - Validate input
  - Check for OpenAI API key
  - Call OpenAI GPT if available
  - Fall back to mock evaluation
  - Validate scores (0-100%)
↓
Output: {accuracy, clarity, completeness, confidence}
```

**API Integration**:
- **Model**: GPT-3.5-turbo or GPT-4
- **Prompt Engineering**: Structured JSON evaluation
- **Error Handling**: Graceful fallback to mock scoring
- **Timeout**: 30 seconds

#### store_result.php (Database Storage)
```
Input: {userId, question, answer, scores}
↓
Process:
  - Validate all inputs
  - Prepare SQL statement
  - Bind parameters (prevent SQL injection)
  - Execute insert
  - Return result ID
↓
Output: {success, resultId}
```

**Database Operations**:
- Type-safe parameter binding
- Error catching and reporting
- Insert confirmation

#### get_results.php (Results Retrieval)
```
Input: user_id
↓
Process:
  - Fetch all results for user
  - Order by created_at DESC
  - Convert to JSON
↓
Output: Array of results with scores
```

---

### 3. Database Schema

```
Database: ai_mock_interview

┌─────────────────────────────────┐
│ users                           │
├─────────────────────────────────┤
│ id (INT) PK                    │
│ username (VARCHAR 255) UNIQUE  │
│ created_at (TIMESTAMP)         │
└─────────────────────────────────┘
         ↑
         │ (FK)
         │
┌─────────────────────────────────┐
│ interview_results               │
├─────────────────────────────────┤
│ id (INT) PK                    │
│ user_id (INT) FK               │
│ question (VARCHAR 500)         │
│ answer_text (LONGTEXT)         │
│ accuracy (DECIMAL 5,2)         │
│ clarity (DECIMAL 5,2)          │
│ completeness (DECIMAL 5,2)     │
│ confidence (DECIMAL 5,2)       │
│ created_at (TIMESTAMP)         │
├─────────────────────────────────┤
│ INDEX: idx_user_created        │
└─────────────────────────────────┘
```

---

## Interview Flow Diagram

```
User opens index.html
         ↓
   [Landing Page]
         ↓
User clicks "Start Interview"
         ↓
   interview.html loads
         ↓
Frontend: initializeSpeechAPI()
         ↓
Frontend: startInterview()
         ↓
Display Question 1
         ↓
AI speaks question via Text-to-Speech
         ↓
Frontend: User either:
  ├─ Voice Mode: Microphone listening
  └─ Text Mode: Typing answer
         ↓
Frontend: Convert speech to text (if voice)
         ↓
         [User clicks Submit Answer]
         ↓
Frontend: showLoadingSpinner()
         ↓
Frontend: evaluateAnswer()
         ↓
Backend: POST /api_evaluate_answer.php
         │
         ├─ Has OpenAI key?
         │  ├─ Yes: callOpenAIAPI()
         │  │   └─ Return structured JSON
         │  └─ No: mockEvaluation()
         │      └─ Return realistic scores
         ↓
Backend: PHP returns scores
         ↓
Frontend: storeResult()
         ↓
Backend: POST /store_result.php
         └─ INSERT into database
         ↓
Frontend: displayResults()
         ↓
   [Show Evaluation]
   ┌─────────────────┐
   │ Accuracy: 85%   │
   │ Clarity: 78%    │
   │ Complete: 92%   │
   │ Confidence: 88% │
   └─────────────────┘
         ↓
User clicks "Next Question"
         ↓
Is this the last question?
         ├─ No: Repeat from "Display Question"
         └─ Yes:
            ↓
         [Calculate Averages]
         ↓
         [Show Summary & History]
         ↓
User can:
  ├─ "Start New Interview" → Reset & restart
  └─ "Back to Home" → Return to index.html
```

---

## Data Flow

### Submitting an Answer

```
┌──────────────────────────────────────────────────────┐
│ 1. USER INTERACTION                                 │
├──────────────────────────────────────────────────────┤
│ Browser Web Speech API recognizes voice             │
│ OR User types answer in textarea                    │
│ Answer stored in: state.currentAnswer               │
└──────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────┐
│ 2. FRONTEND PROCESSING                              │
├──────────────────────────────────────────────────────┤
│ Get question from CONFIG.questions[index]           │
│ Get answer from state.currentAnswer                 │
│ Show loading spinner                                │
└──────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────┐
│ 3. API CALL #1: EVALUATE ANSWER                     │
├──────────────────────────────────────────────────────┤
│ POST /api_evaluate_answer.php                       │
│                                                     │
│ Request Body:                                       │
│ {                                                   │
│   "question": "Tell me about yourself",            │
│   "answer": "I am a software engineer with..."     │
│ }                                                   │
│                                                     │
│ Response:                                           │
│ {                                                   │
│   "success": true,                                  │
│   "evaluation": {                                   │
│     "accuracy": 85.5,                               │
│     "clarity": 78.0,                                │
│     "completeness": 92.0,                           │
│     "confidence": 88.5                              │
│   }                                                 │
│ }                                                   │
└──────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────┐
│ 4. API CALL #2: STORE RESULT                        │
├──────────────────────────────────────────────────────┤
│ POST /store_result.php                              │
│                                                     │
│ Request Body:                                       │
│ {                                                   │
│   "userId": 1,                                      │
│   "question": "Tell me about yourself",            │
│   "answer": "I am a software engineer with...",    │
│   "scores": {                                       │
│     "accuracy": 85.5,                               │
│     "clarity": 78.0,                                │
│     "completeness": 92.0,                           │
│     "confidence": 88.5                              │
│   }                                                 │
│ }                                                   │
│                                                     │
│ Database Operation:                                 │
│ INSERT INTO interview_results                       │
│ VALUES (...all fields...)                           │
│                                                     │
│ Response:                                           │
│ {                                                   │
│   "success": true,                                  │
│   "resultId": 42                                    │
│ }                                                   │
└──────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────┐
│ 5. DISPLAY RESULTS                                  │
├──────────────────────────────────────────────────────┤
│ Hide interview section                              │
│ Show results section                                │
│ Update table with scores                            │
│ Display progress bars                               │
│ Show answer review                                  │
└──────────────────────────────────────────────────────┘
```

---

## API Specifications

### Endpoint: api_evaluate_answer.php

**Method**: POST
**Content-Type**: application/json

**Request**:
```json
{
    "question": "string (required)",
    "answer": "string (required, min 1 char)"
}
```

**Success Response (200)**: 
```json
{
    "success": true,
    "evaluation": {
        "accuracy": "number (0-100)",
        "clarity": "number (0-100)",
        "completeness": "number (0-100)",
        "confidence": "number (0-100)"
    }
}
```

**Error Response (400)**:
```json
{
    "success": false,
    "error": "string"
}
```

---

### Endpoint: store_result.php

**Method**: POST
**Content-Type**: application/json

**Request**:
```json
{
    "userId": "integer (required)",
    "question": "string (required)",
    "answer": "string (required)",
    "scores": {
        "accuracy": "number",
        "clarity": "number",
        "completeness": "number",
        "confidence": "number"
    }
}
```

**Success Response (200)**:
```json
{
    "success": true,
    "message": "Result stored successfully",
    "resultId": "integer"
}
```

---

### Endpoint: get_results.php

**Method**: GET

**Query Parameters**:
```
?user_id=integer (required)
```

**Success Response (200)**:
```json
{
    "success": true,
    "count": "integer",
    "results": [
        {
            "id": "integer",
            "question": "string",
            "answer_text": "string",
            "accuracy": "number",
            "clarity": "number",
            "completeness": "number",
            "confidence": "number",
            "created_at": "datetime"
        }
    ]
}
```

---

## Security Implementation

### Input Validation
- All POST parameters validated before use
- Size limits enforced
- Special characters sanitized

### SQL Injection Prevention
- Prepared statements (MySQLi)
- Parameter binding
- Type-safe operations

### Error Handling
- No sensitive information in error messages
- Logging for debugging
- User-friendly error display

### CORS Headers
- Access-Control-Allow-Origin set appropriately
- Methods restricted to needed operations
- Headers validated

---

## Performance Optimizations

### Frontend
- Vanilla JavaScript (no framework overhead)
- Event delegation for efficiency
- Minimal DOM manipulation
- CSS animations (GPU accelerated)
- Lazy loading of components

### Backend
- Prepared statements (faster execution)
- Database indexes on frequently queried columns
- Connection pooling ready
- Response compression ready

### Database
- Indexed queries (idx_user_created)
- Proper data types (DECIMAL for scores, not FLOAT)
- Foreign key relationships
- UTF-8 charset for international support

---

## Accessibility Features

- ARIA labels on interactive elements
- Keyboard navigation support
- High contrast color scheme
- Semantic HTML5 elements
- Screen reader compatible

---

## Browser Compatibility

```
Chrome 90+    : ✅ Full support
Edge 90+      : ✅ Full support
Safari 14+    : ✅ Full support
Firefox 88+   : ⚠️ Partial (Speech API limited)
Opera 76+     : ✅ Full support
```

---

## Error Handling Strategy

```
User Action
    ↓
Try-Catch Block
    ├─ Success
    │  └─ Process & Display
    └─ Error
       ├─ Log to console
       ├─ Show user-friendly message
       └─ Fallback behavior
```

---

## Extension Points

### Adding Custom Questions
```javascript
CONFIG.questions = [
    "Custom question 1?",
    "Custom question 2?",
    // ...
];
```

### Custom Scoring Logic
```php
// In api_evaluate_answer.php
function customEvaluation($question, $answer) {
    // Custom logic here
    return $scores;
}
```

### User Authentication
```php
// Add to db_config.php
$_SESSION['user_id'] = validateUser();
```

### Multiple Interview Sets
```javascript
CONFIG = {
    sets: {
        'technical': [...],
        'hr': [...],
        'behavioral': [...]
    }
};
```

---

## Deployment Considerations

### Production Checklist

- [ ] Use HTTPS for data encryption
- [ ] Set secure database password
- [ ] Implement user authentication
- [ ] Add rate limiting on API endpoints
- [ ] Enable CORS restrictions
- [ ] Set up logging & monitoring
- [ ] Implement caching strategy
- [ ] Use environment variables for secrets
- [ ] Set up automated backups
- [ ] Enable database query logging

---

## Future Architecture Improvements

1. **Microservices**: Separate LLM service
2. **Caching**: Redis for session management
3. **Queue**: Background jobs for heavy processing
4. **Real-time**: WebSocket for live feedback
5. **Analytics**: User behavior tracking
6. **ML Pipeline**: Custom evaluation model

---

**Document Version**: 1.0
**Last Updated**: February 7, 2026
