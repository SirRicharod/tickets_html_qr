# 📧 EMAIL PROVIDER CONFIGURATIONS (FYI)

This document contains SMTP settings for popular email providers.  
Copy the settings for your provider into `orderprocess.php` (lines 172-179).

---

## 🔵 Gmail (Google Mail)

```php
$mail->Host       = 'smtp.gmail.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@gmail.com';        // Your Gmail address
$mail->Password   = 'your-app-password';           // 16-character App Password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

### How to get App Password:
1. Enable 2-Factor Authentication: https://myaccount.google.com/security
2. Generate App Password: https://myaccount.google.com/apppasswords
3. Select "Mail" and your device
4. Copy the 16-character password (no spaces)

**Note:** Regular Gmail passwords don't work with SMTP!

---

## 🔷 Outlook / Hotmail / Office 365

```php
$mail->Host       = 'smtp.office365.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@outlook.com';      // or @hotmail.com
$mail->Password   = 'your-password';               // Regular password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

**Alternative for Outlook.com:**
```php
$mail->Host       = 'smtp-mail.outlook.com';
$mail->Port       = 587;
```

---

## 🟣 Yahoo Mail

```php
$mail->Host       = 'smtp.mail.yahoo.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@yahoo.com';
$mail->Password   = 'your-app-password';           // App Password required!
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

### How to get App Password:
1. Go to: https://login.yahoo.com/account/security
2. Click "Generate app password"
3. Select "Other App" and name it "Ticket System"
4. Copy the password

---

## 🟠 Zoho Mail

```php
$mail->Host       = 'smtp.zoho.com';
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@zoho.com';
$mail->Password   = 'your-password';               // Regular password
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

**For Zoho EU servers:**
```php
$mail->Host       = 'smtp.zoho.eu';
```

---

## 🟢 SendGrid (Transactional Email Service)

```php
$mail->Host       = 'smtp.sendgrid.net';
$mail->SMTPAuth   = true;
$mail->Username   = 'apikey';                      // Literally "apikey"
$mail->Password   = 'YOUR_SENDGRID_API_KEY';       // Your actual API key
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

Get API key at: https://app.sendgrid.com/settings/api_keys

**Advantages:** High deliverability, 100 emails/day free

---

## 🔴 Mailgun (Transactional Email Service)

```php
$mail->Host       = 'smtp.mailgun.org';
$mail->SMTPAuth   = true;
$mail->Username   = 'postmaster@your-domain.mailgun.org';
$mail->Password   = 'your-smtp-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

Get credentials at: https://app.mailgun.com/app/sending/domains

---

## 🟡 Amazon SES (AWS Simple Email Service)

```php
$mail->Host       = 'email-smtp.us-east-1.amazonaws.com'; // Choose your region
$mail->SMTPAuth   = true;
$mail->Username   = 'YOUR_SMTP_USERNAME';          // From SES console
$mail->Password   = 'YOUR_SMTP_PASSWORD';          // From SES console
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port       = 587;
```

Get credentials at: AWS SES Console → SMTP Settings

**Note:** Verify your email/domain first

---

## 🟤 Custom SMTP Server

```php
$mail->Host       = 'mail.yourdomain.com';         // Your mail server
$mail->SMTPAuth   = true;
$mail->Username   = 'your-email@yourdomain.com';
$mail->Password   = 'your-password';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or ENCRYPTION_SSL
$mail->Port       = 587;                           // or 465 for SSL
```

Check with your hosting provider for:
- SMTP server address
- Port number (usually 587 or 465)
- SSL/TLS requirement

---

## 🔐 Security Ports Explained

| Port | Encryption | Name      | Description                    |
|------|------------|-----------|--------------------------------|
| 25   | None       | SMTP      | Usually blocked by ISPs        |
| 587  | STARTTLS   | Submission| **Recommended** - TLS upgrade  |
| 465  | SSL/TLS    | SMTPS     | Alternative - immediate SSL    |

**Recommendation:** Use port **587** with **STARTTLS** for best compatibility.

---

## 🧪 Testing Your Configuration

After updating the settings, test with:

```bash
php test_email.php your-test-email@example.com
```

Common issues:
- ❌ Wrong username/password → Check credentials
- ❌ Connection timeout → Check firewall/port
- ❌ Authentication failed → Use App Password (Gmail/Yahoo)
- ❌ "Less secure apps" → Enable in account settings or use App Password

---

## 📊 Comparison: Personal vs Transactional Services

### Personal Email (Gmail, Outlook, Yahoo)
✅ Free  
✅ Easy to setup  
❌ Daily sending limits (50-100 emails)  
❌ May end up in spam  
❌ Not designed for bulk sending  

**Good for:** Small sites, testing, personal projects

### Transactional Services (SendGrid, Mailgun, SES, Custom SMTP/COMBELL)
✅ High deliverability  
✅ Better spam score  
✅ Analytics and tracking  
✅ Higher sending limits  
❌ May require payment  
❌ More complex setup  

**Good for:** Production sites, high volume, professional use

---

## 🎯 Recommended Setup

**For Development/Testing:**
→ Use Gmail with App Password

**For Production (low volume < 100 emails/day):**
→ Use custom domain email (looks more professional)

**For Production (high volume or critical):**
→ Use SendGrid or Mailgun (better deliverability)

---

## 📝 Don't Forget!

After updating email settings in `orderprocess.php`:

1. Update lines 172-179 with your provider's settings
2. Update `setFrom()` email address (line 179)
3. Test with `test_email.php`
4. Check spam folder if email doesn't arrive
5. Never commit passwords to Git!

---