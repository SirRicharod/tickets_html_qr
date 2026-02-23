<?php
/**
 * google-login.php
 *
 * This file starts the Google OAuth flow.
 * It creates a Google client, defines scopes, and redirects the user to Google.
 */

require 'vendor/autoload.php';

// Load your local Google OAuth credentials.
if (!file_exists(__DIR__ . '/google_config.php')) {
    die('Missing google_config.php. Copy google_config.template.php to google_config.php first.');
}
require 'google_config.php';

/**
 * Resolve redirect URI for Google OAuth.
 *
 * Priority:
 * 1) GOOGLE_REDIRECT_URI from config (if set and not empty)
 * 2) Auto-detect from current request host (root callback path)
 */
function resolveGoogleRedirectUri(): string
{
    if (defined('GOOGLE_REDIRECT_URI') && trim(GOOGLE_REDIRECT_URI) !== '') {
        return trim(GOOGLE_REDIRECT_URI);
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    if (isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $https = strtolower((string)$_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https';
    }

    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

    // Root-level callback URL (no subfolder like /tickets)
    return $scheme . '://' . $host . '/google-callback.php';
}

// Create and configure the Google OAuth client.
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(resolveGoogleRedirectUri());

// Ask only for basic profile + email (simple educational setup).
$client->addScope('email');
$client->addScope('profile');

// Optional but helpful: always let user choose account.
$client->setPrompt('select_account');

// Build the Google authorization URL and redirect.
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
