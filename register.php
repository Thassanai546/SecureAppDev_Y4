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

if (isset($_POST['signup'])) {
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $user_name = $_POST['user_name'];
    $user_name = sanitise($user_name);

    if ($password != $confirm_password) {
        // If passwords do not match
        $message = "Passwords do not match!";
        echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
    } else {
        // If passwords DO match

        // Check password, print message for any requirements not met
        $valid_pass = pass_check($password);

        if ($valid_pass) {
            //get salt as a hash 
            $salt = generate_salt();

            // get hash of salt+password
            $hashed_password = append_and_hash($salt, $password);

            //insert
            $register_user = insert_user($user_name, $salt, $hashed_password);

            // If a user registers, send them to the login page.
            if ($register_user) {
                reset_attempts($_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT'], $user_name);
                header("Location: login.php");
                die;
            } else {
                $message = "Could not register " . $user_name . ' at this time.';
                echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
            }
        } else {
            // Users can be timed out for repeated failed attempts.
            track_lockout($user_name);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Register</title>
</head>

<body class="bg-light">
    <div class="d-flex align-items-center justify-content-center pt-3" style="margin-top: 50px;">
        <div class="col-11 col-md-8 col-lg-5 col-xxl-4 p-5 bg-dark rounded text-white">

            <form method="post" enctype='multipart/form-data'>
                <h3>Register</h3>
                <div class="mb-3 pt-3">
                    <label for="input" class="form-label">Username</label>
                    <input type="text" class="form-control" id="user_name" name="user_name" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required autocomplete="off">
                </div>

                <br>
                <button type="submit" class="btn btn-primary" name="signup">Register</button>
                <br><br>
                <h5><a href="login.php">Click to Login</a></h5>
            </form>
        </div>
    </div>

    <footer style="position: absolute; bottom: 0; width: 100%; height: 60px; background-color: #212529;">
        <div class="container">
            <p class="text-center text-muted">
                <a href="https://github.com/Thassanai546/Secure_App_Development_Project">GITHUB</a>
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=secure_app">PHPMYADMIN</a>
            </p>
        </div>
    </footer>
</body>

</html>