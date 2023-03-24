<?php
// Note: this calls connection.php twice
// This means "if not exists" checks are important
include "functions.php";
include "connection.php";
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
        <div class="col-11 col-md-8 col-lg-5 col-xxl-4 p-5 bg-dark rounded">
            <div class="text-center text-white">
                <h1>Welcome!</h1>
                <p>This is the index page.</p>
                <p>SADUSER Was created on PHPMyAdmin with ALL PRIVILEGES on Database: "secure_app".</p>
                <a href="login.php" class="btn btn-light">Register / Login</a>
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