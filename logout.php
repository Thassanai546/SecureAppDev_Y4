<?php
session_start();
session_unset();
session_destroy();
setcookie('secapp_cookie', "", time() + 3600, '/', 'localhost', true, true); # Clear session cookie.
header('Location: login.php');
exit;
