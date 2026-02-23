<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure PHPMailer is installed via Composer
include 'includes/header.php';
require 'includes/conn.php';

// Handle Registration Logic
$message = "";

// Optional one-time message from OAuth callback (stored in session)
if (isset($_SESSION['auth_message'])) {
    $message = $_SESSION['auth_message'];
    unset($_SESSION['auth_message']);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password']; // Get raw password
    $name = $conn->real_escape_string($_POST['name']);

    // Hash the password using password_hash() - Use bcrypt by default
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $profile_pic_path = "";
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_pic"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Check file extension
        if($imageFileType != "jpg" && $imageFileType != "jpeg" && $imageFileType != "pdf" ) {
            $message = "Sorry, only JPG & PDF files are allowed.";
        } else {
            // Simple file upload - purely for educational demo
            if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
                $profile_pic_path = $target_file;
            }
        }
    }

    // Insert user into database with HASHED password
    // Using prepared statement is better, but following previous style with mysqli_query for simplicity/consistency if preferred, 
    // but hashing is the key requirement.
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role, profile_pic) VALUES (?, ?, ?, ?, ?)");
    $client = "client";
    if ($stmt && $stmt->bind_param("sssss", $name, $email, $hashed_password, $client, $profile_pic_path) && $stmt->execute()) {
        $message = "New user created successfully!";
        // Store in session as requested ("use $_SESSION to store the user details")
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        // Time to shine - send welcome email using PHPMailer
        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);

        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_OFF;                         //Disable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = '127.0.0.1';                            //Set the SMTP server to send through
            $mail->SMTPAuth   = false;                                  //Disable SMTP authentication for MailHog
            $mail->Username   = 'user@example.com';                     //SMTP username
            $mail->Password   = 'secret';                               //SMTP password
            $mail->SMTPSecure = '';                                     //Disable implicit TLS encryption for MailHog
            $mail->SMTPAutoTLS = false;                                 //Prevent STARTTLS negotiation with MailHog
            $mail->Port       = 1025;                                   //TCP port MailHog listens on

            //Recipients
            $mail->setFrom('hello@ticketman.be', 'TicketMan');
            $mail->addAddress($email, $name);     //Add a recipient
            $mail->addBCC('admin@ticketman.be');

            //Attachments
            //$mail->addAttachment('/var/tmp/file.tar.gz');         //Add attachments
            //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    //Optional name

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $name.' Welcome to TicketMAN';
            $mail->Body    = '<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <title>Minimal HTML Email</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <style>
            /* Some email clients ignore styles in <head>, so keep layout simple
                 and duplicate critical styles inline if needed. */
            body {
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
                font-family: Arial, sans-serif;
            }
        </style>
    </head>
    <body style="margin:0; padding:0; background-color:#f4f4f4;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td align="center" style="padding:24px 0;">
                    <table width="600" cellpadding="0" cellspacing="0" border="0" style="background-color:#ffffff; border-radius:4px; overflow:hidden;">
                        <tr>
                            <td style="padding:16px 24px; background-color:#111827; color:#ffffff; font-size:18px; font-weight:bold; text-align:center;">
                                <img src="https://deblauwevogel.be/images/logofooter.png" alt="Logo" style="max-width:100px; height:auto; margin-bottom:8px;"><br>
                                Minimal Newsletter
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:24px; color:#111827; font-size:14px; line-height:1.5;">
                                <p style="margin:0 0 12px 0;">Hi '.$name.',</p>
                                <p style="margin:0 0 12px 0;">
                                    This is a simple, minimal HTML email example with basic inline styling.
                                </p>
                                <p style="margin:0 0 16px 0;">
                                    You can customize the text, colors, and spacing to match your brand.
                                </p>
                                <p style="margin:0 0 24px 0; text-align:center;">
                                    <a href="https://example.com"
                                         style="display:inline-block; padding:10px 18px; background-color:#2563eb; color:#ffffff; text-decoration:none; font-size:14px; border-radius:4px;">
                                        Call to Action
                                    </a>
                                </p>
                                <p style="margin:0; font-size:12px; color:#6b7280;">
                                    If you didn’t expect this email, you can safely ignore it.
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding:16px 24px; background-color:#f9fafb; color:#9ca3af; font-size:11px; text-align:center;">
                                © 2026 Your Company · Street 1 · City
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
';
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo 'Message has been sent';
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
       
    } else {
        $message = "Error: " . ($stmt ? $stmt->error : $conn->error);
    }

    if ($stmt) {
        $stmt->close();
    }
}

// Fetch Users for Display
$sql = "SELECT * FROM users";
$result = mysqli_query($conn, $sql);
?>

    <div class="container mt-2">
        <?php if ($message): ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="row justify-content-center mt-5">
            <div class="col-md-12">
                <h1>Register</h1>
                <!-- Form submits to self -->
                <form action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" aria-describedby="emailHelp" required>
                        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="profilePic" class="form-label">Profile Picture (JPG or PDF only)</label>
                        <input type="file" class="form-control" name="profile_pic" id="profilePic" accept=".jpg,.jpeg,.pdf">
                    </div>
                    <div class="mb-3">
                        <label for="exampleName" class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <!-- Add name="register" to identify the action -->
                    <button type="submit" name="register" class="btn btn-primary">Submit</button>

                    <!-- Simple Google register/sign-in button for educational demo -->
                    <a href="google-login.php" class="btn btn-outline-dark w-100 mt-2">
                        Register / Sign in with Google
                    </a>

                    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
