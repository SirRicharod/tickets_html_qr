<?php
include 'includes/header.php';
$_SESSION['name'] = 'Massimo';
$_SESSION['email'] = 'mas@mas.com';
?>
    <div class="container mt-2">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <h1>Enter your details</h1>
                <form action="generate.php" method="post">
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Email address</label>
                        <input type="email" class="form-control" name="email" aria-describedby="emailHelp" required>
                        <div id="emailHelp" class="form-text">We'll never share your email with anyone else.</div>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Password</label>
                        <input type="password" class="form-control" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
