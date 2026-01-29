<?php
include 'includes/header.php';
require 'includes/conn.php';

// Handle Registration Logic
$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // Get raw password
    $name = mysqli_real_escape_string($conn, $_POST['name']);

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
    $sql_insert = "INSERT INTO users (name, email, password, profile_pic) VALUES ('$name', '$email', '$hashed_password', '$profile_pic_path')";
    
    if (mysqli_query($conn, $sql_insert)) {
        $message = "New user created successfully!";
        // Store in session as requested ("use $_SESSION to store the user details")
        $_SESSION['user_id'] = mysqli_insert_id($conn);
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
    } else {
        $message = "Error: " . $sql_insert . "<br>" . mysqli_error($conn);
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

        <h3>Existing Users:</h3>
        <?php
        if (mysqli_num_rows($result) > 0) {
            while ($assoc = mysqli_fetch_assoc($result)) {
                echo $assoc['name']." - ".$assoc['email'];
                if (!empty($assoc['profile_pic'])) {
                    echo " <img src='".$assoc['profile_pic']."' width='50'>";
                }
                echo "<hr>";
            }    
        } else {
            echo "0 results";
        }
        ?>

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
                    <p class="mt-3">Already have an account? <a href="login.php">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
