<?php
ob_start();

// Start session only if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOSTNAME', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'courier_system');

define('ROLE_ADMIN', 'admin');
define('ROLE_AGENT', 'agent');
define('ROLE_CUSTOMER', 'customer');

$conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

if ($conn === false) {
    error_log("Database connection failed: " . mysqli_connect_error());
    die("ERROR: Could not connect to the database. Please try again later.");
}

mysqli_set_charset($conn, "utf8mb4");
