<?php
/**
 * Email Test Script
 * 
 * Use this file to test your email configuration before going live.
 * Run this from command line: php test_email.php mas@mas.com
 * Or access it via browser (then delete it for security!)
 */

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

echo "==============================\n";
echo "  EMAIL CONFIGURATION TEST\n";
echo "==============================\n\n";

// Get email address from command line or hardcode for browser
$test_email = isset($argv[1]) ? $argv[1] : 'your-test-email@example.com';

echo "Testing email to: $test_email\n\n";

$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->SMTPDebug = 2; // Enable verbose debug output
    $mail->isSMTP();
    
    // MailHog SMTP settings
    $mail->Host       = 'localhost';
    $mail->SMTPAuth   = false;
    $mail->Username   = '';
    $mail->Password   = '';
    $mail->SMTPSecure = '';
    $mail->Port       = 1025;
    
    // Recipients
    $mail->setFrom('no-reply@example.test', 'Test Sender');
    $mail->addAddress($test_email);
    
    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Email Test - Ticket System';
    $mail->Body    = '<h1>Email Test Successful!</h1><p>Your email configuration is working correctly.</p>';
    $mail->AltBody = 'Email Test Successful! Your email configuration is working correctly.';
    
    $mail->send();
    
    echo "\n==============================\n";
    echo "✅ SUCCESS! Email has been sent\n";
    echo "==============================\n";
    echo "Check your inbox at: $test_email\n";
    echo "(Don't forget to check spam folder)\n\n";
    
} catch (Exception $e) {
    echo "\n==============================\n";
    echo "❌ ERROR! Email could not be sent\n";
    echo "==============================\n";
    echo "Error message: {$mail->ErrorInfo}\n\n";
    echo "Common issues:\n";
    echo "1. Wrong username/password\n";
    echo "2. Need to use App Password (Gmail)\n";
    echo "3. SMTP port is blocked by firewall\n";
    echo "4. 'Less secure app access' not enabled\n\n";
}

echo "==============================\n";
echo "Test complete.\n";
echo "==============================\n";

?>
