# AI Mock Interview System

A complete web application for conducting AI-powered mock interviews with real-time voice interaction, intelligent answer evaluation, and comprehensive performance tracking.

## Features

✅ **Voice-First Interaction**: Default voice mode using Web Speech API for natural interview experience
✅ **AI Answer Evaluation**: LLM-based scoring for accuracy, clarity, completeness, and confidence
✅ **Text Mode**: Optional fallback for users without microphone access
✅ **Database Storage**: All interview results stored in MySQL with timestamps
✅ **Results Dashboard**: Detailed scoring table and performance metrics
✅ **Interview History**: Track and compare multiple interview sessions
✅ **Responsive Design**: Works seamlessly on desktop and mobile devices
✅ **Clean Code**: Well-documented, production-ready code

---

## Tech Stack

| Component | Technology |
|-----------|-----------|
| **Frontend** | HTML5, CSS3, JavaScript (ES6+) |
| **Backend** | PHP 8.0+ |
| **Database** | MySQL 5.7+ |
| **Server** | Apache (via XAMPP) |
| **APIs** | Web Speech API, OpenAI GPT API (optional) |

---

## Installation & Setup

### Prerequisites

- **XAMPP** installed on your system
  - Download from: https://www.apachefriends.org/
  - Includes: Apache, MySQL, PHP
- **Web browser** with Speech API support (Chrome, Edge, Safari recommended)
- **Microphone** for voice interaction

### Step 1: Start XAMPP Services

1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Both should show green status indicators

### Step 2: Locate Project Directory

```
"C:\xampp\htdocs\" (Windows)
"/Applications/XAMPP/htdocs/" (macOS)
"/opt/lampp/htdocs/" (Linux)
```

### Step 3: Copy Project Files

Copy all project files to:
```
C:\xampp\htdocs\ai-mock-interview\
```

File structure should be:
```
ai-mock-interview/
├── index.html                (Landing page)
├── interview.html            (Interview page)
├── style.css                 (Styling)
├── script.js                 (Frontend logic)
├── db_config.php             (Database config)
├── api_evaluate_answer.php   (LLM evaluation)
├── store_result.php          (Store results)
├── get_results.php           (Fetch results)
├── database.sql              (Database schema)
└── README.md                 (This file)
```

### Step 4: Create Database

1. Open **phpMyAdmin**:
   - Go to: http://localhost/phpmyadmin
   - Default username: `root`
   - Default password: (empty)

2. Create database:
   - Click **New** → Enter name: `ai_mock_interview` → Click **Create**

3. Import database schema:
   - Select `ai_mock_interview` database
   - Click **Import** tab
   - Choose `database.sql` file
   - Click **Go**

Alternatively, run SQL directly:
```sql
-- Copy and paste the entire database.sql content into the SQL tab
```

### Step 5: Verify Database Connection

Edit `db_config.php` if needed (usually default settings work):
```php
define('DB_SERVER', 'localhost');   // MySQL server
define('DB_USER', 'root');          // MySQL username
define('DB_PASS', '');              // MySQL password
define('DB_NAME', 'ai_mock_interview'); // Database name
```

### Step 6: Launch Application

1. Open browser and navigate to:
   ```
   http://localhost/ai-mock-interview/
   ```

2. Click **"Start Mock Interview"** button

3. Allow microphone access when prompted

---

## Usage Guide

### Landing Page
- Displays introduction and features
- Click "Start Mock Interview" to begin

### Interview Page

#### Voice Mode (Default)
1. **Listen**: AI reads question aloud
2. **Speak**: Click "Start Listening" and answer using microphone
3. **Review**: Your answer appears in text box
4. **Submit**: Click "Submit Answer"

#### Text Mode (Optional)
1. Click **"Text Mode"** button
2. Type your answer in textarea
3. Click **"Submit Answer"**

### Results Display
- **Accuracy**: Factual correctness of answer (0-100%)
- **Clarity**: How well-articulated the answer is (0-100%)
- **Completeness**: How comprehensive the answer is (0-100%)
- **Confidence**: How assertive/confident the answer sounds (0-100%)

### Interview Flow
```
Start Interview
    ↓
AI Asks Question (Voice)
    ↓
User Answers (Voice/Text)
    ↓
Submit Answer
    ↓
LLM Evaluates Answer
    ↓
Store Result in Database
    ↓
Display Results Table
    ↓
[Next Question] or [End Interview]
    ↓
Final Summary & Statistics
```

---

## LLM API Integration (Optional)

### Using OpenAI GPT API

To enable real LLM evaluation instead of mock scoring:

1. **Get API Key**:
   - Visit: https://platform.openai.com/api-keys
   - Create new secret key
   - Copy the key

2. **Set Environment Variable** (Windows PowerShell):
   ```powershell
   [System.Environment]::SetEnvironmentVariable('OPENAI_API_KEY', 'your-key-here', 'User')
   ```

3. **Restart XAMPP** for changes to take effect

4. **Configure Model** (optional, in `api_evaluate_answer.php`):
   ```php
   'model' => 'gpt-4',  // or 'gpt-3.5-turbo'
   ```

**Note**: Without API key, the system uses intelligent mock evaluation.

---

## Database Schema

### Users Table
```sql
id (INT) - Primary Key
username (VARCHAR) - User identifier
created_at (TIMESTAMP) - Registration time
```

### Interview Results Table
```sql
id (INT) - Primary Key
user_id (INT) - Foreign Key to users
question (VARCHAR) - Interview question
answer_text (LONGTEXT) - User's answer
accuracy (DECIMAL) - Accuracy score (0-100)
clarity (DECIMAL) - Clarity score (0-100)
completeness (DECIMAL) - Completeness score (0-100)
confidence (DECIMAL) - Confidence score (0-100)
created_at (TIMESTAMP) - When answer was submitted
```

---

## API Endpoints

### 1. Evaluate Answer
**Endpoint**: `POST /api_evaluate_answer.php`

**Request**:
```json
{
    "question": "Tell me about your background",
    "answer": "I have 5 years of experience in..."
}
```

**Response**:
```json
{
    "success": true,
    "evaluation": {
        "accuracy": 85.5,
        "clarity": 78.0,
        "completeness": 92.0,
        "confidence": 88.5
    }
}
```

### 2. Store Result
**Endpoint**: `POST /store_result.php`

**Request**:
```json
{
    "userId": 1,
    "question": "Tell me about your background",
    "answer": "I have 5 years of experience...",
    "scores": {
        "accuracy": 85.5,
        "clarity": 78.0,
        "completeness": 92.0,
        "confidence": 88.5
    }
}
```

**Response**:
```json
{
    "success": true,
    "message": "Result stored successfully",
    "resultId": 42
}
```

### 3. Get Results
**Endpoint**: `GET /get_results.php?user_id=1`

**Response**:
```json
{
    "success": true,
    "count": 2,
    "results": [
        {
            "id": 42,
            "question": "Tell me about your background",
            "answer_text": "I have 5 years of experience...",
            "accuracy": 85.5,
            "clarity": 78.0,
            "completeness": 92.0,
            "confidence": 88.5,
            "created_at": "2026-02-07 10:30:45"
        }
    ]
}
```

---

## Browser Compatibility

| Browser | Web Speech API | Text-to-Speech | Status |
|---------|---|---|---|
| Chrome | ✅ | ✅ | Fully Supported |
| Edge | ✅ | ✅ | Fully Supported |
| Safari | ✅ | ✅ | Fully Supported |
| Firefox | ⚠️ | ✅ | Partial Support |
| Opera | ✅ | ✅ | Fully Supported |

**Recommended**: Use Chrome or Edge for best experience

---

## Troubleshooting

### "Microphone not working"
- Check browser microphone permissions
- Allow access when prompted
- Ensure microphone is connected
- Try another browser

### "Speech Recognition not supported"
- Uses Chrome/Edge/Safari instead of Firefox
- Speech mode automatically disables on unsupported browsers
- Text mode remains available as fallback

### "Database connection failed"
- Verify MySQL is running in XAMPP
- Check `db_config.php` credentials
- Ensure `ai_mock_interview` database exists
- Check database.sql was imported successfully

### "API evaluation not working"
- System falls back to intelligent mock evaluation
- To use real LLM: Set OpenAI API key in environment
- Check API key is valid and has usage quota

### "Questions not appearing"
- Refresh the page
- Clear browser cache (Ctrl+Shift+Del)
- Check browser console for errors (F12)

---

## Configuration

### Change Interview Questions

Edit `script.js` (line ~10):
```javascript
const CONFIG = {
    userId: 1,
    questions: [
        "Your question 1?",
        "Your question 2?",
        "Your question 3?",
        // Add more...
    ]
};
```

### Change User ID

For multi-user support, modify in `script.js`:
```javascript
const CONFIG = {
    userId: getUserIdFromSession(), // Implement per your auth
    // ...
};
```

### Adjust Speech Recognition Language

In `script.js` (line ~70):
```javascript
state.recognition.language = 'es-ES'; // Spanish
state.recognition.language = 'fr-FR'; // French
// Full list: https://www.w3.org/TR/speech-api/#lang-param
```

---

## Security Notes

⚠️ **For Production Deployment**:

1. **Secure Database Credentials**
   - Use environment variables for sensitive data
   - Never commit credentials to version control

2. **Input Validation**
   - Already implemented in PHP backend
   - Sanitize all user inputs

3. **API Security**
   - Implement rate limiting
   - Add CORS restrictions
   - Use HTTPS for encrypted transmission

4. **Authentication**
   - Currently uses guest user (ID: 1)
   - Implement user authentication for multi-user support

---

## Performance Optimization

- Frontend uses vanilla JavaScript (no dependencies)
- CSS is optimized and minified-ready
- Database queries are indexed for fast retrieval
- Lazy loading for better UX
- Caching strategies implemented

---

## File Descriptions

| File | Purpose |
|------|---------|
| `index.html` | Landing page with introduction |
| `interview.html` | Main interview interface |
| `style.css` | Global styling (6KB) |
| `script.js` | Interview logic & Web Speech API |
| `db_config.php` | Database connection configuration |
| `api_evaluate_answer.php` | LLM evaluation endpoint |
| `store_result.php` | Save results to database |
| `get_results.php` | Retrieve results from database |
| `database.sql` | MySQL schema & initial data |

---

## Future Enhancements

- [ ] User authentication & profiles
- [ ] Custom interview question sets
- [ ] Interview difficulty levels
- [ ] Detailed feedback on answers
- [ ] Real-time transcription display
- [ ] Export results as PDF
- [ ] Compare performance across sessions
- [ ] Mobile app version
- [ ] Multiple language support
- [ ] Video recording of interviews

---

## Support & Issues

### Getting Help
1. Check browser console (F12) for error messages
2. Verify all files are in correct directory
3. Ensure database is properly set up
4. Check XAMPP services are running

### Report Issues
- Include browser type and version
- Provide error messages from console
- Describe steps to reproduce
- Include environment details

---

## License

This project is provided as-is for educational and professional use.

---

## Author

Created as a comprehensive AI-powered interview preparation system.

**Version**: 1.0
**Last Updated**: February 7, 2026

---

## Quick Start Checklist

- [ ] XAMPP installed and services running
- [ ] Files copied to `C:\xampp\htdocs\ai-mock-interview\`
- [ ] `ai_mock_interview` database created
- [ ] `database.sql` imported successfully
- [ ] `db_config.php` credentials verified
- [ ] php MyAdmin shows tables created
- [ ] Browser supports Web Speech API
- [ ] Microphone connected and permissions granted
- [ ] Application accessible at `http://localhost/ai-mock-interview/`
- [ ] "Start Mock Interview" button works

---

**Ready to start?** Open http://localhost/ai-mock-interview/ in your browser! 🎤🤖
