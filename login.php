<?php
// Start session first before any output
session_start();

// Include the database connection file
require 'includes/conn.php';

// Initialize message variable
$message = "";

// Optional one-time message from OAuth callback (stored in session)
if (isset($_SESSION['auth_message'])) {
    $message = $_SESSION['auth_message'];
    unset($_SESSION['auth_message']);
}

// Check if the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect and sanitize input
    // We use mysqli_real_escape_string for email to prevent SQL injection
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Raw password for verification

    // Prepare SQL query to fetch the user with the given email
    $sql = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);

    // Check if a user with that email exists
    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verify the password: checks if the provided password matches the hash in the database
        // password_verify() handles the hashing algorithm automatically
        if (password_verify($password, $user['password'])) {
            // Password is correct!
            
            // Store user details in the SESSION superglobal
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'] ?? 'client'; // Store role, default to 'client' if not set

            // Redirect based on user role
            if ($_SESSION['role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: order.php");
            }
            exit(); // Stop script execution after redirect
        } else {
            // Password does not match
            $message = "Invalid password.";
        }
    } else {
        // No user found with that email
        $message = "User not found.";
    }
}

// Include the header only AFTER processing logic (to allow redirects)
include 'includes/header.php';
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1>Login</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-danger"><?php echo $message; ?></div>
            <?php endif; ?>

            <form action="login.php" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" class="form-control" name="email" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>

                <!-- Simple Google sign-in button for educational demo -->
                <a href="google-login.php" class="btn btn-outline-dark w-100 mt-2">
                    Sign in with Google
                </a>

                <div class="mt-3">
                    Not registered? <a href="index.php">Create an account</a>.
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
