# Quick Setup Guide for Windows XAMPP Users

## 5-Minute Quick Start

### Step 1: Prepare XAMPP (1 minute)
```
1. Open XAMPP Control Panel
2. Click "Start" for Apache
3. Click "Start" for MySQL
4. Wait until both show green lights
```

### Step 2: Place Files (1 minute)
```
Copy all files to: C:\xampp\htdocs\ai-mock-interview\
```

### Step 3: Setup Database (1 minute)
```
1. Open browser → http://localhost/phpmyadmin
2. Click "New" at top left
3. Enter name: ai_mock_interview
4. Click "Create"
5. Select the new database
6. Click "Import" tab
7. Choose database.sql file
8. Click "Go"
```

### Step 4: Start Using (1 minute)
```
1. Open browser
2. Go to: http://localhost/ai-mock-interview/
3. Click "Start Mock Interview"
4. Allow microphone when prompted
5. Enjoy!
```

---

## Detailed Steps with Screenshots

### XAMPP Setup

**Expected look when running:**
```
Apache: Running [PID: 4532]
MySQL:  Running [PID: 5240]
FileZilla: Not Running
Tomcat: Not Running
```

### File Structure Check

After copying files, you should have:
```
C:\xampp\htdocs\ai-mock-interview\
    ├── index.html              ✓
    ├── interview.html          ✓
    ├── style.css               ✓
    ├── script.js               ✓
    ├── db_config.php           ✓
    ├── api_evaluate_answer.php ✓
    ├── store_result.php        ✓
    ├── get_results.php         ✓
    ├── database.sql            ✓
    ├── README.md               ✓
    └── .env.example            ✓
```

### Database Import Steps

1. **Open phpMyAdmin**
   - URL: http://localhost/phpmyadmin
   - Username: root
   - Password: (leave empty)

2. **Create Database**
   - Look for "New" button in left sidebar
   - Enter "ai_mock_interview"
   - Charset: utf8mb4_unicode_ci
   - Click "Create"

3. **Import SQL File**
   - Select "ai_mock_interview" database
   - Go to "Import" tab
   - Choose "database.sql" file
   - Click "Go"

4. **Verify Tables Created**
   - Should see two tables:
     - `users`
     - `interview_results`

---

## Testing the Application

### Test 1: Landing Page
```
URL: http://localhost/ai-mock-interview/
Expected: Beautiful intro page with "Start Mock Interview" button
```

### Test 2: Interview Page
```
Click "Start Mock Interview"
Expected: Interview interface with question displayed
```

### Test 3: Voice Interaction
```
1. Click "Start Listening"
2. Speak your answer
3. Wait for speech recognition
4. Answer should appear below
```

### Test 4: Answer Submission
```
1. Complete speaking
2. Click "Submit Answer"
3. Wait for evaluation
4. See results table with scores
```

### Test 5: Text Mode (Optional)
```
1. Click "Text Mode" button
2. Type an answer
3. Click "Submit Answer"
4. See results
```

---

## Troubleshooting

### Apache Won't Start
```
Error: Port 80 in use
Solution: 
  - Close Skype, which uses port 80
  - Or change Apache port in XAMPP config
```

### MySQL Won't Start
```
Error: MySQL won't start
Solution:
  - Check MySQL.ini in C:\xampp\mysql\
  - Restart Windows
  - Or reinstall XAMPP
```

### Database Already Exists Error
```
Error: "database ai_mock_interview already exists"
Solution:
  - Go to phpMyAdmin
  - Drop the database
  - Run import again
```

### Microphone Not Working
```
Error: Browser doesn't ask for microphone
Solution:
  - Check browser microphone permissions
  - Chrome: Settings → Privacy → Microphone
  - Allow localhost
  - Refresh page
```

### Speech Recognition Not Supported
```
Error: "Speech Recognition not supported"
Solution:
  - Use Chrome, Edge, or Safari
  - Switch from Firefox
  - Text mode still works as fallback
```

---

## Useful Links

- **XAMPP Download**: https://www.apachefriends.org/
- **phpMyAdmin**: http://localhost/phpmyadmin/
- **MySQL Documentation**: https://dev.mysql.com/doc/
- **Web Speech API**: https://developer.mozilla.org/en-US/docs/Web/API/Web_Speech_API

---

## Common Commands

### Check if MySQL is Running
```cmd
tasklist | find "mysqld"
```

### Check if Apache is Running
```cmd
netstat -ano | findstr :80
```

### View Apache Error Log
```
C:\xampp\apache\logs\error.log
```

### View MySQL Error Log
```
C:\xampp\mysql\data\<hostname>.err
```

---

## Performance Tips

1. **Faster Speech Recognition**
   - Use headphones (reduces echo)
   - Speak clearly and at normal pace
   - Avoid background noise

2. **Better Evaluation**
   - Provide detailed answers (more words = better analysis)
   - Answer questions completely
   - Be clear and articulate

3. **Faster Page Load**
   - Clear browser cache periodically (Ctrl+Shift+Del)
   - Use modern browser (Chrome 90+)
   - Ensure MySQL is running

---

## For Multiple Interviews

### Clear Previous Results
```sql
-- In phpMyAdmin SQL tab
DELETE FROM interview_results WHERE user_id = 1;
```

### See All Results
```sql
SELECT * FROM interview_results ORDER BY created_at DESC;
```

---

## Advanced Configuration

### Change Interview Questions
File: `script.js` (around line 10)
```javascript
const CONFIG = {
    questions: [
        "Your custom question 1?",
        "Your custom question 2?",
        // ...
    ]
};
```

### Use OpenAI API
```
1. Get key from: https://platform.openai.com/api-keys
2. Set environment variable: OPENAI_API_KEY=sk-xxx
3. Restart XAMPP
```

### Change Port (if 80 is busy)
File: `C:\xampp\apache\conf\httpd.conf`
```
Find: Listen 80
Change to: Listen 8080
Access at: http://localhost:8080/ai-mock-interview/
```

---

## Getting Help

### Check Console for Errors
1. Press F12 in browser
2. Go to "Console" tab
3. Look for red error messages
4. Screenshot and share

### Common Error Messages

**"Cannot POST /api_evaluate_answer.php"**
- Solution: File path is wrong, check file location

**"Database connection failed"**
- Solution: MySQL not running, check XAMPP

**"No questions found"**
- Solution: Refresh page, clear cache

---

## Success Checklist

After completing setup, verify:

- [ ] XAMPP Apache is running (green)
- [ ] XAMPP MySQL is running (green)
- [ ] All files copied to correct folder
- [ ] Database created and tables visible
- [ ] Can access http://localhost/ai-mock-interview/
- [ ] Landing page displays correctly
- [ ] "Start Mock Interview" button is clickable
- [ ] Interview page shows question
- [ ] Microphone works (test in browser settings)
- [ ] Can start listening for speech
- [ ] Speech is recognized
- [ ] Answer displays in text box
- [ ] Can submit answer
- [ ] Results table appears
- [ ] Scores display correctly

If all checks pass: ✅ You're ready to use the system!

---

## Support

If you encounter issues:

1. Check browser console (F12)
2. Verify all files are in place
3. Ensure XAMPP services are running
4. Try refreshing the page
5. Clear browser cache (Ctrl+Shift+Del)
6. Try a different browser (Chrome recommended)

---

**Happy interviewing! 🎤🤖**
