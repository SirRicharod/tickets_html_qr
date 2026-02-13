<?php
/**
 * Order Process File
 * 
 * This file handles the complete order workflow:
 * 1. Processes order details from the form
 * 2. Generates a PDF ticket
 * 3. Sends confirmation email with PDF attachment
 * 4. Displays success message
 */

// Include necessary files
include 'includes/header.php';
require 'includes/conn.php';
require 'includes/helper.php';  // Our OpenSSL encrypt/decrypt functions

// Import PHPMailer classes for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Namespaces for our domPDF
use Dompdf\Dompdf;
use Dompdf\Options;

// Namespaces for QR code generation
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;

// Load Composer's autoloader to use installed packages
require 'vendor/autoload.php';

// ===============================================
// STEP 1: Validate Session and Get Order Data
// ===============================================

// Check if user is logged in
if (!isset($_SESSION['email']) || !isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Validate POST data exists
if (!isset($_POST['amount']) || empty($_POST['amount'])) {
    die("Error: No order amount specified");
}

// Get order details from form and session
$amount_tickets = intval($_POST['amount']); // Convert to integer for safety
$price_per_ticket = 45; // Price in EUR
$total_amount = $amount_tickets * $price_per_ticket;

// Get user information from session
$user_email = $_SESSION['email'];
$user_name = $_SESSION['name'];
$user_id = $_SESSION['user_id'];

// Generate unique order number
$order_number = uniqid('ORD-');

// ===============================================
// STEP 2: Store Order in Database (Optional)
// ===============================================

// Prepare SQL statement to prevent SQL injection
$stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, amount_tickets, total_price, order_date) VALUES (?, ?, ?, ?, NOW())");
$stmt->bind_param("isid", $user_id, $order_number, $amount_tickets, $total_amount);

// Execute the statement (commented out if orders table doesn't exist yet)
$stmt->execute();
$order_id = $conn->insert_id;
$stmt->close();

// ===============================================
// STEP 3: Generate PDF Ticket
// ===============================================

// Encrypt the order ID so it cannot be guessed in the URL
// Uses our encryptId() function from helper.php (OpenSSL under the hood)
$token = encryptId((string) $order_id);

// Build the QR code URL: check.php?id=<encrypted_order_id>
$qr_data = "http://localhost:8000/check.php?id=" . $token;
$qrCode = new QrCode(
    data: $qr_data,
    encoding: new Encoding('UTF-8'),
    errorCorrectionLevel: ErrorCorrectionLevel::High,
    size: 300,
    margin: 10,
    foregroundColor: new Color(0, 0, 0),
    backgroundColor: new Color(255, 255, 255)
);
$writer = new PngWriter();
$result = $writer->write($qrCode);

// show the qr code in the browser for testing
// echo '<img src="' . $result->getDataUri() . '" alt="QR Code">';

// convert the qr code to a base64 string to embed it in the PDF
$qr_base64 = $result->getDataUri();



// Create new PDF instance with custom page settings
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans'); // Set default font that supports UTF-8
$dompdf = new Dompdf($options);
// Create HTML content for the PDF ticket
$pdf_html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            text-align: center;
            padding: 50px;
            background: #f9f9f9;
        }
        .ticket {
            border: 2px dashed #333;
            padding: 30px;
            border-radius: 10px;
            background: white;
            display: inline-block;
            width: 500px;
        }
        .event-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .order-info {
            font-size: 18px;
            margin: 10px 0;
        }                   
        .qr-code {
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        <div class="event-name">🎉 Amazing Event 2026 🎉</div>
        <div class="order-info"><strong>Order Number:</strong> ' . htmlspecialchars($order_number) . '</div>
        <div class="order-info"><strong>Name:</strong> ' . htmlspecialchars($user_name) . '</div>
        <div class="order-info"><strong>Tickets:</strong> ' . $amount_tickets . '</div>
        <div class="order-info"><strong>Total:</strong> EUR ' . number_format($total_amount, 2) . '</div>
        <div class="qr-code">
            <img src="' . $qr_base64 . '" alt="QR Code">
        </div>
    </div>
</body>
</html>
'; 
// Load HTML content into domPDF
$dompdf->loadHtml($pdf_html);
// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');
// Render the PDF
$dompdf->render();
// Output the PDF to a string
$pdf_output = $dompdf->output();
// Save the PDF to a file on the server
$pdf_filename = 'ticket_' . $order_number . '.pdf';
$pdf_filepath = 'assets/pdf/' . $pdf_filename;
file_put_contents($pdf_filepath, $pdf_output);


// ===============================================
// STEP 4: Send Email with PDF Attachment
// ===============================================

/**
 * Create and configure email using PHPMailer
 */
$mail = new PHPMailer(true);

try {
    // Server settings
    $mail->isSMTP(); // Use SMTP protocol
    $mail->Host       = 'localhost'; // MailHog SMTP host
    $mail->SMTPAuth   = false; // No SMTP authentication for MailHog
    $mail->Username   = ''; // Not required for MailHog
    $mail->Password   = ''; // Not required for MailHog
    $mail->SMTPSecure = ''; // No encryption for MailHog
    $mail->Port       = 1025; // MailHog SMTP port
    
    // Recipients
    $mail->setFrom('no-reply@example.test', 'Ticket System'); // Sender email
    $mail->addAddress($user_email, $user_name); // Add recipient (customer)
    
    // Attach the PDF file
    //$mail->addAttachment($pdf_filepath, $pdf_filename);
    
    // Email content - HTML format
    $mail->isHTML(true); // Set email format to HTML
    $mail->Subject = 'Your Ticket Order Confirmation - ' . $order_number;
    
    // Create beautiful HTML email body
    $mail->Body = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                max-width: 600px;
                margin: 0 auto;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
                border-radius: 10px 10px 0 0;
            }
            .content {
                background: #f9f9f9;
                padding: 30px;
                border: 1px solid #ddd;
            }
            .order-box {
                background: white;
                padding: 20px;
                border-radius: 5px;
                margin: 20px 0;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            .order-detail {
                margin: 10px 0;
                padding: 10px;
                border-left: 3px solid #667eea;
            }
            .total {
                background: #667eea;
                color: white;
                padding: 15px;
                border-radius: 5px;
                text-align: center;
                font-size: 20px;
                font-weight: bold;
                margin: 20px 0;
            }
            .footer {
                background: #333;
                color: white;
                padding: 20px;
                text-align: center;
                border-radius: 0 0 10px 10px;
                font-size: 12px;
            }
            .button {
                display: inline-block;
                padding: 12px 30px;
                background: #667eea;
                color: white;
                text-decoration: none;
                border-radius: 5px;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>🎟️ Order Confirmation</h1>
            <p>Thank you for your purchase!</p>
        </div>
        
        <div class="content">
            <h2>Hello ' . htmlspecialchars($user_name) . ',</h2>
            
            <p>We are excited to confirm your ticket order! Your tickets are attached to this email as a PDF document.</p>
            
            <div class="order-box">
                <h3>📋 Order Summary</h3>
                
                <div class="order-detail">
                    <strong>Order Number:</strong> ' . htmlspecialchars($order_number) . '
                </div>
                
                <div class="order-detail">
                    <strong>Order Date:</strong> ' . date('F d, Y') . '
                </div>
                
                <div class="order-detail">
                    <strong>Number of Tickets:</strong> ' . $amount_tickets . '
                </div>
                
                <div class="order-detail">
                    <strong>Price per Ticket:</strong> EUR ' . number_format($price_per_ticket, 2) . '
                </div>
                
                <div class="total">
                    TOTAL: EUR ' . number_format($total_amount, 2) . '
                </div>
            </div>
            
            <h3>📎 Your Tickets</h3>
            <p>Your tickets are attached to this email as a PDF file. Please:</p>
            <ul>
                <li>Download and save the PDF for your records</li>
                <li>Print the ticket or show it on your mobile device at the event</li>
                <li>Bring a valid ID along with your ticket</li>
            </ul>
            
            <h3>⚠️ Important Notes</h3>
            <ul>
                <li>Tickets are <strong>non-refundable</strong></li>
                <li>Please arrive 30 minutes before the event starts</li>
                <li>Keep this email for reference</li>
            </ul>
            
            <p>If you have any questions, please don\'t hesitate to contact us.</p>
            
            <p>We look forward to seeing you at the event!</p>
            
            <p><strong>Best regards,</strong><br>The Ticket System Team</p>
        </div>
        
        <div class="footer">
            <p>This is an automated email. Please do not reply directly to this message.</p>
            <p>&copy; ' . date('Y') . ' Ticket System. All rights reserved.</p>
        </div>
    </body>
    </html>
    ';
    
    // Alternative plain text body for email clients that don't support HTML
    $mail->AltBody = 'Thank you for your order, ' . $user_name . '!' . "\n\n"
                   . 'Order Number: ' . $order_number . "\n"
                   . 'Number of Tickets: ' . $amount_tickets . "\n"
                   . 'Total Amount: EUR ' . number_format($total_amount, 2) . "\n\n"
                   . 'Your tickets are attached to this email as a PDF file.' . "\n\n"
                   . 'Best regards,' . "\n"
                   . 'The Ticket System Team';
    
    // Send the email
    $mail->send();
    $email_sent = true;
    $email_message = 'Email sent successfully!';
    
} catch (Exception $e) {
    // If email fails, capture error but continue (user can still download PDF)
    $email_sent = false;
    $email_message = 'Email could not be sent. Error: ' . $mail->ErrorInfo;
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Order Confirmation</title>
    <style>
        .success-box {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .download-btn {
            display: inline-block;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .download-btn:hover {
            background: #0056b3;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col">
            <h1>🎉 Thank You for Your Order!</h1>
            
            <div class="success-box">
                <h3>✅ Order Placed Successfully</h3>
                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order_number); ?></p>
                <p><strong>Number of Tickets:</strong> <?php echo $amount_tickets; ?></p>
                <p><strong>Total Amount:</strong> EUR <?php echo number_format($total_amount, 2); ?></p>
            </div>
            
            <?php if ($email_sent): ?>
                <div class="success-box">
                    <h4>📧 Email Confirmation Sent</h4>
                    <p>A confirmation email with your tickets has been sent to: <strong><?php echo htmlspecialchars($user_email); ?></strong></p>
                    <p>Please check your inbox (and spam folder if you don't see it).</p>
                </div>
            <?php else: ?>
                <div class="warning-box">
                    <h4>⚠️ Email Status</h4>
                    <p><?php echo htmlspecialchars($email_message); ?></p>
                    <p>Don't worry! You can still download your tickets below.</p>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin: 30px 0;">
                <h3>📥 Download Your Tickets</h3>
                <a href="<?php echo htmlspecialchars($pdf_filepath); ?>" class="download-btn" download>
                    ⬇️ Download PDF Ticket
                </a>
                <a href="index.php" class="download-btn" style="background: #28a745;">
                    🏠 Return to Home
                </a>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h4>📋 Next Steps:</h4>
                <ol>
                    <li>Download and save your ticket PDF</li>
                    <li>Print your ticket or keep it on your mobile device</li>
                    <li>Bring your ticket and a valid ID to the event</li>
                    <li>Arrive 30 minutes before the event starts</li>
                </ol>
                
                <p style="color: #dc3545; font-weight: bold;">
                    ⚠️ Important: Tickets are non-refundable
                </p>
            </div>
        </div>
    </div>
</div>

</body>
</html>