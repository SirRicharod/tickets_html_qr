<?php

/**
 * Crypto helpers
 *
 * Set APP_SECRET_KEY in your environment for production.
 */

function getSecretKey(): string
{
    $secret = getenv('APP_SECRET_KEY');
    if (!$secret) {
        $secret = 'dev-secret-change-me';
    }

    return hash('sha256', $secret, true);
}
