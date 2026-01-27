<?php
include 'includes/header.php';
$_SESSION['email'] = $_POST['email'];
$_SESSION['password'] = $_POST['password'];
?>
    <div class="container">

        <div class="row justify-content-center">
            <div class="col-md-12">
                <h1>Your details are:</h1>
               
                <hr>
                <?php echo $_POST['email'] ?>
               
            </div>
        </div>
    </div>
</body>
</html>
