# Google Gemini API Setup Guide (FREE)

## Overview
This guide explains how to get a **FREE** Google Gemini API key and configure it for the AI Mock Interview System.

**Cost: ZERO** ✓ (No credit card required)

---

## Step 1: Get Free Gemini API Key

### Option A: Web Browser (Easiest)

1. **Open Browser** and go to:
   ```
   https://ai.google.dev/
   ```

2. **Sign in** with your Google Account
   - If you don't have one, create a free Google account

3. **Click "Get API Key"** button
   - It's the prominent button on the page

4. **Create API Key in New Project**
   - Click "Create API key in new project"
   - Google will generate a free API key instantly
   - NO payment information needed

5. **Copy Your API Key**
   - You'll see something like:
     ```
     AIzaSyD...2xY5ZwQ (example)
     ```
   - Copy the entire key

---

## Step 2: Configure the API Key

### Method 1: Environment Variable (Recommended - Secure)

**Windows (PowerShell):**
```powershell
[System.Environment]::SetEnvironmentVariable('GEMINI_API_KEY', 'YOUR_API_KEY_HERE', 'User')
```

Then **restart XAMPP** for changes to take effect.

**Windows (CMD):**
```cmd
setx GEMINI_API_KEY YOUR_API_KEY_HERE
```

### Method 2: Direct Configuration (Development Only)

Edit `config.php`:

```php
// Line ~60 in config.php
$GEMINI_API_KEY = 'YOUR_API_KEY_HERE';  // Paste your key here
```

⚠️ **WARNING**: Never commit API keys to Git or version control!

---

## Step 3: Test the Configuration

### Via Browser Console

1. Open the interview page in your browser
2. Press **F12** to open Developer Tools
3. Go to **Console** tab
4. Type and run:
   ```javascript
   console.log('Testing Gemini API...');
   ```

### Via Terminal

```powershell
cd C:\xampp\htdocs\ai-mock-interview\
php -r "require 'config.php'; require 'llm_service.php'; var_dump(LLMService::getStatus());"
```

Expected output:
```
array(4) {
  ["configured"]=>
  bool(true)
  ["api_key_set"]=>
  bool(true)
  ...
}
```

---

## Step 4: Test Full Interview Flow

1. Open: `http://localhost/ai-mock-interview/`
2. Click "Start Mock Interview"
3. Answer a question using voice or text
4. Click "Submit Answer"
5. **Check results table** for evaluation scores

If scores appear (instead of falling back to 70,70,70,70), the Gemini API is **working!** ✓

---

## Free Tier Limits

| Metric | Limit |
|--------|-------|
| Requests per minute | 60 |
| Requests per day | 1,500 |
| Cost | FREE |
| Credit card required | NO |
| Time limit | Forever |

---

## Troubleshooting

### "API not configured" or "Using fallback scores"

**Check 1:** API key is set
```powershell
$env:GEMINI_API_KEY
```
If empty, follow Step 2 above.

**Check 2:** API key is valid
- Go to: https://ai.google.dev/
- Check if your key is still active
- Regenerate if needed

**Check 3:** Browser console for errors
- Press F12
- Go to Console tab
- Look for error messages

### "Network error" or "API call failed"

**Possible causes:**
1. Internet connection down
2. API rate limit exceeded (1,500 requests/day)
3. API key revoked or deleted
4. Google account restrictions

**Solution:**
- Check internet connection
- Wait a few minutes and retry
- Regenerate API key in Google AI Studio
- Verify your Google account is in good standing

### Fallback Scores (70, 70, 70, 70)

This is **by design** and means:
- API is not configured, OR
- API call failed, OR
- Response was invalid

System gracefully falls back so interview never breaks. ✓

---

## FAQ

### Q: Do I need a credit card?
**A:** No. Google Gemini API free tier requires no payment information.

### Q: Is there a monthly cost?
**A:** No. Free tier is completely free forever.

### Q: How many interviews can I conduct?
**A:** Up to 1,500 per day (each answer = 1 evaluation = 1 request).

### Q: Can I use this in production?
**A:** Yes, for educational and small-scale deployments. For high-traffic sites, upgrade to paid tier.

### Q: What if I reach 1,500 requests/day?
**A:** Fallback scores (70, 70, 70, 70) will be used, and interview continues normally.

### Q: Can I use a different LLM API?
**A:** Yes, modify `llm_service.php` to use:
   - OpenAI (requires credit card)
   - Anthropic Claude
   - HuggingFace
   - Any REST-based LLM API

---

## Code Flow

```
User submits answer
    ↓
Frontend sends POST to api_evaluate_answer.php
    ↓
PHP requires llm_service.php
    ↓
LLMService::evaluateAnswer() called
    ↓
Check if Gemini API is configured
    ├─ If NOT: Return fallback scores (70, 70, 70, 70)
    └─ If YES:
       ├─ Build evaluation prompt
       ├─ Call Gemini API with cURL
       ├─ Parse JSON response
       ├─ Validate scores (0-100)
       └─ Return scores to frontend
    ↓
Frontend displays results table
    ↓
Scores stored in database
```

---

## Advanced: Debug Mode

To see detailed logs from the LLM service:

Edit `config.php`:
```php
define('DEBUG_MODE', true);  // Enable debug logging
```

Then check your PHP error log:
```
C:\xampp\apache\logs\error.log
```

Or with PowerShell:
```powershell
Get-Content C:\xampp\apache\logs\error.log -Tail 20
```

---

## Support

If Gemini API isn't working:

1. **Verify setup** with diagnostic function:
   ```php
   php llm_service.php
   ```

2. **Check config.php** for active API key

3. **Review PHP error log** for detailed error messages

4. **Regenerate API key** in https://ai.google.dev/

---

**Setup Complete!** 🎉

Your AI Mock Interview System now has real LLM-powered evaluation using **free Google Gemini API**. No credit card needed, no limits worries for small-to-medium usage.

Interview answers will be evaluated intelligently by Google's Gemini model.
