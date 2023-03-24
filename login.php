<?php
session_start();
include "functions.php";

// Check if the secapp_cookie cookie is set
// no need to keep changing unauth cookie, as this is part of how we will log users.
if (!isset($_COOKIE['secapp_cookie'])) {
    // If the cookie is not set, call the set_secapp_cookie function to set it
    $unauth_user_val = generate_random_value();
    set_secure_cookie($unauth_user_val);
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $password = $_POST['password'];
    $user_name = $_POST['user_name'];

    // Will redirect valid users.
    // valid users will get a new and randomly generated cookie value.
    get_user($password, $user_name);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Login - SECAPP</title>
</head>

<!-- https://getbootstrap.com/docs/5.0/utilities/background/ -->

<body class="bg-light">
    <div class="d-flex flex-column justify-content-center align-items-center" style="margin-top: 50px;">
        <div class="col-11 col-md-8 col-lg-5 col-xxl-4 p-5 bg-dark rounded text-white">
            <form method="post" enctype='multipart/form-data'>
                <h2>Login</h2>
                <div class="mb-3 pt-3">
                    <label for="input" class="form-label">Username</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" required maxlength="50" autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
                </div>
                <br>
                <button type="submit" class="btn btn-primary" name="login">Login</button>
                <br><br>
                <h5><a href="register.php">Click to Register</a></h5>
            </form>
        </div>
    </div>

    <footer style="position: absolute; bottom: 0; width: 100%; height: 60px; background-color: #212529;">
        <div class="container">
            <p class="text-center text-muted">
                <a href="https://github.com/Thassanai546/Secure_App_Development_Project" target="_blank">GITHUB</a>
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=secure_app" target="_blank">PHPMYADMIN</a>
            </p>
        </div>
    </footer>
</body>

</html>