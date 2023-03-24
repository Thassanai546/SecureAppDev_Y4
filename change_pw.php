<?php
session_start();
include "functions.php";

check_session_timeout();

// prevent users who are not authenticated from accessing
// authenticated pages.
// Only valid users will have a $_SESSION['session_value']!
if (!isset($_COOKIE['secapp_cookie'])) {
    // Redirect to the login page
    header('Location: login.php');
    exit;
} else if ($_COOKIE['secapp_cookie'] !== $_SESSION['session_value']) {
    // Redirect to the login page
    header('Location: login.php');
    exit;
}

// Users attemtping a password change get their own csrf token.
// This token is used in the GET Requst.
// If this token is invalid, password change will not occur.
if (!isset($_SESSION['anti_csrf_token'])) {
    $_SESSION['anti_csrf_token'] = generate_random_value();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    // This situation occurs when a CSRF attack is attempted.
    // "No Value" will never be a valid csrf token.
    if (!isset($_GET['anti_csrf_token'])) {
        $_GET['anti_csrf_token'] = "No Value";
    }

    if (hash_equals($_GET['anti_csrf_token'], $_SESSION['anti_csrf_token'])) {

        // without isset(), undefined array key errors would appear on the page when first loading it
        // eg. when nothing was entered in to the form yet.
        if (isset($_GET['old_pass']) && isset($_GET['new_pass']) && isset($_GET['confirm_pass'])) {
            $old_pass = $_GET['old_pass'];
            $new_pass = $_GET['new_pass'];
            $new_pass_confirm = $_GET['confirm_pass'];
            $username = $_SESSION['username'];

            $current_user_salt = $_SESSION['salt'];
            $hashed_password = $_SESSION['hashed_pass'];

            $hashed_appended_form_pw = append_and_hash($current_user_salt, $old_pass);

            // Is the current password correct?
            if (hash_equals($hashed_password, $hashed_appended_form_pw)) {

                // Is the new password correct + does it meet the requirements.
                if ($new_pass != $new_pass_confirm) {
                    // If new passwords do not match
                    $message = "Passwords do not match!";
                    echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
                } else {
                    // If new passwords do match
                    // Check password, print message for any requirements not met
                    $valid_pass = pass_check($new_pass);
                    if ($valid_pass) {
                        update_pw($username, $new_pass_confirm);
                        header("Location: login.php");
                    }
                }
            } else {
                // User eneterd incorrect current password.
                $message = "Current Password Incorrect!";
                echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
            }
        }
    }
}
$name_from_db = $_SESSION['username'];
$sanitised_name = sanitise($name_from_db);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Password Change</title>
</head>

<nav class="navbar navbar-expand-md navbar-dark bg-dark p-2">
    <a class="navbar-brand" href="#">Thassanai + Mohsin - SECAPP</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto">
            <li class="nav-item">
                <a class="nav-link" href="auth_page_B.php">Auth Page B</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="change_pw.php">Change Password</a>
            </li>
            <li class="nav-item">
                <?php
                if ($_SESSION['is_admin'] == 1) {
                    echo '<a class="nav-link" href="admin_page.php">ADMIN PAGE</a>';
                }
                ?>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">Logout</a>
            </li>
        </ul>
    </div>
</nav>

<body class="bg-light">
    <div class="d-flex flex-column min-vh-100 justify-content-center align-items-center">
        <h1>Change your password here <?php echo $sanitised_name; ?> </h1>
        <div class="col-11 col-md-8 col-lg-5 col-xxl-4 p-5 bg-dark rounded text-white">
            <form method="GET">
                <div class="form-group p-3">
                    <label for="old_pass">Current Password</label>
                    <input type="password" class="form-control" id="old_pass" name="old_pass" autofocus required placeholder="Enter current password">
                </div>
                <div class="form-group p-3">
                    <label for="new_pass">New Password</label>
                    <input type="password" class="form-control" id="new_pass" name="new_pass" required placeholder="Enter new password">
                </div>
                <div class="form-group p-3">
                    <label for="confirm_pass">Confirm New Password</label>
                    <input type="password" class="form-control" id="confirm_pass" name="confirm_pass" required placeholder="Confirm new password">
                </div>
                <!-- When calling the generate_csrf_token() function, echo places the value in to the "value" field. -->
                <input type="hidden" name="anti_csrf_token" value="<?php echo $_SESSION['anti_csrf_token'] ?>">
                <br>
                <button type="submit" class="btn btn-primary">Change Password</button>
                <br><br>
                <a href="auth_page_A.php" class="btn btn-secondary">Home</a>
            </form>
        </div>
    </div>

    <footer class="bg-dark py-3">
        <div class="container">
            <p class="text-center text-muted">
                <a href="https://github.com/Thassanai546/Secure_App_Development_Project">GITHUB</a>
                <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=secure_app">PHPMYADMIN</a>
            </p>
        </div>
    </footer>
</body>

</html>