<?php
/**
 * helper.php - Encrypt & Decrypt functions using OpenSSL
 * 
 * We use these functions to encrypt the order ID before putting it in a QR code URL,
 * and to decrypt it when someone scans the QR code (check.php).
 * 
 * WHY ENCRYPT?
 *   If the URL was check.php?id=5, anyone could guess other IDs (6, 7, 8...).
 *   By encrypting, the URL becomes something like check.php?id=aBcDeFgHiJ...
 *   which is impossible to guess.
 * 
 * HOW IT WORKS (simplified):
 *   1. We pick an encryption method (AES-256-CBC) — a strong, industry-standard algorithm
 *   2. We use a SECRET KEY that only the server knows
 *   3. We generate a random IV (Initialization Vector) to make each encryption unique
 *   4. The IV is prepended to the encrypted data so we can use it for decryption later
 * 
 * IMPORTANT: Keep your SECRET_KEY private! If someone gets it, they can forge tickets.
 */

// ============================================================
// CONFIGURATION - Change this key in production!
// ============================================================

// The secret key used for encryption/decryption
// In a real application, store this in an environment variable or config file
define('SECRET_KEY', 'my-super-secret-key-change-me-2024');

// The encryption method - AES-256-CBC is secure and widely supported
define('ENCRYPT_METHOD', 'aes-256-cbc');


/**
 * Encrypt a string (e.g., an order ID) so it can be safely used in a URL.
 *
 * Steps:
 *   1. Hash the secret key to get a 256-bit (32 byte) key
 *   2. Generate a random IV (Initialization Vector)
 *   3. Encrypt the data using openssl_encrypt
 *   4. Combine IV + encrypted data and encode it for use in a URL
 *
 * @param  string $data  The plain text to encrypt (e.g., "42")
 * @return string        The encrypted string, safe to use in a URL
 */
function encryptId(string $data): string
{
    // Step 1: Create a proper 32-byte key from our secret using SHA-256 hash
    // This ensures the key is always the right length for AES-256
    $key = hash('sha256', SECRET_KEY, true); // 'true' = raw binary output

    // Step 2: Generate a random IV (Initialization Vector)
    // The IV makes sure that encrypting the same value twice gives different results
    $ivLength = openssl_cipher_iv_length(ENCRYPT_METHOD); // For AES-256-CBC this is 16 bytes
    $iv = openssl_random_pseudo_bytes($ivLength);          // Generate random bytes

    // Step 3: Encrypt the data
    // openssl_encrypt returns a base64-encoded string by default
    $encrypted = openssl_encrypt($data, ENCRYPT_METHOD, $key, 0, $iv);

    // Step 4: Combine IV + encrypted data
    // We need the IV later for decryption, so we prepend it
    // Then we base64-encode everything and make it URL-safe
    $combined = base64_encode($iv . base64_decode($encrypted));

    // Make the string safe for URLs:
    //   +  becomes  -
    //   /  becomes  _
    //   =  (padding) is removed
    $urlSafe = rtrim(strtr($combined, '+/', '-_'), '=');

    return $urlSafe;
}


/**
 * Decrypt an encrypted string back to the original value (e.g., the order ID).
 *
 * This reverses everything that encryptId() did:
 *   1. Undo the URL-safe encoding
 *   2. Split the IV from the encrypted data
 *   3. Decrypt using the same key and IV
 *
 * @param  string      $token  The encrypted string from the URL
 * @return string|false        The original value, or false if decryption fails
 */
function decryptId(string $token): string|false
{
    // Step 1: Reverse the URL-safe encoding
    // Put back the +, / and = characters that we replaced in encryptId()
    $base64 = strtr($token, '-_', '+/');

    // Add back the base64 padding (= signs) if needed
    $padding = 4 - (strlen($base64) % 4);
    if ($padding < 4) {
        $base64 .= str_repeat('=', $padding);
    }

    // Decode from base64 back to binary
    $combined = base64_decode($base64, true);

    // If base64_decode fails, the token is invalid
    if ($combined === false) {
        return false;
    }

    // Step 2: Create the same key we used for encryption
    $key = hash('sha256', SECRET_KEY, true);

    // Step 3: Split the IV from the encrypted data
    // The IV is always the first 16 bytes (for AES-256-CBC)
    $ivLength = openssl_cipher_iv_length(ENCRYPT_METHOD);
    $iv = substr($combined, 0, $ivLength);               // First 16 bytes = IV
    $encryptedData = substr($combined, $ivLength);        // The rest = encrypted data

    // Step 4: Decrypt the data
    // We encode the encrypted part back to base64 because openssl_decrypt expects that
    $decrypted = openssl_decrypt(
        base64_encode($encryptedData), // encrypted data (base64)
        ENCRYPT_METHOD,                 // same method as encryption
        $key,                           // same key as encryption
        0,                              // default options (base64 input)
        $iv                             // same IV that was used for encryption
    );

    return $decrypted;
}
