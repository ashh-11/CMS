<?php
// login.php - Handles user login authentication
session_start(); // Start the session at the very beginning of the script

// Include the database configuration file
require_once 'config.php';

// Initialize variables for username and password, and their respective error messages
$username = $password = '';
$login_err = ''; // A single error message for login failures to avoid leaking info

// Check if the user is already logged in. If so, redirect them to their respective dashboard.
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'admin') {
            header('location: admin_dashboard.php');
        } elseif ($_SESSION['role'] == 'agent') {
            header('location: agent_dashboard.php');
        } else {
            // For 'customer' role or any other role not having a dedicated dashboard,
            // redirect to a public page like shipment tracking.
            header('location: track_shipment.php');
        }
    } else {
        // If role is not set but logged in, default to a safe page.
        header('location: track_shipment.php');
    }
    exit; // Terminate script execution after redirection
}

// Process login form submission when the form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Validate username input
    if (empty(trim($_POST['username']))) {
        $login_err = "Please enter username.";
    } else {
        $username = trim($_POST['username']);
    }

    // Validate password input
    if (empty(trim($_POST['password']))) {
        // Append to existing error or set new one if username was fine
        $login_err = !empty($login_err) ? $login_err . " And please enter your password." : "Please enter your password.";
    } else {
        $password = trim($_POST['password']);
    }

    // If there are no input errors at this stage
    if (empty($login_err)) {
        // Prepare a SQL SELECT statement to fetch user details from the 'users' table.
        // We fetch user_id, username, password_hash (the stored hashed password), role, and location_id.
        $sql = "SELECT user_id, username, password_hash, role, location_id FROM users WHERE username = ?";

        // Use a prepared statement to prevent SQL injection (CRITICAL SECURITY PRACTICE)
        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind parameters: 's' indicates the parameter is a string
            mysqli_stmt_bind_param($stmt, "s", $param_username);

            // Set parameter
            $param_username = $username;

            // Attempt to execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Store the result of the executed statement
                mysqli_stmt_store_result($stmt);

                // Check if a user with the given username exists (should be exactly one row)
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    // Bind result variables to the columns fetched from the database
                    mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password, $role, $location_id);

                    // Fetch the results
                    if (mysqli_stmt_fetch($stmt)) {
                        // --- SECURE PASSWORD VERIFICATION ---
                        // Use password_verify() to safely check the entered password against the stored hash.
                        // The $hashed_password must have been created using password_hash() upon user creation/update.
                        if (password_verify($password, $hashed_password)) {
                            // Password is correct, so start a new session
                            session_regenerate_id(true); // Regenerate session ID for security against session fixation attacks

                            // Store session variables
                            $_SESSION['loggedin'] = true;
                            $_SESSION['user_id'] = $user_id;
                            $_SESSION['username'] = $username;
                            $_SESSION['role'] = $role;
                            $_SESSION['location_id'] = $location_id; // Store agent's assigned location (will be NULL for admin/customer)

                            // Log the successful login event (for auditing)
                            $sql_audit = "INSERT INTO user_login_audit (user_id, login_time, ip_address) VALUES (?, NOW(), ?)";
                            if ($stmt_audit = mysqli_prepare($conn, $sql_audit)) {
                                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'; // Get client IP address
                                mysqli_stmt_bind_param($stmt_audit, "is", $user_id, $ip_address);
                                mysqli_stmt_execute($stmt_audit);
                                mysqli_stmt_close($stmt_audit);
                            } else {
                                error_log("Failed to prepare user_login_audit statement: " . mysqli_error($conn));
                            }

                            // Redirect user based on their role
                            if ($role == 'admin') {
                                header('location: admin_dashboard.php');
                            } elseif ($role == 'agent') {
                                header('location: agent_dashboard.php');
                            } else { // 'customer' or any other non-admin/agent role
                                header('location: track_shipment.php');
                            }
                            exit; // Terminate script after successful login and redirection
                        } else {
                            // Password is not valid. Provide a generic error message.
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else {
                    // No account found with the provided username. Provide a generic error message.
                    $login_err = "Invalid username or password.";
                }
            } else {
                // Error executing the prepared statement. Log the error, provide generic message.
                error_log("Login query execution failed: " . mysqli_error($conn));
                $login_err = "An unexpected error occurred. Please try again later.";
            }
            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            // Error preparing the statement. Log the error, provide generic message.
            error_log("Login query preparation failed: " . mysqli_error($conn));
            $login_err = "An unexpected error occurred. Please try again later.";
        }
    }
    // Close the database connection if the form submission was processed and connection wasn't closed by redirection
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login - Courier Management System</title>
  <meta content="Login page for Admin, Agent, and Customer roles in the Courier Management System." name="description">
  <meta content="courier, login, admin, agent, customer" name="keywords">

  <!-- Favicons -->
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">

  <!-- Custom CSS for Login Page -->
  <style>
    body {
      background: #f0f2f5; /* A light grey background */
      /* You can use a background image instead, e.g.: */
      /* background-image: url('assets/img/login-bg.jpg'); */
      /* background-size: cover; */
      /* background-position: center; */
      /* filter: grayscale(50%); /* Optional: for a desaturated look */
    }

    .register.section {
      background: none; /* Override any background set by the template */
    }

    .card {
      border-radius: 10px; /* Slightly rounded corners for the card */
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Softer, more pronounced shadow */
    }

    .card-title {
      color: #012970; /* Match primary brand color */
      font-weight: 700;
    }

    .btn-primary {
      background-color: #012970; /* Primary brand color for the button */
      border-color: #012970;
    }

    .btn-primary:hover {
      background-color: #02388c; /* Slightly darker on hover */
      border-color: #02388c;
    }

    .input-group-text {
      background-color: #f8f9fa; /* Light background for the username icon */
      border-color: #ced4da;
    }

    /* Adjust logo and title styling if needed for better contrast on new background */
    .logo span {
      color: #012970; /* Ensure logo text is visible */
    }

    /* Optional: Style for the "Don't have an account?" link */
    .small a {
      color: #012970;
      font-weight: 600;
    }
  </style>

</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets/img/logo.png" alt="">
                  <span class="d-none d-lg-block">Courier System</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                    <p class="text-center small">Enter your username & password to login</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <?php if (!empty($login_err)) { ?>
                        <div class="col-12">
                            <div class="alert alert-danger" role="alert">
                                <?php echo $login_err; ?>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="yourUsername" value="<?php echo htmlspecialchars($username); ?>" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="yourPassword" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Login</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Don't have an account? <a href="register.php">Create an account</a></p>
                    </div>
                  </form>

                </div>
              </div>


            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <!-- Template Main JS File -->
  <script src="assets/js/main.js"></script>

</body>

</html>