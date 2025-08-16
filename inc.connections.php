<?php
// inc.connections.php - Handles initial setup and database connection

// Start output buffering. This is important for header redirects and preventing output issues.
ob_start();

// Start the session. Essential for user authentication and session data.
// session_start();

// Include the configuration file which defines database credentials and other constants.
// Use 'require_once' to ensure it's loaded only once and is critical for the script.
require_once 'config.php';

// Include custom functions file.
// Ensure 'inc.functions.php' exists or remove this line if not used.
// include_once 'inc.functions.php'; // Using include_once if functions might be conditionally included elsewhere

// Create database connection using constants from config.php
$conn = mysqli_connect(DB_HOSTNAME, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check database connection
if ($conn === false) {
    // Log the error securely and provide a generic message to the user
    error_log("Database connection failed: " . mysqli_connect_error());
    die("ERROR: Could not connect to the database. Please try again later.");
}

// Set database character set to prevent encoding issues
mysqli_set_charset($conn, "utf8mb4");

// At this point, $conn is available for use in any script that includes inc.connections.php
// $_SESSION is also available.
// ob_start() is active.

?>