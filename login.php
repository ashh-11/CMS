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
    $sql = "SELECT user_id, username, password_hash, role, location_id FROM users WHERE username = '" . mysqli_real_escape_string($conn, $username) . "'";
    $result = mysqli_query($conn, $sql);

    if ($result && mysqli_num_rows($result) == 1) {
      $row = mysqli_fetch_assoc($result);
      $user_id = $row['user_id'];
      $stored_username = $row['username'];
      $hashed_password = $row['password_hash'];
      $role = $row['role'];
      $location_id = $row['location_id'];
      mysqli_free_result($result);

      if (password_verify($password, $hashed_password)) {
        session_regenerate_id(true);

        $_SESSION['loggedin'] = true;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $stored_username;
        $_SESSION['role'] = $role;
        $_SESSION['location_id'] = $location_id;

        $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
        $sql_audit = "INSERT INTO user_login_audit (user_id, login_time, ip_address) VALUES ('$user_id', NOW(), '$ip_address')";
        mysqli_query($conn, $sql_audit);

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
    } else {
      $login_err = "Invalid username or password.";
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
  <title>Login - Courier Management System</title>
  <meta content="Login page for Admin, Agent, and Customer roles in the Courier Management System." name="description">
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
      background: #f0f2f5;
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
              </div>

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
  </main>

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

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