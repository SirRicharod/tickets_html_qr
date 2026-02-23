<?php
/**
 * google-callback.php
 *
 * This file receives the Google OAuth callback.
 * Flow:
 * 1) Exchange auth code for access token
 * 2) Read user profile (email, name, google id, picture)
 * 3) Find local user by email
 * 4) If user exists -> sign in
 * 5) If user does not exist -> create user, then sign in
 */

session_start();
require 'vendor/autoload.php';
require 'includes/conn.php';

// Helper to store a one-time message and redirect safely.
function redirectWithMessage(string $message, string $location = 'login.php'): void
{
    $_SESSION['auth_message'] = $message;
    header('Location: ' . $location);
    exit;
}

// Load credentials from local config file.
if (!file_exists(__DIR__ . '/google_config.php')) {
    redirectWithMessage('Missing google_config.php. Ask admin to configure Google login first.');
}
require 'google_config.php';

/**
 * Resolve redirect URI exactly like google-login.php.
 *
 * Important: for OAuth code exchange, redirect URI must be identical
 * to the one used when creating the auth URL.
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

// Create Google client with your app credentials.
$client = new Google_Client();
$client->setClientId(GOOGLE_CLIENT_ID);
$client->setClientSecret(GOOGLE_CLIENT_SECRET);
$client->setRedirectUri(resolveGoogleRedirectUri());

// Google sends an authorization code when user approves access.
if (!isset($_GET['code'])) {
    redirectWithMessage('Google login canceled or failed. Please try again.');
}

// Exchange code for access token.
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
if (isset($token['error'])) {
    redirectWithMessage('Google token error: ' . $token['error']);
}

$client->setAccessToken($token);

// Read user data from Google.
$oauth2 = new Google_Service_Oauth2($client);
$googleUser = $oauth2->userinfo->get();

$googleId = (string)($googleUser->id ?? '');
$email = (string)($googleUser->email ?? '');
$name = (string)($googleUser->name ?? 'Google User');
$picture = (string)($googleUser->picture ?? '');

if ($email === '') {
    redirectWithMessage('Google account has no email. Cannot continue.');
}

// STEP A: Check if this email already exists in local users table.
$stmt = $conn->prepare('SELECT id, name, email, role FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    redirectWithMessage('Database error while preparing user lookup.');
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
$existingUser = $result->fetch_assoc();
$stmt->close();

if ($existingUser) {
    // Existing user: optionally refresh name/profile picture to keep data current.
    $update = $conn->prepare('UPDATE users SET name = ?, profile_pic = ? WHERE id = ?');
    if ($update) {
        $update->bind_param('ssi', $name, $picture, $existingUser['id']);
        $update->execute();
        $update->close();
    }

    // Log the user in.
    $_SESSION['user_id'] = $existingUser['id'];
    $_SESSION['email'] = $existingUser['email'];
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $existingUser['role'] ?? 'client';
} else {
    // New user: create local account with random password hash.
    // Why random password? Your app expects a password column.
    $generatedPasswordHash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
    $defaultRole = 'client';

    $insert = $conn->prepare('INSERT INTO users (name, email, password, role, profile_pic) VALUES (?, ?, ?, ?, ?)');
    if (!$insert) {
        redirectWithMessage('Database error while preparing user creation.');
    }

    $insert->bind_param('sssss', $name, $email, $generatedPasswordHash, $defaultRole, $picture);
    if (!$insert->execute()) {
        $insert->close();
        redirectWithMessage('Could not create local account from Google profile.');
    }

    $newUserId = (int)$conn->insert_id;
    $insert->close();

    // Log in the newly created user.
    $_SESSION['user_id'] = $newUserId;
    $_SESSION['email'] = $email;
    $_SESSION['name'] = $name;
    $_SESSION['role'] = $defaultRole;
}

// Keep the same redirect style as your current login flow.
if (($_SESSION['role'] ?? 'client') === 'admin') {
    header('Location: admin.php');
} else {
    header('Location: order.php');
}
exit;
