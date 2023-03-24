<?php
// Try to keep key functions in here
include "connection.php";

function sanitise($input)
{
    $input = preg_replace('/</', '&lt;', $input);
    $input = preg_replace('/>/', '&gt;', $input);
    $input = preg_replace('/[(]/', '&#40;', $input);
    $input = preg_replace('/[)]/', '&#41;', $input);
    $input = preg_replace('/\//', '&#47;', $input);
    $input = preg_replace('/=/', '&#61;', $input);
    $input = preg_replace('/"/', '&quot;', $input);
    $input = preg_replace("/'/", '&apos;', $input);
    return $input;
}
function update_pw($user_name, $new_pass)
{
    global $conn;
    $new_salt = generate_salt(); // Returns a bin2hex value.

    // Hash new password with new salt
    $new_hashed_pw = append_and_hash($new_salt, $new_pass);

    $sql = $conn->prepare("UPDATE users SET hashed_pass = ?, salt= ? WHERE username = ?");
    $sql->bind_param('sss', $new_hashed_pw, $new_salt, $user_name);
    $sql->execute();
    $result = $sql->get_result();
    log_login_attempt($user_name, 2);
    if (!$result) {
        $message = "Password not updated.";
        echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
    }
}
function pass_check($form_password)
{
    // array of unmet reqs
    // If this is empty by the end of the function, the form_password is allowed
    // and "true" is returned.
    $reqs = [];

    if (strlen($form_password) < 8) {
        $reqs[] = "Password must be at least 8 characters long.";
    }
    if (!preg_match('/[A-Z]/', $form_password)) {
        $reqs[] = "Password must contain at least one uppercase letter.";
    }
    if (!preg_match('/[a-z]/', $form_password)) {
        $reqs[] = "Password must contain at least one lowercase letter.";
    }
    if (!preg_match('/[0-9]/', $form_password)) {
        $reqs[] = "Password must contain at least one number.";
    }
    if (!preg_match('/[^a-zA-Z0-9]/', $form_password)) {
        // https://stackoverflow.com/questions/3938021/how-to-check-for-special-characters-php
        $reqs[] = "Password must contain at least one special character.";
    }

    if (count($reqs) > 0) {
        // alert-warning = yellow box
        echo '<div class="alert alert-secondary" role="alert" align="center" style="width: 60%; margin: 0 auto;">
        Your password has not met the requirements!';

        foreach ($reqs as $requirement) {
            echo '<div class= "text-danger" style="width: 50%; margin: 0 auto;">' . "[ ! ] " . $requirement . '</div>';
        }
        echo '</div>';

        return false;
    } else {
        return true;
    }
}
function insert_user($username, $salt, $hashed_pass)
{
    global $conn;
    $username = sanitise($username);

    // Check if the username already exists in the database
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        // Username already exists, return false
        $stmt->close();
        return false;
    }

    // Username doesn't exist already
    $stmt = $conn->prepare("INSERT INTO users (username, salt, hashed_pass, is_admin) VALUES (?,?,?,?)");
    $zero = 0; // User admin flag must be set here
    $stmt->bind_param("sssi", $username, $salt, $hashed_pass, $zero);
    $result = $stmt->execute();
    $stmt->close();
    return $result;
}
function get_logs()
{
    global $conn;
    // Prepared Statement
    $sql = $conn->prepare("SELECT * FROM logs ORDER BY ID DESC"); //gets results from log db
    $sql->execute();
    $result = $sql->get_result();

    echo "<table>";
    echo "<tr>";
    echo "<th>Username</th>";
    echo "<th>Log Time</th>";
    echo "<th>Event</th>";
    echo "</tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . sanitise($row["username"]) . "</td>";
        $date_str = $row["log_time"];
        $date_time = new DateTime($date_str);
        $date_formatted = $date_time->format('d/M/Y h:i:s A');
        echo "<td>" . $date_formatted . "</td>";
        if ($row["flag_successful"] == 1) {
            $flag = "Successful Login";
            $color_class = "text-success";
        } elseif ($row["flag_successful"] == 0) {
            $flag = "Unsuccessful Login";
            $color_class = "text-danger";
        } elseif ($row["flag_successful"] == 2) {
            $flag = "Password Change";
            $color_class = "text-info";
        }
        echo "<td class='fw-bold {$color_class}'>" . $flag . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}
function get_user($form_password, $user_name)
{
    global $conn;

    if (isset($_COOKIE['secapp_cookie'])) {
        $un_auth_cookie = $_COOKIE['secapp_cookie'];
    }

    // Sanitize user name before output
    // Dev note: why sanitise before the SELECT?
    // A user called <script> would be stored as &lt;script&gt;
    // our SELECT would fail if we try to select "<script>" but it will work if we SELECT "&lt;script&gt;"
    $user_name = sanitise($user_name);

    // Prepare SQL statement and bind parameters
    $sql = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $sql->bind_param('s', $user_name); // 's' = string
    $sql->execute();
    $result = $sql->get_result();

    // Check if user exists in the database
    if ($result->num_rows > 0) {
        // Get user details from database
        $row = mysqli_fetch_assoc($result);
        $usersalt = $row["salt"];
        $userpass = $row["hashed_pass"];
        $usernamedb = $row["username"];
        $admin_flag = $row["is_admin"];

        // Hash and append the user-entered password
        $hashed_appended_form_pw = append_and_hash($usersalt, $form_password);


        // Check if hashed password matches the stored password
        if (hash_equals($userpass, $hashed_appended_form_pw)) {
            // Successful login
            log_login_attempt($user_name, 1);
            $_SESSION['username'] = $usernamedb;
            $_SESSION['is_admin'] = $admin_flag;
            $_SESSION['hashed_pass'] = $userpass;
            $_SESSION['salt'] = $usersalt;

            // Authenticated users get a new session.
            $generated_session = generate_random_value();

            // Set this new session
            $_SESSION['session_value'] = $generated_session;
            set_secure_cookie($generated_session);

            // If a user authenticates, set their login attempts to 0
            header("Location: auth_page_A.php");
            exit;
        } else {
            // Note that both messages are the same as information on whether a use exists or not is not revealed
            // User exists, but their password is wrong
            $message = "The username: '$user_name' and password could not be authenticated at the moment.";
            track_lockout($user_name);
            log_login_attempt($user_name, 0);
        }
    } else {
        // User does not exist and could not be found on the database. (num_rows is 0)
        $message = "The username: '$user_name' and password could not be authenticated at the moment.";
        track_lockout($user_name);
        log_login_attempt($user_name, 0);
    }

    // Output error message, red box at the top of the screen.
    // The user sees the error message and is not sent to an authenticated page.
    echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
}
function log_login_attempt($username, $success)
{
    global $conn;
    if (!isset($_SESSION['log_flag'])) {
        $_SESSION['log_flag'] = True;
    }

    if ($_SESSION['log_flag']) {
        $date = new DateTime("UTC");
        $date_str = $date->format("Y-m-d H:i:s");

        $stmt = $conn->prepare("INSERT INTO logs (username, log_time, flag_successful) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $date_str, $success);

        $result = $stmt->execute();
        $stmt->close();
    }
}
function generate_salt()
{
    try {
        $salt = random_bytes(32);
    } catch (\Exception $ex) {
        echo $ex;
        die("Error generating salt.");
    }
    $salt_in_hex = bin2hex($salt);
    return $salt_in_hex;
}
function hash_this($value)
{
    // Hash a value using sha256.
    $ha = "sha256";
    $result = hash($ha, $value);
    return $result;
}
function append_and_hash($salt, $value_to_hash)
{
    // Takes salt and a password
    // appends salt to start of password
    // hashes that combo, and returns result.
    $appended = $salt . $value_to_hash;
    $hashed_appended = hash_this($appended);
    return $hashed_appended;
}
function check_session_timeout()
{
    // Idle lockout where being idle means staying on the same page.

    // 600 secs = 10 minutes.
    $max_time = 600;

    // If activity var set, check it.
    if (isset($_SESSION['last_activity'])) {
        // Calculate how long it has been since the user's last activity
        $elapsed_time = time() - $_SESSION['last_activity'];

        // If the user has been inactive for more than 10 minutes, send to logout.
        if ($elapsed_time > $max_time) {
            header("Location: logout.php");
            exit;
        }
    }
    // If activity var not set, set it.
    // Next time this function runs, it will be checked.
    $_SESSION['last_activity'] = time();
}
// Dev notes:
// Cookie management
// non auth users get their own session
// this session is changes upon successful login
// logout = change session to a non auth session
function generate_random_value()
{
    $val = bin2hex(random_bytes(32));
    return $val;
}
function set_secure_cookie($cookie_value)
{
    //setcookie(name, value, timeout in seconds, path,domain, https only flag True, http only flag true)
    // The '/' = path of the cookie.
    // 3600 seconds = 1hr
    setcookie('secapp_cookie', $cookie_value, time() + 3600, '/', 'localhost', true, true);
}
function track_lockout($attempted_username)
{
    global $conn;

    // Get the current time
    $now = time();

    // When tracking a logout, first increment login attempt count.
    if (!isset($_SESSION['login_attempts'])) {
        $_SESSION['login_attempts'] = 1;
    } else {
        $_SESSION['login_attempts']++;
    }

    // Get the user agent string
    // This is sanitised before insertion AND comparison to DB Values
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $user_agent = sanitise($user_agent);

    // Get the user's IP address
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $ip_address = sanitise($ip_address);

    // 5 attempts to login
    $max_attempts = 5;
    if ($_SESSION['login_attempts'] >= $max_attempts) {
        // Lockout time is set.
        // Attempts reset.
        // A user is not locked out based on their attempts, but instead on their lockout time.
        $_SESSION['lockout_time'] = $now;
        reset_attempts($ip_address, $user_agent, $attempted_username);
    }

    // Check if the user is currently locked out
    // 180 secs = 3 minutes
    if (isset($_SESSION['lockout_time']) && $_SESSION['lockout_time'] + 180 > $now) {
        $message = "You have been locked out for 3 minutes!";
        echo '<div class="alert alert-danger" role="alert" align="center" style="width: 50%; margin: 0 auto;">' . $message . '</div>';
        $_SESSION['log_flag'] = false; # log flag true = log user, log flag false = do not log.

        // Why set to 0 and not 1?
        // When calling track_lockout(), we increment login_attempts
        // Setting this to 1 at this point, will only allow the user 4 attempts
        // if they get locked out, and wish to log back in again
        $_SESSION['login_attempts'] = 0;
        return;
    } else {
        $_SESSION['log_flag'] = true;
    }

    // Prepare SQL statement and bind parameters
    $sql = $conn->prepare("SELECT * FROM login_attempts WHERE user_ip = ? AND user_agent = ? AND user_name = ?");
    $sql->bind_param("sss", $ip_address, $user_agent, $attempted_username);
    $sql->execute();
    $result = $sql->get_result();

    if ($result->num_rows > 0) {
        // Update the existing row with a new value of attempts and last_attempt
        $row = $result->fetch_assoc();
        $attempts = $row['attempts'] + 1;
        $user_ip = $row['user_ip'];
        $user_agent = $row['user_agent'];
        $user_name = $row['user_name'];

        $sql = $conn->prepare("UPDATE login_attempts SET attempts = ?, last_attempt = NOW() WHERE user_ip = ? AND user_agent = ? AND user_name = ?");
        $sql->bind_param("isss", $attempts, $user_ip, $user_agent, $user_name); # Increment attempt count (integer) of composite primary key (3 strings and an integer). "isss" = 1 integer and 3 strings
        $sql->execute();
    } else {
        // Insert a new row with a value of attempts = 1 and last_attempt = NOW()
        $sql = $conn->prepare("INSERT INTO login_attempts (user_ip, user_agent, user_name, attempts, last_attempt) VALUES (?, ?, ?, 1, NOW())");
        $sql->bind_param("sss", $ip_address, $user_agent, $attempted_username); # "sss" = 3 strings
        $sql->execute();
    }

    // Close the statement
    $sql->close();
}
function reset_attempts($ip_address, $user_agent, $attempted_username)
{
    // Called when we can get_user()
    global $conn;

    // Prepare SQL statement and bind parameters
    $sql = $conn->prepare("UPDATE login_attempts SET attempts = 0 WHERE user_ip = ? AND user_agent = ? AND user_name = ?");
    $sql->bind_param("sss", $ip_address, $user_agent, $attempted_username);
    $sql->execute();

    // Close the statement
    $sql->close();
}
