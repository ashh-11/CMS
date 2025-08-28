<?php
require_once 'inc.connections.php';

$username = $email = $password = $confirm_password = '';
$username_err = $email_err = $password_err = $confirm_password_err = '';
$success_message = $general_error = '';
$is_first_admin_registration = false;

$sql_check_admin = "SELECT COUNT(*) FROM users WHERE role = 'admin'";
$result_check_admin = mysqli_query($conn, $sql_check_admin);
$row_check_admin = mysqli_fetch_row($result_check_admin);
$admin_count = $row_check_admin[0];
mysqli_free_result($result_check_admin);

if ($admin_count == 0) {
    $is_first_admin_registration = true;
    $assigned_role = ROLE_ADMIN;
} else {
    $assigned_role = ROLE_CUSTOMER;
}

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == ROLE_ADMIN) {
            header('location: admin_dashboard.php');
        } elseif ($_SESSION['role'] == ROLE_AGENT) {
            header('location: agent_dashboard.php');
        } else {
            header('location: track_shipment.php');
        }
    } else {
        header('location: track_shipment.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (empty(trim($_POST['username']))) {
        $username_err = "Please enter a username (your email).";
    } else {
        $email = trim($_POST['username']);
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
                $general_error = "<div class='alert alert-danger'>Oops! Something went wrong checking username: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_error = "<div class='alert alert-danger'>Database error preparing username check: " . mysqli_error($conn) . "</div>";
        }
    }

    if (empty(trim($_POST['password']))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = "Please confirm password.";
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    if (!isset($_POST['terms'])) {
        $general_error = "<div class='alert alert-danger'>You must accept the terms and conditions.</div>";
    }

    if (empty($username_err) && empty($password_err) && empty($confirm_password_err) && empty($general_error)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, ?, 'active')";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sss", $param_username, $param_password_hash, $param_role);

            $param_username = $username;
            $param_password_hash = $hashed_password;
            $param_role = $assigned_role;

            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['registration_success'] = "Account created successfully! " .
                                                    ($assigned_role == ROLE_ADMIN ? "You are the first administrator." : "Please log in with your new customer account.");
                header('location: login.php');
                exit;
            } else {
                $general_error = "<div class='alert alert-danger'>Something went wrong during registration: " . mysqli_error($conn) . "</div>";
            }
            mysqli_stmt_close($stmt);
        } else {
            $general_error = "<div class='alert alert-danger'>Database error preparing insert statement: " . mysqli_error($conn) . "</div>";
        }
    }
}
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">

  <title>Register - TrackIt Courier</title>
  <meta content="Register for a new account in the TrackIt Courier." name="description">
  <meta content="courier, register, signup, account" name="keywords">

  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">

  <link href="assets/css/style.css" rel="stylesheet">

  <style>
    body {
      background-color: #819CDD;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Open Sans', sans-serif;
      color: #3B428A;
    }

    .section.register {
      background: none;
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container { /* Reusing name for consistency with login.php */
        background-color: #FFFFFF;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        padding: 40px;
        max-width: 1200px;
        width: 90%;
        display: flex;
        overflow: hidden;
    }

    .login-form-col {
        flex: 1;
        padding-right: 30px;
    }

    .login-image-col {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding-left: 30px;
    }
    .login-image-col img {
        max-width: 100%;
        height: auto;
        display: block;
        border-radius: 10px;
    }


    .logo-container {
        text-align: center;
        margin-bottom: 30px;
    }
    .logo-container .logo {
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .logo-container .logo svg path,
    .logo-container .logo svg rect {
        fill: #3B428A !important;
    }
    .logo-container .logo svg text {
        fill: #3B428A !important;
    }
    .logo-container .logo:hover svg path,
    .logo-container .logo:hover svg rect,
    .logo-container .logo:hover svg text {
        fill: #5A90D7 !important;
    }
    .logo-container .logo span {
        display: none;
    }


    .card-title {
      color: #3B428A;
      font-weight: 700;
      margin-bottom: 20px;
      font-size: 1.8em;
    }

    .btn-primary {
      background-color: #7AAEEA !important;
      border-color: #7AAEEA !important;
      font-weight: 600;
    }

    .btn-primary:hover {
      background-color: #5A90D7 !important;
      border-color: #5A90D7 !important;
    }

    .input-group-text {
      background-color: #E5D1CF;
      border-color: #819CDD;
      color: #5A90D7;
    }
    .form-control:focus {
        border-color: #7AAEEA !important;
        box-shadow: 0 0 0 0.25rem rgba(122, 174, 234, 0.25) !important;
    }
    .form-control.is-invalid {
        border-color: #FE6A53 !important;
    }
    .invalid-feedback {
        color: #FE6A53;
    }


    .small a {
      color: #7AAEEA;
      font-weight: 600;
    }
    .small a:hover {
        color: #5A90D7;
    }

    #header { display: none !important; }
    #footer { display: none !important; }
    .back-to-top { display: none !important; }

    @media (max-width: 992px) {
        .login-container {
            flex-direction: column;
            padding: 30px;
        }
        .login-form-col {
            padding-right: 0;
            padding-bottom: 30px;
        }
        .login-image-col {
            padding-left: 0;
        }
    }
  </style>

</head>

<body>

  <main>
    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-10 d-flex flex-column align-items-center justify-content-center">

              <div class="login-container">
                <div class="login-form-col">
                    <div class="logo-container">
                        <a href="index.html" class="logo">
                          <svg width="180" height="35" viewBox="0 0 180 35" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M15.9 0C7.16 0 0 7.02 0 15.6C0 24.18 15.9 35 15.9 35S31.8 24.18 31.8 15.6C31.8 7.02 24.64 0 15.9 0ZM15.9 21.28A5.68 5.68 0 1 1 15.9 10.12 5.68 5.68 0 0 1 15.9 21.28Z" fill="#3B428A"/>
                            <rect x="12" y="12" width="7" height="7" fill="#FE6A53"/>
                            <text x="35" y="23" font-family="Nunito, sans-serif" font-size="22" font-weight="900" fill="#3B428A">TrackIt</text>
                            <text x="110" y="23" font-family="Nunito, sans-serif" font-size="18" fill="#FE6A53">Couriers</text>
                          </svg>
                        </a>
                    </div>

                    <div class="pt-4 pb-2">
                      <h5 class="card-title text-center pb-0 fs-4">Create an Account</h5>
                      <?php if ($is_first_admin_registration) { ?>
                          <p class="text-center small text-danger">
                              ** Important: This will be your **Administrator** account. **
                              <br>
                              Please use a strong password.
                          </p>
                      <?php } else { ?>
                          <p class="text-center small">Enter your personal details to create a customer account.</p>
                      <?php } ?>
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
                          <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-person"></i></span>
                          <input type="email" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" id="yourUsername" value="<?php echo htmlspecialchars($username); ?>" required>
                          <div class="invalid-feedback"><?php echo $username_err; ?></div>
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="yourPassword" class="form-label">Password</label>
                        <div class="input-group has-validation">
                          <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-lock"></i></span>
                          <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" id="yourPassword" required>
                          <div class="invalid-feedback">Please enter your password!</div>
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="yourConfirmPassword" class="form-label">Confirm Password</label>
                        <div class="input-group has-validation">
                          <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-lock"></i></span>
                          <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" id="yourConfirmPassword" required>
                          <div class="invalid-feedback">Please confirm password!</div>
                        </div>
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

                <div class="login-image-col d-none d-md-flex">
                    <img src="assets/img/expdel.png" alt="Delivery Illustration">
                </div>
              </div>


            </div>
          </div>
        </div>

      </section>

    </div>
  </main>

  <script src="assets/vendor/apexcharts/apexcharts.min.js"></script>
  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/chart.js/chart.umd.js"></script>
  <script src="assets/vendor/echarts/echarts.min.js"></script>
  <script src="assets/vendor/quill/quill.min.js"></script>
  <script src="assets/vendor/simple-datatables/simple-datatables.js"></script>
  <script src="assets/vendor/tinymce/tinymce.min.js"></script>
  <script src="assets/vendor/php-email-form/validate.js"></script>

  <script src="assets/js/main.js"></script>

</body>

</html>