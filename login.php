<?php
require_once 'inc.connections.php';

$username = $password = '';
$login_err = '';

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
  if (isset($_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === ROLE_ADMIN) {
      header('Location: admin_dashboard.php');
    } elseif ($role === ROLE_AGENT) {
      header('Location: agent_dashboard.php');
    } else {
      header('Location: track_shipment.php');
    }
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if (empty(trim($_POST['username']))) {
    $login_err = "Please enter username.";
  } else {
    $username = trim($_POST['username']);
  }

  if (empty(trim($_POST['password']))) {
    $login_err = !empty($login_err) ? $login_err . " And please enter your password." : "Please enter your password.";
  } else {
    $password = trim($_POST['password']);
  }

  if (empty($login_err)) {
    $sql = "SELECT user_id, username, password_hash, role, location_id FROM users WHERE username = ?";

    if ($stmt = mysqli_prepare($conn, $sql)) {
      mysqli_stmt_bind_param($stmt, "s", $param_username);
      $param_username = $username;
      if (mysqli_stmt_execute($stmt)) {
        mysqli_stmt_store_result($stmt);
        if (mysqli_stmt_num_rows($stmt) == 1) {
          mysqli_stmt_bind_result($stmt, $user_id, $username, $hashed_password, $role, $location_id);
          if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hashed_password)) {
              session_regenerate_id(true);

              $_SESSION['loggedin'] = true;
              $_SESSION['user_id'] = $user_id;
              $_SESSION['username'] = $username;
              $_SESSION['role'] = $role;
              $_SESSION['location_id'] = $location_id;

              $sql_audit = "INSERT INTO user_login_audit (user_id, login_time, ip_address) VALUES (?, NOW(), ?)";
              if ($stmt_audit = mysqli_prepare($conn, $sql_audit)) {
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
                mysqli_stmt_bind_param($stmt_audit, "is", $user_id, $ip_address);
                mysqli_stmt_execute($stmt_audit);
                mysqli_stmt_close($stmt_audit);
              } else {
                error_log("Failed to prepare user_login_audit statement: " . mysqli_error($conn));
              }

              if ($role === ROLE_ADMIN) {
                header('Location: admin_dashboard.php');
              } elseif ($role === ROLE_AGENT) {
                header('Location: agent_dashboard.php');
              } else {
                header('Location: track_shipment.php');
              }
              exit;
            } else {
              $login_err = "Invalid username or password.";
            }
          }
        } else {
          $login_err = "Invalid username or password.";
        }
      } else {
        error_log("Login query execution failed: " . mysqli_error($conn));
        $login_err = "An unexpected error occurred. Please try again later.";
      }
      mysqli_stmt_close($stmt);
    } else {
      error_log("Login query preparation failed: " . mysqli_error($conn));
      $login_err = "An unexpected error occurred. Please try again later.";
    }
  }
  if (isset($conn) && $conn) {
    mysqli_close($conn);
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Login - TrackIt Courier</title>
  <meta content="Login page for Admin, Agent, and Customer roles in the TrackIt Courier." name="description">
  <meta content="courier, login, admin, agent, customer" name="keywords">

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
      background-image: linear-gradient(135deg, #819CDD 10%, #ffffff 50%, #5A90D7 100%);
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      font-family: 'Open Sans', sans-serif;
      color: #3B428A;
    }

    .section.register { /* Reusing register class but it's for login page */
      background: none;
      flex-grow: 1;
      display: flex;
      justify-content: center;
      align-items: center;
    }

    .login-container {
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
    .logo-container .logo-brand { /* Wrapper for image and text */
        display: inline-flex;
        align-items: center;
        text-decoration: none;
        gap: 8px; /* Space between image and text */
    }
    .logo-container .logo-brand img {
        height: 80px; /* Fixed height for the logo image */
        width: auto;
    }
    .logo-container .logo-brand .logo-text-trackit {
        font-family: "Nunito", sans-serif;
        font-size: 30px; /* Larger text for TrackIt */
        font-weight: 800;
        color: #3B428A; /* Main Blue */
    }
    .logo-container .logo-brand .logo-text-couriers {
        font-family: "Nunito", sans-serif;
        font-size: 30px; /* Slightly smaller for Couriers */
        font-weight: 800;
        color: #FE6A53; /* Coral */
        /* margin-left: 2px; Further separation for Couriers */
    }
    .logo-container .logo-brand:hover .logo-text-trackit,
    .logo-container .logo-brand:hover .logo-text-couriers {
        color: #5A90D7; /* Main Blue on hover */
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
                        <a href="index.html" class="logo-brand">
                          <img src="assets/img/logo.png" alt="TrackIt Couriers Logo">
                          <span class="logo-text-trackit">TrackIt</span>
                          <span class="logo-text-couriers">Couriers</span>
                        </a>
                    </div>

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
                          <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-person"></i></span>
                          <input type="text" name="username" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="yourUsername" value="<?php echo htmlspecialchars($username); ?>" required>
                          <div class="invalid-feedback">Please enter your username.</div>
                        </div>
                      </div>

                      <div class="col-12">
                        <label for="yourPassword" class="form-label">Password</label>
                        <div class="input-group has-validation">
                          <span class="input-group-text" id="inputGroupPrepend"><i class="bi bi-lock"></i></span>
                          <input type="password" name="password" class="form-control <?php echo (!empty($login_err)) ? 'is-invalid' : ''; ?>" id="yourPassword" required>
                          <div class="invalid-feedback">Please enter your password!</div>
                        </div>
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
<style>

  .container {
    padding: 20px;
  }

  .row {
    margin: 0;
  }

  .col-lg-10 {
    padding: 20px;
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  }

  .form-label {
    color: #495057;
  }

  .form-control {
    border: 1px solid #ced4da;
  }

  .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
  }

  .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
  }

  .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
  }

  .small a {
    color: #007bff;
  }

  .small a:hover {
    text-decoration: underline;
  }
</style>