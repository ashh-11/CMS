<?php
// register.php - Handles new user registration (customer role)
session_start(); // Start the session to store temporary messages or user status

// Include the database connection configuration
require_once 'config.php'; // Defines DB constants
require_once 'inc.connections.php'; // Establishes $conn, starts ob_start() and session_start()

// Initialize variables for form fields and error/success messages
$username = $email = $password = $confirm_password = '';
$username_err = $email_err = $password_err = $confirm_password_err = '';
$success_message = $general_error = '';

// If user is already logged in, redirect them (e.g., to their dashboard or tracking page)
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == ROLE_ADMIN) { // Using constant from config.php
            header('location: admin_dashboard.php');
        } elseif ($_SESSION['role'] == ROLE_AGENT) {
            header('location: agent_dashboard.php');
        } else {
            header('location: track_shipment.php'); // Default for customer or unknown role
        }
    } else {
        header('location: track_shipment.php');
    }
    exit;
}

// Process form submission when the form is posted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // 1. Validate Username (using email as username as per common practice)
    if (empty(trim($_POST['username']))) {
        $username_err = "Please enter a username (your email).";
    } else {
        $email = trim($_POST['username']); // Assuming username is email
        // Check if username (email) already exists
        $sql = "SELECT user_id FROM users WHERE username = ?";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $email;
            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_store_result($stmt);
                if (mysqli_stmt_num_rows($stmt) == 1) {
                    $username_err = "This username (email) is already taken.";
                } else {
                    $username = $email;
                }
            } else {
                $general_error = "<div class='alert alert-danger'>Oops! Something went wrong. Please try again later.</div>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_error = "<div class='alert alert-danger'>Database error. Please try again.</div>";
        }
    }

    // 2. Validate Password
    if (empty(trim($_POST['password']))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST['password']);
    }

    // 3. Validate Confirm Password
    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // If no validation errors, attempt to insert new user into database
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($general_error)) {
        // Prepare an INSERT statement
        $sql = "INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, ?, 'active')";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Bind parameters: 's' for string (username), 's' for string (hashed password), 's' for string (role)
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password_hash, $param_role);

            // Set parameters
            $param_username = $username;
            // Hash the password securely before storing
            $param_password_hash = password_hash($password, PASSWORD_DEFAULT);
            $param_role = ROLE_CUSTOMER; // Automatically assign 'customer' role for public registration

            // Execute the prepared statement
            if (mysqli_stmt_execute($stmt)) {
                // Registration successful, redirect to login page with a success message
                $_SESSION['registration_success'] = "Account created successfully! Please log in.";
                header('location: login.php');
                exit;
            } else {
                $general_error = "<div class='alert alert-danger'>Something went wrong. Please try again later.</div>";
                error_log("User registration failed: " . mysqli_error($conn));
            }
            // Close statement
            mysqli_stmt_close($stmt);
        } else {
            $general_error = "<div class='alert alert-danger'>Database error preparing statement.</div>";
            error_log("Prepare statement failed: " . mysqli_error($conn));
        }
    }
}
// Close database connection at the end of the script (handled by inc.footer.php if included, otherwise here)
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Register - Courier Management System</title>
  <meta content="Register for a new account in the Courier Management System." name="description">
  <meta content="courier, register, signup, account" name="keywords">

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

  <!-- Custom CSS for Register Page (similar to login for consistency) -->
  <style>
    body {
      background: #f0f2f5; /* A light grey background */
    }

    .register.section {
      background: none;
    }

    .card {
      border-radius: 10px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .card-title {
      color: #012970;
      font-weight: 700;
    }

    .btn-primary {
      background-color: #012970;
      border-color: #012970;
    }

    .btn-primary:hover {
      background-color: #02388c;
      border-color: #02388c;
    }

    .input-group-text {
      background-color: #f8f9fa;
      border-color: #ced4da;
    }

    .logo span {
      color: #012970;
    }

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
                    <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                    <p class="text-center small">Enter your personal details to create account</p>
                  </div>

                  <form class="row g-3 needs-validation" novalidate action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                    <?php if (!empty($general_error)) { ?>
                        <div class="col-12">
                            <?php echo $general_error; ?>
                        </div>
                    <?php } ?>

                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Your Username (Email)</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="email" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="yourUsername" value="<?php echo htmlspecialchars($username); ?>" required>
                        <div class="invalid-feedback"><?php echo $username_err; ?></div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="yourPassword" required>
                      <div class="invalid-feedback"><?php echo $password_err; ?></div>
                    </div>

                    <div class="col-12">
                      <label for="yourConfirmPassword" class="form-label">Confirm Password</label>
                      <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" id="yourConfirmPassword" required>
                      <div class="invalid-feedback"><?php echo $confirm_password_err; ?></div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" name="terms" type="checkbox" value="true" id="acceptTerms" required>
                        <label class="form-check-label" for="acceptTerms">I agree and accept the <a href="#">terms and conditions</a></label>
                        <div class="invalid-feedback">You must agree before submitting.</div>
                      </div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Create Account</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Already have an account? <a href="login.php">Log in</a></p>
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