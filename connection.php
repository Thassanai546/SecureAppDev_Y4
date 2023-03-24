<?php
$host = 'localhost';
$username = 'root';
$password = '';
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
  die('Connect Error: ' . $conn->connect_error);
}

// create database
$sql = 'CREATE DATABASE IF NOT EXISTS secure_app;';
if (!($conn->query($sql) === TRUE)) {
  die('Error creating database: ' . $conn->error);
}

// create user if it doesn't already exist
// I was getting errors without the "if not exists"
$sql = "CREATE USER IF NOT EXISTS 'SADUSER'@'localhost' IDENTIFIED BY 'SADUSER';";
if (!($conn->query($sql) === TRUE)) {
  die('Error creating user: ' . $conn->error);
}

// grant privileges to user
// Note: this grants "SADUSER" with "ALL PRIVILEGES" on the database "secure_app"
$sql = "GRANT ALL PRIVILEGES ON secure_app.* TO 'SADUSER'@'localhost';";
if (!($conn->query($sql) === TRUE)) {
  die('Error granting privileges: ' . $conn->error);
}

// Reload privileges
$conn->query("FLUSH PRIVILEGES");

// connect as SADUSER
$conn = new mysqli($host, 'SADUSER', 'SADUSER', 'secure_app');
if ($conn->connect_error) {
  die('Connect Error: ' . $conn->connect_error);
}

// create tables
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    salt VARCHAR(255) NOT NULL,
    hashed_pass VARCHAR(255) NOT NULL,
    is_admin BOOLEAN NOT NULL DEFAULT false
)";
if (!$conn->query($sql) === TRUE) {
  die('Error creating table: ' . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS login_attempts (
    user_ip VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255) NOT NULL,
    user_name VARCHAR(255) NOT NULL,
    attempts INT(11) NOT NULL,
    last_attempt DATETIME NOT NULL
)";
if (!$conn->query($sql) === TRUE) {
  die('Error creating table: ' . $conn->error);
}

$sql = "CREATE TABLE IF NOT EXISTS logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL,
    log_time VARCHAR(255) NOT NULL,
    flag_successful INT(1) NOT NULL
)";
if (!$conn->query($sql) === TRUE) {
  die('Error creating table: ' . $conn->error);
}
// Make an ADMIN user.
$adminUsername = "ADMIN";
$adminPassword = "SaD_2023";
$salt = generate_salt();
$hashed_password = append_and_hash($salt, $adminPassword);
$zero = 1;

// Check if user already exists
$sql = $conn->prepare("SELECT * FROM users WHERE username = ? AND is_admin = ?");
$sql->bind_param("ss", $adminUsername, $zero);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows == 0) {
  // User does not exist, create a new record
  $sql = $conn->prepare("INSERT INTO users (username, salt, hashed_pass, is_admin) VALUES (?, ?, ?, ?)");
  $sql->bind_param("ssss", $adminUsername, $salt, $hashed_password, $zero);
  $result = $sql->execute();
  $sql->close();

  if ($result !== TRUE) {
    die('Error creating user: ' . $conn->error);
  }
}
