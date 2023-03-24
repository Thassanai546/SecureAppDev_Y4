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

// sanitise name before output
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
    <title>Authenticated Page 1</title>
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
    <div class="d-flex flex-column min-vh-100 justify-content-center align-items-center text-dark">
        <h1>Welcome, <?php echo $sanitised_name; ?> </h1>
        <h3>Navigate the site using the header!</h3>
        <p>Only authenticated users can access these pages.</p>
        <p>Footer contains link to phpmyadmin + github code.</p>
        <p>There are no vulnerabilites to find here!</p>
        <img src="https://www.explainxkcd.com/wiki/images/d/d4/air_gap.png" alt="">
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