# NEXUS Ω cPanel Configuration Guide

## 🚨 CRITICAL: Complete These Steps Before Using Forge & Deploy

The Forge & Deploy feature requires cPanel API access to automatically create subdomains and deploy websites. Follow these steps carefully.

---

## Step 1: Get Your cPanel Credentials

### 1.1 Find Your cPanel Username
- Your cPanel username is usually your domain name without the `.com/.net/etc.`
- Example: If your domain is `puppybeginnersguide.store`, your username might be `puppybeg` or similar
- **How to find it:**
  - Log into your hosting control panel
  - Look for "cPanel" or "Control Panel" login
  - Your username is displayed on the login page or in the URL

### 1.2 Find Your Root Domain
- This is your main domain (e.g., `puppybeginnersguide.store`)
- Make sure it's exactly as registered (no www., no https://)

---

## Step 2: Create cPanel API Token

### 2.1 Log into cPanel
1. Go to your hosting provider's website
2. Click "cPanel" or "Control Panel"
3. Log in with your username and password

### 2.2 Navigate to API Tokens
1. In cPanel, scroll down to the "Security" section
2. Click on "API Tokens" (or "Manage API Tokens")

### 2.3 Create New Token
1. Click "Create" or "Generate Token"
2. Give it a name like "NEXUS_OMEGA_API"
3. **IMPORTANT:** Select these permissions:
   - `SubDomain::addsubdomain` (to create subdomains)
   - `Fileman::upload` (to upload files)
   - `Fileman::mkdir` (to create directories)
4. Click "Create" or "Generate"
5. **CRITICAL:** Copy the token immediately - you won't see it again!

---

## Step 3: Configure nexus_controller.php

### 3.1 Open the File
- Open `nexus_controller.php` in your code editor

### 3.2 Fill in the Configuration
Replace the placeholder values in the CRITICAL CONFIGURATION section:

```php
// --- [START] CRITICAL CONFIGURATION ---
$cpanelUser = "your_cpanel_username_here";      // e.g., "puppybeg"
$apiToken = "your_api_token_here";              // The long token you copied
$rootDomain = "yourdomain.com";                 // e.g., "puppybeginnersguide.store"
// --- [END] CRITICAL CONFIGURATION ---
```

### 3.3 Example Configuration
```php
$cpanelUser = "puppybeg";
$apiToken = "ABC123DEF456GHI789JKL012MNO345PQR678STU901VWX234YZ";
$rootDomain = "puppybeginnersguide.store";
```

---

## Step 4: Test the Configuration

### 4.1 Upload Files to Server
- Upload all your NEXUS Ω files to your web server
- Make sure `nexus_controller.php` is in the same directory as `index.html`

### 4.2 Test Forge & Deploy
1. Open your website
2. Go to the "NEXUS AI" tab
3. Enter your Gemini API key
4. Try the "Forge & Deploy" feature with a test subdomain
5. Check if it creates the subdomain successfully

---

## Troubleshooting

### Common Issues:

#### "cPanel API call failed"
- Check your cPanel username is correct
- Verify the API token is copied exactly (no extra spaces)
- Make sure the token has the required permissions

#### "Subdomain creation failed"
- Ensure your root domain is correct
- Check if you have subdomain creation permissions
- Try a different subdomain name

#### "SSL certificate issues"
- Some hosts require specific SSL settings
- Contact your hosting provider if you get SSL errors

---

## Security Notes

- **Never share your API token** - it gives full access to your hosting account
- **Use HTTPS** when accessing your NEXUS Ω system
- **Regularly rotate API tokens** for security
- **Test on a subdomain first** before using on your main domain

---

## Need Help?

If you encounter issues:
1. Check the browser console for error messages
2. Verify all configuration values are correct
3. Contact your hosting provider for cPanel-specific issues
4. Test with a simple subdomain creation first

---

**⚠️ WARNING:** The Forge & Deploy feature will create live websites on your domain. Test carefully and use responsibly.
