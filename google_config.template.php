<?php
/**
 * Google OAuth Configuration Template
 *
 * 1) Copy this file to: google_config.php
 * 2) Fill in your real Google OAuth credentials
 * 3) Keep google_config.php out of git (contains secrets)
 */

// OAuth Client ID from Google Cloud Console
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID');

// OAuth Client Secret from Google Cloud Console
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET');

// Must exactly match the Authorized redirect URI in Google Console
// Example: http://localhost/tickets/google-callback.php
define('GOOGLE_REDIRECT_URI', 'http://localhost/tickets/google-callback.php');

?>